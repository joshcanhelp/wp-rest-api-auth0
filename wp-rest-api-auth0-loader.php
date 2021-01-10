<?php
/**
 * Plugin Name: WP REST API Auth0
 * Plugin URL: https://github.com/joshcanhelp/wp-rest-api-auth0
 * Description: Protect your WordPress REST API with Auth0
 * Version: 1.2.0
 * Author: Josh Cunningham <josh@joshcanhelp.com>
 * Author URI: https://www.joshcanhelp.com
 *
 * @package WpRestApiAuth0
 */

declare(strict_types=1);

if (
    ! class_exists( 'Auth0\\SDK\\Helpers\\Tokens\\SymmetricVerifier' )
    && is_file( __DIR__ . '/vendor/autoload.php' )
) {
    require_once __DIR__ . '/vendor/autoload.php';
}

require_once __DIR__ . '/src/wp-rest-api-auth0.php';
