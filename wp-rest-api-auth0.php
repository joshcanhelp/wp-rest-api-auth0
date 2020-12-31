<?php
/**
 * MU-Plugin for looking for and validating Auth0 access tokens on WP REST API routes.
 *
 * @package WpRestApiAuth0
 */

declare(strict_types=1);

namespace JoshCanHelp\WordPress\RestApiAuth0;

use Auth0\SDK\Helpers\Tokens\SymmetricVerifier;
use Auth0\SDK\Helpers\Tokens\TokenVerifier;

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

	// Only checked, not saved or output anywhere.
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] ?? '' );

	// Validated below with TokenVerifier.
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$auth_header_raw = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
	$auth_header_raw = wp_unslash( $auth_header_raw );

	// Check if this is a WP REST API request.
	if ( 1 !== preg_match( '/^\/' . rest_get_url_prefix() . '(.*)/', $request_uri ) ) {
		return $user;
	}

	// Check for a token in the Authorization header.
	$auth_header_parts = explode( ' ', $auth_header_raw );
	if ( 'Bearer' !== $auth_header_parts[0] || empty( $auth_header_parts[1] ) ) {
		return $user;
	}
	$access_token = $auth_header_parts[1];

	// From this point on, we're going to treat the request as OAuth2 protected.
	// If we cannot validate the token for some reason, the request is processed without auth.

	// Verify the incoming access token.
	$token_verifier = new TokenVerifier(
		'https://' . AUTH0_DOMAIN . '/',
		AUTH0_API_AUDIENCE,
		new SymmetricVerifier( AUTH0_API_SIGNING_SECRET )
	);

	try {
		$decoded_token = $token_verifier->verify( $access_token );
	} catch ( \Exception $e ) {
		// An exception here means the token was invalid for some reason.
		// While debugging, it might be helpful to output the exception message.
		return null;
	}

	// We don't have a user to associate this call to.
	if ( ! $decoded_token['sub'] ) {
		return null;
	}

	// Look for a WordPress user with the incoming Auth0 ID.
	$wp_user = current(
		get_users(
			array(
				'meta_key'   => 'wp_auth0_id',
				'meta_value' => $decoded_token['sub'],
			)
		)
	);

	// Could not find a user with the incoming Auth0 ID.
	if ( ! $wp_user ) {
		return null;
	}

	// Pull the scopes out of the access token and adjust the user accordingly.
	$access_token_scopes = explode( ' ', $decoded_token['scope'] );
	foreach ( $wp_user->allcaps as $cap => $value ) {
		if ( ! in_array( $cap, $access_token_scopes, true ) ) {
			$wp_user->allcaps[ $cap ] = 0;
		}
	}

	// Set the current user as this modified user.
	global $current_user;
	$current_user = $wp_user;
	return $wp_user->ID;
}
