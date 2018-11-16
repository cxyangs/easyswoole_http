<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | Route.php  Version 2018/11/12
// +----------------------------------------------------------------------
namespace App;

use EasySwoole\Http\AbstractInterface\AbstractRouter;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{

    function initialize(RouteCollector $routeCollector)
    {
        // TODO: Implement initialize() method.
        //此处可设置自定义路由
        $routeCollector->get('/test/{id:\d+}','Admin/Auth/Admin/test');
    }
}
