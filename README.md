# Protect WordPress REST API with Auth0

This repo contains a working MU plugin that will receive and validate access tokens obtained from Auth0. For information on how this works and the values used withing please see the post here:

**[Protect your WordPress REST API with OAuth2 using Auth0](https://www.joshcanhelp.com/protect-wordpress-rest-api-with-oauth2-auth0/)**

## Installation with Composer

Add your Auth0 credentials to `wp-config.php`.

```php
// Auth0 credentials
define( 'AUTH0_DOMAIN', 'Your Auth0 domain' );
define( 'AUTH0_API_AUDIENCE', 'API identifier for the WP REST API' );
define( 'AUTH0_API_SIGNING_SECRET', 'API signing secret from Auth0' );
```

Install this package.

```bash
composer require joshcanhelp/wp-rest-api-auth0
```

## Installation with git

This repo is more of an example rather than a deployable piece of software. To get this running on an existing WordPress site:

1. Clone this repo somewhere outside of the WordPress installation
1. Run `composer install` in the `wp-rest-api-auth0` directory
1. Move those `define()` statements from `env.example.php` to your `wp-config.php` and define them
1. Move `wp-rest-api-auth0.php` and the `wp-rest-api-auth0` directory into the `wp-content/mu-plugins` directory in the WordPress install (make one if it does exist)

## Testing with Docker

You can get this running to test using Docker [using this Gist](https://gist.github.com/joshcanhelp/0e35b657ca03142e3d79595c28bb3ed7).

TODO Move that gist into this repo.
