<?php

namespace WP_CLI\AiCommand;

use WP_CLI\AiCommand\RESTControllerList\Whitelist;

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
				'type' => $type,
				'description' => $description,
			];
		}
		return $schema;
	}

	public function get_endpoint_description( $route ) {
		return str_replace( '/wp/v2/', '', $route );
	}

	public function map_rest_to_mcp( $server ) {
		$whitelist = $this->whitelist->get();

		$routes = rest_get_server()->get_routes();
		foreach ( $routes as $route => $endpoints ) {
			foreach ( $endpoints as $endpoint ) {
				if ( ! isset( $whitelist[ $route ] ) ) {
					continue; // Route not whitelisted.
				}

				// Generate a tool name based on route and method (e.g., "GET_/wp/v2/posts")
				$tool_name = strtolower( str_replace(['/', '(', ')', '?', '[', ']', '+', '\\', '<', '>', ':', '-'], '_', $route ) );
				$tool_name = preg_replace('/_+/', '_', trim($tool_name, '_'));

				foreach( $endpoint['methods'] as $method_name => $enabled ) {
					if ( ! isset( $whitelist[ $route ][ $method_name ] ) ) {
						continue; // Method not whitelisted.
					}

					$server->register_tool( [
						'name' => $tool_name,
						'description' => $whitelist[ $route ][ $method_name ],
						'inputSchema' => $this->args_to_schema( $endpoint['args'] ),

						'callable' => function ( $inputs ) use ( $route, $method_name ){
							$request = new \WP_REST_Request( $method_name, $route );
							$request->set_body_params( $inputs );

                            $response = rest_get_server()->dispatch( $request );

                            // TODO $embed parameter is forced to true now
                            return rest_get_server()->response_to_data( $response, true );
						},
					] );
				}

			}
		}
	}
}
