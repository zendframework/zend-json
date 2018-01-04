<?php
/**
 * @see       https://github.com/zendframwork/zend-json for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframwork/zend-json/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Json\TestAsset;

use Exception;

class Bar
{
    protected $val;

    public function __construct($someval)
    {
        $this->val = $someval;
    }

    /**
     * Bar
     *
     * @param  bool $one
     * @param  string $two
     * @param  mixed $three
     * @return array
     */
    public function foo($one, $two = 'two', $three = null)
    {
        return [$one, $two, $three, $this->val];
    }

    /**
     * Baz
     *
     * @return void
     * @throws Exception
     */
    public function baz()
    {
        throw new Exception('application error');
    }
}
