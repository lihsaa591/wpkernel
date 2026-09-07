<?php
/**
 * Application configuration.
 *
 * Plain PHP array, no magic. This is the one file most consumers of the
 * boilerplate will edit: add a provider, a REST controller, or an admin
 * page here and nothing else needs to change.
 *
 * @package WPSprout
 */

declare( strict_types=1 );

return array(

	/*
	 * Service providers, booted in this order. register() runs for every
	 * provider (in order) before boot() runs for any of them — see
	 * Plugin::run().
	 */
	'providers' => array(
		\WPSprout\Providers\AppServiceProvider::class,
		\WPSprout\Providers\DatabaseServiceProvider::class,
		\WPSprout\Providers\RestApiServiceProvider::class,
		\WPSprout\Providers\AdminServiceProvider::class,
	),

	'rest'      => array(
		'controllers' => array(
			\WPSprout\Examples\ExampleItemsController::class,
		),
	),

	'admin'     => array(
		'pages' => array(
			array(
				'page_title'       => __( 'WPSprout Example', 'wpsprout' ),
				'menu_title'       => __( 'WPSprout', 'wpsprout' ),
				'capability'       => 'manage_options',
				'menu_slug'        => 'wpsprout-example',
				'mount_element_id' => 'wpsprout-example-root',
				'script_entry'     => 'admin',
				'icon'             => 'dashicons-carrot',
				'position'         => 30,
			),
		),
	),

);
