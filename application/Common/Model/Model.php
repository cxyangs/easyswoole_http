<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | Model.php  Version 2018/11/13
// +----------------------------------------------------------------------
namespace App\Common\Model;

use EasySwoole\EasySwoole\Config;
use Extend\Utility\Pool\MysqlObject;

class Model
{
    protected $db;

    public $table;

    public function __construct(MysqlObject $db)
    {
        $this->db = $db;
        $this->table = Config::getInstance()->getConf('databases.prefix').(function(){
            $class = humpToLine(get_class($this));
            return ltrim(basename(str_replace('\\', '/', $class)),'_');
        })();
    }

    public function getDbConnection()
    {
        return $this->db;
    }


    public function CommonIndex($where, $sort, $order, $offset, $limit)
    {
        foreach ($where as $item) {
            $this->db->where(...$item);
        }
        $this->db->withTotalCount();
        $this->db->orderBy($sort, $order);
        $list = $this->db->get($this->table,[$offset,$limit],'*');
        return array("total" => $this->db->getTotalCount(), "rows" => $list);
    }

    /**
     * @Mark:公共数据插入事件
     * @param $param
     * @return bool|int
     * @throws \Exception
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/15
     */
    public function commonAdd($param)
    {
        try{
            $param['createtime'] = $param['updatetime'] = time();
            $this->db->insert($this->table,$param);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @Mark:公共数据更新事件
     * @param $param
     * @return bool|int
     * @throws \Exception
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/15
     */
    public function commonEdit($param)
    {
        try{
            $param['updatetime'] = time();
            $id = $param['id'];
            unset($param['id']);
            $this->db->where('id',$id)->update($this->table,$param);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @Mark:公共删除方法(假删)
     * @param $ids string|array
     * @return int
     * @throws \Exception
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/15
     */
    public function commonDel($ids)
    {
        if (is_array($ids)) {
            $this->db->where('id',$ids,'in')->update($this->table,['is_del'=>1]);
        } else {
            $this->db->where('id',$ids)->update($this->table,['is_del'=>1]);
        }
        return $this->db->getAffectRows();
    }

    /**
     * @Mark:公共删除方法(真删)
     * @param $ids string|array
     * @return int
     * @throws \Exception
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/15
     */
    public function commonDestroy($ids)
    {
        if (is_array($ids)) {
            $this->db->where('id',$ids,'in')->delete($this->table);
        } else {
            $this->db->where('id',$ids)->update($this->table,['is_del'=>1]);
        }
        return $this->db->getAffectRows();
    }

    /**
     * @Mark:公共恢复方法
     * @param $ids string|array
     * @return int
     * @throws \Exception
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/15
     */
    public function commonRestore($ids)
    {
        if (is_array($ids)) {
            $this->db->where('id',$ids,'in')->update($this->table,['is_del'=>0]);
        } else {
            $this->db->where('id',$ids)->update($this->table,['is_del'=>0]);
        }
        return $this->db->getAffectRows();
    }


}
