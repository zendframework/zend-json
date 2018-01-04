<?php
/**
 * @see       https://github.com/zendframwork/zend-json for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframwork/zend-json/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Json\TestAsset;

/**
 * Test class for encoding classes.
 */
class TestObject
{
    const FOO = 'bar';

    public $foo = 'bar';
    public $bar = 'baz';

    // @codingStandardsIgnoreStart
    protected $_foo = 'fooled you';
    // @codingStandardsIgnoreEnd

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
