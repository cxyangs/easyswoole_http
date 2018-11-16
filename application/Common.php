<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | 公共函数文件  Version 2018/11/9
// +----------------------------------------------------------------------
if (!function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本
     * @param int $size 大小
     * @param string $delimiter 分隔符
     * @return string
     */
    function format_bytes($size, $delimiter = '')
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++)
            $size /= 1024;
        return round($size, 2) . $delimiter . $units[$i];
    }

}

if (!function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间
     * @param int $time 时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }

}

if (!function_exists('time_ago')){

    function time_ago($agoTime)
    {
        $agoTime = (int)$agoTime;

        // 计算出当前日期时间到之前的日期时间的毫秒数，以便进行下一步的计算
        $time = time() - $agoTime;

        if ($time >= 31104000) { // N年前
            $num = (int)($time / 31104000);
            return $num.'年前';
        }
        if ($time >= 2592000) { // N月前
            $num = (int)($time / 2592000);
            return $num.'月前';
        }
        if ($time >= 86400) { // N天前
            $num = (int)($time / 86400);
            return $num.'天前';
        }
        if ($time >= 3600) { // N小时前
            $num = (int)($time / 3600);
            return $num.'小时前';
        }
        if ($time > 60) { // N分钟前
            $num = (int)($time / 60);
            return $num.'分钟前';
        }
        return '1分钟前';
    }

}

if (!function_exists('pp')) {
    /**
     * @Mark:打印数据 断点调试
     * @param $arr
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/13
     */
    function pp($arr){
        $arr = var_export($arr,true);
        $response = \Extend\Utility\Request::getInstance()->response();
        $response->write($arr);
        $response->withHeader('Content-Type', 'text/plain;charset=utf-8');
        $response->end();
    }
}

if (!function_exists('humpToLine')) {

    /**
     * @Mark:驼峰转下划线
     * @param $str
     * @return null|string|string[]
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/13
     */
    function humpToLine($str){
        $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
            return '_'.strtolower($matches[0]);
        },$str);
        return $str;
    }
}

if (!function_exists('buildUrl')) {

    /**
     * @Mark:生成url
     * @param $url
     * @param array|string $param
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/13
     */
    function buildUrl($url,$param = []){
        $domain = \Extend\Utility\Request::getInstance()->host();
        $query = !empty($param) ? is_array($param) ? '?'.http_build_query($param) : $param : '';
        return $domain.DS.ltrim($url,'/').$query;
    }
}