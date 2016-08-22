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
	 * Tests origins gathering based on outputed resources.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function testOutput() {
		$di = new FactoryDefault();
		FactoryDefault::setDefault( $di );

		$faker = FakerFactory::create();
		$manager = new AssetsManager();

		$expectedOrigins = array(
			'css' => array(),
			'js'  => array(),
		);

		$types = array(
			'css' => 'addCss',
			'js'  => 'addJs',
		);

		foreach ( $types as $type => $callback ) {
			for ( $i = 0, $len = $faker->numberBetween( 1, 20 ); $i < $len; $i++ ) {
				$url = $faker->url;
				$path = parse_url( $url, PHP_URL_PATH );
				if ( '/' != $path ) {
					$expectedOrigins[ $type ][] = substr( $url, 0, stripos( $url, $path ) );
					call_user_func_array( array( $manager, $callback ), array( $url, false ) );
				}
			}
		}

		ob_start();
		$manager->outputCss();
		$manager->outputJs();
		ob_end_clean();

		$outputtedOrigins = array(
			'css' => $manager->getOrigins( 'css' ),
			'js'  => $manager->getOrigins( 'js' ),
		);

		$this->assertArraySubset( $expectedOrigins['css'], $outputtedOrigins['css'] );
		$this->assertArraySubset( $expectedOrigins['js'], $outputtedOrigins['js'] );
	}

}