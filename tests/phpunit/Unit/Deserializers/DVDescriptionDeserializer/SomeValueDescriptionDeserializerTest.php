<?php

namespace SMW\Tests\Deserializers\DVDescriptionDeserializer;

use SMW\ApplicationFactory;
use SMW\Deserializers\DVDescriptionDeserializer\SomeValueDescriptionDeserializer;

/**
 * @covers \SMW\Deserializers\DVDescriptionDeserializer\SomeValueDescriptionDeserializer
 * @group semantic-mediawiki
 *
 * @license GNU GPL v2+
 * @since 2.3
 *
 * @author mwjames
 */
class SomeValueDescriptionDeserializerTest extends \PHPUnit_Framework_TestCase {

	private $dataItemFactory;

	protected function setUp() {
		parent::setUp();

		$this->dataItemFactory = ApplicationFactory::getInstance()->getDataItemFactory();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SMW\Deserializers\DVDescriptionDeserializer\SomeValueDescriptionDeserializer',
			new SomeValueDescriptionDeserializer()
		);
	}

	public function testIsDeserializerForDataValue() {

		$dataValue = $this->getMockBuilder( '\SMWDataValue' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$instance = new SomeValueDescriptionDeserializer();

		$this->assertTrue(
			$instance->isDeserializerFor( $dataValue )
		);
	}

	/**
	 * @dataProvider valueProvider
	 */
	public function testDeserialize( $value, $decription ) {

		$dataValue = $this->getMockBuilder( '\SMWDataValue' )
			->disableOriginalConstructor()
			->setMethods( array( 'isValid', 'getDataItem', 'getProperty', 'setUserValue' ) )
			->getMockForAbstractClass();

		$dataValue->expects( $this->once() )
			->method( 'setUserValue' )
			->with(
				$this->anything(),
				$this->equalTo( false ) );

		$dataValue->expects( $this->any() )
			->method( 'isValid' )
			->will( $this->returnValue( true ) );

		$dataValue->expects( $this->any() )
			->method( 'getDataItem' )
			->will( $this->returnValue($this->dataItemFactory->newDITime( 1, '1970' ) ) );

		$dataValue->expects( $this->any() )
			->method( 'getProperty' )
			->will( $this->returnValue( $this->dataItemFactory->newDIProperty( 'Foo' ) ) );

		$instance = new SomeValueDescriptionDeserializer();
		$instance->setDataValue( $dataValue );

		$this->assertInstanceOf(
			$decription,
			$instance->deserialize( $value )
		);
	}

	/**
	 * @dataProvider likeNotLikeProvider
	 */
	public function testDeserializeForLikeNotLike( $value ) {

		$dataValue = $this->getMockBuilder( '\SMWDataValue' )
			->disableOriginalConstructor()
			->setMethods( array( 'setUserValue' ) )
			->getMockForAbstractClass();

		$dataValue->expects( $this->once() )
			->method( 'setUserValue' )
			->with(
				$this->anything(),
				$this->equalTo( false ) );

		$instance = new SomeValueDescriptionDeserializer();
		$instance->setDataValue( $dataValue );

		$instance->deserialize( $value );
	}

	public function testInvalidDataValueRetunsThingDescription() {

		$dataValue = $this->getMockBuilder( '\SMWDataValue' )
			->disableOriginalConstructor()
			->setMethods( array( 'isValid' ) )
			->getMockForAbstractClass();

		$dataValue->expects( $this->any() )
			->method( 'isValid' )
			->will( $this->returnValue( false ) );

		$instance = new SomeValueDescriptionDeserializer();
		$instance->setDataValue( $dataValue );

		$this->assertInstanceOf(
			'\SMW\Query\Language\ThingDescription',
			$instance->deserialize( 'Foo' )
		);
	}

	public function testNonStringThrowsException() {

		$dataValue = $this->getMockBuilder( '\SMWDataValue' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$instance = new SomeValueDescriptionDeserializer();
		$instance->setDataValue( $dataValue );

		$this->setExpectedException( 'InvalidArgumentException' );
		$instance->deserialize( array() );
	}

	public function testWikiPageValueOnNonMainNamespace() {

		$dataValue = $this->getMockBuilder( '\SMWDataValue' )
			->disableOriginalConstructor()
			->setMethods( array( 'isValid', 'getDataItem', 'getProperty', 'setUserValue' ) )
			->getMockForAbstractClass();

		$dataValue->expects( $this->atLeastOnce() )
			->method( 'setUserValue' )
			->with(
				$this->anything(),
				$this->equalTo( false ) );

		$dataValue->expects( $this->any() )
			->method( 'isValid' )
			->will( $this->returnValue( true ) );

		$dataValue->expects( $this->any() )
			->method( 'getDataItem' )
			->will( $this->returnValue( $this->dataItemFactory->newDIWikiPage( '~Foo', NS_HELP ) ) );

		$dataValue->expects( $this->any() )
			->method( 'getProperty' )
			->will( $this->returnValue( $this->dataItemFactory->newDIProperty( 'Foo' ) ) );

		$instance = new SomeValueDescriptionDeserializer();
		$instance->setDataValue( $dataValue );

		$this->assertInstanceOf(
			'\SMW\Query\Language\Conjunction',
			$instance->deserialize( 'Help:~Foo' )
		);
	}

	public function valueProvider() {

		$provider[] = array(
			'Foo',
			'\SMW\Query\Language\ValueDescription'
		);

		return $provider;
	}


	public function likeNotLikeProvider() {

		$provider[] = array(
			'~Foo'
		);

		$provider[] = array(
			'!~Foo'
		);

		return $provider;
	}

}
