<?php

namespace App\Repositories;

use App\Criteria\BaseRequestCriteria;
use App\Criteria\GlobalCriteria;
use App\Models\BaseModel;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Container\Container as Application;

/**
 * Class PostRepositoryEloquent
 * @package namespace App\Repositories;
 */
abstract class BaseRepositoryEloquent extends BaseRepository implements BaseRepositoryInterface
{


    // 键值之间的分隔符
    protected $keyValueSeparator;
    // 参数之间的分隔符
    protected $paramSeparator;
    // 默认每页记录数
    protected $pageSize;
    // Request 对象
    protected $request;

    /**
     * @var array
     */
    protected $fieldSortable = [
        'id' => 'desc'
    ];

    public function __construct(Application $app)
    {
        $this->keyValueSeparator = config('repository.criteria.params.key_value_separator', '|:');
        $this->paramSeparator = config('repository.criteria.params.param_separator', ';');
        $this->pageSize = config('repository.pagination.limit', 20);
        $this->request = app('request');
        parent::__construct($app);
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(GlobalCriteria::class));
    }

    public function paginate($limit = null, $columns = ['*'])
    {
        $limit = empty($limit) ? $this->pageSize : $limit;
        $this->pushCriteria(app(BaseRequestCriteria::class));
        return parent::paginate($limit, $columns);
    }

    public function simplePaginate($limit = null, $columns = ['*'])
    {
        $limit = empty($limit) ? $this->pageSize : $limit;
        $this->pushCriteria(app(BaseRequestCriteria::class));
        return parent::simplePaginate($limit, $columns);
    }


    public function isForceAndWhere()
    {
        return true;
    }

    /**
     * 因为 model 与 builder 对象相互转换
     * 这儿获取真是的 model 对象
     * User: Howard
     * Date: 2016-06-16
     * @return mixed
     */
    public function getModel()
    {
        return $this->model->getModel();
    }

    /**
     * 可显示的部分 一般用于API
     * User: Howard
     * Date: 2016-05-12
     * @return mixed
     */
    public function getDisplayAble()
    {
        return $this->getModel()->getDisplayAble();
    }

    /**
     * 获取搜索的参数数据
     * @param null $param 参数名称
     * @param bool $is_origin 是否提取原始数据
     * User: Howard
     * Date: 2017-03-03
     * @return mixed
     */
    public function getSearchData($param = null, $is_origin = true)
    {
        $search = $this->request->get(config('repository.criteria.params.search', 'search'), null);
        $searchData = $this->parserOriginSearchData($search);
        if (false == $is_origin) {
            $searchData = $this->parserSearchData($searchData);
        }
        if (!empty($param)) {
            if (isset($searchData[$param])) {
                $searchData = $searchData[$param];
            } else {
                $searchData = null;
            }
        }
        return $searchData;
    }

    /**
     * 使用数组 填充 where 条件
     * 参考 BaseRepository applyConditions
     * @param array $where
     * User: Howard
     * Date: 2016-05-12
     * @return $this
     */
    public function whereWithParams(array $where)
    {
        if (!empty($where) && is_array($where)) {
            foreach ($where as $field => $value) {
                if (is_null($value)) {
                    continue;
                }
                if (is_array($value)) {
                    list($field, $condition, $val) = $value;
                    if (false === stripos($condition, 'where')) {
                        // < > 什么的
                        $this->model = $this->model->where($field, $condition, $val);
                    } else {
                        if (is_null($val)) {
                            // where NotNull Null
                            $this->model = $this->model->{$condition}($field);
                        } else {
                            // where between
                            $this->model = $this->model->{$condition}($field, $val);
                        }
                    }
                } else {
                    // 最正常的
//                    $this->model = $this->model->where($field, '=', $value);
                    $this->model = $this->model->where($field, $value);
                }
            }
        }
        return $this;
    }

    /**
     * 设置获取记录的条数
     * @param null $limit
     * User: Howard
     * Date: 2017-03-16
     * @return $this
     */
    public function take($limit = null)
    {
        if (!empty($limit)) {
            $this->model->take($limit);
        }
        return $this;
    }


    /**
     * 解析搜索数据
     * @param $searchData
     * User: Howard
     * Date: 2016-06-17
     * @return array
     */
    public function parserSearchData($searchData)
    {
        if (!empty($searchData) && is_array($searchData)) {
            foreach ($searchData as $index => $item) {
                $method = sprintf("parser%s4Search", ucfirst(camel_case(str_replace('@', '', $index))));
                $model = $this->getModel();
                if (method_exists($model, $method)) {
                    $searchData[$index] = $model->{$method}($item);
                }
            }
        }

        return $searchData;
    }

    /**
     * 解析原始的搜索数据
     * @param $search
     * User: Howard
     * Date: 2016-06-17
     * @return array
     */
    public function parserOriginSearchData($search)
    {
        $searchData = [];
        if (stripos($search, $this->keyValueSeparator)) {
            $fields = explode($this->paramSeparator, $search);

            foreach ($fields as $row) {
                try {
                    list($field, $value) = explode($this->keyValueSeparator, $row);
                    $searchData[trim($field)] = trim($value);
                } catch (\Exception $e) {
                    //Surround offset error
                }
            }
        }
        return $searchData;
    }

    public function getFieldSortable()
    {
        return $this->fieldSortable;
    }

    /**
     * 将已经删除的也提取出来
     * User: Howard
     * Date: 2016-06-16
     * @return $this
     */
    public function withTrashed()
    {
        $this->model = $this->model->withTrashed();
        return $this;
    }

    /**
     * @deprecated
     * todo howard  这个方法 删除  使用 findWhere 或 findWhereIn 06-16
     * 根据ID 获取 列表
     * @param mixed $ids
     * @param array|null $params
     * @param array $columns
     * User: Howard
     * Date: 2016-05-12
     * @return array
     */
    public function findList($ids = null, array $params = null, array $columns = ['*'])
    {
        if (!empty($ids)) {
            $this->model = $this->model->whereIn($ids);
        }
        if (!empty($params) && is_array($params)) {
            $this->whereWithParams($params);
        }
        return $this->all($columns);
    }

    public function findOrFail($id, $columns = ['*'])
    {
        $model = null;

        if (!empty($id)) {
            $model = $this->find($id, $columns);
        } else {
            $model = $this->getModel();
        }

        return $model;
    }

    /**
     * 重写 find 方法  调用 model 的 find 而不是 findOrFail
     * @param $id
     * @param array $columns
     * User: Howard
     * Date: 2017-06-07
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();
//        $model = $this->model->findOrFail($id, $columns);
        $model = $this->model->find($id, $columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * count
     * User: Howard
     * Date: 2017-06-08
     * @return mixed
     */
    public function count()
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->count();
        $this->resetModel();

        return $this->parserResult($model);
    }

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
    ) {
        $data = null;
        $ids = $this->getIdByGroup($group_field, $where, $operate, $operate_field);
        if (!empty($ids)) {
            $data = $this->orderBy($order_field, $order_direction)
                ->findWhereIn($operate_field, $ids, $columns)->keyBy($group_field);
        }
        return $data;
    }


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
    public function getIdByGroup($group_field, $where = [], $operate = 'max', $operate_field = 'id')
    {
        $ids = [];
        if (empty($where)) {
            $self = $this;
        } else {
            $self = $this->whereWithParams($where);
        }
        $selfModel = $self->model->groupBy($group_field);
        $select = sprintf('%s(`%s`) as `%s`', strtolower($operate), $operate_field, $operate_field);
        $selfModel = $selfModel->select(DB::raw($select));
        $result = $selfModel->get();

        $this->resetModel();
        $this->resetCriteria();
        $this->resetScope();

        if (!is_empty($result)) {
            $ids = array_pluck($result, $operate_field);
        }

        return $ids;
    }

    /**
     * 为打开链接和监测链接添加 http:// 头，如果没有的话
     * @param $url
     * User: zzp
     * Date: 2016-12-15
     * @return string
     */
    public function filterUrl($url)
    {
        if ($url) {
            $httpStr = 'http://';
            $httpsStr = 'https://';
            $pos = stripos($url, $httpStr);
            $poss = stripos($url, $httpsStr);
            if (!($pos === 0 || $poss === 0)) {
                return $httpStr . $url;
            }
        }
        return $url;
    }


    /**
     * openssl 加密
     * @param string $data
     * User: zzp
     * Date: 2017-04-24
     * @return string
     */

    public function opensslEncrypt($data)
    {
        // 获取公匙
        $publicKeyPath = app_path('Knowledge/OpensslKeys/') . 'rsa_public_key.pem';
        //这个函数可用来判断公钥是否是可用的
        $publicKey = openssl_pkey_get_public(file_get_contents($publicKeyPath));

//        $privateKeyPath = app_path('Knowledge/OpensslKeys/') . 'rsa_private_key.pem';
        //这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
//        $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));

        // 使用公钥加密，客户端使用私钥解密
        if (!is_empty($publicKey)) {
            $encrypted = "";
            $decrypted = "";
//            openssl_private_encrypt($data, $encrypted, $privateKey);//私钥加密
//            $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
//
//            $decrypted = "";
//            openssl_public_decrypt(base64_decode($encrypted), $decrypted, $publicKey);//私钥加密的内容通过公钥可解密出来
//            dd($decrypted);


            openssl_public_encrypt($data, $encrypted, $publicKey);//公钥加密
            return base64_encode($encrypted); // 加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        }
    }

    /**
     * @comment 随机获取一个设备信息
     * @param int $mOS
     * @return string
     * @author zzp
     * @date 2017-07-26
     */
    public function getSingleDeviceInfo($mOS = 2)
    {
        $adCacheStore = BaseModel::AD_CACHE_STORE;
        $singleDeviceInfo = [];
        $groupCountCacheKey = ($mOS == 2) ? BaseModel::CACHE_KEY_AD_DEVICE_INFO_IOS_GROUP_COUNT : BaseModel::CACHE_KEY_AD_DEVICE_INFO_ANDROID_GROUP_COUNT;
        $groupCount = Cache::store($adCacheStore)->get($groupCountCacheKey);
        if (!empty($groupCount)) {
            $groupCacheKey = ($mOS == 2) ? BaseModel::CACHE_KEY_AD_DEVICE_INFO_IOS_GROUP : BaseModel::CACHE_KEY_AD_DEVICE_INFO_ANDROID_GROUP;
            $deviceInfo = Cache::store($adCacheStore)->get(sprintf($groupCacheKey, rand(1, $groupCount)));

            if (!is_empty($deviceInfo)) {
                $singleDeviceInfo = $deviceInfo[rand(0, count($deviceInfo) - 1)];
            }
        }

        return $singleDeviceInfo;
    }
}
