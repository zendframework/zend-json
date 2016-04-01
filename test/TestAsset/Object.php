<?php
/**
 * @link      http://github.com/zendframework/zend-json for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Json\TestAsset;

/**
 * Test class for encoding classes.
 */
class Object
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
