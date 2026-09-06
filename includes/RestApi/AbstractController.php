<?php
/**
 * REST API controller base.
 *
 * @package WPKernel
 */

declare( strict_types=1 );

namespace WPKernel\RestApi;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Every route registered through register_route() below is capability-gated
 * by default (require_capability()). A route is public ONLY if the
 * subclass explicitly overrides permission_callback() for that route to
 * return true — there is no "forgot to add a check" failure mode, because
 * the default fails closed.
 */
abstract class AbstractController {

	/**
	 * REST namespace, e.g. "your-plugin/v1". Set by the subclass.
	 *
	 * @var string
	 */
	protected string $namespace = 'wpkernel/v1';

	/**
	 * Route base, e.g. "items" -> registered at {namespace}/items.
	 *
	 * @var string
	 */
	protected string $rest_base = '';

	/**
	 * Register this controller's routes. Called on rest_api_init.
	 */
	abstract public function register_routes(): void;

	/**
	 * Register a single method/callback endpoint with a capability-gated
	 * default permission_callback.
	 *
	 * One call = one HTTP method on one route. Registering GET and POST
	 * on the same route is two separate calls (WordPress core merges
	 * multiple register_rest_route() calls to the same route) — this
	 * keeps each endpoint's args and permission_callback unambiguous,
	 * rather than bundling several endpoint definitions into one call
	 * where a single "did they set permission_callback?" check can't
	 * tell which endpoint it belongs to.
	 *
	 * @param string $route               Route, relative to {namespace}/{rest_base}. Pass '' for the base itself.
	 * @param array  $endpoint            Single endpoint definition: methods/callback/args, as passed to register_rest_route().
	 * @param string $required_capability WordPress capability required to access this route. Ignored if $is_public is true.
	 * @param bool   $is_public           Explicitly mark this route as intentionally public. Must be opted into per-route.
	 */
	protected function register_route( string $route, array $endpoint, string $required_capability = 'manage_options', bool $is_public = false ): void {
		$full_route = '/' . trim( $this->rest_base . '/' . ltrim( $route, '/' ), '/' );

		if ( ! isset( $endpoint['permission_callback'] ) ) {
			$endpoint['permission_callback'] = $is_public
				? '__return_true'
				: fn( WP_REST_Request $request ) => $this->require_capability( $required_capability, $request );
		}

		register_rest_route( $this->namespace, $full_route, $endpoint );
	}

	/**
	 * Default permission callback: deny unless the current user has the
	 * given capability. Deliberately verbose in its WP_Error so a 403
	 * during development says exactly why.
	 *
	 * @param string          $capability WordPress capability required for this request.
	 * @param WP_REST_Request $request    The current request. Unused in the base
	 *                                    implementation — accepted so the method matches
	 *                                    WordPress's permission_callback signature, and so a
	 *                                    subclass overriding this can inspect it (e.g. to allow
	 *                                    a user to access only their own resource).
	 */
	protected function require_capability( string $capability, WP_REST_Request $request ): bool|WP_Error { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- see docblock.
		if ( current_user_can( $capability ) ) {
			return true;
		}

		return new WP_Error(
			'wpkernel_rest_forbidden',
			sprintf(
				/* translators: %s: required WordPress capability */
				__( 'You do not have the "%s" capability required for this request.', 'wpkernel' ),
				$capability
			),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * Standard success envelope.
	 *
	 * @param mixed $data   Response payload.
	 * @param int   $status HTTP status code.
	 */
	protected function success( mixed $data, int $status = 200 ): WP_REST_Response {
		return new WP_REST_Response( $data, $status );
	}

	/**
	 * Standard error envelope. Prefer returning a WP_Error directly from
	 * route callbacks — WordPress converts it correctly — but this is
	 * available for handlers that need to compose an error inline.
	 *
	 * @param string $code    Machine-readable error code.
	 * @param string $message Human-readable error message.
	 * @param int    $status  HTTP status code.
	 */
	protected function error( string $code, string $message, int $status = 400 ): WP_Error {
		return new WP_Error( $code, $message, array( 'status' => $status ) );
	}
}
