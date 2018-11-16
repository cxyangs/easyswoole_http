<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | 封装验证类  Version 2018/11/15
// +----------------------------------------------------------------------
namespace App\Common\Validate;

class Validate extends \EasySwoole\Validate\Validate
{
    /**
     * @Mark:验证规则
     * @var $rules = [
     *      [
     *          'name'                                  //验证字段
     *          'required|lengthMin:10'                 //验证规则,更多验证规则请自行查看父类
     *          '姓名必须|姓名最小长度必须大于10'    //验证提示信息
     *      ]
     * ]
     * @Author: yang <502204678@qq.com>
     */
    protected $rules = [];
    public function __construct()
    {
        foreach ($this->rules as $item) {
            $rule = explode('|',$item[1]);
            $msg = explode('|',$item[2]);
            $validate = $this->addColumn(trim($item[0]),$msg[0]);
            foreach ($rule as $k=>$v) {
                $rule_param = explode(':',$v);
                $name = $rule_param[0];
                $param = isset($rule_param[1]) ? $rule_param[1] : 0;
                if ($param) {
                    $validate->$name($param,$msg[$k]);
                } else {
                    $validate->$name($msg[$k]);
                }
            }
        }

    }
}
