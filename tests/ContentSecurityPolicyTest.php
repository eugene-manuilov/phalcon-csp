<?php

use Phalcon\Plugin\CSP\ContentSecurityPolicy;

/**
 * Tests ContentSecurityPolicy class.
 *
 * @since 1.0.0
 * @author Eugene Manuilov <eugene.manuilov@gmail.com>
 */
class ContentSecurityPolicyTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Tests whether afterDispatchLoop method is called when dispatcher exits
	 * from dispatch loop.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function testAfterDispatchLoop() {
		$di = new \Phalcon\Di\FactoryDefault();

		$csp = $this->createMock( ContentSecurityPolicy::class );
		$csp->expects( $this->once() )->method( 'afterDispatchLoop' );
		$csp->setDI( $di );

		$eventsManager = new \Phalcon\Events\Manager();
		$eventsManager->attach( 'dispatch:afterDispatchLoop', $csp );

		$dispatcher = new \Phalcon\Mvc\Dispatcher();
		$dispatcher->setDI( $di );
		$dispatcher->setEventsManager( $eventsManager );
		$dispatcher->dispatch();
	}

}

/**
 * Blank controller class which is used to test dispatcher loop.
 *
 * @since 1.0.0
 * @author Eugene Manuilov <eugene.manuilov@gmail.com>
 */
class IndexController {

	/**
	 * Default index action.
	 *
	 * @since 1.0.0
	 * 
	 * @access public
	 */
	public function indexAction() {
	}

}