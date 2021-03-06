<?php
/**
 * @see       https://github.com/zendframwork/zend-json for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframwork/zend-json/blob/master/LICENSE.md New BSD License
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
