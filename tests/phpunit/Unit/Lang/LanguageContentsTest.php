<?php

namespace SMW\Tests\Lang;

use SMW\Lang\FallbackFinder;
use SMW\Lang\JsonContentsFileReader;
use SMW\Lang\LanguageContents;

/**
 * @covers \SMW\Lang\LanguageContents
 * @group semantic-mediawiki
 *
 * @license GNU GPL v2+
 * @since 2.5
 *
 * @author mwjames
 */
class LanguageContentsTest extends \PHPUnit_Framework_TestCase {

	private $jsonContentsFileReader;
	private $fallbackFinder;

	protected function setUp() {

		$this->jsonContentsFileReader = $this->getMockBuilder( JsonContentsFileReader::class )
			->disableOriginalConstructor()
			->getMock();

		$this->fallbackFinder = $this->getMockBuilder( FallbackFinder::class )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			LanguageContents::class,
			new LanguageContents( $this->jsonContentsFileReader, $this->fallbackFinder )
		);
	}

	public function testGetCanonicalFallbackLanguageCode() {

		$this->fallbackFinder->expects( $this->atLeastOnce() )
			->method( 'getCanonicalFallbackLanguageCode' );

		$instance = new LanguageContents(
			$this->jsonContentsFileReader,
			$this->fallbackFinder
		);

		$instance->getCanonicalFallbackLanguageCode();
	}

	public function testPrepareWithLanguageWithoutFallback() {

		$languageCode = 'Foo';

		$this->jsonContentsFileReader->expects( $this->atLeastOnce() )
			->method( 'canReadByLanguageCode' )
			->will( $this->returnValue( true ) );

		$this->jsonContentsFileReader->expects( $this->atLeastOnce() )
			->method( 'readByLanguageCode' )
			->with( $this->equalTo( $languageCode ) );

		$instance = new LanguageContents(
			$this->jsonContentsFileReader,
			$this->fallbackFinder
		);

		$this->assertFalse(
			$instance->isLoaded( $languageCode )
		);

		$instance->load( $languageCode );

		$this->assertTrue(
			$instance->isLoaded( $languageCode )
		);
	}

	public function testGetContentsByLanguageWithIndexWithFallback() {

		$languageCode = 'Foo';
		$fallback = 'Foobar';

		$this->jsonContentsFileReader->expects( $this->at( 0 ) )
			->method( 'readByLanguageCode' )
			->with( $this->equalTo( $languageCode ) )
			->will( $this->returnValue( array() ) );

		$this->jsonContentsFileReader->expects( $this->at( 1 ) )
			->method( 'readByLanguageCode' )
			->with( $this->equalTo( $fallback ) )
			->will( $this->returnValue( array( 'Bar' => 123 ) ) );

		$this->fallbackFinder->expects( $this->atLeastOnce() )
			->method( 'getCanonicalFallbackLanguageCode' )
			->will( $this->returnValue( 'en' ) );

		$this->fallbackFinder->expects( $this->at( 1 ) )
			->method( 'getFallbackLanguageBy' )
			->will( $this->returnValue( $fallback ) );

		$instance = new LanguageContents(
			$this->jsonContentsFileReader,
			$this->fallbackFinder
		);

		$this->assertEquals(
			123,
			$instance->get( 'Bar', $languageCode )
		);
	}

	public function testGetContentsByLanguageWithIndexWithFallbackButMissingIndexThrowsException() {

		$languageCode = 'Foo';
		$fallback = 'Foobar';

		$this->jsonContentsFileReader->expects( $this->at( 0 ) )
			->method( 'readByLanguageCode' )
			->with( $this->equalTo( $languageCode ) )
			->will( $this->returnValue( array() ) );

		$this->jsonContentsFileReader->expects( $this->at( 1 ) )
			->method( 'readByLanguageCode' )
			->with( $this->equalTo( $fallback ) )
			->will( $this->returnValue( array() ) );

		$this->fallbackFinder->expects( $this->atLeastOnce() )
			->method( 'getCanonicalFallbackLanguageCode' )
			->will( $this->returnValue( 'en' ) );

		$this->fallbackFinder->expects( $this->at( 1 ) )
			->method( 'getFallbackLanguageBy' )
			->will( $this->returnValue( $fallback ) );

		$this->fallbackFinder->expects( $this->at( 3 ) )
			->method( 'getFallbackLanguageBy' )
			->will( $this->returnValue( 'en' ) );

		$instance = new LanguageContents(
			$this->jsonContentsFileReader,
			$this->fallbackFinder
		);

		$this->setExpectedException( 'RuntimeException' );
		$instance->get( 'Bar', $languageCode );
	}

}
