<?php

namespace App\Repositories\Contracts;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface PostRepository
 * @package namespace App\Repositories\Contracts;
 */
interface BaseRepositoryInterface extends RepositoryInterface
{
    //

    public function isForceAndWhere();

    /**
     * 因为 model 与 builder 对象相互转换
     * 这儿获取真实的 model 对象
     * User: Howard
     * Date: 2016-06-16
     * @return mixed
     */
    public function getModel();

    /**
     * 解析 搜索的 数据
     * @param $searchData
     * User: Howard
     * Date: 2016-05-9
     * @return mixed
     */
    public function parserSearchData($searchData);

    /**
     * 解析 搜索的 原始 数据
     * @param $search
     * User: Howard
     * Date: 2016-05-09
     * @return mixed
     */
    public function parserOriginSearchData($search);

    /**
     * 根据哪些字段 进行排序
     * User: Howard
     * Date: 2016-05-10
     * @return mixed
     */
    public function getFieldSortable();

    /**
     * 可显示的部分 一般用于API
     * User: Howard
     * Date: 2016-05-12
     * @return mixed
     */
    public function getDisplayAble();

    /**
     * 获取搜索的参数数据
     * @param null $param 参数名称
     * @param bool $is_origin 是否提取原始数据
     * User: Howard
     * Date: 2017-03-03
     * @return mixed
     */
    public function getSearchData($param = null, $is_origin = true);

    /**
     * 使用数组 填充 where 条件
     * @param array $where
     * User: Howard
     * Date: 2016-05-12
     * @return $this
     */
    public function whereWithParams(array $where);

    /**
     * 设置获取记录的条数
     * @param null $limit
     * User: Howard
     * Date: 2017-03-16
     * @return Model
     */
    public function take($limit = null);

    /**
     * 重写 find 方法  调用 model 的 find 而不是 findOrFail
     * @param $id
     * @param array $columns
     * User: Howard
     * Date: 2017-06-07
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * count
     * User: Howard
     * Date: 2017-06-08
     * @return mixed
     */
    public function count();

    /**
     * @deprecated
     * 根据ID 获取 列表
     * @param mixed $ids
     * @param array|null $params
     * @param array $columns
     * User: Howard
     * Date: 2016-05-12
     * @return array
     */
    public function findList($ids = null, array $params = null, array $columns = ['*']);


    /**
     * 获取model 对象 如果没有数据 则返回 空的model对象 而不是null
     * @param $id
     * @param array $columns
     * User: Howard
     * Date: 2016-05-26
     * @return mixed connection or model
     */
    public function findOrFail($id, $columns = ['*']);

    /**
     * 为打开链接和监测链接添加 http:// 头，如果没有的话
     * @param $url
     * User: zzp
     * Date: 2016-12-15
     * @return string
     */
    public function filterUrl($url);

    /**
     * openssl 加密
     * @param $data
     * User: zzp
     * Date: 2017-04-24
     * @return string
     */
    public function opensslEncrypt($data);

    /**
     * 单表分组取最大或最小 的数据
     * @param string $group_field
     * @param array $where
     * @param string $operate
     * @param string $operate_field
     * @param null $columns
     * @param string $order_field
     * @param string $order_direction
     * User: Howard
     * Date: 2017-07-07
     * @return Collection|null
     */
    public function getDataByGroup(
        $group_field,
        $where = [],
        $operate = 'max',
        $operate_field = 'id',
        $columns = null,
        $order_field = 'id',
        $order_direction = 'desc'
    );

    /**
     * 单表分组取最大或最小 的ID
     * @param $group_field
     * @param array $where
     * @param string $operate
     * @param string $operate_field
     * User: Howard
     * Date: 2017-07-07
     * @return array
     */
    public function getIdByGroup($group_field, $where = [], $operate = 'max', $operate_field = 'id');

    /**
     * @comment 随机获取一个设备信息
     * @param int $mOS
     * @return string
     * @author zzp
     * @date 2017-07-26
     */
    public function getSingleDeviceInfo($mOS = 2);
}
