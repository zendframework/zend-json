<?php
/**
 * @link      http://github.com/zendframework/zend-json for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Json\TestAsset;

use Zend\Stdlib\JsonSerializable;

class JsonSerializableZFImpl implements JsonSerializable
{
    public function jsonSerialize()
    {
        return [__FUNCTION__];
    }
}
