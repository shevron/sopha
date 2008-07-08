<?php

/**
 * Sopha - A PHP 5.x Interface to CouchDB
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://prematureoptimization.org/sopha/license/new-bsd
 * 
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @package    Sopha
 * @category   Tests
 * @version    $Id: DbTest.php 26 2008-07-07 17:40:48Z shahar $
 * @license    http://prematureoptimization.org/sopha/license/new-bsd 
 */

/**
 * This code was mostly adapted from Zend_Json - a part of the Zend Framework
 * Copyright (c) 2005-2008 Zend Technologies USA Inc., licensed under the
 * New BSD License. See http://framework.zend.com for more information.
 */

// Load the test helper
require_once dirname(__FILE__) . '/../TestHelper.php';

require_once 'Sopha/Json.php';
require_once 'Sopha/Json/Encoder.php';
require_once 'Sopha/Json/Decoder.php';

class Sopha_JsonTest extends PHPUnit_Framework_TestCase
{

    public function testJsonWithPhpJsonExtension()
    {
        if (!extension_loaded('json')) {
            $this->markTestSkipped('JSON extension is not loaded');
        }
        $u = Sopha_Json::$useBuiltinEncoderDecoder;
        Sopha_Json::$useBuiltinEncoderDecoder = false;
        $this->_testJson(array('string', 327, true, null));
        Sopha_Json::$useBuiltinEncoderDecoder = $u;
    }

    public function testJsonWithBuiltins()
    {
        $u = Sopha_Json::$useBuiltinEncoderDecoder;
        Sopha_Json::$useBuiltinEncoderDecoder = true;
        $this->_testJson(array('string', 327, true, null));
        Sopha_Json::$useBuiltinEncoderDecoder = $u;
    }

    /**
     * Test encoding and decoding in a single step
     * @param array $values   array of values to test against encode/decode
     */
    protected function _testJson($values)
    {
        $encoded = Sopha_Json::encode($values);
        $this->assertEquals($values, Sopha_Json::decode($encoded));
    }

    /**
     * test null encoding/decoding
     */
    public function testNull()
    {
        $this->_testEncodeDecode(array(null));
    }


    /**
     * test boolean encoding/decoding
     */
    public function testBoolean()
    {
        $this->assertTrue(Sopha_Json_Decoder::decode(Sopha_Json_Encoder::encode(true)));
        $this->assertFalse(Sopha_Json_Decoder::decode(Sopha_Json_Encoder::encode(false)));
    }


    /**
     * test integer encoding/decoding
     */
    public function testInteger()
    {
        $this->_testEncodeDecode(array(-2));
        $this->_testEncodeDecode(array(-1));

        $zero = Sopha_Json_Decoder::decode(Sopha_Json_Encoder::encode(0));
        $this->assertEquals(0, $zero, 'Failed 0 integer test. Encoded: ' . serialize(Sopha_Json_Encoder::encode(0)));
    }


    /**
     * test float encoding/decoding
     */
    public function testFloat()
    {
        $this->_testEncodeDecode(array(-2.1, 1.2));
    }

    /**
     * test string encoding/decoding
     */
    public function testString()
    {
        $this->_testEncodeDecode(array('string'));
        $this->assertEquals('', Sopha_Json_Decoder::decode(Sopha_Json_Encoder::encode('')), 'Empty string encoded: ' . serialize(Sopha_Json_Encoder::encode('')));
    }

    /**
     * Test backslash escaping of string
     */
    public function testString2()
    {
        $string   = 'INFO: Path \\\\test\\123\\abc';
        $expected = '"INFO: Path \\\\\\\\test\\\\123\\\\abc"';
        $encoded = Sopha_Json_Encoder::encode($string);
        $this->assertEquals($expected, $encoded, 'Backslash encoding incorrect: expected: ' . serialize($expected) . '; received: ' . serialize($encoded) . "\n");
        $this->assertEquals($string, Sopha_Json_Decoder::decode($encoded));
    }

    /**
     * Test newline escaping of string
     */
    public function testString3()
    {
        $expected = '"INFO: Path\nSome more"';
        $string   = "INFO: Path\nSome more";
        $encoded  = Sopha_Json_Encoder::encode($string);
        $this->assertEquals($expected, $encoded, 'Newline encoding incorrect: expected ' . serialize($expected) . '; received: ' . serialize($encoded) . "\n");
        $this->assertEquals($string, Sopha_Json_Decoder::decode($encoded));
    }

    /**
     * Test tab/non-tab escaping of string
     */
    public function testString4()
    {
        $expected = '"INFO: Path\\t\\\\tSome more"';
        $string   = "INFO: Path\t\\tSome more";
        $encoded  = Sopha_Json_Encoder::encode($string);
        $this->assertEquals($expected, $encoded, 'Tab encoding incorrect: expected ' . serialize($expected) . '; received: ' . serialize($encoded) . "\n");
        $this->assertEquals($string, Sopha_Json_Decoder::decode($encoded));
    }

    /**
     * Test double-quote escaping of string
     */
    public function testString5()
    {
        $expected = '"INFO: Path \"Some more\""';
        $string   = 'INFO: Path "Some more"';
        $encoded  = Sopha_Json_Encoder::encode($string);
        $this->assertEquals($expected, $encoded, 'Quote encoding incorrect: expected ' . serialize($expected) . '; received: ' . serialize($encoded) . "\n");
        $this->assertEquals($string, Sopha_Json_Decoder::decode($encoded));
    }

    /**
     * test indexed array encoding/decoding
     */
    public function testArray()
    {
        $array = array(1, 'one', 2, 'two');
        $encoded = Sopha_Json_Encoder::encode($array);
        $this->assertSame($array, Sopha_Json_Decoder::decode($encoded), 'Decoded array does not match: ' . serialize($encoded));
    }

    /**
     * test associative array encoding/decoding
     */
    public function testAssocArray()
    {
        $this->_testEncodeDecode(array(array('one' => 1, 'two' => 2)));
    }

    /**
     * test associative array encoding/decoding, with mixed key types
     */
    public function testAssocArray2()
    {
        $this->_testEncodeDecode(array(array('one' => 1, 2 => 2)));
    }

    /**
     * test associative array encoding/decoding, with integer keys not starting at 0
     */
    public function testAssocArray3()
    {
        $this->_testEncodeDecode(array(array(1 => 'one', 2 => 'two')));
    }

    /**
     * test object encoding/decoding (decoding to array)
     */
    public function testObject()
    {
        $value = new stdClass();
        $value->one = 1;
        $value->two = 2;

        $array = array('__className' => 'stdClass', 'one' => 1, 'two' => 2);

        $encoded = Sopha_Json_Encoder::encode($value);
        $this->assertSame($array, Sopha_Json_Decoder::decode($encoded));
    }

    /**
     * test object encoding/decoding (decoding to stdClass)
     */
    public function testObjectAsObject()
    {
        $value = new stdClass();
        $value->one = 1;
        $value->two = 2;

        $encoded = Sopha_Json_Encoder::encode($value);
        $decoded = Sopha_Json_Decoder::decode($encoded, Sopha_Json::TYPE_OBJECT);
        $this->assertTrue(is_object($decoded), 'Not decoded as an object');
        $this->assertTrue($decoded instanceof StdClass, 'Not a StdClass object');
        $this->assertTrue(isset($decoded->one), 'Expected property not set');
        $this->assertEquals($value->one, $decoded->one, 'Unexpected value');
    }

    /**
     * Test that arrays of objects decode properly; see issue #144
     */
    public function testDecodeArrayOfObjects()
    {
        $value = '[{"id":1},{"foo":2}]';
        $expect = array(array('id' => 1), array('foo' => 2));
        $this->assertEquals($expect, Sopha_Json_Decoder::decode($value));
    }

    /**
     * Test that objects of arrays decode properly; see issue #107
     */
    public function testDecodeObjectOfArrays()
    {
        $value = '{"codeDbVar" : {"age" : ["int", 5], "prenom" : ["varchar", 50]}, "234" : [22, "jb"], "346" : [64, "francois"], "21" : [12, "paul"]}';
        $expect = array(
            'codeDbVar' => array(
                'age'   => array('int', 5),
                'prenom' => array('varchar', 50),
            ),
            234 => array(22, 'jb'),
            346 => array(64, 'francois'),
            21  => array(12, 'paul')
        );
        $this->assertEquals($expect, Sopha_Json_Decoder::decode($value));
    }

    /**
     * Test encoding and decoding in a single step
     * @param array $values   array of values to test against encode/decode
     */
    protected function _testEncodeDecode($values)
    {
        foreach ($values as $value) {
            $encoded = Sopha_Json_Encoder::encode($value);
            $this->assertEquals($value, Sopha_Json_Decoder::decode($encoded));
        }
    }

    /**
     * Test that version numbers such as 4.10 are encoded and decoded properly;
     * See ZF-377
     */
    public function testEncodeReleaseNumber()
    {
        $value = '4.10';

        $this->_testEncodeDecode(array($value));
    }

    /**
     * Tests that spaces/linebreaks prior to a closing right bracket don't throw
     * exceptions. See ZF-283.
     */
    public function testEarlyLineBreak()
    {
        $expected = array('data' => array(1, 2, 3, 4));

        $json = '{"data":[1,2,3,4' . "\n]}";
        $this->assertEquals($expected, Sopha_Json_Decoder::decode($json));

        $json = '{"data":[1,2,3,4 ]}';
        $this->assertEquals($expected, Sopha_Json_Decoder::decode($json));
    }

    /**
     * Tests for ZF-504
     *
     * Three confirmed issues reported:
     * - encoder improperly encoding empty arrays as structs
     * - decoder happily decoding clearly borked JSON
     * - decoder decoding octal values improperly (shouldn't decode them at all, as JSON does not support them)
     */
    public function testZf504()
    {
        $test = array();
        $this->assertSame('[]', Sopha_Json_Encoder::encode($test));

        try {
            $json = '[a"],["a],[][]';
            $test = Sopha_Json_Decoder::decode($json);
            $this->fail("Should not be able to decode '$json'");

            $json = '[a"],["a]';
            $test = Sopha_Json_Decoder::decode($json);
            $this->fail("Should not be able to decode '$json'");
        } catch (Exception $e) {
            // success
        }

        try {
            $expected = 010;
            $test = Sopha_Json_Decoder::decode('010');
            $this->fail('Octal values are not supported in JSON notation');
        } catch (Exception $e) {
            // sucess
        }
    }

    /**
     * Tests for ZF-461
     *
     * Check to see that cycling detection works properly
     */
    public function testZf461()
    {
        $item1 = new Sopha_JsonTest_Item() ;
        $item2 = new Sopha_JsonTest_Item() ;
        $everything = array() ;
        $everything['allItems'] = array($item1, $item2) ;
        $everything['currentItem'] = $item1 ;

        try {
            $encoded = Sopha_Json_Encoder::encode($everything);
        } catch (Exception $e) {
            $this->fail('Object cycling checks should check for recursion, not duplicate usage of an item');
        }

        try {
            $encoded = Sopha_Json_Encoder::encode($everything, true);
            $this->fail('Object cycling not allowed when cycleCheck parameter is true');
        } catch (Exception $e) {
            // success
        }
    }

    public function testEncodeObject()
    {
        $actual  = new Sopha_JsonTest_Object();
        $encoded = Sopha_Json_Encoder::encode($actual);
        $decoded = Sopha_Json_Decoder::decode($encoded, Sopha_Json::TYPE_OBJECT);

        $this->assertTrue(isset($decoded->__className));
        $this->assertEquals('Sopha_JsonTest_Object', $decoded->__className);
        $this->assertTrue(isset($decoded->foo));
        $this->assertEquals('bar', $decoded->foo);
        $this->assertTrue(isset($decoded->bar));
        $this->assertEquals('baz', $decoded->bar);
        $this->assertFalse(isset($decoded->_foo));
    }

    public function testEncodeClass()
    {
        $encoded = Sopha_Json_Encoder::encodeClass('Sopha_JsonTest_Object');

        $this->assertContains("Class.create('Sopha_JsonTest_Object'", $encoded);
        $this->assertContains("ZAjaxEngine.invokeRemoteMethod(this, 'foo'", $encoded);
        $this->assertContains("ZAjaxEngine.invokeRemoteMethod(this, 'bar'", $encoded);
        $this->assertNotContains("ZAjaxEngine.invokeRemoteMethod(this, 'baz'", $encoded);

        $this->assertContains('variables:{foo:"bar",bar:"baz"}', $encoded);
        $this->assertContains('constants : {FOO: "bar"}', $encoded);
    }

    public function testEncodeClasses()
    {
        $encoded = Sopha_Json_Encoder::encodeClasses(array('Sopha_JsonTest_Object', 'Sopha_JsonTest'));

        $this->assertContains("Class.create('Sopha_JsonTest_Object'", $encoded);
        $this->assertContains("Class.create('Sopha_JsonTest'", $encoded);
    }
}

/**
 * Sopha_JsonTest_Item: test item for use with testZf461()
 */
class Sopha_JsonTest_Item
{
}

/**
 * Sopha_JsonTest_Object: test class for encoding classes
 */
class Sopha_JsonTest_Object
{
    const FOO = 'bar';

    public $foo = 'bar';
    public $bar = 'baz';

    protected $_foo = 'fooled you';

    public function foo($bar, $baz)
    {
    }

    public function bar($baz)
    {
    }

    protected function baz()
    {
    }
}
