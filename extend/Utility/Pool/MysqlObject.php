<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | MysqlObject.php  Version 2018/11/12
// +----------------------------------------------------------------------
namespace Extend\Utility\Pool;


use EasySwoole\Component\Pool\PoolObjectInterface;
use EasySwoole\Mysqli\Mysqli;

class MysqlObject extends Mysqli implements PoolObjectInterface
{

    function gc()
    {
        // TODO: Implement gc() method.
        // 重置为初始状态
        $this->resetDbStatus();
        // 关闭数据库连接
        $this->getMysqlClient()->close();
    }

    function objectRestore()
    {
        // TODO: Implement objectRestore() method.
        // 重置为初始状态
        $this->resetDbStatus();
    }

    function beforeUse(): bool
    {
        // TODO: Implement beforeUse() method.
        //使用前调用,当返回true，表示该对象可用。返回false，该对象失效，需要回收
        //根据个人逻辑修改,只要做好了断线处理逻辑,就可直接返回true
        return $this->getMysqlClient()->connected;
    }
}
