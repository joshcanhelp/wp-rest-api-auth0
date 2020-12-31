# Protect your WordPress REST API with Auth0

This repo contains a working MU plugin that will receive and validate access tokens obtained from Auth0. For information on how this works and the values used withing please see the post here:

**[Protect your WordPress REST API with OAuth2 using Auth0](https://www.joshcanhelp.com/protect-wordpress-rest-api-with-oauth2-auth0/)**

## Installation 

Add your Auth0 credentials to `wp-config.php` or another location that will get loaded before plugins.

```php
// Auth0 credentials
define( 'AUTH0_DOMAIN', 'Your Auth0 domain' );
define( 'AUTH0_API_AUDIENCE', 'API identifier for the WP REST API' );
define( 'AUTH0_API_SIGNING_SECRET', 'API signing secret from Auth0' );
```

### Install with Composer

Install this package:

```bash
composer require joshcanhelp/wp-rest-api-auth0
```

### Install manually

To get this running on an existing WordPress site not managed with Composer:

1. Download or clone this repo outside of your WordPress installation
1. Make a directory called `wp-rest-api-auth0` in the root of the repo and move `wp-rest-api-auth0.php` and `composer.json` into that directory
1. [Download Composer locally](https://getcomposer.org/download/) into that direcory and run `php composer.phar install` there
1. Move `wp-rest-api-auth0-loader.php` and the `wp-rest-api-auth0` directory into the `wp-content/mu-plugins` directory of the WordPress install (make one if it does not exist)

## Testing with Docker

You can get this running to test using Docker [using this Gist](https://gist.github.com/joshcanhelp/0e35b657ca03142e3d79595c28bb3ed7).
