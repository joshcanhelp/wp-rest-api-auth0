# Protect the WP REST API with Auth0

This repo contains a working MU plugin that will receive and validate access tokens obtained from Auth0. For information on how this works and the values used withing please see the post here:

**[Protect your WordPress REST API with OAuth2 using Auth0](https://www.joshcanhelp.com/protect-wordpress-rest-api-with-oauth2-auth0/)**

## Installation

This repo is more of an example rather than a deployable piece of software. To get this running on an existing WordPress site:

1. Clone this repo somewhere outside of the WordPress installation
1. Run `composer install` in the `wp-rest-api-auth0` directory
1. Move those `define()` statements from `env.example.php` to your `wp-config.php` and define
1. Move `wp-rest-api-auth0.php` and the `wp-rest-api-auth0` directory into the `wp-content/mu-plugins` directory in the WordPress install (make one if it does exist) 
