<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | 公共数据请求获取trait  Version 2018/11/14
// +----------------------------------------------------------------------
namespace App\Common\Traits;

use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Http\Message\Uri;

trait Request
{
    //请求参数
    private $param   = [];
    private $get     = [];
    private $post    = [];
    private $method;
    // 全局过滤规则
    protected $filter;
    /**
     * @var string 域名（含协议和端口）
     */
    private $domain;

    private $ip;

    private $uri;

    protected function uri()
    {
        $this->uri = new Uri($this->request()->getUri());
        return $this->uri;
    }

    /**
     * 获取变量 支持过滤和默认值
     * @param array         $data 数据源
     * @param string|false  $name 字段名
     * @param mixed         $default 默认值
     * @param string|array  $filter 过滤函数
     * @return mixed
     */
    public function input($data = [], $name = '', $default = null, $filter = ''){
        if (false === $name) {
            // 获取原始数据
            return $data;
        }
        $name = (string) $name;
        if ('' != $name) {
            // 解析name
            if (strpos($name, '/')) {
                list($name, $type) = explode('/', $name);
            } else {
                $type = 's';
            }
            // 按.拆分成多维数组进行判断
            foreach (explode('.', $name) as $val) {
                if (isset($data[$val])) {
                    $data = $data[$val];
                } else {
                    // 无输入数据，返回默认值
                    return $default;
                }
            }
            if (is_object($data)) {
                return $data;
            }
        }
        // 解析过滤器
        $filter = $this->getFilter($filter, $default);

        if (is_array($data)) {
            array_walk_recursive($data, [$this, 'filterValue'], $filter);
            reset($data);
        } else {
            $this->filterValue($data, $name, $filter);
        }

        if (isset($type) && $data !== $default) {
            // 强制类型转换
            $this->typeCast($data, $type);
        }
        return $data;
    }

    protected function getFilter($filter, $default)
    {
        if (is_null($filter)) {
            $filter = [];
        } else {
            $filter = $filter ?: $this->filter;
            if (is_string($filter) && false === strpos($filter, '/')) {
                $filter = explode(',', $filter);
            } else {
                $filter = (array) $filter;
            }
        }

        $filter[] = $default;
        return $filter;
    }

    /**
     * 递归过滤给定的值
     * @param mixed     $value 键值
     * @param mixed     $key 键名
     * @param array     $filters 过滤方法+默认值
     * @return mixed
     */
    private function filterValue(&$value, $key, $filters)
    {
        $default = array_pop($filters);
        foreach ($filters as $filter) {
            if (is_callable($filter)) {
                // 调用函数或者方法过滤
                $value = call_user_func($filter, $value);
            } elseif (is_scalar($value)) {
                if (false !== strpos($filter, '/')) {
                    // 正则过滤
                    if (!preg_match($filter, $value)) {
                        // 匹配不成功返回默认值
                        $value = $default;
                        break;
                    }
                } elseif (!empty($filter)) {
                    // filter函数不存在时, 则使用filter_var进行过滤
                    // filter为非整形值时, 调用filter_id取得过滤id
                    $value = filter_var($value, is_int($filter) ? $filter : filter_id($filter));
                    if (false === $value) {
                        $value = $default;
                        break;
                    }
                }
            }
        }
        return $this->filterExp($value);
    }

    /**
     * 过滤表单中的表达式
     * @param string $value
     * @return void
     */
    public function filterExp(&$value)
    {
        // 过滤查询特殊字符
        if (is_string($value) && preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT LIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOT EXISTS|NOTEXISTS|EXISTS|NOT NULL|NOTNULL|NULL|BETWEEN TIME|NOT BETWEEN TIME|NOTBETWEEN TIME|NOTIN|NOT IN|IN)$/i', $value)) {
            $value .= ' ';
        }
        // TODO 其他安全过滤
    }

    /**
     * 强制类型转换
     * @param string $data
     * @param string $type
     * @return mixed
     */
    private function typeCast(&$data, $type)
    {
        switch (strtolower($type)) {
            // 数组
            case 'a':
                $data = (array) $data;
                break;
            // 数字
            case 'd':
                $data = (int) $data;
                break;
            // 浮点
            case 'f':
                $data = (float) $data;
                break;
            // 布尔
            case 'b':
                $data = (boolean) $data;
                break;
            // 字符串
            case 's':
            default:
                if (is_scalar($data)) {
                    $data = (string) $data;
                } else {
                    throw new \InvalidArgumentException('variable type error：' . gettype($data));
                }
        }
    }




    /**
     * 设置获取GET参数
     * @access public
     * @param string|array  $name 变量名
     * @param mixed         $default 默认值
     * @param string|array  $filter 过滤方法
     * @return mixed
     */
    public function get($name = '', $default = null, $filter = '')
    {
        //if (empty($this->get)) {
            $this->get = $this->request()->getQueryParams();
        //}
        if (is_array($name)) {
            $this->param      = [];
            return $this->get = array_merge($this->get, $name);
        }
        return $this->input($this->get, $name, $default, $filter);
    }

    /**
     * 设置获取POST参数
     * @access public
     * @param string        $name 变量名
     * @param mixed         $default 默认值
     * @param string|array  $filter 过滤方法
     * @return mixed
     */
    public function post($name = '', $default = null, $filter = '')
    {
        if (empty($this->post)) {
            $this->post = $this->request()->getParsedBody();
        }
        if (is_array($name)) {
            $this->param      = [];
            return $this->post = array_merge($this->get, $name);
        }
        return $this->input($this->post, $name, $default, $filter);
    }

    /**
     * @Mark:获取所有请求参数
     * @param string $name
     * @param null $default
     * @param string $filter
     * @return array|mixed
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/14
     */
    public function param($name = '', $default = null, $filter = '')
    {
        if (!$this->param) {
            $this->param = $this->request()->getRequestParam();
        }
        if (is_array($name)) {
            return $this->param = array_merge($this->param, $name);
        }

        return $this->input($this->param, $name, $default, $filter);
    }

    /**
     * 设置或获取当前包含协议的域名
     * @access public
     * @param string $domain 域名
     * @return string
     */
    public function domain()
    {
       if (!$this->domain) {
            $this->domain = $this->uri()->getHost();
        }
        return $this->domain;
    }

    /**
     * 当前请求的host
     * @access public
     * @param bool $strict  true 仅仅获取HOST
     * @return string
     */
    public function host($strict = false)
    {
        if ($strict) {
            $host = $this->scheme().'://'.$this->domain().':'.$this->uri()->getPort();
        } else {
            $host = $this->scheme().'://'.$this->domain();
        }

        return $host;
    }

    /**
     * 当前URL地址中的scheme参数
     * @access public
     * @return string
     */
    public function scheme()
    {
        return $this->uri()->getScheme();
    }

    /**
     * 当前是否ssl
     * @access public
     * @return bool
     */
    public function isSsl()
    {
        if ($this->scheme() === 'https') {
            return true;
        }
        return false;
    }

    /**
     * 获取客户端IP地址
     * @param integer   $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean   $adv 是否获取真实IP地址
     * @return mixed
     */
    public function ip($type = 0, $adv = true)
    {
        $type      = $type ? 1 : 0;
        if (null !== $this->ip) {
            return $this->ip[$type];
        }
        $headers_param = $this->request()->getHeaders();
        $connect_info = ServerManager::getInstance()->getSwooleServer()->connection_info($this->request()->getSwooleRequest()->fd);
        if ($adv) {
            $ip = $connect_info['remote_ip'];
        } else if (isset($headers_param['x-forwarded-for'])) {
            $arr = explode(',', $headers_param['x-forwarded-for']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim(current($arr));
        } else {
            $ip = $connect_info['remote_ip'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $this->ip   = $long ? [$ip, $long] : ['0.0.0.0', 0];
        return $this->ip[$type];
    }


    public function isAjax()
    {
        $header = $this->request()->getHeader('x-requested-with');
        if (!empty($header)) return true;
        return false;
    }

    /**
     * 是否为POST请求
     * @access public
     * @return bool
     */
    public function isPost()
    {
        return $this->method == 'POST';
    }

    /**
     * 是否为POST请求
     * @access public
     * @return bool
     */
    public function isGet()
    {
        return $this->method == 'GET';
    }

    /**
     * 是否为PUT请求
     * @access public
     * @return bool
     */
    public function isPut()
    {
        return $this->method() == 'PUT';
    }

    /**
     * 是否为DELTE请求
     * @access public
     * @return bool
     */
    public function isDelete()
    {
        return $this->method() == 'DELETE';
    }

    /**
     * 是否为HEAD请求
     * @access public
     * @return bool
     */
    public function isHead()
    {
        return $this->method() == 'HEAD';
    }

    /**
     * 是否为PATCH请求
     * @access public
     * @return bool
     */
    public function isPatch()
    {
        return $this->method() == 'PATCH';
    }

    /**
     * 是否为OPTIONS请求
     * @access public
     * @return bool
     */
    public function isOptions()
    {
        return $this->method() == 'OPTIONS';
    }

    /**
     * @Mark:http请求方法
     * @return array|mixed
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/11
     */
    public function method()
    {
        return $this->request()->getMethod();
    }

    public function resetStatus()
    {
        $this->param = [];
        $this->get = [];
        $this->post = [];
        $this->method = null;
        $this->domain = null;
        $this->ip = null;
        $this->filter = null;
    }
}