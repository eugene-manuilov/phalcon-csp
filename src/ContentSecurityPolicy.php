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

	const HEADER_NAME                         = 'Content-Security-Policy';
	const DIRECTIVE_BASE_URI                  = 'base-uri';
	const DIRECTIVE_DEFAULT_SRC               = 'default-src';
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
	 * Array of supported directives.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access protected
	 * @var array
	 */
	protected static $_directives = array(
        self::DIRECTIVE_BASE_URI,
        self::DIRECTIVE_DEFAULT_SRC,
        self::DIRECTIVE_CHILD_SRC,
        self::DIRECTIVE_CONNECT_SRC,
        self::DIRECTIVE_FONT_SRC,
        self::DIRECTIVE_FORM_ACTION,
        self::DIRECTIVE_FRAME_ANCESTORS,
        self::DIRECTIVE_IMG_SRC,
        self::DIRECTIVE_MEDIA_SRC,
        self::DIRECTIVE_OBJECT_SRC,
        self::DIRECTIVE_PLUGIN_TYPES,
        self::DIRECTIVE_STYLE_SRC,
        self::DIRECTIVE_SCRIPT_SRC,
    );

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
	 * Adds a new policy to specific directive. Returns FALSE if a policy can't
	 * be added. If you want to set a report URI, then use setReportURI() method.
	 * If you need to activate insecure requests upgrade, then use
	 * setUpgradeInsecureRequests() method.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $directive The dirictive name to add policy to.
	 * @param string $value The policy value.
	 * @return boolean TRUE if policy has been added, otherwise FALSE.
	 */
	public function addPolicy( $directive, $value ) {
		if ( ! in_array( $directive, self::$_directives ) ) {
			return false;
		}

		if ( ! isset( $this->_policies[ $directive ] ) || ! is_array( $this->_policies[ $directive ] ) ) {
			$this->_policies[ $directive ] = array();
		}

		if ( ! in_array( $value, $this->_policies[ $directive ] ) ) {
			$this->_policies[ $directive ][] = $value;
		}

		return true;
	}

	/**
	 * Sets report-uri directive to log all violations of content security policy.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $uri The report URI.
	 */
	public function setReportURI( $uri ) {
		$this->_policies[ self::DIRECTIVE_REPORT_URL ] = array( $uri );
	}

	/**
	 * Sets upgrade-insecure-requests policy.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function setUpgradeInsecureRequests() {
		$this->_policies[ self::DIRECTIVE_UPGRADE_INSECURE_REQUESTS ] = true;
	}

	/**
	 * Compiles content security policies to send it in the header.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string Compiled policies string.
	 */
	public function compilePolicies() {
		$policies = array();
		foreach ( $this->_policies as $directive => $values ) {
			$policies[] = is_array( $values ) && ! empty( $values )
				? $directive . ' ' . implode( ' ', $values )
				: $directive;
		}

		return trim( implode( '; ', $policies ) );
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

		$this->addHeaderToResponse();
	}

	/**
	 * Adds CSP header to response.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function addHeaderToResponse() {
		$policies = $this->compilePolicies();
		if ( ! empty( $policies ) ) {
			$this->response->setHeader( self::HEADER_NAME, $policies );
		}
	}

}