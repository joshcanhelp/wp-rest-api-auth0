<?php
/**
 * MU-Plugin for looking for and validating Auth0 access tokens on WP REST API routes.
 *
 * @package WpRestApiAuth0
 */

declare(strict_types=1);

namespace JoshCanHelp\WordPress\RestApiAuth0;

add_filter( 'determine_current_user', __NAMESPACE__ . '\\determine_current_user', 10, 1 );

/**
 * Look for and validate Auth0 access tokens on WP REST API routes.
 * Hooked to determine_current_user.
 *
 * @param int $user WordPress user ID so far.
 *
 * @return int|null
 */
function determine_current_user( $user ) {
	global $wpdb;

	$debug_mode = defined( 'AUTH0_API_DEBUG' ) && \AUTH0_API_DEBUG;

	// Only checked, not saved or output anywhere.
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] ?? '' );

	// Check if this is a WP REST API request.
	if ( 1 !== preg_match( '/^\/' . rest_get_url_prefix() . '(.*)/', $request_uri ) ) {
		return $user;
	}

	// Unslashed and validated as JWT below.
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	$auth_header   = '';
	$check_headers = [ 'Authorization', 'authorization', 'HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION' ];
	foreach ( $check_headers as $header ) {
		if ( isset( $_SERVER[ $header ] ) ) {
			$auth_header = wp_unslash( $_SERVER[ $header ] );
			break;
		}
	}
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotValidated

	$auth_header_parts = explode( ' ', $auth_header_raw ?? '' );
	if ( 'bearer' !== strtolower( $auth_header_parts[0] ) || empty( $auth_header_parts[1] ) ) {
		if ( $debug_mode ) {
			error_log( 'WP REST API Auth0: No access token found in the request' );
		}
		return $user;
	}
	$access_token = $auth_header_parts[1];

	//
	// From this point on, we're going to treat the request as OAuth2 protected.
	// If we cannot validate the token for some reason, the request is processed without auth.
	//

	// Verify the incoming JWT access token.
	// Auth0-generated access tokens for users will be ID token shaped.
	$token_verifier = new \WP_Auth0_IdTokenVerifier(
		'https://' . \WP_Auth0_Options::Instance()->get( 'domain' ) . '/',
		\AUTH0_API_AUDIENCE,
		new \WP_Auth0_SymmetricVerifier( \AUTH0_API_SIGNING_SECRET )
	);

	try {
		$decoded_token = $token_verifier->verify( $access_token );
	} catch ( \Exception $e ) {
		// An exception here means the token was invalid for some reason and cannot be accepted.
		if ( $debug_mode ) {
			error_log( 'WP REST API Auth0: Access token could not be verified - ' . $e->getMessage() );
		}
		return null;
	}

	// We don't have a user to associate this call to.
	if ( ! $decoded_token['sub'] ) {
		if ( $debug_mode ) {
			error_log( 'WP REST API Auth0: No sub claim found in the access token' );
		}
		return null;
	}

	if ( $debug_mode ) {
		error_log( 'WP REST API Auth0: Looking for Auth0 user ID ' . $decoded_token['sub'] . '...' );
	}

	// Look for a WordPress user with the incoming Auth0 ID.
	if ( 'client-credentials' === $decoded_token['gty'] ) {
		$m2m_client_id = str_replace( '@clients', '', $decoded_token['sub'] );
		$wp_user       = get_user_by( 'login', $m2m_client_id );
	} else {
		$wp_user = current(
			get_users(
				[
					'meta_key'   => $wpdb->prefix . 'auth0_id',
					'meta_value' => $decoded_token['sub'],
				]
			)
		);
	}

	// Could not find a user with the incoming Auth0 ID.
	if ( ! $wp_user ) {
		if ( $debug_mode ) {
			error_log( 'WP REST API Auth0: Could not find a user matching this the sub claim' );
		}
		return null;
	}

	// Pull the scopes out of the access token and adjust the user accordingly.
	if ( $decoded_token['scope'] ) {
		$access_token_scopes = explode( ' ', $decoded_token['scope'] );

		if ( $debug_mode ) {
			error_log( 'WP REST API Auth0: Scopes requested - ' . $decoded_token['scope'] );
		}

		foreach ( $wp_user->allcaps as $cap => $value ) {
			if ( ! in_array( $cap, $access_token_scopes, true ) ) {
				$wp_user->allcaps[ $cap ] = 0;
			}
		}

		// This is not ideal but there isn't another way to adjust current user caps.
		global $current_user;
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_user = $wp_user;
	}

	if ( $debug_mode ) {
		error_log( 'WP REST API Auth0: Setting user as WP UID ' . $wp_user->ID );
	}

	return $wp_user->ID;
}
