<?php
/**
 * Example REST controller.
 *
 * Demonstrates AbstractController end to end against the table created
 * by 2026_01_01_000000_create_example_items_table.php: a public,
 * capability-free GET (deliberately opted in) and a capability-gated
 * POST (the default, no opt-in needed). Uses $wpdb directly — the
 * boilerplate deliberately ships no Model/Repository layer; build your
 * own domain layer on top of this pattern for a real plugin.
 *
 * Delete this file (and the accompanying migration) once you no longer
 * need the worked example.
 *
 * @package WPKernel
 */

declare( strict_types=1 );

namespace WPKernel\Examples;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WPKernel\RestApi\AbstractController;

/**
 * Worked example: a minimal CRUD REST controller over the
 * wpkernel_example_items table.
 */
final class ExampleItemsController extends AbstractController {

	/**
	 * REST namespace for this example controller.
	 *
	 * @var string
	 */
	protected string $namespace = 'wpkernel/v1';

	/**
	 * Route base — registers under wpkernel/v1/items.
	 *
	 * @var string
	 */
	protected string $rest_base = 'items';

	/**
	 * Register the list (GET) and create (POST) routes.
	 */
	public function register_routes(): void {
		$this->register_route(
			'',
			array(
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => array( $this, 'list_items' ),
			),
			is_public: true // Demo-only: a real list endpoint should almost always require a capability.
		);

		$this->register_route(
			'',
			array(
				'methods'  => \WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'create_item' ),
				'args'     => array(
					'title' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			),
			required_capability: 'manage_options'
		);
	}

	/**
	 * GET /items — list the 50 most recent items.
	 */
	public function list_items(): WP_REST_Response {
		global $wpdb;

		$rows = $wpdb->get_results(
			"SELECT id, title, created_at FROM {$wpdb->prefix}wpkernel_example_items ORDER BY id DESC LIMIT 50", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- static query, no user input.
			ARRAY_A
		);

		return $this->success( null === $rows ? array() : $rows );
	}

	/**
	 * POST /items — create a new item.
	 *
	 * @param WP_REST_Request $request The current request.
	 */
	public function create_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		global $wpdb;

		$title = (string) $request->get_param( 'title' );

		$inserted = $wpdb->insert(
			$wpdb->prefix . 'wpkernel_example_items',
			array(
				'title'      => $title,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s' )
		);

		if ( false === $inserted ) {
			return $this->error( 'wpkernel_insert_failed', __( 'Could not create the item.', 'wpkernel' ), 500 );
		}

		return $this->success(
			array(
				'id'    => $wpdb->insert_id,
				'title' => $title,
			),
			201
		);
	}
}
