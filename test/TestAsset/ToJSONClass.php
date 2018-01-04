<?php // @codingStandardsIgnoreFile
/**
 * @see       https://github.com/zendframwork/zend-json for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframwork/zend-json/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Json\TestAsset;

use Zend\Json\Json;

class ToJSONClass
{
    private $_firstName = 'John';

    private $_lastName = 'Doe';

    private $_email = 'john@doe.com';

    public function toJson()
    {
        $data = [
            'firstName' => $this->_firstName,
            'lastName'  => $this->_lastName,
            'email'     => $this->_email
        ];

        return Json::encode($data);
    }
}
