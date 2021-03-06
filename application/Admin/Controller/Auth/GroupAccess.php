<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | GroupAccess.php  Version 2018/11/15
// +----------------------------------------------------------------------
namespace App\Admin\Controller\Auth;

use App\Admin\Model\AuthGroupAccess;
use App\Common\Controller\Backend;

class GroupAccess extends Backend
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->model = new AuthGroupAccess($this->getDbConnection());
    }

}
