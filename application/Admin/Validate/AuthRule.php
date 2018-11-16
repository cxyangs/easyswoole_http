<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | AuthRule.php  Version 2018/11/15
// +----------------------------------------------------------------------
namespace App\Admin\Validate;

use App\Common\Validate\Validate;

class AuthRule extends Validate
{
    protected $rules = [
        ['name','required|lengthMix:35','请输入权限规则|最大长度不能超过35位'],
        ['title','required|lengthMix:10','请输入权限名称|最大长度不能超过10位']
    ];
}
