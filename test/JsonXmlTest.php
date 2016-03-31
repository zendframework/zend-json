<?php
/**
 * @link      http://github.com/zendframework/zend-json for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Json;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Json;

class JsonXmlTest extends TestCase
{
    public function testXmlToJsonWithXMLContainingOnlyChildElements()
    {
        // Set the XML contents that will be tested here.
        $xmlStringContents = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<contacts>
    <contact>
        <name>
            John Doe
        </name>
        <phone>
            123-456-7890
        </phone>
    </contact>

    <contact>
        <name>
            Jane Doe
        </name>
        <phone>
            123-456-0000
        </phone>
    </contact>

    <contact>
        <name>
            John Smith
        </name>
        <phone>
            123-456-1111
        </phone>
    </contact>

    <contact>
        <name>
            Jane Smith
        </name>
        <phone>
            123-456-9999
        </phone>
    </contact>

</contacts>

EOT;

        // There are no XML attributes in this test XML, so passing boolean
        // true to the second argument, allowing them to be ignored.
        $jsonContents = Json\Json::fromXml($xmlStringContents, true);

        // Convert the JSON string into a PHP array.
        $phpArray = Json\Json::decode($jsonContents, Json\Json::TYPE_ARRAY);

        // Test if it is not a NULL object.
        $this->assertNotNull($phpArray, 'Received null value when converting XML with child elements');

        // Test for one of the expected fields in the JSON result.
        $this->assertSame(
            'Jane Smith',
            $phpArray['contacts']['contact'][3]['name'],
            'The last contact name converted from XML input 1 is not correct'
        );
    }

    public function testXmlToJsonWithXmlContainingAttributes()
    {
        // Set the XML contents that will be tested here.
        $xmlStringContents = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<books>
    <book id="1">
        <title>Code Generation in Action</title>
        <author><first>Jack</first><last>Herrington</last></author>
        <publisher>Manning</publisher>
    </book>
    <book id="2">
        <title>PHP Hacks</title>
        <author><first>Jack</first><last>Herrington</last></author>
        <publisher>O'Reilly</publisher>
    </book>
    <book id="3">
        <title>Podcasting Hacks</title>
        <author><first>Jack</first><last>Herrington</last></author>
        <publisher>O'Reilly</publisher>
    </book>
</books>

EOT;

        // There ARE XML attributes in this test XML, so passing boolean
        // false to the second argument, specifying they should be returned.
        $jsonContents = Json\Json::fromXml($xmlStringContents, false);

        // Convert the JSON string into a PHP array.
        $phpArray = Json\Json::decode($jsonContents, Json\Json::TYPE_ARRAY);

        // Test if it is not a NULL object.
        $this->assertNotNull($phpArray, 'Received null value when converting XML with child elements and attributes');

        // Test for one of the expected fields in the JSON result.
        $this->assertSame(
            'Podcasting Hacks',
            $phpArray['books']['book'][2]['title'],
            'The last book title converted is incorrect'
        );

        // Test one of the expected XML attributes carried over in the JSON result.
        $this->assertSame(
            '3',
            $phpArray['books']['book'][2]['@attributes']['id'],
            'The last id attribute converted from XML is incorrect'
        );
    }

    public function testXmlToJsonWithXmlContainingNestedChildren()
    {
        // Set the XML contents that will be tested here.
        $xmlStringContents = <<<EOT
<?xml version="1.0" encoding="ISO-8859-1" ?>
<breakfast_menu>
    <food>
        <name>Belgian Waffles</name>
        <price>$5.95</price>
        <description>
            two of our famous Belgian Waffles with plenty of real maple
            syrup
        </description>
        <calories>650</calories>
    </food>
    <food>
        <name>Strawberry Belgian Waffles</name>
        <price>$7.95</price>
        <description>
            light Belgian waffles covered with strawberries and whipped
            cream
        </description>
        <calories>900</calories>
    </food>
    <food>
        <name>Berry-Berry Belgian Waffles</name>
        <price>$8.95</price>
        <description>
            light Belgian waffles covered with an assortment of fresh
            berries and whipped cream
        </description>
        <calories>900</calories>
    </food>
    <food>
        <name>French Toast</name>
        <price>$4.50</price>
        <description>
            thick slices made from our homemade sourdough bread
        </description>
        <calories>600</calories>
    </food>
    <food>
        <name>Homestyle Breakfast</name>
        <price>$6.95</price>
        <description>
            two eggs, bacon or sausage, toast, and our ever-popular hash
            browns
        </description>
        <calories>950</calories>
    </food>
</breakfast_menu>
EOT;

        // No attributes in the XML, so safe to ignore attributes in the
        // conversion.
        $jsonContents = Json\Json::fromXml($xmlStringContents, true);

        // Convert the JSON string into a PHP array.
        $phpArray = Json\Json::decode($jsonContents, Json\Json::TYPE_ARRAY);

        // Test if it is not a NULL object.
        $this->assertNotNull($phpArray, 'NULL result received when converting XML with nested children');

        // Test for one of the expected fields in the JSON result.
        $this->assertContains(
            'Homestyle Breakfast',
            $phpArray['breakfast_menu']['food'][4],
            'The last breakfast item name converted from XML is incorrect'
        );
    }

    public function testXmlToJsonWithXmlContainingChildElementsAndMultipleAttributes()
    {
        // @codingStandardsIgnoreStart
        // Set the XML contents that will be tested here.
        $xmlStringContents = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<PurchaseRequisition>
    <Submittor>
        <SubmittorName>John Doe</SubmittorName>
        <SubmittorEmail>john@nodomain.net</SubmittorEmail>
        <SubmittorTelephone>1-123-456-7890</SubmittorTelephone>
    </Submittor>
    <Billing/>
    <Approval/>
    <Item number="1">
        <ItemType>Electronic Component</ItemType>
        <ItemDescription>25 microfarad 16 volt surface-mount tantalum capacitor</ItemDescription>
        <ItemQuantity>42</ItemQuantity>
        <Specification>
            <Category type="UNSPSC" value="32121501" name="Fixed capacitors"/>
            <RosettaNetSpecification>
                <query max.records="1">
                    <element dicRef="XJA039">
                        <name>CAPACITOR - FIXED - TANTAL - SOLID</name>
                    </element>
                    <element>
                        <name>Specific Features</name>
                        <value>R</value>
                    </element>
                    <element>
                        <name>Body Material</name>
                        <value>C</value>
                    </element>
                    <element>
                        <name>Terminal Position</name>
                        <value>A</value>
                    </element>
                    <element>
                        <name>Package: Outline Style</name>
                        <value>CP</value>
                    </element>
                    <element>
                        <name>Lead Form</name>
                        <value>D</value>
                    </element>
                    <element>
                        <name>Rated Capacitance</name>
                        <value>0.000025</value>
                    </element>
                    <element>
                        <name>Tolerance On Rated Capacitance (%)</name>
                        <value>10</value>
                    </element>
                    <element>
                        <name>Leakage Current (Short Term)</name>
                        <value>0.0000001</value>
                    </element>
                    <element>
                        <name>Rated Voltage</name>
                        <value>16</value>
                    </element>
                    <element>
                        <name>Operating Temperature</name>
                        <value type="max">140</value>
                        <value type="min">-10</value>
                    </element>
                    <element>
                        <name>Mounting</name>
                        <value>Surface</value>
                    </element>
                </query>
            </RosettaNetSpecification>
        </Specification>
        <Vendor number="1">
            <VendorName>Capacitors 'R' Us, Inc.</VendorName>
            <VendorIdentifier>98-765-4321</VendorIdentifier>
            <VendorImplementation>http://sylviaearle/capaciorsRus/wsdl/buyerseller-implementation.wsdl</VendorImplementation>
        </Vendor>
    </Item>
</PurchaseRequisition>
EOT;
        // @codingStandardsIgnoreEnd

        // Attributes are expected in converted JSON.
        $jsonContents = Json\Json::fromXml($xmlStringContents, false);

        // Convert the JSON string into a PHP array.
        $phpArray = Json\Json::decode($jsonContents, Json\Json::TYPE_ARRAY);

        // Test if it is not a NULL object.
        $this->assertNotNull(
            $phpArray,
            'Null received when converting XML with nested children and multiple attributes'
        );

        // Test for one of the expected fields in the JSON result.
        $this->assertContains(
            '98-765-4321',
            $phpArray['PurchaseRequisition']['Item']['Vendor'],
            'The vendor id converted from XML is incorrect'
        );

        // Test for the presence of multiple XML attributes in the resultant JSON.
        $this->assertContains(
            'UNSPSC',
            $phpArray['PurchaseRequisition']['Item']['Specification']['Category']['@attributes'],
            'The type attribute converted from XML is incorrect'
        );
        $this->assertContains(
            '32121501',
            $phpArray['PurchaseRequisition']['Item']['Specification']['Category']['@attributes'],
            'The value attribute converted from XML is incorrect'
        );
        $this->assertContains(
            'Fixed capacitors',
            $phpArray['PurchaseRequisition']['Item']['Specification']['Category']['@attributes'],
            'The name attribute converted from XML is incorrect'
        );
    }

    public function testXmlToJsonWithXmlContainingInlineCDATA()
    {
        // Set the XML contents that will be tested here.
        $xmlStringContents = <<<EOT
<?xml version="1.0"?>
<tvshows>
    <show>
        <name>The Simpsons</name>
    </show>

    <show>
        <name><![CDATA[Lois & Clark]]></name>
    </show>
</tvshows>
EOT;

        // No attributes to convert.
        $jsonContents = Json\Json::fromXml($xmlStringContents, false);

        // Convert the JSON string into a PHP array.
        $phpArray = Json\Json::decode($jsonContents, Json\Json::TYPE_ARRAY);

        // Test if it is not a NULL object.
        $this->assertNotNull($phpArray, 'Null result received for XML containing inline CDATA');

        // Test for one of the expected CDATA fields in the JSON result.
        $this->assertContains(
            'Lois & Clark',
            $phpArray['tvshows']['show'][1]['name'],
            'The CDATA name converted from XML is incorrect'
        );
    }

    public function testXmlToJsonWithXmlContainingMultilineCDATA()
    {
        // Set the XML contents that will be tested here.
        $xmlStringContents = <<<EOT
<?xml version="1.0"?>
<demo>
    <application>
        <name>Killer Demo</name>
    </application>

    <author>
        <name>John Doe</name>
    </author>

    <platform>
        <name>LAMP</name>
    </platform>

    <framework>
        <name>Zend</name>
    </framework>

    <language>
        <name>PHP</name>
    </language>

    <listing>
        <code>
            <![CDATA[
/*
It may not be a syntactically valid PHP code.
It is used here just to illustrate the CDATA feature of Zend_Xml2JSON
*/
<?php
include 'example.php';
new SimpleXMLElement();
echo(getMovies()->movie[0]->characters->addChild('character'));
getMovies()->movie[0]->characters->character->addChild('name', "Mr. Parser");
getMovies()->movie[0]->characters->character->addChild('actor', "John Doe");
// Add it as a child element.
getMovies()->movie[0]->addChild('rating', 'PG');
getMovies()->movie[0]->rating->addAttribute("type", 'mpaa');
echo getMovies()->asXML();
?>
            ]]>
        </code>
    </listing>
</demo>

EOT;

        // No attributes to convert.
        $jsonContents = Json\Json::fromXml($xmlStringContents, true);

        // Convert the JSON string into a PHP array.
        $phpArray = Json\Json::decode($jsonContents, Json\Json::TYPE_ARRAY);

        // Test if it is not a NULL object.
        $this->assertNotNull($phpArray, 'Null result received for XML containing multiline CDATA');

        // Test for one of the expected fields in the JSON result.
        $this->assertContains(
            'Zend',
            $phpArray['demo']['framework']['name'],
            'The framework name field converted from XML is incorrect'
        );

        // Test for one of the expected CDATA fields in the JSON result.
        $this->assertContains(
            'echo getMovies()->asXML();',
            $phpArray['demo']['listing']['code'],
            'The CDATA code converted from XML is incorrect'
        );
    }

    /**
     *  @group ZF-3257
     */
    public function testXmlToJsonWithXmlContainingSelfClosingElement()
    {
        // Set the XML contents that will be tested here.
        $xmlStringContents = <<<EOT
<?xml version="1.0"?>
<a><b id="foo" />bar</a>

EOT;

        // We want to convert attributes.
        $jsonContents = Json\Json::fromXml($xmlStringContents, false);

        // Convert the JSON string into a PHP array.
        $phpArray = Json\Json::decode($jsonContents, Json\Json::TYPE_ARRAY);

        // Test if it is not a NULL object.
        $this->assertNotNull($phpArray, 'Null result resceived for XML containing self-closing element');

        $this->assertSame('bar', $phpArray['a']['@text'], 'The text element of a is incorrect');
        $this->assertSame('foo', $phpArray['a']['b']['@attributes']['id'], 'The id attribute of b is incorrect');
    }

    /**
     * @group ZF-11385
     * @expectedException Zend\Json\Exception\RecursionException
     * @dataProvider providerNestingDepthIsHandledProperly
     */
    public function testNestingDepthIsHandledProperlyWhenNestingDepthExceedsMaximum($xmlStringContents)
    {
        Json\Json::$maxRecursionDepthAllowed = 1;
        Json\Json::fromXml($xmlStringContents, true);
    }

    /**
     * @group ZF-11385
     * @dataProvider providerNestingDepthIsHandledProperly
     */
    public function testNestingDepthIsHandledProperlyWhenNestingDepthDoesNotExceedMaximum($xmlStringContents)
    {
        Json\Json::$maxRecursionDepthAllowed = 25;
        $jsonString = Json\Json::fromXml($xmlStringContents, true);
        $jsonArray = Json\Json::decode($jsonString, Json\Json::TYPE_ARRAY);
        $this->assertNotNull($jsonArray, "JSON decode result is NULL");
        $this->assertSame('A', $jsonArray['response']['message_type']['defaults']['close_rules']['after_responses']);
    }

    /**
     * XML document provider for ZF-11385 tests
     * @return array
     */
    public static function providerNestingDepthIsHandledProperly()
    {
        $xmlStringContents = <<<EOT
<response>
    <status>success</status>
    <description>200 OK</description>
    <message_type>
        <system_name>A</system_name>
        <shortname>B</shortname>
        <long_name>C</long_name>
        <as_verb>D</as_verb>
        <as_noun>E</as_noun>
        <challenge_phrase>F</challenge_phrase>
        <recipient_details>G</recipient_details>
        <sender_details>H</sender_details>
        <example_text>A</example_text>
        <short_description>B</short_description>
        <long_description>C</long_description>
        <version>D</version>
        <developer>E</developer>
        <config_instructions>A</config_instructions>
        <config_fragment>B</config_fragment>
        <icon_small>C</icon_small>
        <icon_medium>D</icon_medium>
        <icon_large>E</icon_large>
        <defaults>
            <close_rules>
                <after_responses>A</after_responses>
            </close_rules>
            <recipient_visibility>B</recipient_visibility>
            <recipient_invite>C</recipient_invite>
            <results_visibility>A</results_visibility>
            <response_visibility>B</response_visibility>
            <recipient_resubmit>C</recipient_resubmit>
            <feed_status>D</feed_status>
        </defaults>
    </message_type>
    <execution_time>0.0790269374847</execution_time>
</response>
EOT;
        return [[$xmlStringContents]];
    }
}
