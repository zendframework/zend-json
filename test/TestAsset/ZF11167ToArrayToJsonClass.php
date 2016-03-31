<?php
/**
 * @link      http://github.com/zendframework/zend-json for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Json\TestAsset;

use Zend\Json\Json;

/**
 * Serializable class exposing both toArray() and toJson() methods.
 */
class ZF11167ToArrayToJsonClass extends ZF11167ToArrayClass
{
    public function toJson()
    {
        return Json::encode('bogus');
    }
}
