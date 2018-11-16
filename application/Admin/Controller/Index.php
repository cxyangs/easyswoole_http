<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | Index.php  Version 2018/11/11
// +----------------------------------------------------------------------
namespace App\Admin\Controller;

use App\Common\Controller\Backend;

class Index extends Backend
{
    protected $noNeedLogin = ['login'];
    protected $noNeedRight = ['index','logout'];
    public function index()
    {
        $this->success('验证通过啦');
    }

    public function login()
    {
        $username = $this->post('username','','htmlentities');
        $password = $this->post('password','','htmlentities');
        $keeplogin = $this->post('kepplogin');
        if (!$username) $this->error('请输入用户名');
        if (!$password) $this->error('请输入密码');
        $result = $this->auth->login($username, $password, $keeplogin ? 86400 : 0);
        if (is_bool($result)) {
            if ($result === true) {
                $this->success('登录成功');
            }
        } else {
            $this->error($result);
        }
        $this->error('登录失败，请检查用户名或密码是否正确');
    }

    public function logout()
    {

    }
}
