<?php

namespace WP_CLI\AiCommand;

use WP_CLI;

if ( ! class_exists( '\WP_CLI' ) ) {
	return;
}

$ai_command_autoloader = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $ai_command_autoloader ) ) {
	require_once $ai_command_autoloader;
}

WP_CLI::add_command( 'ai', function ( $args, $assoc_args ) {
	$tools = new ToolCollection();

	// TODO Register your tool here and add it to the collection

	$ai_command = new AiCommand( $tools );
	$ai_command( $args, $assoc_args );
} );
