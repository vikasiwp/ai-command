<?php

namespace WP_CLI\AiCommand\MCP;

use Exception;

class Client {

	private $server; // Instance of MCPServer

	public function __construct( Server $server ) {
		$this->server = $server;
	}

	public function sendRequest( $method, $params = [] ) {
		$request = [
			'jsonrpc' => '2.0',
			'method'  => $method,
			'params'  => $params,
			'id'      => uniqid(), // Generate a unique ID for each request
		];

		$requestData  = json_encode( $request );
		$responseData = $this->server->processRequest( $requestData );
		$response     = json_decode( $responseData, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new Exception( 'Invalid JSON response: ' . json_last_error_msg() );
		}

		if ( isset( $response['error'] ) ) {
			throw new Exception( "JSON-RPC Error: " . $response['error']['message'], $response['error']['code'] );
		}

		return $response['result'];
	}

	public function __call( $name, $arguments ) { // Magic method for calling any method
		return $this->sendRequest( $name, $arguments[0] ?? [] );
	}

	public function list_resources() {
		return $this->sendRequest( 'resources/list' );
	}

	public function read_resource( $uri ) {
		return $this->sendRequest( 'resources/read', [ 'uri' => $uri ] );
	}

	public function callGemini( $contents ) {
		$capabilities = $this->get_capabilities();

		$tools = [];

		foreach ( $capabilities['methods'] ?? [] as $tool ) {
			$tools[] = [
				"name"        => $tool['name'],
				"description" => $tool['description'] ?? "", // Provide a description
				"parameters"  => $tool['inputSchema'] ?? [], // Provide the inputSchema
			];
		}

		\WP_CLI::log( 'Calling Gemini...' . json_encode( [

				'contents' => $contents,
				'tools'    => [
					'function_declarations' => $tools,
				],
			] ) );

		$GOOGLE_API_KEY = getenv( 'GEMINI_API_KEY' );

		$response = \WP_CLI\Utils\http_request(
			'POST',
//			"https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$GOOGLE_API_KEY",
			"https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=$GOOGLE_API_KEY",
			json_encode( [
					'contents' => $contents,
					'tools'    => [
						'function_declarations' => $tools,
					],
				]
			),
			[
				'Content-Type' => 'application/json'
			]
		);

		$data = json_decode( $response->body );

		\WP_CLI::log( 'Receiving response...' . json_encode( $data ) );

		$new_contents = $contents;

		foreach ( $data->candidates[0]->content->parts as $part ) {
			// Check for tool calls in Gemini response
			if ( isset( $part->functionCall ) ) {
				$name = $part->functionCall->name;
				$args = (array) $part->functionCall->args;

				$functionResult = $this->$name( $args );

				\WP_CLI::log( "Calling function $name... Result:" . print_r( $functionResult, true ) );

				$new_contents[] = [
					'role'  => 'model',
					'parts' => [
						$part
					]
				];
				$new_contents[] = [
					'role'  => 'user',
					'parts' => [
						[
							'functionResponse' => [
								'name'     => $name,
								'response' => [
									'name'    => $name,
									'content' => $functionResult,
								]
							]
						]
					]
				];
			}
		}

		if ( $new_contents !== $contents ) {
			return $this->callGemini( $new_contents );
		}

		foreach ( $data->candidates[0]->content->parts as $part ) {
			if ( isset( $part->text ) ) {
				return $part->text;
			}
		}

		return 'Unknown!';
	}
}
