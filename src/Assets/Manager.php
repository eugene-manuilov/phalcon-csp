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
	 * Array of outputted origins.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_origins = array();

	/**
	 * Returns array of outputted origins for specific resource type.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $type The resource type. Could be either CSS or JS.
	 * @return array Array of outputted domains.
	 */
	public function getOrigins( $type ) {
		return ! empty( $this->_origins[ $type ] )
			? array_values( $this->_origins[ $type ] )
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

		foreach ( $collection->getResources() as $resource ) {
			$uri = $resource->getRealTargetUri();
			if ( filter_var( $uri, FILTER_VALIDATE_URL ) ) {
				$domain = parse_url( $uri, PHP_URL_HOST );
				if ( ! empty( $domain ) ) {
					$this->_origins[ $type ][ $domain ] = $domain;
				}
			} else {
				$this->_origins[ $type ]['self'] = 'self';
			}
		}

		return $output;
	}

}