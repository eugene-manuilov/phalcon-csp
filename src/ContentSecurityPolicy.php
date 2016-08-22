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

	const DIRECTIVE_BASE_URI                  = 'base-uri';
	const DIRECTIVE_SCRIPT_SRC                = 'script-src';
	const DIRECTIVE_STYLE_SRC                 = 'style-src';
	const DIRECTIVE_CHILD_SRC                 = 'child-src';
	const DIRECTIVE_CONNECT_SRC               = 'connect-src';
	const DIRECTIVE_FONT_SRC                  = 'font-src';
	const DIRECTIVE_FORM_ACTION               = 'form-action';
	const DIRECTIVE_FRAME_ANCESTORS           = 'frame-ancestors';
	const DIRECTIVE_IMG_SRC                   = 'img-src';
	const DIRECTIVE_MEDIA_SRC                 = 'media-src';
	const DIRECTIVE_OBJECT_SRC                = 'object-src';
	const DIRECTIVE_PLUGIN_TYPES              = 'plugin-types';
	const DIRECTIVE_REPORT_URL                = 'report-uri';
	const DIRECTIVE_UPGRADE_INSECURE_REQUESTS = 'upgrade-insecure-requests';

	/**
	 * The array of policies.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_policies = array();

	/**
	 * Adds a new policy to specific directive.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $directive The dirictive name to add policy to.
	 * @param string $value The policy value.
	 */
	public function addPolicy( $directive, $value ) {
		if ( ! isset( $this->_policies[ $directive ] ) || ! is_array( $this->_policies[ $directive ] ) ) {
			$this->_policies[ $directive ] = array();
		}

		if ( ! in_array( $value, $this->_policies[ $directive ] ) ) {
			$this->_policies[ $directive ][] = $value;
		}
	}

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
		if ( $this->assets instanceof \Phalcon\Plugin\CSP\Assets\Manager ) {
			$types = array(
				'css' => self::DIRECTIVE_STYLE_SRC,
				'js'  => self::DIRECTIVE_SCRIPT_SRC,
			);

			foreach ( $types as $type => $directive ) {
				$origins = $this->assets->getOrigins( $type );
				if ( ! empty( $origins ) ) {
					foreach ( $origins as $origin ) {
						$this->addPolicy( $directive, $origin );
					}
				}
			}
		}
	}

}