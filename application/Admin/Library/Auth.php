<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | Auth.php  Version 2018/11/14
// +----------------------------------------------------------------------
namespace App\Admin\Library;

use EasySwoole\EasySwoole\Config;
use EasySwoole\Http\Response;
use EasySwoole\Http\Session\Session;
use Extend\Utility\Pool\MysqlObject;

class Auth extends \Extend\Core\Auth
{
    protected $_error = '';
    protected $requestUri = '';
    protected $breadcrumb = [];
    protected $logined = false; //登录状态
    protected $id;
    protected $tables = [
        'admin'=>'bs_admin'
    ];

    public function __construct(MysqlObject $db,Response $response,Session $session)
    {
        parent::__construct($db,$response,$session);
    }

    /**
     * @Mark:管理员登录
     * @param   string $username 用户名
     * @param   string $password 密码
     * @param   int $keeptime 有效时长
     * @return  boolean|string
     * @return  bool|string
     * @throws \Exception
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/15
     */
    public function login($username, $password, $keeptime = 0)
    {
        $admin = $this->db->where('username',$username)->getOne($this->tables['admin'],['*']);
        if (!$admin) {
            return '用户名错误';
        }
        if (Config::getInstance()->getConf('site.login_failure_retry') && $admin['loginfailure'] >= 10 && time() - $admin['updatetime'] < 86400) {
            return '您的登录失败次数已超出安全限制，请一天后再试！';
        }
        if ($admin['password'] != md5(md5($password) . $admin['salt'])) {
            $this->db->where('username',$username)->update($this->tables['admin'],['loginfailure'=>$this->db->inc()]);
            return '密码错误';
        }
        $update = [
            'loginfailure'=>0,
            'logintime'=>time(),
            'token'=>time(),
        ];
        $this->id = $admin['id'];
        $this->db->where('username',$username)->update($this->tables['admin'],$update);
        $this->session->set("admin", $admin);
        $this->keeplogin($keeptime);
        return true;
    }

    /**
     * 刷新保持登录的Cookie
     *
     * @param   int $keeptime
     * @return  boolean
     */
    protected function keeplogin($keeptime = 0)
    {
        if ($keeptime) {
            $expiretime = time() + $keeptime;
            $key = md5(md5($this->id) . md5($keeptime) . md5($expiretime) . $this->token);
            $data = [$this->id, $keeptime, $expiretime, $key];
            $this->response->setCookie('keeplogin', implode('|', $data), 86400 * 30);
            return true;
        }
        return false;
    }

    public function check($name, $uid = '', $relation = 'or', $mode = 'url')
    {
        return parent::check($name, $this->id, $relation, $mode);
    }

    /**
     * 检测是否登录
     *
     * @return boolean
     */
    public function isLogin()
    {
        if ($this->logined) {
            return true;
        }
        $admin = $this->session->get('admin');
        if (!$admin) {
            return false;
        }
        //判断是否同一时间同一账号只能在一个地方登录
        if (Config::getInstance()->getConf('site.login_unique')) {
            $my = $this->db->where('id',$admin['id'])->get($this->tables['admin']);
            if (!$my || $my['token'] != $admin['token']) {
                return false;
            }
        }
        $this->logined = true;
        return true;
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     *
     * @param array $arr 需要验证权限的数组
     */
    public function match($arr = [],$action)
    {
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return false;
        }

        $arr = array_map('strtolower', $arr);
        // 是否存在
        if (in_array(strtolower($action), $arr) || in_array('*', $arr)) {
            return true;
        }

        // 没找到匹配
        return false;
    }

}
