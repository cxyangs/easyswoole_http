<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | AuthRule.php  Version 2018/11/13
// +----------------------------------------------------------------------
namespace App\Admin\Model;

use App\Common\Model\Model;

class AuthRule extends Model
{
    public function getMenu()
    {
        $menu_list = $this->db
            ->withTotalCount()
            ->where('ismenu',1)
            ->where('status','normal')
            ->orderBy('weigh','asc')
            ->get($this->table,null,'*');
        $menu_list = $this->menuTree($menu_list);
        return $menu_list;
    }

    /**
     * @Mark:生成菜单结构树
     * @param $tree
     * @param int $parentid
     * @return array
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/13
     */
    public function menuTree($tree, $parentid = 0)
    {
        $return = array();
        foreach ($tree as $k=>$leaf) {
            if ($leaf['pid'] == $parentid) {
                $leaf['name'] = buildUrl($leaf['name'],$leaf['condition']);
                foreach ($tree as $subleaf) {
                    if ($subleaf['pid'] == $leaf['id']) {
                        $leaf['child'] = $this->menuTree($tree, $leaf['id']);
                        break;
                    }
                }
                $return[] = $leaf;
            }
        }
        return $return;
    }
}
