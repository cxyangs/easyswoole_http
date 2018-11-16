<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | RedisObject.php  Version 2018/11/14
// +----------------------------------------------------------------------
namespace Extend\Utility\Pool;

use EasySwoole\Component\Pool\PoolObjectInterface;
use Swoole\Coroutine\Redis;
class RedisObject extends Redis implements PoolObjectInterface
{
    function gc()
    {
        // TODO: Implement gc() method.
        // 重置为初始状态
        $this->close();
    }
    function objectRestore()
    {
        // TODO: Implement objectRestore() method.
    }
    function beforeUse(): bool
    {
        // TODO: Implement beforeUse() method.
        return true;
    }
}
