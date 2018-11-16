<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | Admin.php  Version 2018/11/11
// +----------------------------------------------------------------------
namespace App\Admin\Controller\Auth;

use App\Admin\Model\Admin as AdminModel;
use App\Common\Controller\Backend;

class Admin extends Backend
{
    protected $noNeedLogin = ['test'];
    protected $noNeedRight = ['test'];
    public function _initialize()
    {
        $this->model = new AdminModel($this->getDbConnection());
        parent::_initialize();
    }

    public function test()
    {
        global $arr;
        $this->response()->withHeader('Content-type','text/plain;charset=utf-8');
        $this->response()->write('请求参数id:'.$this->request()->getQueryParam('id').PHP_EOL);
        $this->response()->write('协程ID:'.\Swoole\Coroutine::getuid().PHP_EOL);
        if ($this->request()->getQueryParam('id') == 10) {
            $arr['a'] = 10;
            \co::sleep(5.0);
            $this->response()->write('全局变量$arr='.$arr['a']);
            $this->response()->end();
        } else {
            $arr['a'] = 12;
            $this->response()->write('全局变量$arr='.$arr['a']);
            $this->response()->end();
        }
    }
}
