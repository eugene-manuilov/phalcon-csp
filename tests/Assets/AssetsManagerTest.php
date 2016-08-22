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

		for ( $i = 0, $len = $faker->numberBetween( 1, 20 ); $i < $len; $i++ ) {
			$url = $faker->url;
			$expectedOrigins['css'][] = parse_url( $url, PHP_URL_HOST );
			$manager->addCss( $url, false );
		}

		for ( $i = 0, $len = $faker->numberBetween( 1, 20 ); $i < $len; $i++ ) {
			$url = $faker->url;
			$expectedOrigins['js'][] = parse_url( $url, PHP_URL_HOST );
			$manager->addJs( $url, false );
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