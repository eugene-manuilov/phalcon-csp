Content Security Policy for Phalcon Framework
=================

This plugin allows you to add CSP policies to your Phalcon based website. Content Security Policy (CSP) is a security standard to prevent cross-site scripting (XSS), clickjacking and other code injection attacks. Take a look at [An Introduction to Content Security Policy](http://www.html5rocks.com/en/tutorials/security/content-security-policy/) article for more details.

Usage
-----------

To use CSP plugin in your site you just need to add it to the dependency injection container and register it as event listener for dispatcher events.

```php
<?php

use Phalcon\Plugin\CSP\ContentSecurityPolicy;

// register CSP service
$di->set( 'csp', function() {
    $csp = new ContentSecurityPolicy();
	return $csp;
}, true );

// add CSP to dispatcher's event listener
$di->set( 'dispatcher', function() use ( $di ) {
    $csp = $di->getShared( 'csp' );

    $eventsManager = new \Phalcon\Events\Manager();
    $eventsManager->attach( 'dispatch:afterDispatchLoop', $csp );

    $dispatcher = new \Phalcon\Mvc\Dispatcher();
    $dispatcher->setEventsManager( $eventsManager );

    return $dispatcher;
}, true );
```

Now all your policies will be compiled into `Content-Security-Policy` header and added to the response instance. To add a new policy you need to call `addPolicy()` function which accepts policy name and a value:

```php
<?php

use Phalcon\Plugin\CSP\ContentSecurityPolicy as CSP;

class IndexController extends \Phalcon\Mvc\Controller {

    public function indexAction() {
        // whitelist Google fonts origin
        $this->csp->addPolicy( CSP::DIRECTIVE_FONT_SRC, 'https://fonts.gstatic.com' );
    }

}
```

If you want to specify report URL which will be used to report all violations, then you need to call `setReportURI()` function.

```php
$di->set( 'csp', function() {
    $csp = new ContentSecurityPolicy();
    $csp->setReportURI( '/path/to/report/endpoint' );

	return $csp;
}, true );
```

Using Content Security Policy header you can also tell browsers that you want to upgrade all insecure requests to use its secure versions. To do it you need to use `setUpgradeInsecureRequests()` function.

```php
$di->set( 'csp', function() {
    $csp = new ContentSecurityPolicy();
    $csp->setUpgradeInsecureRequests();

	return $csp;
}, true );
```
Assets Manager
-----------

This plugin also provides assets manager class which extends standard assets manager class and automatically gathers origins of scripts and styles added with it. It also generates nonces for inline scripts and styles.

```php
<?php

$di->set( 'assets', function() {
    $manager = new \Phalcon\Plugin\CSP\Assets\Manager();

	return $manager;
}, true );
```

Later on you can use it as standard assets manager class to add you scripts and styles files as well as inline blocks.