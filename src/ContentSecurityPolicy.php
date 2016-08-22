<?php

namespace Phalcon\Plugin\CSP;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

/**
 * Content Security Policy plugin.
 *
 * @since 1.0.0
 * @author Eugene Manuilov <eugene.manuilov@gmail.com>
 */
class ContentSecurityPolicy extends \Phalcon\Mvc\User\Plugin {

	/**
	 * Builds CSP header and adds it to the response after exiting dispatch loop.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param \Phalcon\Events\Event $event The event object.
	 * @param \Phalcon\Mvc\Dispatcher $dispatcher The dispatcher intsance.
	 */
	public function afterDispatchLoop( Event $event, Dispatcher $dispatcher ) {

	}

}