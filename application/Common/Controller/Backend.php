<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | Backend.php  Version 2018/11/11
// +----------------------------------------------------------------------
namespace App\Common\Controller;

use App\Admin\Library\Auth;
use App\SysConst;

class Backend extends Common
{
    use \App\Common\Traits\Backend;
    /**
     * 权限控制类
     * @var Auth
     */
    protected $auth = null;
    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];
    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    public function _initialize()
    {
        parent::_initialize();

        $this->auth = new Auth($this->getDbConnection(),$this->response(),$this->session());
        $path = $this->controller . '/' . $this->action;
        // 检测是否需要验证登录
        if (!$this->auth->match($this->noNeedLogin,$this->action)) {
            //检测是否登录
            $this->session()->start();
            if (!$this->auth->isLogin()) {
                $this->error('请登录', buildUrl('admin/index/login'),SysConst::DATA_NULL,SysConst::NO_LOGIN);
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight,$this->action)) {
                // 判断控制器和方法判断是否有对应权限
                if (!$this->auth->check($path)) {
                    $this->error(null,'您没有权限访问',null,SysConst::NO_PERMISSION);
                }
            }
            $this->session()->writeClose();
        }
        //初始化smarty
//        $this->smarty();
//        $domain = $this->host();
//        $this->assign('css_path',$domain.'/assets/css/');
//        $this->assign('js_path',$domain.'/assets/js/');
//        $this->assign('local_img_path',$domain.'/assets/img/');
    }
}
