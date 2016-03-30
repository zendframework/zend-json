<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Json\Server;

use Zend\Json\Server\Smd;
use Zend\Json\Server\Exception\InvalidArgumentException;
use Zend\Json\Server\Exception\RuntimeException;
use Zend\Json;

/**
 * Test class for Zend\JSON\Server\Smd
 *
 * @group      Zend_Json
 * @group      Zend_Json_Server
 * @covers  Zend\Json\Server\SMD<extended>
 */
class SmdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->smd = new SMD();
    }

    public function testTransportShouldDefaultToPost()
    {
        $this->assertEquals('POST', $this->smd->getTransport());
    }

    public function testTransportAccessorsShouldWorkUnderNormalInput()
    {
        $this->smd->setTransport('POST');
        $this->assertEquals('POST', $this->smd->getTransport());
    }

    public function testTransportShouldBeLimitedToPost()
    {
        foreach (['GET', 'REST'] as $transport) {
            try {
                $this->smd->setTransport($transport);
                $this->fail('Invalid transport should throw exception');
            } catch (InvalidArgumentException $e) {
                $this->assertContains('Invalid transport', $e->getMessage());
            }
        }
    }

    public function testEnvelopeShouldDefaultToJSONRpcVersion1()
    {
        $this->assertEquals(Smd::ENV_JSONRPC_1, $this->smd->getEnvelope());
    }

    public function testEnvelopeAccessorsShouldWorkUnderNormalInput()
    {
        $this->testEnvelopeShouldDefaultToJSONRpcVersion1();
        $this->smd->setEnvelope(Smd::ENV_JSONRPC_2);
        $this->assertEquals(Smd::ENV_JSONRPC_2, $this->smd->getEnvelope());
        $this->smd->setEnvelope(Smd::ENV_JSONRPC_1);
        $this->assertEquals(Smd::ENV_JSONRPC_1, $this->smd->getEnvelope());
    }

    public function testEnvelopeShouldBeLimitedToJSONRpcVersions()
    {
        foreach (['URL', 'PATH', 'JSON'] as $env) {
            try {
                $this->smd->setEnvelope($env);
                $this->fail('Invalid envelope type should throw exception');
            } catch (InvalidArgumentException $e) {
                $this->assertContains('Invalid envelope', $e->getMessage());
            }
        }
    }

    public function testContentTypeShouldDefaultToApplicationJSON()
    {
        $this->assertEquals('application/json', $this->smd->getContentType());
    }

    public function testContentTypeAccessorsShouldWorkUnderNormalInput()
    {
        foreach (['text/json', 'text/plain', 'application/x-json'] as $type) {
            $this->smd->setContentType($type);
            $this->assertEquals($type, $this->smd->getContentType());
        }
    }

    public function testContentTypeShouldBeLimitedToMimeFormatStrings()
    {
        foreach (['plain', 'json', 'foobar'] as $type) {
            try {
                $this->smd->setContentType($type);
                $this->fail('Invalid content type should raise exception');
            } catch (InvalidArgumentException $e) {
                $this->assertContains('Invalid content type', $e->getMessage());
            }
        }
    }

    public function testTargetShouldDefaultToNull()
    {
        $this->assertNull($this->smd->getTarget());
    }

    public function testTargetAccessorsShouldWorkUnderNormalInput()
    {
        $this->testTargetShouldDefaultToNull();
        $this->smd->setTarget('foo');
        $this->assertEquals('foo', $this->smd->getTarget());
    }

    public function testIdShouldDefaultToNull()
    {
        $this->assertNull($this->smd->getId());
    }

    public function testIdAccessorsShouldWorkUnderNormalInput()
    {
        $this->testIdShouldDefaultToNull();
        $this->smd->setId('foo');
        $this->assertEquals('foo', $this->smd->getId());
    }

    public function testDescriptionShouldDefaultToNull()
    {
        $this->assertNull($this->smd->getDescription());
    }

    public function testDescriptionAccessorsShouldWorkUnderNormalInput()
    {
        $this->testDescriptionShouldDefaultToNull();
        $this->smd->setDescription('foo');
        $this->assertEquals('foo', $this->smd->getDescription());
    }

    public function testDojoCompatibilityShouldBeDisabledByDefault()
    {
        $this->assertFalse($this->smd->isDojoCompatible());
    }

    public function testDojoCompatibilityFlagShouldBeMutable()
    {
        $this->testDojoCompatibilityShouldBeDisabledByDefault();
        $this->smd->setDojoCompatible(true);
        $this->assertTrue($this->smd->isDojoCompatible());
        $this->smd->setDojoCompatible(false);
        $this->assertFalse($this->smd->isDojoCompatible());
    }

    public function testServicesShouldBeEmptyByDefault()
    {
        $services = $this->smd->getServices();
        $this->assertInternalType('array', $services);
        $this->assertEmpty($services);
    }

    public function testShouldBeAbleToUseServiceObjectToAddService()
    {
        $service = new Smd\Service('foo');
        $this->smd->addService($service);
        $this->assertSame($service, $this->smd->getService('foo'));
    }

    public function testShouldBeAbleToUseArrayToAddService()
    {
        $service = [
            'name' => 'foo',
        ];
        $this->smd->addService($service);
        $foo = $this->smd->getService('foo');
        $this->assertInstanceOf('Zend\Json\Server\Smd\Service', $foo);
        $this->assertEquals('foo', $foo->getName());
    }

    public function testAddingServiceWithExistingServiceNameShouldThrowException()
    {
        $service = new Smd\Service('foo');
        $this->smd->addService($service);
        $test    = new Smd\Service('foo');
        try {
            $this->smd->addService($test);
            $this->fail('Adding service with existing service name should throw exception');
        } catch (RuntimeException $e) {
            $this->assertContains('already register', $e->getMessage());
        }
    }

    public function testAttemptingToRegisterInvalidServiceShouldThrowException()
    {
        foreach (['foo', false, 1, 1.0] as $service) {
            try {
                $this->smd->addService($service);
                $this->fail('Attempt to register invalid service should throw exception');
            } catch (InvalidArgumentException $e) {
                $this->assertContains('Invalid service', $e->getMessage());
            }
        }
    }

    public function testShouldBeAbleToAddManyServicesAtOnceWithArrayOfServiceObjects()
    {
        $one   = new Smd\Service('one');
        $two   = new Smd\Service('two');
        $three = new Smd\Service('three');
        $services = [$one, $two, $three];
        $this->smd->addServices($services);
        $test = $this->smd->getServices();
        $this->assertSame($services, array_values($test));
    }

    public function testShouldBeAbleToAddManyServicesAtOnceWithArrayOfArrays()
    {
        $services = [
            ['name' => 'one'],
            ['name' => 'two'],
            ['name' => 'three'],
        ];
        $this->smd->addServices($services);
        $test = $this->smd->getServices();
        $this->assertSame(['one', 'two', 'three'], array_keys($test));
    }

    public function testShouldBeAbleToAddManyServicesAtOnceWithMixedArrayOfObjectsAndArrays()
    {
        $two = new Smd\Service('two');
        $services = [
            ['name' => 'one'],
            $two,
            ['name' => 'three'],
        ];
        $this->smd->addServices($services);
        $test = $this->smd->getServices();
        $this->assertSame(['one', 'two', 'three'], array_keys($test));
        $this->assertEquals($two, $test['two']);
    }

    public function testSetServicesShouldOverwriteExistingServices()
    {
        $this->testShouldBeAbleToAddManyServicesAtOnceWithMixedArrayOfObjectsAndArrays();
        $five = new Smd\Service('five');
        $services = [
            ['name' => 'four'],
            $five,
            ['name' => 'six'],
        ];
        $this->smd->setServices($services);
        $test = $this->smd->getServices();
        $this->assertSame(['four', 'five', 'six'], array_keys($test));
        $this->assertEquals($five, $test['five']);
    }

    public function testShouldBeAbleToRetrieveServiceByName()
    {
        $this->testShouldBeAbleToUseServiceObjectToAddService();
    }

    public function testShouldBeAbleToRemoveServiceByName()
    {
        $this->testShouldBeAbleToUseServiceObjectToAddService();
        $this->assertTrue($this->smd->removeService('foo'));
        $this->assertFalse($this->smd->getService('foo'));
    }

    public function testShouldBeAbleToCastToArray()
    {
        $options = $this->getOptions();
        $this->smd->setOptions($options);
        $service = $this->smd->toArray();
        $this->validateServiceArray($service, $options);
    }

    public function testShouldBeAbleToCastToDojoArray()
    {
        $options = $this->getOptions();
        $this->smd->setOptions($options);
        $smd = $this->smd->toDojoArray();

        $this->assertInternalType('array', $smd);

        $this->assertArrayHasKey('SMDVersion', $smd);
        $this->assertArrayHasKey('serviceType', $smd);
        $this->assertArrayHasKey('methods', $smd);

        $this->assertEquals('.1', $smd['SMDVersion']);
        $this->assertEquals('JSON-RPC', $smd['serviceType']);
        $methods = $smd['methods'];
        $this->assertEquals(2, count($methods));

        $foo = array_shift($methods);
        $this->assertArrayHasKey('name', $foo);
        $this->assertArrayHasKey('serviceURL', $foo);
        $this->assertArrayHasKey('parameters', $foo);
        $this->assertEquals('foo', $foo['name']);
        $this->assertEquals($this->smd->getTarget(), $foo['serviceURL']);
        $this->assertInternalType('array', $foo['parameters']);
        $this->assertEquals(1, count($foo['parameters']));

        $bar = array_shift($methods);
        $this->assertArrayHasKey('name', $bar);
        $this->assertArrayHasKey('serviceURL', $bar);
        $this->assertArrayHasKey('parameters', $bar);
        $this->assertEquals('bar', $bar['name']);
        $this->assertEquals($this->smd->getTarget(), $bar['serviceURL']);
        $this->assertInternalType('array', $bar['parameters']);
        $this->assertEquals(1, count($bar['parameters']));
    }

    public function testShouldBeAbleToRenderAsJSON()
    {
        $options = $this->getOptions();
        $this->smd->setOptions($options);
        $json = $this->smd->toJSON();
        $smd  = Json\Json::decode($json, Json\Json::TYPE_ARRAY);
        $this->validateServiceArray($smd, $options);
    }

    public function testToStringImplementationShouldProxyToJSON()
    {
        $options = $this->getOptions();
        $this->smd->setOptions($options);
        $json = $this->smd->__toString();
        $smd  = Json\Json::decode($json, Json\Json::TYPE_ARRAY);
        $this->validateServiceArray($smd, $options);
    }

    public function getOptions()
    {
        return [
            'target'   => '/test/me',
            'id'       => '/test/me',
            'services' => [
                [
                    'name'   => 'foo',
                    'params' => [
                        ['type' => 'boolean'],
                    ],
                    'return' => 'boolean',
                ],
                [
                    'name'   => 'bar',
                    'params' => [
                        ['type' => 'integer'],
                    ],
                    'return' => 'string',
                ],
            ]
        ];
    }

    public function validateServiceArray(array $smd, array $options)
    {
        $this->assertInternalType('array', $smd);

        $this->assertArrayHasKey('SMDVersion', $smd);
        $this->assertArrayHasKey('target', $smd);
        $this->assertArrayHasKey('id', $smd);
        $this->assertArrayHasKey('transport', $smd);
        $this->assertArrayHasKey('envelope', $smd);
        $this->assertArrayHasKey('contentType', $smd);
        $this->assertArrayHasKey('services', $smd);

        $this->assertEquals(Smd::SMD_VERSION, $smd['SMDVersion']);
        $this->assertEquals($options['target'], $smd['target']);
        $this->assertEquals($options['id'], $smd['id']);
        $this->assertEquals($this->smd->getTransport(), $smd['transport']);
        $this->assertEquals($this->smd->getEnvelope(), $smd['envelope']);
        $this->assertEquals($this->smd->getContentType(), $smd['contentType']);
        $services = $smd['services'];
        $this->assertEquals(2, count($services));
        $this->assertArrayHasKey('foo', $services);
        $this->assertArrayHasKey('bar', $services);
    }

    /**
     * @group ZF2-5624
     */
    public function testSetOptionsShouldAccommodateToArrayOutput()
    {
        $smdSource = new Smd();
        $smdSource->setContentType('application/json');
        $smdSource->setDescription('description');
        $smdSource->setEnvelope(Smd::ENV_JSONRPC_1);
        $smdSource->setId(uniqid());
        $smdSource->setTarget('http://foo');
        $smdSource->setTransport('POST');
        $smdSource->setServices([
            ['name' => 'foo']
        ]);

        $smdDestination = new Smd();
        // prior to fix the following resulted in:
        // .. Zend\Json\Server\Exception\InvalidArgumentException
        // ... : SMD service description requires a name; none provided
        $smdDestination->setOptions($smdSource->toArray());

        $this->assertEquals($smdSource->getContentType(),
            $smdDestination->getContentType());
        $this->assertEquals($smdSource->getDescription(),
            $smdDestination->getDescription());
        $this->assertEquals($smdSource->getEnvelope(),
            $smdDestination->getEnvelope());
        $this->assertEquals($smdSource->getId(),
            $smdDestination->getId());
        $this->assertEquals($smdSource->getTarget(),
            $smdDestination->getTarget());
        $this->assertEquals($smdSource->getTransport(),
            $smdDestination->getTransport());
        $this->assertEquals($smdSource->getServices(),
            $smdDestination->getServices());
    }
}
