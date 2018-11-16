<?php
// +----------------------------------------------------------------------
// | Szbsit [ Rapid development framework for Cross border Mall ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.szbsit.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yang <502204678@qq.com>
// +----------------------------------------------------------------------
// | Backend.php  Version 2018/11/14
// +----------------------------------------------------------------------
namespace App\Common\Traits;

use EasySwoole\EasySwoole\Config;

trait Backend
{
    protected $with = "";
    /**
     * 快速搜索时执行查找的字段
     */
    protected $searchFields = 'id';
    /**
     * 是否是关联查询
     */
    protected $relationSearch = false;
    /**
     * 数据限制字段
     */
    protected $dataLimitField = 'admin_id';
    /**
     * 是否开启数据限制
     * 支持auth/personal
     * 表示按权限判断/仅限个人
     * 默认为禁用,若启用请务必保证表中存在admin_id字段
     */
    protected $dataLimit = false;
    /**
     * 是否开启Validate验证
     */
    protected $modelValidate = false;

    /**
     * @Mark:列表
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/14
     */
    public function index()
    {
        $this->filter = ['strip_tags'];
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $list = $this->model->commonIndex($where, $sort, $order, $offset, $limit);
        if ($list['total']) {
            $this->success($list);
        } else {
            $this->error($list);
        }
    }

    /**
     * @Mark:新增
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/14
     */
    public function add()
    {
        $params = $this->post("row/a");
        if ($params){
            try {
                //是否采用模型验证
                if ($this->modelValidate) {
                    $name = preg_replace('/Model/','Validate',get_class($this->model),1);
                    $validate = new $name();
                    $validate_res = $validate->validate($params);
                    if (!$validate_res) $this->error($validate->getError()->getErrorRuleMsg());
                }
                $result = $this->model->commonAdd($params);
                if ($result === true) {
                    $this->success('创建成功');
                } else {
                    $this->error($result);
                }
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        } else {
            $this->error('参数为空');
        }
    }

    /**
     * @Mark 编辑
     * @param null $ids
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/14
     */
    public function edit($ids = NULL)
    {
        $params = $this->post("row/a");
        if ($params) {
            try {
                //是否采用模型验证
                if ($this->modelValidate) {
                    $name = preg_replace('/Model/','Validate',get_class($this->model),1);
                    $validate = new $name();
                    $validate_res = $validate->validate($params);
                    if (!$validate_res) $this->error($validate->getError()->getErrorRuleMsg());
                }
                if ($ids) $params['id'] = $ids;
                $result = $this->model->commonEdit($params);
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error($result);
                }
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
        $this->error('参数不得为空');
    }

    /**
     * @Mark:假删
     * @param $ids string
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/14
     */
    public function del($ids = "")
    {
        $ids = $this->post("ids/s",$ids);
        if ($ids) {
            try{
                $ids = explode(',',$ids);
                if (count($ids) > 1) {
                    $result = $this->model->commonDel($ids[0]);
                } else {
                    $result = $this->model->commonDel($ids);
                }
                if ($result > 0) {
                    $this->success('删除成功');
                } else {
                    $this->error('删除失败');
                }
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
        $this->error('参数不得为空');
    }

    /**
     * @Mark:真删
     * @param string $ids
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/14
     */
    public function destroy($ids = "")
    {
        $ids = $this->post("ids/s",$ids);
        if ($ids) {
            try{
                $ids = explode(',',$ids);
                if (count($ids) > 1) {
                    $result = $this->model->commonDestroy($ids[0]);
                } else {
                    $result = $this->model->commonDestroy($ids);
                }
                if ($result > 0) {
                    $this->success('删除成功');
                } else {
                    $this->error('删除失败');
                }
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
        $this->error('参数不得为空');
    }

    /**
     * @Mark:回收站
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/14
     */
    public function recyclebin()
    {
        $this->filter = ['strip_tags'];
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $where[] = ['is_del',1];
        $list = $this->model->commonIndex($where, $sort, $order, $offset, $limit);
        if ($list['total']) {
            $this->success($list);
        } else {
            $this->error($list);
        }
    }

    /**
     * @Mark:还原
     * @Author: yang <502204678@qq.com>
     * @Version 2018/11/14
     */
    public function restore($ids)
    {
        $ids = $this->post("ids/s",$ids);
        if ($ids) {
            try{
                $ids = explode(',',$ids);
                if (count($ids) > 1) {
                    $result = $this->model->commonRestore($ids[0]);
                } else {
                    $result = $this->model->commonRestore($ids);
                }
                if ($result > 0) {
                    $this->success('恢复成功');
                } else {
                    $this->error('恢复失败');
                }
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
        $this->error('参数不得为空');
    }


    /**
     * 生成查询所需要的条件,排序方式
     * @param mixed $searchfields 快速查询的字段
     * @param boolean $relationSearch 是否关联查询
     * @return array
     */
    protected function buildparams($searchfields = null, $relationSearch = null)
    {
        $searchfields = is_null($searchfields) ? $this->searchFields : $searchfields;
        $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
        $search = $this->get("search", '');
        $filter = $this->get("filter", '');
        $op = $this->get("op", '', 'trim');
        $sort = $this->get("sort", "id");
        $order = $this->get("order", "DESC");
        $offset = $this->get("offset", 0);
        $limit = $this->get("limit", 15);
        $filter = (array)json_decode($filter, TRUE);
        $op = (array)json_decode($op, TRUE);
        $filter = $filter ? $filter : [];
        $where = [];
        $tableName = '';
        if ($relationSearch) {
            if (!empty($this->model)) {
                $name = Config::getInstance()->getConf('databases.prefix').(function(){
                        $class = humpToLine($this->model);
                        return ltrim(basename(str_replace('\\', '/', $class)),'_');
                    })();
                $tableName = $name . '.';
            }
            $sortArr = explode(',', $sort);
            foreach ($sortArr as $index => & $item) {
                $item = stripos($item, ".") === false ? $tableName . trim($item) : $item;
            }
            unset($item);
            $sort = implode(',', $sortArr);
        }
        if ($search) {
            $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $tableName . $v : $v;
            }
            unset($v);
            $where[] = [implode("|", $searcharr), "%{$search}%", "LIKE"];
        }
        foreach ($filter as $k => $v) {
            $sym = isset($op[$k]) ? $op[$k] : '=';
            if (stripos($k, ".") === false) {
                $k = $tableName . $k;
            }
            $v = !is_array($v) ? trim($v) : $v;
            $sym = strtoupper(isset($op[$k]) ? $op[$k] : $sym);
            switch ($sym) {
                case '=':
                    $where[] = [$k , (string)$v ,$sym];
                    break;
                case '!=':
                    $where[] = [$k , (string)$v ,$sym];
                    break;
                case 'LIKE':
                    $where[] = [$k , "%{$v}%" ,$sym];
                    break;
                case 'NOT LIKE':
                    $where[] = [$k , "%{$v}%" ,$sym];
                    break;
                case '>':
                    $where[] = [$k , intval($v) ,$sym];
                    break;
                case '>=':
                    $where[] = [$k , intval($v) ,$sym];
                    break;
                case '<':
                    $where[] = [$k , intval($v) ,$sym];
                    break;
                case '<=':
                    $where[] = [$k , intval($v), $sym];
                    break;
                case 'IN':
                    $where[] = [$k , is_array($v) ? $v : explode(',', $v), $sym];
                    break;
                case 'NOT IN':
                    $where[] = [$k , is_array($v) ? $v : explode(',', $v), $sym];
                    break;
                case 'BETWEEN':
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr))
                        continue;
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = '<';
                        $arr = $arr[1];
                    } else if ($arr[1] === '') {
                        $sym = '>';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, $arr, $sym];
                    break;
                case 'NOT BETWEEN':
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr))
                        continue;
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = '>';
                        $arr = $arr[1];
                    } else if ($arr[1] === '') {
                        $sym = '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, $arr, $sym];
                    break;
                case 'NULL':
                    $where[] = [$k, 'IS NULL',strtoupper($sym)];
                    break;
                case 'NOT NULL':
                    $where[] = [$k, strtoupper(str_replace('NOT', '', $sym)),'IS '.strtoupper(str_replace('NULL', '', $sym))];
                    break;
                default:
                    break;
            }
        }
        return [$where, $sort, $order, $offset, $limit];
    }
}
