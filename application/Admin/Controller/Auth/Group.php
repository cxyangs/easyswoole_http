<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | Group.php  Version 2018/11/15
// +----------------------------------------------------------------------
namespace App\Admin\Controller\Auth;

use App\Admin\Model\AuthGroup;
use App\Common\Controller\Backend;

class Group extends Backend
{
    public function _initialize()
    {
        $this->model = new AuthGroup($this->getDbConnection());
        parent::_initialize();
    }
}

