# Rack'em CGI

Use these to serve CGI compliant applications via Rack'em.

## Example

```php
return \Rackem::run(new \Rackem\Php(null, __DIR__));
```

This will serve the current directory as a PHP CGI application. The first parameter can be used to pass in a Rack'em application so you can also use this as a middleware.

## Web Applications

This is a great way to run some PHP applications locally (for development), so I included a `Rewritable` app to do just that.

```php
return \Rackem::run(new \Rackem\Rewritable(null, __DIR__.'/wordpress'));
```

_This will serve a Wordpress site that is located in `./wordpress/`_

The Rewritable class can also serve other web applications like Drupal etc.
