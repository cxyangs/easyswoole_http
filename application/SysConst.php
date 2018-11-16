<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | 系统常量定义  Version 2018/11/14
// +----------------------------------------------------------------------
namespace App;

class SysConst extends \EasySwoole\EasySwoole\SysConst
{
    /**
     * http请求正常
     */
    const NORMAL = 200;
    /**
     * 未登录请求
     */
    const NO_LOGIN = 401;
    /**
     * 无访问权限
     */
    const NO_PERMISSION = 403;

    /**
     * 接口有数据
     */
    const DATA_SUCCESS = 1;
    /**
     * 接口无数据
     */
    const DATA_NULL = 0;
}
