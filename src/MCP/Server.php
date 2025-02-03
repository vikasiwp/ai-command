<?php

namespace WP_CLI\AiCommand\MCP;

use Exception;
use InvalidArgumentException;

class Server {

	private array $data = [];
	private array $tools = [];
	private array $resources = [];

	public function __construct() {
		// Sample data (replace with your actual data handling)
		$this->data['users']    = [
			[ 'id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com' ],
			[ 'id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com' ],
		];
		$this->data['products'] = [
			[ 'id' => 101, 'name' => 'Product A', 'price' => 20 ],
			[ 'id' => 102, 'name' => 'Product B', 'price' => 30 ],
		];
	}

	public function registerTool( $toolDefinition ): void {
		if ( ! is_array( $toolDefinition ) || ! isset( $toolDefinition['name'] ) || ! is_callable( $toolDefinition['callable'] ) ) {
			throw new InvalidArgumentException( "Invalid tool definition. Must be an array with 'name' and 'callable'." );
		}

		$name        = $toolDefinition['name'];
		$callable    = $toolDefinition['callable'];
		$description = $toolDefinition['description'] ?? null;
		$inputSchema = $toolDefinition['inputSchema'] ?? null;

		$this->tools[ $name ] = [
			'name'        => $name,
			'callable'    => $callable,
			'description' => $description,
			'inputSchema' => $inputSchema,
		];
	}

	public function registerResource( $resourceDefinition ) {
		// Validate the resource definition (similar to tool validation)
		if ( ! is_array( $resourceDefinition ) || ! isset( $resourceDefinition['name'] ) || ! isset( $resourceDefinition['uri'] ) ) {
			throw new InvalidArgumentException( "Invalid resource definition." );
		}

		$this->resources[ $resourceDefinition['name'] ] = $resourceDefinition;
	}

	public function getCapabilities(): array {
		$capabilities = [
			'version'        => '1.0', // MCP version (adjust as needed)
			'methods'        => [],
			'data_resources' => [],
		];

		foreach ( $this->tools as $tool ) { // Iterate through the tools array
			$capabilities['methods'][] = [ // Add each tool as an element in the array
				'name'        => $tool['name'],
				'description' => $tool['description'],
				'inputSchema' => $tool['inputSchema'],
			];
		}

		// Add data resources
		// Add resources to capabilities
		foreach ( $this->resources as $resource ) {
			$capabilities['data_resources'] = [
				'name' => $resource['name'],
				// You can add more details about the resource here if needed
			];
		}

		return $capabilities;
	}

	public function handleRequest( $requestData ): false|string {
		$request = json_decode( $requestData, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return $this->createErrorResponse( null, 'Invalid JSON', - 32700 ); // Parse error
		}

		if ( ! isset( $request['jsonrpc'] ) || $request['jsonrpc'] !== '2.0' ) {
			return $this->createErrorResponse( $request['id'] ?? null, 'Invalid JSON-RPC version', - 32600 ); // Invalid Request
		}

		if ( ! isset( $request['method'] ) ) {
			return $this->createErrorResponse( $request['id'] ?? null, 'Missing method', - 32600 ); // Invalid Request
		}

		$method = $request['method'];
		$params = $request['params'] ?? [];
		$id     = $request['id'] ?? null;

		if ( $method === 'get_capabilities' ) { // Handle capabilities request
			$capabilities = $this->getCapabilities();

			return $this->createSuccessResponse( $id, $capabilities );
		}

		try {
			// Check if it's a data access request (starts with "get_")
			if ( str_starts_with( $method, 'get_' ) ) {
				$resource = substr( $method, 4 ); // Extract the resource name (e.g., "users" from "get_users")

				if ( isset( $this->data[ $resource ] ) ) {
					$result = $this->handleGetRequest( '/' . $resource, $params ); // Re-use handleGetRequest
				} else if ( isset( $this->data["{$resource}s"] ) ) {
					$result = $this->handleGetRequest( '/' . "{$resource}s", $params ); // Re-use handleGetRequest
				} else {
					return $this->createErrorResponse( $id, 'Resource not found', - 32601 ); // Method not found
				}

			} else if ( $method === 'resources/list' ) {
				$result = $this->listResources();
			} elseif ( $method === 'resources/read' ) {
				$result = $this->readResource( $params['uri'] ?? null );
			} else {  // Treat as a tool call

				$tool = $this->tools[ $method ] ?? null;
				if ( ! $tool ) {
					return $this->createErrorResponse( $id, 'Method not found', - 32601 );
				}

				// Validate input parameters against the schema
				$inputSchema = $tool['inputSchema'] ?? null;
				if ( $inputSchema ) {
					$isValid = $this->validateInput( $params, $inputSchema );
					if ( ! $isValid['valid'] ) {
						return $this->createErrorResponse( $id, 'Invalid input parameters: ' . implode( ", ", $isValid['errors'] ), - 32602 ); // Invalid params
					}
				}


				$result = call_user_func( $tool['callable'], $params );  // Call the 'callable' property

				return $this->createSuccessResponse( $id, $result ); // Return success immediately

			}

			return $this->createSuccessResponse( $id, $result );

		} catch ( Exception $e ) {
			return $this->createErrorResponse( $id, $e->getMessage(), - 32000 ); // Application error
		}
	}

	private function listResources() {
		$result = [];
		foreach ( $this->resources as $resource ) {
			$result[] = [
				'uri'         => $resource['uri'],
				'name'        => $resource['name'],
				'description' => $resource['description'] ?? null,
				'mimeType'    => $resource['mimeType'] ?? null,
			];
		}

		return $result;
	}

	private function readResource( $uri ) {
		// Find the resource by URI
		$resource = null;
		foreach ( $this->resources as $r ) {
			if ( $r['uri'] === $uri ) {
				$resource = $r;
				break;
			}
		}

		if ( ! $resource ) {
			throw new Exception( "Resource not found." );
		}

		// Access the resource data (replace with your actual data access logic)
		$data = $this->getResourceData( $resource );

		// Determine if it's text or binary
		$isBinary = isset( $resource['mimeType'] ) && ! str_starts_with( $resource['mimeType'], 'text/' );

		return [
			'uri'                           => $resource['uri'],
			'mimeType'                      => $resource['mimeType'] ?? null,
			( $isBinary ? 'blob' : 'text' ) => $data,
		];
	}

	private function getResourceData( $resource ) {
		// Replace this with your actual logic to access the resource data
		// based on the resource definition.

		// Example: If the resource is a file, read the file contents.
		if ( isset( $resource['filePath'] ) ) {
			return file_get_contents( $resource['filePath'] );
		}

		// Example: If the resource is in the $data array, return the data.
		if ( isset( $resource['dataKey'] ) ) {
			return $this->data[ $resource['dataKey'] ];
		}

		//... other data access logic...

		throw new Exception( "Unable to access resource data." );
	}

	private function validateInput( $input, $schema ): array {
		// Basic input validation (you might want to use a dedicated JSON schema validator library)
		$errors = [];
		foreach ( $schema['properties'] ?? [] as $paramName => $paramSchema ) {
			if ( isset( $paramSchema['required'] ) && $paramSchema['required'] === true && ! isset( $input[ $paramName ] ) ) {
				$errors[] = $paramName . " is required";
			}
			// Add more validation rules as needed (e.g., type checking)
			if ( isset( $input[ $paramName ] ) && isset( $paramSchema['type'] ) ) {
				$inputType = gettype( $input[ $paramName ] );
				if ( $inputType !== $paramSchema['type'] ) {
					$errors[] = $paramName . " must be of type " . $paramSchema['type'] . " but " . $inputType . " was given.";
				}
			}
		}

		return [ 'valid' => empty( $errors ), 'errors' => $errors ];
	}

	private function handleGetRequest( $path, $params ) {
		$parts    = explode( '/', ltrim( $path, '/' ) );
		$resource = $parts[0];
		$id       = $params['id'] ?? null; // Simplified parameter handling

		if ( isset( $this->data[ $resource ] ) ) {
			$data = $this->data[ $resource ];

			if ( $id !== null ) {
				foreach ( $data as $item ) {
					if ( $item['id'] == $id ) {
						return $item;
					}
				}
				throw new Exception( 'Resource not found' );
			} else {
				return $data;
			}
		} else {
			throw new Exception( 'Resource not found' );
		}
	}

	private function createSuccessResponse( $id, $result ): false|string {
		return json_encode( [
			'jsonrpc' => '2.0',
			'result'  => $result,
			'id'      => $id,
		] );
	}

	private function createErrorResponse( $id, $message, $code ): false|string {
		return json_encode( [
			'jsonrpc' => '2.0',
			'error'   => [
				'code'    => $code,
				'message' => $message,
			],
			'id'      => $id,
		] );
	}

	public function processRequest( $requestData ): false|string {
		return $this->handleRequest( $requestData );
	}
}

