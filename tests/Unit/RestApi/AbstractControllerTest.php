<?php
/**
 * Verifies the "capability check is opt-out, not opt-in" safety
 * property described in AbstractController's docblock.
 *
 * @package WPSprout
 */

declare( strict_types=1 );

namespace WPSprout\Tests\Unit\RestApi;

use Brain\Monkey\Functions;
use WP_Error;
use WPSprout\RestApi\AbstractController;
use WPSprout\Tests\TestCase;

final class AbstractControllerTest extends TestCase {

	public function test_route_is_capability_gated_by_default(): void {
		$captured = array();
		Functions\when( 'register_rest_route' )->alias(
			function ( $namespace, $route, $args ) use ( &$captured ) {
				$captured = $args;
			}
		);

		$controller = new class() extends AbstractController {
			protected string $rest_base = 'items';
			public function register_routes(): void {
				$this->register_route(
					'',
					array( 'methods' => 'GET', 'callback' => '__return_null' )
				);
			}
		};
		$controller->register_routes();

		self::assertIsCallable( $captured['permission_callback'], 'A permission_callback must be present even when the caller never set one.' );

		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\when( 'rest_authorization_required_code' )->justReturn( 403 );

		$result = $captured['permission_callback']( $this->fake_request() );

		self::assertInstanceOf( WP_Error::class, $result, 'Without the capability, the default permission_callback must deny access.' );
	}

	public function test_route_can_opt_into_being_public(): void {
		$captured = array();
		Functions\when( 'register_rest_route' )->alias(
			function ( $namespace, $route, $args ) use ( &$captured ) {
				$captured = $args;
			}
		);

		$controller = new class() extends AbstractController {
			protected string $rest_base = 'items';
			public function register_routes(): void {
				$this->register_route(
					'',
					array( 'methods' => 'GET', 'callback' => '__return_null' ),
					is_public: true
				);
			}
		};
		$controller->register_routes();

		self::assertSame( '__return_true', $captured['permission_callback'], 'An explicitly public route must not be capability-gated.' );
	}

	private function fake_request(): \WP_REST_Request {
		return new \WP_REST_Request();
	}
}
