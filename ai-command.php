<?php

namespace WP_CLI\AiCommand;

use WP_CLI\AiCommand\ToolRepository\CollectionToolRepository;
use WP_CLI;

if ( ! class_exists( '\WP_CLI' ) ) {
	return;
}

$ai_command_autoloader = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $ai_command_autoloader ) ) {
	require_once $ai_command_autoloader;
}

WP_CLI::add_command( 'ai', static function ( $args, $assoc_args ) {
	$server = new MCP\Server();
	$client = new MCP\Client($server);

	$tools = new ToolCollection();

	// TODO Register your tool here and add it to the collection

	$image_tools = new ImageTools($client, $server);

	foreach($image_tools->get_tools() as $tool){
		$tools->add($tool);
	}


	// WordPress REST calls
	$rest_tools = new MapRESTtoMCP();

	foreach ($rest_tools as $tool) {
		$tools->add($tool);
	}

	$ai_command = new AiCommand(
		new CollectionToolRepository( $tools ),
		$server,
		$client
	);
	$ai_command( $args, $assoc_args );
} );



if(!function_exists('\WP_CLI\AiCommand\custom_tome_log')) {
	function custom_tome_log( $message, $data = '' ) {

		$log = trailingslashit( dirname(__FILE__)) . 'log/';
		if ( ! is_dir( $log ) ) {
				mkdir( $log );
		}

		$file = $log . date( 'Y-m-d' ) . '.log';
		if ( ! is_file( $file ) ) {
				file_put_contents( $file, '' );
		}
		if ( ! empty( $data ) ) {
				$message = array( $message => $data );
		}
		$data_string = print_r( $message, true ) . "\n";
		file_put_contents( $file, $data_string, FILE_APPEND );
	}

}