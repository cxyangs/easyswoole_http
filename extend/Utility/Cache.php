<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | Cache.php  Version 2018/11/14
// +----------------------------------------------------------------------
namespace Extend\Utility;

use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use Extend\Utility\Pool\RedisPool;

class Cache
{
    use Singleton;
    protected $redis;
    protected $options;
    protected $tag;

    public function __construct()
    {
        $redis = PoolManager::getInstance()->getPool(RedisPool::class)->getObj();
        if ($redis) {
            $this->options = Config::getInstance()->getConf('redis');
            $this->redis = $redis;
        } else {
            //直接抛给异常处理，不往下
            throw new \Exception('url :'.$this->request()->getUri()->getPath().' error,Redis Pool is Empty');
        }
    }

    /**
     * 获取实际的缓存标识
     * @access public
     * @param string $name 缓存名
     * @return string
     */
    protected function getCacheKey($name)
    {
        return $this->options['prefix'] . $name;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $value = $this->redis->get($this->getCacheKey($name));
        if (is_null($value) || false === $value) {
            return $default;
        }

        try {
            $result = 0 === strpos($value, 'bsit_serialize:') ? unserialize(substr($value, 15)) : $value;
        } catch (\Exception $e) {
            $result = $default;
        }

        return $result;
    }

    /**
     * 写入缓存
     * @access public
     * @param string            $name 缓存变量名
     * @param mixed             $value  存储数据
     * @param integer|\DateTime $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        if ($expire instanceof \DateTime) {
            $expire = $expire->getTimestamp() - time();
        }
        if ($this->tag && !$this->has($name)) {
            $first = true;
        }
        $key   = $this->getCacheKey($name);
        $value = is_scalar($value) ? $value : 'bsit_serialize:' . serialize($value);
        if ($expire) {
            $result = $this->redis->setex($key, $expire, $value);
        } else {
            $result = $this->redis->set($key, $value);
        }
        isset($first) && $this->setTagItem($key);
        return $result;
    }

    /**
     * 更新标签
     * @access public
     * @param string $name 缓存标识
     * @return void
     */
    protected function setTagItem($name)
    {
        if ($this->tag) {
            $key       = 'tag_' . md5($this->tag);
            $this->tag = null;
            if ($this->has($key)) {
                $value   = explode(',', $this->get($key));
                $value[] = $name;
                $value   = implode(',', array_unique($value));
            } else {
                $value = $name;
            }
            $this->set($key, $value, 0);
        }
    }

    /**
     * 判断缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name)
    {
        return $this->redis->exists($this->getCacheKey($name));
    }

    /**
     * 读取缓存并删除
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function pull($name)
    {
        $result = $this->get($name, false);
        if ($result) {
            $this->rm($name);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 缓存标签
     * @access public
     * @param string        $name 标签名
     * @param string|array  $keys 缓存标识
     * @param bool          $overlay 是否覆盖
     * @return $this
     */
    public function tag($name, $keys = null, $overlay = false)
    {
        if (is_null($name)) {

        } elseif (is_null($keys)) {
            $this->tag = $name;
        } else {
            $key = 'tag_' . md5($name);
            if (is_string($keys)) {
                $keys = explode(',', $keys);
            }
            $keys = array_map([$this, 'getCacheKey'], $keys);
            if ($overlay) {
                $value = $keys;
            } else {
                $value = array_unique(array_merge($this->getTagItem($name), $keys));
            }
            $this->set($key, implode(',', $value), 0);
        }
        return $this;
    }

    /**
     * 获取标签包含的缓存标识
     * @access public
     * @param string $tag 缓存标签
     * @return array
     */
    protected function getTagItem($tag)
    {
        $key   = 'tag_' . md5($tag);
        $value = $this->get($key);
        if ($value) {
            return array_filter(explode(',', $value));
        } else {
            return [];
        }
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param  string    $name 缓存变量名
     * @param  int       $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        $key = $this->getCacheKey($name);

        return $this->redis->incrby($key, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param  string    $name 缓存变量名
     * @param  int       $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1)
    {
        $key = $this->getCacheKey($name);

        return $this->redis->decrby($key, $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        return $this->redis->delete($this->getCacheKey($name));
    }

    /**
     * 清除缓存
     * @access public
     * @param string $tag 标签名
     * @return boolean
     */
    public function clear($tag = null)
    {
        if ($tag) {
            // 指定标签清除
            $keys = $this->getTagItem($tag);
            foreach ($keys as $key) {
                $this->redis->delete($key);
            }
            $this->rm('tag_' . md5($tag));
            return true;
        }
        return $this->redis->flushDB();
    }

    public function gc()
    {
        PoolManager::getInstance()->getPool(RedisPool::class)->recycleObj($this->redis);
    }
}