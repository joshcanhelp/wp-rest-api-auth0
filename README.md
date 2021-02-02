# Protect your WordPress REST API with Auth0

[![Latest Stable Version](https://poser.pugx.org/joshcanhelp/wp-rest-api-auth0/v/stable)](https://packagist.org/packages/joshcanhelp/wp-rest-api-auth0)
[![License](https://poser.pugx.org/joshcanhelp/wp-rest-api-auth0/license)](https://packagist.org/packages/joshcanhelp/wp-rest-api-auth0)
[![Total Downloads](https://poser.pugx.org/joshcanhelp/wp-rest-api-auth0/downloads)](https://packagist.org/packages/joshcanhelp/wp-rest-api-auth0)

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

Require the autoloader at some point when `add_action` is available, like in `wp-content/mu-plugins`.

### Install manually

To install this manually without Composer, just download the [latest release ZIP](https://github.com/joshcanhelp/wp-rest-api-auth0/releases) and upload through the admin interface. Please note that this plugin will not update automatically; updates will need to be made by deleting and re-adding (make sure your site is in maintenance mode) or directly via an FTP client (not recommended).

## Testing with Docker

You can get this running to test using Docker [using this Gist](https://gist.github.com/joshcanhelp/0e35b657ca03142e3d79595c28bb3ed7).

### Troubleshooting

If API requsts aren't working, Apache might not be passing authorization headers to PHP. Try adding this line (or similar methods) to `.htaccess`:

```
SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0
```

Also, make sure your WP API endpoint doesn't follow this pattern, where `/index.php/` is required before `/wp-json/`:

```
Example:
https://<your.site>/index.php/wp-json/
```
See [this solution](http://dejanjanosevic.info/remove-index-php-permalink-in-wordpress/) to help resolve this index.php issue.
