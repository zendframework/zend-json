<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Json\Server\Smd;

use Zend\Json\Server\Smd\Service;
use Zend\Json\Server;

/**
 * Test class for Zend\JSON\Server\Smd\Service
 *
 * @group      Zend_JSON
 * @group      Zend_JSON_Server
 * @covers  Zend\Json\Server\Smd\Service<extended>
 */
class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->service = new Service('foo');
    }

    public function testConstructorShouldThrowExceptionWhenNoNameSetWhenNullProvided()
    {
        $this->setExpectedException('Zend\Json\Server\Exception\InvalidArgumentException', 'requires a name');
        $service = new Service(null);
    }

    public function testConstructorShouldThrowExceptionWhenNoNameSetWhenArrayProvided()
    {
        $this->setExpectedException('Zend\Json\Server\Exception\InvalidArgumentException', 'requires a name');
        $service = new Service(null);
    }

    public function testSettingNameShouldThrowExceptionWhenContainingInvalidFormatStartingWithInt()
    {
        $this->setExpectedException('Zend\Json\Server\Exception\InvalidArgumentException', 'Invalid name');
        $this->service->setName('0ab-?');
    }

    public function testSettingNameShouldNotThrowExceptionWhenContainingValidFormatStartingWithUnderscore()
    {
        $this->service->setName('_getMyProperty');
        $this->assertEquals('_getMyProperty', $this->service->getName());
    }

    public function testSettingNameShouldThrowExceptionWhenContainingInvalidFormatStartingWithRpc()
    {
        $this->setExpectedException('Zend\Json\Server\Exception\InvalidArgumentException', 'Invalid name');
        $this->service->setName('rpc.Foo');
    }

    public function testSettingNameShouldThrowExceptionWhenContainingInvalidFormatStartingWithRpcWithoutPeriodChar()
    {
        $this->service->setName('rpcFoo');
        $this->assertEquals('rpcFoo', $this->service->getName());
    }

    public function testSettingNameShouldNotThrowExceptionWhenContainingInvalidFormatStartingWithRpcInsensitiveCase()
    {
        $this->service->setName('RpcFoo');
        $this->assertEquals('RpcFoo', $this->service->getName());
    }

    public function testSettingNameShouldNotThrowExceptionWhenContainingValidFormatContainingRpc()
    {
        $this->service->setName('_rpcFoo');
        $this->assertEquals('_rpcFoo', $this->service->getName());

        $this->service->setName('MyRpcFoo');
        $this->assertEquals('MyRpcFoo', $this->service->getName());
    }

    public function testNameAccessorsShouldWorkWithNormalInput()
    {
        $this->assertEquals('foo', $this->service->getName());
        $this->service->setName('bar');
        $this->assertEquals('bar', $this->service->getName());
    }

    public function testTransportShouldDefaultToPost()
    {
        $this->assertEquals('POST', $this->service->getTransport());
    }

    public function testSettingTransportThrowsExceptionWhenSetToGet()
    {
        $this->setExpectedException('Zend\Json\Server\Exception\InvalidArgumentException', 'Invalid transport');
        $this->service->setTransport('GET');
    }

    public function testSettingTransportThrowsExceptionWhenSetToRest()
    {
        $this->setExpectedException('Zend\Json\Server\Exception\InvalidArgumentException', 'Invalid transport');
        $this->service->setTransport('REST');
    }

    public function testTransportAccessorsShouldWorkUnderNormalInput()
    {
        $this->service->setTransport('POST');
        $this->assertEquals('POST', $this->service->getTransport());
    }

    public function testTargetShouldBeNullInitially()
    {
        $this->assertNull($this->service->getTarget());
    }

    public function testTargetAccessorsShouldWorkUnderNormalInput()
    {
        $this->testTargetShouldBeNullInitially();
        $this->service->setTarget('foo');
        $this->assertEquals('foo', $this->service->getTarget());
    }

    public function testTargetAccessorsShouldNormalizeToString()
    {
        $this->testTargetShouldBeNullInitially();
        $this->service->setTarget(123);
        $value = $this->service->getTarget();
        $this->assertInternalType('string', $value);
        $this->assertEquals((string) 123, $value);
    }

    public function testEnvelopeShouldBeJSONRpc1CompliantByDefault()
    {
        $this->assertEquals(Server\Smd::ENV_JSONRPC_1, $this->service->getEnvelope());
    }

    public function testEnvelopeShouldOnlyComplyWithJSONRpc1And2()
    {
        $this->testEnvelopeShouldBeJSONRpc1CompliantByDefault();
        $this->service->setEnvelope(Server\Smd::ENV_JSONRPC_2);
        $this->assertEquals(Server\Smd::ENV_JSONRPC_2, $this->service->getEnvelope());
        $this->service->setEnvelope(Server\Smd::ENV_JSONRPC_1);
        $this->assertEquals(Server\Smd::ENV_JSONRPC_1, $this->service->getEnvelope());
        try {
            $this->service->setEnvelope('JSON-P');
            $this->fail('Should not be able to set non-JSON-RPC spec envelopes');
        } catch (Server\Exception\InvalidArgumentException $e) {
            $this->assertContains('Invalid envelope', $e->getMessage());
        }
    }

    public function testShouldHaveNoParamsByDefault()
    {
        $params = $this->service->getParams();
        $this->assertEmpty($params);
    }

    public function testShouldBeAbleToAddParamsByTypeOnly()
    {
        $this->service->addParam('integer');
        $params = $this->service->getParams();
        $this->assertEquals(1, count($params));
        $param = array_shift($params);
        $this->assertEquals('integer', $param['type']);
    }

    public function testParamsShouldAcceptArrayOfTypes()
    {
        $type   = ['integer', 'string'];
        $this->service->addParam($type);
        $params = $this->service->getParams();
        $param  = array_shift($params);
        $test   = $param['type'];
        $this->assertInternalType('array', $test);
        $this->assertEquals($type, $test);
    }

    public function testInvalidParamTypeShouldThrowException()
    {
        $this->setExpectedException('Zend\Json\Server\Exception\InvalidArgumentException', 'Invalid param type');
        $this->service->addParam(new \stdClass);
    }

    public function testShouldBeAbleToOrderParams()
    {
        $this->service->addParam('integer', [], 4)
                      ->addParam('string')
                      ->addParam('boolean', [], 3);
        $params = $this->service->getParams();

        $this->assertEquals(3, count($params));

        $param = array_shift($params);
        $this->assertEquals('string', $param['type'], var_export($params, 1));
        $param = array_shift($params);
        $this->assertEquals('boolean', $param['type'], var_export($params, 1));
        $param = array_shift($params);
        $this->assertEquals('integer', $param['type'], var_export($params, 1));
    }

    public function testShouldBeAbleToAddArbitraryParamOptions()
    {
        $this->service->addParam(
            'integer',
            [
                'name'        => 'foo',
                'optional'    => false,
                'default'     => 1,
                'description' => 'Foo parameter',
            ]
        );
        $params = $this->service->getParams();
        $param  = array_shift($params);
        $this->assertEquals('foo', $param['name']);
        $this->assertFalse($param['optional']);
        $this->assertEquals(1, $param['default']);
        $this->assertEquals('Foo parameter', $param['description']);
    }

    public function testShouldBeAbleToAddMultipleParamsAtOnce()
    {
        $this->service->addParams([
            ['type' => 'integer', 'order' => 4],
            ['type' => 'string', 'name' => 'foo'],
            ['type' => 'boolean', 'order' => 3],
        ]);
        $params = $this->service->getParams();

        $this->assertEquals(3, count($params));
        $param = array_shift($params);
        $this->assertEquals('string', $param['type']);
        $this->assertEquals('foo', $param['name']);

        $param = array_shift($params);
        $this->assertEquals('boolean', $param['type']);

        $param = array_shift($params);
        $this->assertEquals('integer', $param['type']);
    }

    public function testSetparamsShouldOverwriteExistingParams()
    {
        $this->testShouldBeAbleToAddMultipleParamsAtOnce();
        $params = $this->service->getParams();
        $this->assertEquals(3, count($params));

        $this->service->setParams([
            ['type' => 'string'],
            ['type' => 'integer'],
        ]);
        $test = $this->service->getParams();
        $this->assertNotEquals($params, $test);
        $this->assertEquals(2, count($test));
    }

    public function testReturnShouldBeNullByDefault()
    {
        $this->assertNull($this->service->getReturn());
    }

    public function testReturnAccessorsShouldWorkWithNormalInput()
    {
        $this->testReturnShouldBeNullByDefault();
        $this->service->setReturn('integer');
        $this->assertEquals('integer', $this->service->getReturn());
    }

    public function testReturnAccessorsShouldAllowArrayOfTypes()
    {
        $this->testReturnShouldBeNullByDefault();
        $type = ['integer', 'string'];
        $this->service->setReturn($type);
        $this->assertEquals($type, $this->service->getReturn());
    }

    public function testInvalidReturnTypeShouldThrowException()
    {
        $this->setExpectedException('Zend\Json\Server\Exception\InvalidArgumentException', 'Invalid param type');
        $this->service->setReturn(new \stdClass);
    }

    public function testToArrayShouldCreateSmdCompatibleHash()
    {
        $this->setupSmdValidationObject();
        $smd = $this->service->toArray();
        $this->validateSmdArray($smd);
    }

    public function testTojsonShouldEmitJSON()
    {
        $this->setupSmdValidationObject();
        $json = $this->service->toJSON();
        $smd  = \Zend\Json\Json::decode($json, \Zend\Json\Json::TYPE_ARRAY);

        $this->assertArrayHasKey('foo', $smd);
        $this->assertInternalType('array', $smd['foo']);

        $this->validateSmdArray($smd['foo']);
    }

    public function setupSmdValidationObject()
    {
        $this->service->setName('foo')
                      ->setTransport('POST')
                      ->setTarget('/foo')
                      ->setEnvelope(Server\Smd::ENV_JSONRPC_2)
                      ->addParam('boolean')
                      ->addParam('array')
                      ->addParam('object')
                      ->setReturn('boolean');
    }

    public function validateSmdArray(array $smd)
    {
        $this->assertArrayHasKey('transport', $smd);
        $this->assertEquals('POST', $smd['transport']);

        $this->assertArrayHasKey('envelope', $smd);
        $this->assertEquals(Server\Smd::ENV_JSONRPC_2, $smd['envelope']);

        $this->assertArrayHasKey('parameters', $smd);
        $params = $smd['parameters'];
        $this->assertEquals(3, count($params));
        $param = array_shift($params);
        $this->assertEquals('boolean', $param['type']);
        $param = array_shift($params);
        $this->assertEquals('array', $param['type']);
        $param = array_shift($params);
        $this->assertEquals('object', $param['type']);

        $this->assertArrayHasKey('returns', $smd);
        $this->assertEquals('boolean', $smd['returns']);
    }
}
