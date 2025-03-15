<?php

namespace WP_CLI\AiCommand;

use WP_CLI;
use WP_CLI_Command;

/**
 *
 * Resources: File-like data that can be read by clients (like API responses or file contents)
 * Tools: Functions that can be called by the LLM (with user approval)
 * Prompts: Pre-written templates that help users accomplish specific tasks
 *
 * MCP follows a client-server architecture where:
 *
 * Hosts are LLM applications (like Claude Desktop or IDEs) that initiate connections
 * Clients maintain 1:1 connections with servers, inside the host application
 * Servers provide context, tools, and prompts to clients
 */
class AiCommand extends WP_CLI_Command {

	public function args_to_schema( $args ) {
		$schema = [];
		foreach ( $args as $title => $arg ) {
			$schema[ $title ] = [
				'type' => $arg['type'],
				'description' => $arg['description'],
			];
		}
		return $schema;
	}

	public function get_endpoint_description( $endpoint ) {
		return str_replace( '/wp/v2/', '', $endpoint );
	}

	/**
	 * Greets the world.
	 *
	 * ## OPTIONS
	 *
	 *  <prompt>
	 *  : AI prompt.
	 *
	 * ## EXAMPLES
	 *
	 *     # Greet the world.
	 *     $ wp ai "What are the titles of my last three posts?"
	 *     Success: Hello World!
	 *
	 *     # Greet the world.
	 *     $ wp ai "create 10 test posts about swiss recipes and include generated featured images"
	 *     Success: Hello World!
	 *
	 * @when after_wp_load
	 *
	 * @param array $args Indexed array of positional arguments.
	 * @param array $assoc_args Associative array of associative arguments.
	 */
	public function __invoke( $args, $assoc_args ) {
		$server = new MCP\Server();

		require_once( 'MapRESTtoMCP.php' );
		$map_rest_to_mcp = new MapRESTtoMCP();
		
		global $wp_rest_server;

		if (empty($wp_rest_server)) {
			$wp_rest_server = rest_get_server();
		}

		$routes = $wp_rest_server->get_routes();
		$controllers = [];

		include_once( 'RESTControllerList.php' );
		// Assume $rest_api_routes is the structured array defined above
		foreach ($rest_api_routes as $controller => $routes) {
			foreach ($routes as $route => $methods) {
				foreach ($methods as $http_method => $description) {

					// Generate a tool name based on route and method (e.g., "GET_/wp/v2/posts")
					$tool_name = strtolower($http_method . '_' . str_replace(['/', '(', ')', '?', '[', ']', '+', '\\', '<', '>', ':', '-'], '_', $route));
					$tool_name = preg_replace('/_+/', '_', trim($tool_name, '_'));

					// Fetch the endpoint schema dynamically
					$request = new \WP_REST_Request('OPTIONS', $route);
					$rest_server  = rest_get_server();
					$response = $rest_server->dispatch($request);
					$schema = $response->get_data()['endpoints'][0]['args'] ?? [];

					// Build inputSchema from retrieved schema
					$inputSchema = [
						'type' => 'object',
						'properties' => [],
						'required' => [],
					];

					foreach ($schema as $arg_name => $arg_details) {
						$inputSchema['properties'][$arg_name] = [
							'type' => isset($arg_details['type']) ? $arg_details['type'] : 'string',
							'description' => isset($arg_details['description']) ? $arg_details['description'] : '',
						];

						if (!empty($arg_details['required'])) {
							$inputSchema['required'][] = $arg_name;
						}
					}

					// Closure to handle the callable action
					$callable = function ($params) use ($http_method, $route) {
						$request = new \WP_REST_Request($http_method, $route);

						if ($http_method === 'GET') {
							$request->set_query_params($params);
						} else {
							$request->set_body_params($params);
						}

						$rest_server = rest_get_server();
						$response = $rest_server->dispatch($request);
						return $response->get_data();
					};

					// Register the tool
					$server->register_tool([
						'name' => $tool_name,
						'description' => $description,
						'inputSchema' => $inputSchema,
						'callable' => $callable,
					]);
				}
			}
		}

		
		// foreach ($routes as $route => $handlers) {
		// 	if ( $route === '/wp/v2/posts' ) {
		// 		foreach ($handlers as $handler) { 
		// 			$methods   = isset($handler['methods']) ? $handler['methods'] : 'unknown';
		// 			$args      = isset($handler['args']) ? $handler['args'] : [];
		// 			$callback  = isset($handler['callback']) ? $handler['callback'] : 'unknown';

		// 			$controllers[ $route ] = [
		// 				'namespace' => $namespace,
		// 				'methods'   => $methods,
		// 				'args'      => $args,
		// 				'callback'  => $callback,
		// 			];
		// 		}
		// 	}
		// }

		// print_r( $controllers );
		// return;
		
		$server->register_tool(
			[
				'name' => 'create_post',
				'description' => 'Creates a post.',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'title' => [
							'type' => 'string',
							'description' => 'The title of the post.',
						],
						'content' => [
							'type' => 'string',
							'description' => 'The content of the post.',
						],
						'category' => [
							'type' => 'string',
							'description' => 'The category of the post.',
						],
					],
					'required' => [ 'title', 'content' ],
				],
				'callable' => function ( $params ) {
					$request = new \WP_REST_Request( 'POST', '/wp/v2/posts' );
					$request->set_body_params( [
						'title'      => $params['title'],
						'content'    => $params['content'],
						'categories' => [ $params['category'] ],
						'status'     => 'publish',
					] );
					$controller = new \WP_REST_Posts_Controller( 'post' );
					$response   = $controller->create_item( $request );
					$data       = $response->get_data();
					return $data;
				},
			]
		);

		$client = new MCP\Client( $server );
		$result = $client->call_ai_service_with_prompt( $args[0] );

		WP_CLI::success( $result );
		return;

		$server->register_tool(
			[
				'name'        => 'calculate_total',
				'description' => 'Calculates the total price.',
				'inputSchema' => [
					'type'       => 'object',
					'properties' => [
						'price'    => [
							'type'        => 'integer',
							'description' => 'The price of the item.',
						],
						'quantity' => [
							'type'        => 'integer',
							'description' => 'The quantity of items.',
						],
					],
					'required'   => [ 'price', 'quantity' ],
				],
				'callable'    => function ( $params ) {
					$price    = $params['price'] ?? 0;
					$quantity = $params['quantity'] ?? 1;

					return $price * $quantity;
				},
			]
		);

		$server->register_tool(
			[
				'name'        => 'greet',
				'description' => 'Greets the user.',
				'inputSchema' => [
					'type'       => 'object',
					'properties' => [
						'name' => [
							'type'        => 'string',
							'description' => 'The name of the user.',
						],
					],
					'required'   => [ 'name' ],
				],
				'callable'    => function ( $params ) {
					return 'Hello, ' . $params['name'] . '!';
				},
			]
		);

		// Register resources:
		$server->register_resource(
			[
				'name'        => 'users',
				'uri'         => 'data://users',
				'description' => 'List of users',
				'mimeType'    => 'application/json',
				'dataKey'     => 'users', // This tells getResourceData() to look in the $data array
			]
		);

		$server->register_resource(
			[
				'name'        => 'product_catalog',
				'uri'         => 'file://./products.json',
				'description' => 'Product catalog',
				'mimeType'    => 'application/json',
				'filePath'    => './products.json', // This tells getResourceData() to read from a file
			]
		);

		$client = new MCP\Client( $server );

		$server->register_tool(
			[
				'name'        => 'generate_image',
				'description' => 'Generates an image.',
				'inputSchema' => [
					'type'       => 'object',
					'properties' => [
						'prompt' => [
							'type'        => 'string',
							'description' => 'The prompt for generating the image.',
						],
					],
					'required'   => [ 'prompt' ],
				],
				'callable'    => function ( $params ) use ( $client ) {
					return $client->get_image_from_ai_service( $params['prompt'] );
				},
			]
		);

		$result = $client->call_ai_service_with_prompt( $args[0] );

		WP_CLI::success( $result );

	}
}
