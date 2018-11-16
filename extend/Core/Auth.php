<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | 权限控制类  Version 2018/11/14
// +----------------------------------------------------------------------
namespace Extend\Core;

use EasySwoole\Http\Response;
use EasySwoole\Http\Session\Session;
use Extend\Utility\Cache;
use Extend\Utility\Pool\MysqlObject;
use Extend\Utility\Request;

class Auth
{
    //默认配置
    protected $config = [
        'auth_on'           => 1, // 权限开关
        'auth_type'         => 1, // 认证方式，1为实时认证；2为登录认证。
        'auth_group'        => 'bs_auth_group', // 用户组数据表名
        'auth_group_access' => 'bs_auth_group_access', // 用户-用户组关系表
        'auth_rule'         => 'bs_auth_rule', // 权限规则表
        'auth_user'         => 'bs_user', // 用户信息表
    ];

    protected $db;
    protected $response;
    protected $session;
    protected $rules = [];

    static $user_info;
    //保存用户验证通过的权限列表
    static $_rulelist;

    public function __construct(MysqlObject $db , Response $response,Session $session)
    {
        $this->db = $db;
        $this->response = $response;
        $this->session = $session;
    }

    /**
     * 检查权限
     * @param       $name   string|array    需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param       $uid    int             认证用户的id
     * @param       string  $relation       如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @param       string  $mode           执行验证的模式,可分为url,normal
     * @return bool               通过验证返回true;失败返回false
     */
    public function check($name, $uid, $relation = 'or', $mode = 'url')
    {
        if (!$this->config['auth_on'])
        {
            return true;
        }
        // 获取用户需要验证的所有有效规则列表
        $rulelist = $this->getRuleList($uid);
        if (in_array('*', $rulelist))
            return true;

        if (is_string($name))
        {
            $name = strtolower($name);
            if (strpos($name, ',') !== false)
            {
                $name = explode(',', $name);
            }
            else
            {
                $name = [$name];
            }
        }
        $list = []; //保存验证通过的规则名
        if ('url' == $mode)
        {
            $REQUEST = unserialize(strtolower(serialize(Request::getInstance()->param())));
        }
        foreach ($rulelist as $rule)
        {
            $query = preg_replace('/^.+\?/U', '', $rule);
            if ('url' == $mode && $query != $rule)
            {
                parse_str($query, $param); //解析规则中的param
                $intersect = array_intersect_assoc($REQUEST, $param);
                $rule = preg_replace('/\?.*$/U', '', $rule);
                if (in_array($rule, $name) && $intersect == $param)
                {
                    //如果节点相符且url参数满足
                    $list[] = $rule;
                }
            }
            else
            {
                if (in_array($rule, $name))
                {
                    $list[] = $rule;
                }
            }
        }
        if ('or' == $relation && !empty($list))
        {
            return true;
        }
        $diff = array_diff($name, $list);
        if ('and' == $relation && empty($diff))
        {
            return true;
        }

        return false;
    }

    /**
     * 获得权限规则列表
     * @param integer $uid 用户id
     * @return array
     */
    public function getRuleList($uid)
    {
        if (isset(self::$_rulelist[$uid]))
        {
            return self::$_rulelist[$uid];
        }
        if (2 == $this->config['auth_type'] && Cache::getInstance()->has('_rule_list_' . $uid))
        {
            return Cache::getInstance()->get('_rule_list_' . $uid);
        }

        // 读取用户规则节点
        $ids = $this->getRuleIds($uid);
        if (empty($ids))
        {
            $_rulelist[$uid] = [];
            return [];
        }

        // 筛选条件
        $where = [
            'status' => 'normal'
        ];
        if (!in_array('*', $ids))
        {
            $where['id'] = ['in', $ids];
        }
        //读取用户组所有权限规则
        $this->db->where('status','normal');
        if (!in_array('*', $ids))
        {
            $this->db->where('id',$ids,'in');
        }
        $this->rules = $this->db->get($this->config['auth_rule'],null,'id,pid,condition,icon,name,title,ismenu');

        //循环规则，判断结果。
        $rulelist = [];
        if (in_array('*', $ids))
        {
            $rulelist[] = "*";
        }
        foreach ($this->rules as $rule)
        {
            //超级管理员无需验证condition
            if (!empty($rule['condition']) && !in_array('*', $ids))
            {
                //根据condition进行验证
                $user = $this->getUserInfo($uid); //获取用户信息,一维数组
                $command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule['condition']);
                @(eval('$condition=(' . $command . ');'));
                if ($condition)
                {
                    $rulelist[$rule['id']] = strtolower($rule['name']);
                }
            }
            else
            {
                //只要存在就记录
                $rulelist[$rule['id']] = strtolower($rule['name']);
            }
        }
        Cache::getInstance()->set('_rule_list_' . $uid,$rulelist,3600);
        self::$_rulelist[$uid] = $rulelist;
        //登录验证则需要保存规则列表
        if (2 == $this->config['auth_type'])
        {
            //规则列表结果保存到缓存
            Cache::getInstance()->set('_rule_list_' . $uid,$rulelist,3600);
        }
        return array_unique($rulelist);
    }

    /**
     * 根据用户id获取用户组,返回值为数组
     * @param  $uid int     用户id
     * @return array       用户所属的用户组 array(
     *              array('uid'=>'用户id','group_id'=>'用户组id','name'=>'用户组名称','rules'=>'用户组拥有的规则id,多个,号隔开'),
     *              ...)
     */
    public function getGroups($uid)
    {
        if (Cache::getInstance()->has('user_group_'.$uid)) {
            return Cache::getInstance()->get('user_group_'.$uid);
        }
        $user_groups = $this->db
            ->join($this->config['auth_group'].' ag','aga.group_id = ag.id','LEFT')
            ->where('aga.uid',$uid)
            ->where('ag.status','normal')
            ->get($this->config['auth_group_access'].' aga',null,'aga.uid,aga.group_id,ag.id,ag.pid,ag.name,ag.rules');
        $user_groups = $user_groups ? $user_groups : [];
        Cache::getInstance()->set('user_group_'.$uid,$user_groups,3600);//缓存1小时
        return $user_groups;
    }

    public function getRuleIds($uid)
    {
        //读取用户所属用户组
        $groups = $this->getGroups($uid);
        $ids = []; //保存用户所属用户组设置的所有权限规则id
        foreach ($groups as $g)
        {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        return $ids;
    }

    /**
     * 获得用户资料
     * @param $uid
     * @return mixed
     */
    public function getUserInfo($uid)
    {
        // 获取用户表主键
        if (!isset(self::$user_info[$uid]))
        {
            $user_info[$uid] = $this->db->where('id',$uid)->getOne($this->config['auth_user']);
        }
        return self::$user_info[$uid];
    }

    public function __destruct()
    {
        self::$user_info = null;
        self::$_rulelist = null;
    }
}
