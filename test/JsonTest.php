<?php
/**
 * @see       https://github.com/zendframwork/zend-json for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframwork/zend-json/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Json;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use stdClass;
use Zend\Json;

class JsonTest extends TestCase
{
    private $originalUseBuiltinEncoderDecoderValue;

    public function setUp()
    {
        $this->originalUseBuiltinEncoderDecoderValue = Json\Json::$useBuiltinEncoderDecoder;
    }

    public function tearDown()
    {
        Json\Json::$useBuiltinEncoderDecoder = $this->originalUseBuiltinEncoderDecoderValue;
    }

    /**
     * Test encoding and decoding in a single step
     *
     * @param scalar|array $values array of values to test against encode/decode
     * @param $message
     */
    public function assertEncodesToDecodable($values, $message = null)
    {
        $message = $message ?: 'One or more values could not be decoded after encoding';

        $values = is_null($values) ? [null] : $values;
        $values = is_scalar($values) ? [$values] : $values;

        foreach ($values as $value) {
            $encoded = Json\Encoder::encode($value);

            if (is_array($value) || is_object($value)) {
                $message = $message ?: sprintf(
                    'Value could not be decoded after encoding: %s',
                    var_export($value, true)
                );
                $this->assertEquals(
                    $this->toArray($value),
                    Json\Decoder::decode($encoded, Json\Json::TYPE_ARRAY),
                    $message
                );
                continue;
            }

            $message = $message ?: sprintf(
                'Value could not be decoded after encoding: %s',
                $value
            );
            $this->assertEquals($value, Json\Decoder::decode($encoded), $message);
        }
    }

    public function testJSONWithPhpJSONExtension()
    {
        if (! extension_loaded('json')) {
            $this->markTestSkipped('JSON extension is not loaded');
        }

        Json\Json::$useBuiltinEncoderDecoder = false;
        $this->assertEncodesToDecodable(['string', 327, true, null]);
    }

    public function testJSONWithBuiltins()
    {
        Json\Json::$useBuiltinEncoderDecoder = true;
        $this->assertEncodesToDecodable(['string', 327, true, null]);
    }

    /**
     * test null encoding/decoding
     */
    public function testNull()
    {
        $this->assertEncodesToDecodable(null, 'Null could not be decoded after encoding');
    }

    /**
     * test boolean encoding/decoding
     */
    public function testBoolean()
    {
        $this->assertTrue(Json\Decoder::decode(Json\Encoder::encode(true)));
        $this->assertFalse(Json\Decoder::decode(Json\Encoder::encode(false)));
    }

    public function integerProvider()
    {
        return [
            'negative' => [-1],
            'zero'     => [0],
            'positive' => [1],
        ];
    }

    /**
     * test integer encoding/decoding
     * @dataProvider integerProvider
     */
    public function testInteger($int)
    {
        $this->assertEncodesToDecodable($int);
    }

    public function floatProvider()
    {
        return [
            'negative' => [-1.1],
            'zero'     => [0.0],
            'positive' => [1.1],
        ];
    }

    /**
     * test float encoding/decoding
     * @dataProvider floatProvider
     */
    public function testFloat($float)
    {
        $this->assertEncodesToDecodable($float);
    }

    public function stringProvider()
    {
        return [
            'empty'  => [''],
            'string' => ['string'],
        ];
    }

    /**
     * test string encoding/decoding
     * @dataProvider stringProvider
     */
    public function testString($string)
    {
        $this->assertEncodesToDecodable($string);
    }

    /**
     * Test backslash escaping of string
     */
    public function testString2()
    {
        $string   = 'INFO: Path \\\\test\\123\\abc';
        $expected = '"INFO: Path \\\\\\\\test\\\\123\\\\abc"';
        $encoded = Json\Encoder::encode($string);
        $this->assertEquals(
            $expected,
            $encoded,
            sprintf(
                'Backslash encoding incorrect: expected: %s; received: %s',
                serialize($expected),
                serialize($encoded)
            )
        );
        $this->assertEncodesToDecodable($string);
    }

    /**
     * Test newline escaping of string
     */
    public function testString3()
    {
        $expected = '"INFO: Path\nSome more"';
        $string   = "INFO: Path\nSome more";
        $encoded  = Json\Encoder::encode($string);
        $this->assertEquals(
            $expected,
            $encoded,
            sprintf(
                'Newline encoding incorrect: expected %s; received: %s',
                serialize($expected),
                serialize($encoded)
            )
        );
        $this->assertEncodesToDecodable($string);
    }

    /**
     * Test tab/non-tab escaping of string
     */
    public function testString4()
    {
        $expected = '"INFO: Path\\t\\\\tSome more"';
        $string   = "INFO: Path\t\\tSome more";
        $encoded  = Json\Encoder::encode($string);
        $this->assertEquals(
            $expected,
            $encoded,
            sprintf(
                'Tab encoding incorrect: expected %s; received: %s',
                serialize($expected),
                serialize($encoded)
            )
        );
        $this->assertEncodesToDecodable($string);
    }

    /**
     * Test double-quote escaping of string
     */
    public function testString5()
    {
        $expected = '"INFO: Path \\u0022Some more\\u0022"';
        $string   = 'INFO: Path "Some more"';
        $encoded  = Json\Encoder::encode($string);
        $this->assertEquals(
            $expected,
            $encoded,
            'Quote encoding incorrect: expected ' . serialize($expected) . '; received: ' . serialize($encoded) . "\n"
        );
        $this->assertEncodesToDecodable($string); // Bug: does not accept \u0022 as token!
    }

    /**
     * Test decoding of unicode escaped special characters
     */
    public function testStringOfHtmlSpecialCharsEncodedToUnicodeEscapes()
    {
        Json\Json::$useBuiltinEncoderDecoder = false;
        $expected = '"\\u003C\\u003E\\u0026\\u0027\\u0022"';
        $string   = '<>&\'"';
        $encoded  = Json\Encoder::encode($string);
        $this->assertEquals(
            $expected,
            $encoded,
            'Encoding error: expected ' . serialize($expected) . '; received: ' . serialize($encoded) . "\n"
        );
        $this->assertEncodesToDecodable($string);
    }

    /**
     * Test decoding of unicode escaped ASCII (non-HTML special) characters
     *
     * Note: covers chars that MUST be escaped. Does not test any other non-printables.
     */
    public function testStringOfOtherSpecialCharsEncodedToUnicodeEscapes()
    {
        Json\Json::$useBuiltinEncoderDecoder = false;
        $string   = "\\ - \n - \t - \r - " . chr(0x08) . " - " . chr(0x0C) . " - / - \v";
        $encoded  = '"\u005C - \u000A - \u0009 - \u000D - \u0008 - \u000C - \u002F - \u000B"';
        $this->assertEquals($string, Json\Decoder::decode($encoded));
    }

    /**
     * test indexed array encoding/decoding
     */
    public function testArray()
    {
        $this->assertEncodesToDecodable([[1, 'one', 2, 'two']]);
    }

    /**
     * test associative array encoding/decoding
     */
    public function testAssocArray()
    {
        $this->assertEncodesToDecodable([['one' => 1, 'two' => 2]]);
    }

    /**
     * test associative array encoding/decoding, with mixed key types
     */
    public function testAssocArray2()
    {
        $this->assertEncodesToDecodable([['one' => 1, 2 => 2]]);
    }

    /**
     * test associative array encoding/decoding, with integer keys not starting at 0
     */
    public function testAssocArray3()
    {
        $this->assertEncodesToDecodable([[1 => 'one', 2 => 'two']]);
    }

    /**
     * test object encoding/decoding (decoding to array)
     */
    public function testObject()
    {
        $value = new stdClass();
        $value->one = 1;
        $value->two = 2;

        $array = ['__className' => 'stdClass', 'one' => 1, 'two' => 2];

        $encoded = Json\Encoder::encode($value);
        $this->assertSame($array, Json\Decoder::decode($encoded, Json\Json::TYPE_ARRAY));
    }

    /**
     * test object encoding/decoding (decoding to stdClass)
     */
    public function testObjectAsObject()
    {
        $value = new stdClass();
        $value->one = 1;
        $value->two = 2;

        $encoded = Json\Encoder::encode($value);
        $decoded = Json\Decoder::decode($encoded, Json\Json::TYPE_OBJECT);
        $this->assertInstanceOf('stdClass', $decoded);
        $this->assertObjectHasAttribute('one', $decoded);
        $this->assertEquals($value->one, $decoded->one, 'Unexpected value');
    }

    /**
     * Test that arrays of objects decode properly; see issue #144
     */
    public function testDecodeArrayOfObjects()
    {
        $value = '[{"id":1},{"foo":2}]';
        $expect = [['id' => 1], ['foo' => 2]];
        $this->assertEquals($expect, Json\Decoder::decode($value, Json\Json::TYPE_ARRAY));
    }

    /**
     * Test that objects of arrays decode properly; see issue #107
     */
    public function testDecodeObjectOfArrays()
    {
        // @codingStandardsIgnoreStart
        $value = '{"codeDbVar" : {"age" : ["int", 5], "prenom" : ["varchar", 50]}, "234" : [22, "jb"], "346" : [64, "francois"], "21" : [12, "paul"]}';
        // @codingStandardsIgnoreEnd

        $expect = [
            'codeDbVar' => [
                'age'   => ['int', 5],
                'prenom' => ['varchar', 50],
            ],
            234 => [22, 'jb'],
            346 => [64, 'francois'],
            21  => [12, 'paul']
        ];

        $this->assertEquals($expect, Json\Decoder::decode($value, Json\Json::TYPE_ARRAY));
    }

    /**
     * Cast a value to an array, if possible.
     *
     * Casts objects to arrays for expectation comparisons.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function toArray($value)
    {
        if (! is_array($value) || ! is_object($value)) {
            return $value;
        }

        $array = [];
        foreach ((array) $value as $k => $v) {
            $array[$k] = $this->toArray($v);
        }

        return $array;
    }

    /**
     * Test that version numbers such as 4.10 are encoded and decoded properly;
     * See ZF-377
     */
    public function testEncodeReleaseNumber()
    {
        $value = '4.10';

        $this->assertEncodesToDecodable($value);
    }

    /**
     * Tests that spaces/linebreaks prior to a closing right bracket don't throw
     * exceptions. See ZF-283.
     */
    public function testEarlyLineBreak()
    {
        $expected = ['data' => [1, 2, 3, 4]];

        $json = '{"data":[1,2,3,4' . "\n]}";
        $this->assertEquals($expected, Json\Decoder::decode($json, Json\Json::TYPE_ARRAY));

        $json = '{"data":[1,2,3,4 ]}';
        $this->assertEquals($expected, Json\Decoder::decode($json, Json\Json::TYPE_ARRAY));
    }

    /**
     * @group ZF-504
     */
    public function testEncodeEmptyArrayAsStruct()
    {
        $this->assertSame('[]', Json\Encoder::encode([]));
    }

    /**
     * @group ZF-504
     */
    public function testDecodeBorkedJsonShouldThrowException1()
    {
        $this->expectException(Json\Exception\RuntimeException::class);
        Json\Decoder::decode('[a"],["a],[][]');
    }

    /**
     * @group ZF-504
     */
    public function testDecodeBorkedJsonShouldThrowException2()
    {
        $this->expectException(Json\Exception\RuntimeException::class);
        Json\Decoder::decode('[a"],["a]');
    }

    /**
     * @group ZF-504
     */
    public function testOctalValuesAreNotSupportedInJsonNotation()
    {
        $this->expectException(Json\Exception\RuntimeException::class);
        Json\Decoder::decode('010');
    }

    /**
     * Tests for ZF-461
     *
     * Check to see that cycling detection works properly
     */
    public function testZf461()
    {
        $item1 = new TestAsset\Item();
        $item2 = new TestAsset\Item();
        $everything = [];
        $everything['allItems'] = [$item1, $item2];
        $everything['currentItem'] = $item1;

        // should not fail
        $encoded = Json\Encoder::encode($everything);

        // should fail
        $this->expectException(Json\Exception\RecursionException::class);
        Json\Encoder::encode($everything, true);
    }

    /**
     * Test for ZF-4053
     *
     * Check to see that cyclical exceptions are silenced when
     * $option['silenceCyclicalExceptions'] = true is used
     */
    public function testZf4053()
    {
        $item1 = new TestAsset\Item();
        $item2 = new TestAsset\Item();
        $everything = [];
        $everything['allItems'] = [$item1, $item2];
        $everything['currentItem'] = $item1;

        $options = ['silenceCyclicalExceptions' => true];

        Json\Json::$useBuiltinEncoderDecoder = true;
        $encoded = Json\Json::encode($everything, true, $options);

        // @codingStandardsIgnoreStart
        $json = '{"allItems":[{"__className":"ZendTest\\\\Json\\\\TestAsset\\\\Item"},{"__className":"ZendTest\\\\Json\\\\TestAsset\\\\Item"}],"currentItem":"* RECURSION (ZendTest\\\\Json\\\\TestAsset\\\\Item) *"}';
        // @codingStandardsIgnoreEnd

        $this->assertEquals($json, $encoded);
    }

    public function testEncodeObject()
    {
        $actual  = new TestAsset\TestObject();
        $encoded = Json\Encoder::encode($actual);
        $decoded = Json\Decoder::decode($encoded, Json\Json::TYPE_OBJECT);

        $this->assertAttributeEquals(TestAsset\TestObject::class, '__className', $decoded);
        $this->assertAttributeEquals('bar', 'foo', $decoded);
        $this->assertAttributeEquals('baz', 'bar', $decoded);
        $this->assertFalse(isset($decoded->_foo));
    }

    public function testEncodeClass()
    {
        $encoded = Json\Encoder::encodeClass(TestAsset\TestObject::class);

        $this->assertContains("Class.create('ZendTest\\Json\\TestAsset\\TestObject'", $encoded);
        $this->assertContains("ZAjaxEngine.invokeRemoteMethod(this, 'foo'", $encoded);
        $this->assertContains("ZAjaxEngine.invokeRemoteMethod(this, 'bar'", $encoded);
        $this->assertNotContains("ZAjaxEngine.invokeRemoteMethod(this, 'baz'", $encoded);

        $this->assertContains('variables:{foo:"bar",bar:"baz"}', $encoded);
        $this->assertContains('constants:{FOO: "bar"}', $encoded);
    }

    public function testEncodeClasses()
    {
        $encoded = Json\Encoder::encodeClasses(['ZendTest\Json\TestAsset\TestObject', 'Zend\Json\Json']);

        $this->assertContains("Class.create('ZendTest\\Json\\TestAsset\\TestObject'", $encoded);
        $this->assertContains("Class.create('Zend\\Json\\Json'", $encoded);
    }

    public function testToJSONSerialization()
    {
        $toJSONObject = new TestAsset\ToJSONClass();

        $result = Json\Json::encode($toJSONObject);

        $this->assertEquals('{"firstName":"John","lastName":"Doe","email":"john@doe.com"}', $result);
    }

    public function testJsonSerializableWithBuiltinImplementation()
    {
        $encoded = Json\Encoder::encode(
            new TestAsset\JsonSerializableBuiltinImpl()
        );

        $this->assertEquals('["jsonSerialize"]', $encoded);
    }

    public function testJsonSerializableWithZFImplementation()
    {
        $encoded = Json\Encoder::encode(
            new TestAsset\JsonSerializableZFImpl()
        );

        $this->assertEquals('["jsonSerialize"]', $encoded);
    }

    /**
     * test encoding array with Zend_JSON_Expr
     *
     * @group ZF-4946
     */
    public function testEncodingArrayWithExpr()
    {
        $expr = new Json\Expr('window.alert("Zend JSON Expr")');
        $array = ['expr' => $expr, 'int' => 9, 'string' => 'text'];
        $result = Json\Json::encode($array, false, ['enableJsonExprFinder' => true]);
        $expected = '{"expr":window.alert("Zend JSON Expr"),"int":9,"string":"text"}';
        $this->assertEquals($expected, $result);
    }

    /**
     * test encoding object with Zend_JSON_Expr
     *
     * @group ZF-4946
     */
    public function testEncodingObjectWithExprAndInternalEncoder()
    {
        Json\Json::$useBuiltinEncoderDecoder = true;

        $expr = new Json\Expr('window.alert("Zend JSON Expr")');
        $obj = new stdClass();
        $obj->expr = $expr;
        $obj->int = 9;
        $obj->string = 'text';
        $result = Json\Json::encode($obj, false, ['enableJsonExprFinder' => true]);
        $expected = '{"__className":"stdClass","expr":window.alert("Zend JSON Expr"),"int":9,"string":"text"}';
        $this->assertEquals($expected, $result);
    }

    /**
     * Test encoding object with Zend\Json\Expr
     *
     * @group ZF-4946
     */
    public function testEncodingObjectWithExprAndExtJSON()
    {
        if (! function_exists('json_encode')) {
            $this->markTestSkipped('Test only works with ext/json enabled!');
        }

        Json\Json::$useBuiltinEncoderDecoder = false;

        $expr = new Json\Expr('window.alert("Zend JSON Expr")');
        $obj = new stdClass();
        $obj->expr = $expr;
        $obj->int = 9;
        $obj->string = 'text';
        $result = Json\Json::encode($obj, false, ['enableJsonExprFinder' => true]);
        $expected = '{"expr":window.alert("Zend JSON Expr"),"int":9,"string":"text"}';
        $this->assertEquals($expected, $result);
    }

    /**
     * test encoding object with toJson and Zend\Json\Expr
     *
     * @group ZF-4946
     */
    public function testToJSONWithExpr()
    {
        Json\Json::$useBuiltinEncoderDecoder = true;

        $obj = new TestAsset\ToJSONWithExpr();
        $result = Json\Json::encode($obj, false, ['enableJsonExprFinder' => true]);
        $expected = '{"expr":window.alert("Zend JSON Expr"),"int":9,"string":"text"}';
        $this->assertEquals($expected, $result);
    }

    /**
     * Regression tests for Zend\Json\Expr and multiple keys with the same name.
     *
     * @group ZF-4946
     */
    public function testEncodingMultipleNestedSwitchingSameNameKeysWithDifferentJSONExprSettings()
    {
        $data = [
            0 => [
                "alpha" => new Json\Expr("function () {}"),
                "beta"  => "gamma",
            ],
            1 => [
                "alpha" => "gamma",
                "beta"  => new Json\Expr("function () {}"),
            ],
            2 => [
                "alpha" => "gamma",
                "beta" => "gamma",
            ]
        ];
        $result = Json\Json::encode($data, false, ['enableJsonExprFinder' => true]);

        // @codingStandardsIgnoreStart
        $this->assertEquals(
            '[{"alpha":function () {},"beta":"gamma"},{"alpha":"gamma","beta":function () {}},{"alpha":"gamma","beta":"gamma"}]',
            $result
        );
        // @codingStandardsIgnoreEnd
    }

    /**
     * Regression tests for Zend\Json\Expr and multiple keys with the same name.
     *
     * @group ZF-4946
     */
    public function testEncodingMultipleNestedIteratedSameNameKeysWithDifferentJSONExprSettings()
    {
        $data = [
            0 => [
                "alpha" => "alpha"
            ],
            1 => [
                "alpha" => "beta",
            ],
            2 => [
                "alpha" => new Json\Expr("gamma"),
            ],
            3 => [
                "alpha" => "delta",
            ],
            4 => [
                "alpha" => new Json\Expr("epsilon"),
            ]
        ];
        $result = Json\Json::encode($data, false, ['enableJsonExprFinder' => true]);

        // @codingStandardsIgnoreStart
        $this->assertEquals('[{"alpha":"alpha"},{"alpha":"beta"},{"alpha":gamma},{"alpha":"delta"},{"alpha":epsilon}]', $result);
        // @codingStandardsIgnoreEnd
    }

    public function testDisabledJSONExprFinder()
    {
        Json\Json::$useBuiltinEncoderDecoder = true;

        $data = [
            0 => [
                "alpha" => new Json\Expr("function () {}"),
                "beta"  => "gamma",
            ],
        ];
        $result = Json\Json::encode($data);

        $this->assertEquals(
            '[{"alpha":{"__className":"Zend\\\\Json\\\\Expr"},"beta":"gamma"}]',
            $result
        );
    }

    /**
     * @group ZF-4054
     */
    public function testEncodeWithUtf8IsTransformedToPackedSyntax()
    {
        $data = ["Отмена"];
        $result = Json\Encoder::encode($data);

        $this->assertEquals('["\u041e\u0442\u043c\u0435\u043d\u0430"]', $result);
    }

    /**
     * @group ZF-4054
     *
     * This test contains assertions from the Solar Framework by Paul M. Jones
     * @link http://solarphp.com
     */
    public function testEncodeWithUtf8IsTransformedSolarRegression()
    {
        $expect = '"h\u00c3\u00a9ll\u00c3\u00b6 w\u00c3\u00b8r\u00c5\u201ad"';
        $this->assertEquals($expect, Json\Encoder::encode('hÃ©llÃ¶ wÃ¸rÅ‚d'));
        $this->assertEquals('hÃ©llÃ¶ wÃ¸rÅ‚d', Json\Decoder::decode($expect));

        $expect = '"\u0440\u0443\u0441\u0441\u0438\u0448"';
        $this->assertEquals($expect, Json\Encoder::encode("руссиш"));
        $this->assertEquals("руссиш", Json\Decoder::decode($expect));
    }

    /**
     * @group ZF-4054
     */
    public function testEncodeUnicodeStringSolarRegression()
    {
        $value    = 'hÃ©llÃ¶ wÃ¸rÅ‚d';
        $expected = 'h\u00c3\u00a9ll\u00c3\u00b6 w\u00c3\u00b8r\u00c5\u201ad';
        $this->assertEquals($expected, Json\Encoder::encodeUnicodeString($value));

        $value    = "\xC3\xA4";
        $expected = '\u00e4';
        $this->assertEquals($expected, Json\Encoder::encodeUnicodeString($value));

        $value    = "\xE1\x82\xA0\xE1\x82\xA8";
        $expected = '\u10a0\u10a8';
        $this->assertEquals($expected, Json\Encoder::encodeUnicodeString($value));
    }

    /**
     * @group ZF-4054
     */
    public function testDecodeUnicodeStringSolarRegression()
    {
        $expected = 'hÃ©llÃ¶ wÃ¸rÅ‚d';
        $value    = 'h\u00c3\u00a9ll\u00c3\u00b6 w\u00c3\u00b8r\u00c5\u201ad';
        $this->assertEquals($expected, Json\Decoder::decodeUnicodeString($value));

        $expected = "\xC3\xA4";
        $value    = '\u00e4';
        $this->assertEquals($expected, Json\Decoder::decodeUnicodeString($value));

        $value    = '\u10a0';
        $expected = "\xE1\x82\xA0";
        $this->assertEquals($expected, Json\Decoder::decodeUnicodeString($value));
    }

    /**
     * @group ZF-4054
     *
     * This test contains assertions from the Solar Framework by Paul M. Jones
     * @link http://solarphp.com
     */
    public function testEncodeWithUtf8IsTransformedSolarRegressionEqualsJSONExt()
    {
        if (function_exists('json_encode') == false) {
            $this->markTestSkipped('Test can only be run, when ext/json is installed.');
        }

        $this->assertEquals(
            json_encode('hÃ©llÃ¶ wÃ¸rÅ‚d'),
            Json\Encoder::encode('hÃ©llÃ¶ wÃ¸rÅ‚d')
        );

        $this->assertEquals(
            json_encode("руссиш"),
            Json\Encoder::encode("руссиш")
        );
    }

    /**
     * @group ZF-4946
     */
    public function testUtf8JSONExprFinder()
    {
        $data = ["Отмена" => new Json\Expr("foo")];

        Json\Json::$useBuiltinEncoderDecoder = true;
        $result = Json\Json::encode($data, false, ['enableJsonExprFinder' => true]);
        $this->assertEquals('{"\u041e\u0442\u043c\u0435\u043d\u0430":foo}', $result);
        Json\Json::$useBuiltinEncoderDecoder = false;

        $result = Json\Json::encode($data, false, ['enableJsonExprFinder' => true]);
        $this->assertEquals('{"\u041e\u0442\u043c\u0435\u043d\u0430":foo}', $result);
    }

    /**
     * @group ZF-4437
     */
    public function testCommaDecimalIsConvertedToCorrectJSONWithDot()
    {
        setlocale(LC_ALL, 'Spanish_Spain', 'es_ES', 'es_ES.utf-8');
        if (strcmp('1,2', (string) floatval(1.20)) !== 0) {
            $this->markTestSkipped('This test only works for platforms where "," is the decimal point separator.');
        }

        Json\Json::$useBuiltinEncoderDecoder = true;

        $actual = Json\Encoder::encode([floatval(1.20), floatval(1.68)]);
        $this->assertEquals('[1.2,1.68]', $actual);
    }

    public function testEncodeObjectImplementingIterator()
    {
        $iterator = new ArrayIterator([
            'foo' => 'bar',
            'baz' => 5
        ]);
        $target = '{"__className":"ArrayIterator","foo":"bar","baz":5}';

        Json\Json::$useBuiltinEncoderDecoder = true;
        $this->assertEquals($target, Json\Json::encode($iterator));
    }

    /**
     * @group ZF-12347
     */
    public function testEncodeObjectImplementingIteratorAggregate()
    {
        $iterator = new TestAsset\TestIteratorAggregate();
        $target = '{"__className":"ZendTest\\\\Json\\\\TestAsset\\\\TestIteratorAggregate","foo":"bar","baz":5}';

        Json\Json::$useBuiltinEncoderDecoder = true;
        $this->assertEquals($target, Json\Json::encode($iterator));
    }

    /**
     * @group ZF-8663
     */
    public function testNativeJSONEncoderWillProperlyEncodeSolidusInStringValues()
    {
        $source = "</foo><foo>bar</foo>";
        $target = '"\u003C\/foo\u003E\u003Cfoo\u003Ebar\u003C\/foo\u003E"';

        // first test ext/json
        Json\Json::$useBuiltinEncoderDecoder = false;
        $this->assertEquals($target, Json\Json::encode($source));
    }

    public function testNativeJSONEncoderWillProperlyEncodeHtmlSpecialCharsInStringValues()
    {
        $source = "<>&'\"";
        $target = '"\u003C\u003E\u0026\u0027\u0022"';

        // first test ext/json
        Json\Json::$useBuiltinEncoderDecoder = false;
        $this->assertEquals($target, Json\Json::encode($source));
    }

    /**
     * @group ZF-8663
     */
    public function testBuiltinJSONEncoderWillProperlyEncodeSolidusInStringValues()
    {
        $source = "</foo><foo>bar</foo>";
        $target = '"\u003C\/foo\u003E\u003Cfoo\u003Ebar\u003C\/foo\u003E"';

        // first test ext/json
        Json\Json::$useBuiltinEncoderDecoder = true;
        $this->assertEquals($target, Json\Json::encode($source));
    }

    public function testBuiltinJSONEncoderWillProperlyEncodeHtmlSpecialCharsInStringValues()
    {
        $source = "<>&'\"";
        $target = '"\u003C\u003E\u0026\u0027\u0022"';

        // first test ext/json
        Json\Json::$useBuiltinEncoderDecoder = true;
        $this->assertEquals($target, Json\Json::encode($source));
    }

    /**
     * @group ZF-8918
     */
    public function testDecodingInvalidJSONShouldRaiseAnException()
    {
        $this->expectException(Json\Exception\RuntimeException::class);
        Json\Json::decode(' some string ');
    }

    /**
     * Encoding an iterator using the internal encoder should handle undefined keys
     *
     * @group ZF-9416
     */
    public function testIteratorWithoutDefinedKey()
    {
        $inputValue = new ArrayIterator(['foo']);
        $encoded = Json\Encoder::encode($inputValue);
        $expectedDecoding = '{"__className":"ArrayIterator",0:"foo"}';
        $this->assertEquals($expectedDecoding, $encoded);
    }

    /**
     * The default json decode type should be TYPE_OBJECT
     *
     * @group ZF-8618
     */
    public function testDefaultTypeObject()
    {
        $this->assertInstanceOf(stdClass::class, Json\Decoder::decode('{"var":"value"}'));
    }

    /**
     * @group ZF-10185
     */
    public function testJsonPrettyPrintWorksWithArrayNotationInStringLiteral()
    {
        $o = new stdClass();
        $o->test = 1;
        $o->faz = 'fubar';

        // The escaped double-quote in item 'stringwithjsonchars' ensures that
        // escaped double-quotes do not throw off string literal detection in
        // prettyPrint
        $test = [
            'simple' => 'simple test string',
            'stringwithjsonchars' => '\"[1,2]',
            'complex' => [
                'foo' => 'bar',
                'far' => 'boo',
                'faz' => [
                    'obj' => $o,
                ],
                'fay' => ['foo', 'bar'],
            ],
        ];
        $pretty = Json\Json::prettyPrint(Json\Json::encode($test), ['indent' => ' ']);
        $expected = <<<EOB
{
 "simple": "simple test string",
 "stringwithjsonchars": "\\\\\\u0022[1,2]",
 "complex": {
  "foo": "bar",
  "far": "boo",
  "faz": {
   "obj": {
    "test": 1,
    "faz": "fubar"
   }
  },
  "fay": [
   "foo",
   "bar"
  ]
 }
}
EOB;
        $this->assertSame($expected, $pretty);
    }

    public function testPrettyPrintDoublequoteFollowingEscapedBackslashShouldNotBeTreatedAsEscaped()
    {
        $this->assertEquals(
            "[\n    1,\n    \"\\\\\",\n    3\n]",
            Json\Json::prettyPrint(Json\Json::encode([1, '\\', 3]))
        );

        $this->assertEquals(
            "{\n    \"a\": \"\\\\\"\n}",
            Json\Json::prettyPrint(Json\Json::encode(['a' => '\\']))
        );
    }

    public function testPrettyPrintEmptyArray()
    {
        $original = <<<JSON
[]
JSON;

        $this->assertSame($original, Json\Json::prettyPrint($original));
    }

    public function testPrettyPrintEmptyObject()
    {
        $original = <<<JSON
{}
JSON;

        $this->assertSame($original, Json\Json::prettyPrint($original));
    }

    public function testPrettyPrintEmptyProperties()
    {
        $original = <<<JSON
{
    "foo": [],
    "bar": {}
}
JSON;

        $this->assertSame($original, Json\Json::prettyPrint($original));
    }
    public function testPrettyPrintEmptyPropertiesWithWhitespace()
    {
        $original = <<<JSON
{
"foo": [

            ],
    "bar": {
    
    
}
}
JSON;

        $pretty = <<<JSON
{
    "foo": [],
    "bar": {}
}
JSON;

        $this->assertSame($pretty, Json\Json::prettyPrint($original));
    }

    public function testPrettyPrintRePrettyPrint()
    {
        $expected = <<<EOB
{
 "simple": "simple test string",
 "stringwithjsonchars": "\\\\\\u0022[1,2]",
 "complex": {
  "foo": "bar",
  "far": "boo",
  "faz": {
   "obj": {
    "test": 1,
    "faz": "fubar"
   }
  }
 }
}
EOB;

        $this->assertSame(
            $expected,
            Json\Json::prettyPrint($expected, ['indent'  => ' '])
        );
    }

    public function testEncodeWithPrettyPrint()
    {
        $o = new stdClass();
        $o->test = 1;
        $o->faz = 'fubar';

        // The escaped double-quote in item 'stringwithjsonchars' ensures that
        // escaped double-quotes do not throw off string literal detection in
        // prettyPrint
        $test = [
            'simple' => 'simple test string',
            'stringwithjsonchars' => '\"[1,2]',
            'complex' => [
                'foo' => 'bar',
                'far' => 'boo',
                'faz' => [
                    'obj' => $o,
                ],
            ],
        ];
        $pretty = Json\Json::encode($test, false, ['prettyPrint' => true]);

        $expected = <<<EOB
{
    "simple": "simple test string",
    "stringwithjsonchars": "\\\\\\u0022[1,2]",
    "complex": {
        "foo": "bar",
        "far": "boo",
        "faz": {
            "obj": {
                "test": 1,
                "faz": "fubar"
            }
        }
    }
}
EOB;
        $this->assertSame($expected, $pretty);
    }

    /**
     * @group ZF-11167
     */
    public function testEncodeWillUseToArrayMethodWhenAvailable()
    {
        $o = new TestAsset\ZF11167ToArrayClass();
        $objJson = Json\Json::encode($o);
        $arrJson = Json\Json::encode($o->toArray());
        $this->assertSame($arrJson, $objJson);
    }

    /**
     * @group ZF-11167
     */
    public function testEncodeWillUseToJsonWhenBothToJsonAndToArrayMethodsAreAvailable()
    {
        $o = new TestAsset\ZF11167ToArrayToJsonClass();
        $objJson = Json\Json::encode($o);
        $this->assertEquals('"bogus"', $objJson);
        $arrJson = Json\Json::encode($o->toArray());
        $this->assertNotSame($objJson, $arrJson);
    }

    /**
     * @group ZF-9521
     */
    public function testWillEncodeArrayOfObjectsEachWithToJsonMethod()
    {
        $array = ['one' => new TestAsset\ToJsonClass()];

        // @codingStandardsIgnoreStart
        $expected = '{"one":{"__className":"ZendTest\\\\Json\\\\TestAsset\\\\ToJSONClass","firstName":"John","lastName":"Doe","email":"john@doe.com"}}';
        // @codingStandardsIgnoreEnd

        Json\Json::$useBuiltinEncoderDecoder = true;
        $json = Json\Encoder::encode($array);
        $this->assertEquals($expected, $json);
    }

    /**
     * @group ZF-7586
     */
    public function testWillDecodeStructureWithEmptyKeyToObjectProperly()
    {
        Json\Json::$useBuiltinEncoderDecoder = true;

        $json = '{"":"test"}';
        $object = Json\Json::decode($json, Json\Json::TYPE_OBJECT);
        $this->assertAttributeEquals('test', '_empty_', $object);
    }
}
