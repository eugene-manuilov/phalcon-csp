<?php

use Faker\Factory as FakerFactory;
use Phalcon\Di\FactoryDefault;
use Phalcon\Plugin\CSP\Assets\Manager as AssetsManager;

/**
 * Tests Assets\Manager class.
 *
 * @since 1.0.0
 * @author Eugene Manuilov <eugene.manuilov@gmail.com>
 */
class AssetsManagerTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Assets manager instance.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var \Phalcon\Plugin\CSP\Assets\Manager
	 */
	protected $_manager;

	/**
	 * Instance of faker generator.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var \Faker\Generator
	 */
	protected $_faker;

	/**
	 * Setups working environment for each test.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function setUp() {
		$di = new FactoryDefault();
		FactoryDefault::setDefault( $di );

		$this->_manager = new AssetsManager();
		$this->_manager->useImplicitOutput( false );

		$this->_faker = FakerFactory::create();
	}

	/**
	 * Tests origins gathering based on outputed resources.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function testOutput() {
		$expectedOrigins = array(
			'css' => array(),
			'js'  => array(),
		);

		$types = array(
			'css' => 'addCss',
			'js'  => 'addJs',
		);

		foreach ( $types as $type => $callback ) {
			$len = $this->_faker->numberBetween( 1, 20 );
			for ( $i = 0; $i < $len; $i++ ) {
				$url = $this->_faker->url;
				$path = parse_url( $url, PHP_URL_PATH );
				if ( '/' != $path ) {
					$expectedOrigins[ $type ][] = substr( $url, 0, stripos( $url, $path ) );
					call_user_func_array( array( $this->_manager, $callback ), array( $url, false ) );
				}
			}
		}

		$this->_manager->outputCss();
		$this->_manager->outputJs();

		$outputtedOrigins = array(
			'css' => $this->_manager->getOrigins( 'css' ),
			'js'  => $this->_manager->getOrigins( 'js' ),
		);

		$this->assertArraySubset( $expectedOrigins['css'], $outputtedOrigins['css'] );
		$this->assertArraySubset( $expectedOrigins['js'], $outputtedOrigins['js'] );
	}

	/**
	 * Tests nonces generation for inline scripts.
	 *
	 * @sicne 1.0.0
	 *
	 * @access public
	 */
	public function testInlineJsOutput() {
		$this->_manager->addInlineJs( "alert('hello')" );
		$this->_manager->addInlineJs( "console.log('hello')" );

		$this->_manager->outputInlineJs();

		$nonces = $this->_manager->getNonces( 'script' );

		$this->assertNotEmpty( $nonces );
		$this->assertCount( 2, $nonces );
	}

	/**
	 * Tests nonces generation for inline styles.
	 *
	 * @sicne 1.0.0
	 *
	 * @access public
	 */
	public function testInlineCssOutput() {
		$this->_manager->addInlineCss( '.line { text-align: center }' );
		$this->_manager->addInlineCss( '.menu { background-color: red }' );

		$this->_manager->outputInlineCss();

		$nonces = $this->_manager->getNonces( 'style' );
		$this->assertNotEmpty( $nonces );
		$this->assertCount( 2, $nonces );
	}

}