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
			$type 		 = $arg['type'] ?? 'string';

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
					];

					$mcp_server->register_tool( $tool);
				}

			}
		}
	}
}
