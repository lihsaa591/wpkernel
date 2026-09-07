<?php
/**
 * Base test case wiring up Brain Monkey.
 *
 * @package WPSprout
 */

declare( strict_types=1 );

namespace WPSprout\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		$this->stub_common_wordpress_functions();
	}

	/**
	 * i18n and escaping functions touched by nearly every WordPress-aware
	 * class under test. Stubbed centrally as no-op pass-throughs so
	 * individual tests don't each have to remember to mock them — a test
	 * that cares about a specific one of these can still override it
	 * locally with its own Functions\when() call.
	 */
	private function stub_common_wordpress_functions(): void {
		foreach ( array( '__', 'esc_html__', 'esc_attr__', '_x', '_n' ) as $fn ) {
			Functions\when( $fn )->returnArg( 1 );
		}
		foreach ( array( 'esc_html', 'esc_attr', 'esc_url', 'esc_url_raw', 'sanitize_text_field', 'wp_kses_post' ) as $fn ) {
			Functions\when( $fn )->returnArg( 1 );
		}
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}
