<?php // @codingStandardsIgnoreFile
/**
 * @link      http://github.com/zendframework/zend-json for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Json\TestAsset;

use Zend\Json\Expr;
use Zend\Json\Json;

/**
 * ISSUE  ZF-4946
 */
class ToJSONWithExpr
{
    private $_string = 'text';
    private $_int = 9;
    private $_expr = 'window.alert("Zend JSON Expr")';

    public function toJSON()
    {
        $data = [
            'expr'   => new Expr($this->_expr),
            'int'    => $this->_int,
            'string' => $this->_string
        ];

        return Json::encode($data, false, ['enableJsonExprFinder' => true]);
    }
}
