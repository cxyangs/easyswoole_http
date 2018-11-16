<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | MysqlPool.php  Version 2018/11/12
// +----------------------------------------------------------------------
namespace Extend\Utility\Pool;

use EasySwoole\Component\Pool\AbstractPool;
use EasySwoole\EasySwoole\Config;

class MysqlPool extends AbstractPool
{
    protected function createObject()
    {
        // TODO: Implement createObject() method.
        $conf = Config::getInstance()->getConf("databases");
        $dbConf = new \EasySwoole\Mysqli\Config($conf);
        return new MysqlObject($dbConf);
    }
}
