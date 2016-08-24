<?php

namespace Phalcon\Plugin\CSP\Assets;

/**
 * Assets manager class which preserves origins of outputted resources. This
 * class is used by ContentSecurityPolicy plugin to populate script-src and
 * style-src directives.
 *
 * @since 1.0.0
 * @author Eugene Manuilov <eugene.manuilov@gmail.com>
 */
class Manager extends \Phalcon\Assets\Manager {

	/**
	 * Rendered nonces index.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access protected
	 * @var int
	 */
	protected static $_index = 0;

	/**
	 * Array of outputted origins.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_origins = array();

	/**
	 * Array of nonces of outputted inline scripts and styles.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_nonces = array();

	/**
	 * Returns array of outputted origins for specific resource type.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $type The resource type. Could be either CSS or JS.
	 * @return array Array of outputted origins.
	 */
	public function getOrigins( $type ) {
		return ! empty( $this->_origins[ $type ] )
			? array_values( $this->_origins[ $type ] )
			: array();
	}

	/**
	 * Returns array of outputted nonces for specific resource type.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $type The resource type. Could be either CSS or JS.
	 * @return array Array of outputted nonces.
	 */
	public function getNonces( $type ) {
		return ! empty( $this->_nonces[ $type ] )
			? $this->_nonces[ $type ]
			: array();
	}

    /**
     * Traverses a collection calling the callback to generate its HTML.
     *
	 * @since 1.0.0
	 *
	 * @access public
     * @param \Phalcon\Assets\Collection $collection The collection to output.
     * @param callback $callback Callback method to render single resource.
     * @param string $type The resource type to output.
     * @return string|null Generated HTML for collection resources.
     */
	public function output( \Phalcon\Assets\Collection $collection, $callback, $type ) {
		$output = parent::output( $collection, $callback, $type );

		if ( ! isset( $this->_origins[ $type ] ) ) {
			$this->_origins[ $type ] = array();
		}

		$prefix = $collection->getPrefix();
		foreach ( $collection->getResources() as $resource ) {
			$uri = $prefix . $resource->getRealTargetUri();
			if ( filter_var( $uri, FILTER_VALIDATE_URL ) ) {
				$path = parse_url( $uri, PHP_URL_PATH );
				$origin = substr( $uri, 0, stripos( $uri, $path ) );
				if ( ! empty( $origin ) ) {
					$this->_origins[ $type ][ $origin ] = $origin;
				}
			} else {
				$this->_origins[ $type ]['self'] = "'self'";
			}
		}

		return $output;
	}

	/**
	 * Traverses a collection and generate its HTML.
	 *
	 * @since 1.0.0
	 *
	 * @access public
     * @param \Phalcon\Assets\Collection $collection The collection to output.
     * @param string $type The resource type to output.
     * @return string|null Generated HTML for collection resources.
	 */
	public function outputInline( \Phalcon\Assets\Collection $collection, $type ) {
		$codes = $collection->getCodes();
		foreach ( $codes as $code ) {
			$attributes = $code->getAttributes();
			if ( empty( $attributes ) || ! is_array( $attributes ) ) {
				$attributes = array();
			}

			if ( empty( $this->_nonces[ $type ] ) || ! is_array( $this->_nonces[ $type ] ) ) {
				$this->_nonces[ $type ] = array();
			}

			$nonce = sha1( ++self::$_index . time() );
			$this->_nonces[ $type ][] = $nonce;
			$attributes['nonce'] = $nonce;

			$code->setAttributes( $attributes );
		}

		return parent::outputInline( $collection, $type );
	}

}