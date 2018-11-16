<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | Rule.php  Version 2018/11/15
// +----------------------------------------------------------------------
namespace App\Admin\Controller\Auth;

use App\Admin\Model\AuthRule;
use App\Common\Controller\Backend;

class Rule extends Backend
{
    public function _initialize()
    {
        $this->model = new AuthRule($this->getDbConnection());
        parent::_initialize();
    }
}