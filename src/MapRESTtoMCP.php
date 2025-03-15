<?php

namespace WP_CLI\AiCommand;

use WP_CLI\AiCommand\MCP\Server;
use WP_CLI\AiCommand\RESTControllerList\Whitelist;
use WP_REST_Request;

class MapRESTtoMCP {

    public function __construct(
        private WhiteList $whitelist,
    ) {}

	public function args_to_schema( $args = [] ) {
		$schema = [];

		if ( empty( $args ) ) {
			return [];
		}

		foreach ( $args as $title => $arg ) {
			$description = $arg['description'] ?? $title;
			$type 		 = $this->sanitize_type( $arg['type'] ?? 'string' );

			$schema[ $title ] = [
				'type' => $type, // TODO can be array.
				'description' => $description,
			];
		}

		return [
			'type' => 'object',
			'properties' => $schema
		];
	}

	protected function sanitize_type( $type) {
		// Validated types:
		if ( $type === 'string' || $type === 'integer' || $type === 'boolean' ) {
			return $type;
		}

		if ( $type === 'array' || $type === 'object' ) {
			return 'string'; // TODO, better solution.
		}
		if (empty( $type ) || $type === 'null' ) {
			return 'string';
		}

		if ( !\is_array( $type ) ) {
			throw new \Exception( 'Invalid type: ' . $type );
			return 'string';
		}

		// Find valid values in array.
		if ( \in_array( 'string', $type ) ) {
			return 'string';
		}
		if ( \in_array( 'integer', $type ) ) {
			return 'integer';
		}
		// TODO, better types handling.
		return 'string';

	}

	public function get_endpoint_description( $route ) {
		return str_replace( '/wp/v2/', '', $route );
	}

	public function map_rest_to_mcp( Server $mcp_server ) {
		$allowed_list = $this->whitelist->get();

		$server = rest_get_server();
		$routes = $server->get_routes();

        foreach ( $routes as $route => $endpoints ) {
			foreach ( $endpoints as $endpoint ) {
				// Only allowed routes
				if ( ! isset( $allowed_list[ $route ] ) ) {
					continue;
				}

				// Generate a tool name off route e.g. /wp/v2/posts
				$tool_name = sanitize_key($route);

				foreach( $endpoint['methods'] as $method_name => $enabled ) {
					// Only allowed methods
					if ( ! isset( $allowed_list[ $route ][ $method_name ] ) ) {
						continue;
					}

					$tool = [
						'name' => $tool_name . '_' . strtolower( $method_name ),
						'description' => $allowed_list[ $route ][ $method_name ],
						'inputSchema' => $this->args_to_schema( $endpoint['args'] ),
						'callable' => function ( $inputs ) use ( $route, $method_name, $server ){
							$request = new WP_REST_Request( $method_name, $route  );
							$request->set_body_params( $inputs );

							$response = $server->dispatch( $request );

							// TODO $embed parameter is forced to true now
							return $server->response_to_data( $response, true );
						},
						'required' => ['id'], // TODO
					];
				}

			}
		}
	}
}
