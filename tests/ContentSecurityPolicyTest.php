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

	/**
	 * Tests ability to add a new policy to the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function testAddPolicy() {
		$csp = new ContentSecurityPolicy();

		$added = $csp->addPolicy( ContentSecurityPolicy::DIRECTIVE_CHILD_SRC, 'test' );
		$this->assertTrue( $added, 'Check whether we can add a policy for valid directive' );

		$added = $csp->addPolicy( 'not-existing-directive', 'test' );
		$this->assertFalse( $added, 'Check whether we cannot add a policy for invalid directive' );
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