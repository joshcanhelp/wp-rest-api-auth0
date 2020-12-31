<?php
/**
 * Loader for joshcanhelp/wp-rest-api-auth0 MU-Plugin.
 *
 * Copy this file to wp-content/mu-plugins/
 *
 * @package WpRestApiAuth0
 */

declare(strict_types=1);

if (
    ! class_exists( 'Auth0\\SDK\\Helpers\\Tokens\\SymmetricVerifier' )
    && is_file( __DIR__ . '/wp-rest-api-auth0/vendor/autoload.php' )
) {
    require_once __DIR__ . '/wp-rest-api-auth0/vendor/autoload.php';
}

require_once __DIR__ . '/wp-rest-api-auth0/wp-rest-api-auth0.php';
