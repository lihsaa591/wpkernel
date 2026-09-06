<?php
/**
 * Application configuration.
 *
 * Plain PHP array, no magic. This is the one file most consumers of the
 * boilerplate will edit: add a provider, a REST controller, or an admin
 * page here and nothing else needs to change.
 *
 * @package WPKernel
 */

declare( strict_types=1 );

return array(

	/*
	 * Service providers, booted in this order. register() runs for every
	 * provider (in order) before boot() runs for any of them — see
	 * Plugin::run().
	 */
	'providers' => array(
		\WPKernel\Providers\AppServiceProvider::class,
		\WPKernel\Providers\DatabaseServiceProvider::class,
		\WPKernel\Providers\RestApiServiceProvider::class,
		\WPKernel\Providers\AdminServiceProvider::class,
	),

	'rest'      => array(
		'controllers' => array(
			\WPKernel\Examples\ExampleItemsController::class,
		),
	),

	'admin'     => array(
		'pages' => array(
			array(
				'page_title'       => __( 'WPKernel Example', 'wpkernel' ),
				'menu_title'       => __( 'WPKernel', 'wpkernel' ),
				'capability'       => 'manage_options',
				'menu_slug'        => 'wpkernel-example',
				'mount_element_id' => 'wpkernel-example-root',
				'script_entry'     => 'admin',
				'icon'             => 'dashicons-carrot',
				'position'         => 30,
			),
		),
	),

);
