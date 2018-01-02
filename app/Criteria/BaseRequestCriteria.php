<?php
/**
 * Created by PhpStorm.
 * User: howard
 * Date: 16/4/27
 * Time: 17:48
 */

namespace App\Criteria;

use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Criteria\RequestCriteria;

class BaseRequestCriteria extends RequestCriteria implements CriteriaInterface
{

    protected $model;
    protected $repository;
    // 键值之间的分隔符
    protected $keyValueSeparator;
    // 参数之间的分隔符
    protected $paramSeparator;


    public function __construct(Request $request)
    {

        $this->keyValueSeparator = config('repository.criteria.params.key_value_separator', '|:');
        $this->paramSeparator = config('repository.criteria.params.param_separator', ';');
        parent::__construct($request);
    }

    /**
     * Apply criteria in query repository
     *
     * @param         Builder|Model $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     * @throws \Exception
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $this->model = $model;
        $this->repository = $repository;

//        return parent::apply($model, $repository);
        return $this->customApply($model, $repository);

    }

    public function customApply($model, RepositoryInterface $repository)
    {
        $fieldsSearchable = $repository->getFieldsSearchable();
        $search = $this->request->get(config('repository.criteria.params.search', 'search'), null);
        $searchFields = $this->request->get(config('repository.criteria.params.searchFields', 'searchFields'), null);
        $filter = $this->request->get(config('repository.criteria.params.filter', 'filter'), null);
        $orderBy = $this->request->get(config('repository.criteria.params.orderBy', 'orderBy'), null);
        $sortedBy = $this->request->get(config('repository.criteria.params.sortedBy', 'sortedBy'), 'asc');
        $with = $this->request->get(config('repository.criteria.params.with', 'with'), null);
        $sortedBy = !empty($sortedBy) ? $sortedBy : 'asc';

        if ($search && is_array($fieldsSearchable) && count($fieldsSearchable)) {
            view()->share([
                'searchMode' => true,
            ]);

            $searchFields = is_array($searchFields) || is_null($searchFields) ? $searchFields : explode($this->paramSeparator, $searchFields);
            $fields = $this->parserFieldsSearch($fieldsSearchable, $searchFields);
            $isFirstField = true;
            $searchData = $this->parserSearchData($search);
            $search = $this->parserSearchValue($search);

            $modelForceAndWhere = $repository->isForceAndWhere();

            $model = $model->where(function ($query) use ($fields, $search, $searchData, $isFirstField, $modelForceAndWhere) {
                /** @var Builder $query */

                foreach ($fields as $field => $condition) {
                    if (is_numeric($field)) {
                        $field = $condition;
                        $condition = "=";
                    }

                    $value = null;

                    $condition = trim(strtolower($condition));

                    if (isset($searchData[$field])) {
                        $value = $condition == "like" ? "%{$searchData[$field]}%" : $searchData[$field];
                    } else {
                        if (!is_null($search)) {
                            $value = $condition == "like" ? "%{$search}%" : $search;
                        }
                    }

                    if (stripos($field, '@')) {
                        $field = str_replace('@', '', $field);
                    }

                    $relation = null;
                    if (stripos($field, '.')) {
                        $explode = explode('.', $field);
                        $field = array_pop($explode);
                        $relation = implode('.', $explode);
                    }
                    if ($isFirstField || $modelForceAndWhere) {
                        if (!is_null($value)) {
                            if (!is_null($relation)) {
                                $query->whereHas($relation, function ($query) use ($field, $condition, $value) {
                                    $query->where($field, $condition, $value);
                                });
                            } else {
                                $query->where($field, $condition, $value);
                            }
                            $isFirstField = false;
                        }
                    } else {
                        if (!is_null($value)) {
                            if (!is_null($relation)) {
                                $query->orWhereHas($relation, function ($query) use ($field, $condition, $value) {
                                    $query->where($field, $condition, $value);
                                });
                            } else {
                                $query->orWhere($field, $condition, $value);
                            }
                        }
                    }
                }
            });
        }

        if (isset($orderBy) && !empty($orderBy)) {
            $split = explode('|', $orderBy);
            if (count($split) > 1) {
                /*
                 * ex.
                 * products|description -> join products on current_table.product_id = products.id order by description
                 *
                 * products:custom_id|products.description -> join products on current_table.custom_id = products.id order
                 * by products.description (in case both tables have same column name)
                 */
                $table = $model->getModel()->getTable();
                $sortTable = $split[0];
                $sortColumn = $split[1];

                $split = explode($this->keyValueSeparator, $sortTable);
                if (count($split) > 1) {
                    $sortTable = $split[0];
                    $keyName = $table . '.' . $split[1];
                } else {
                    /*
                     * If you do not define which column to use as a joining column on current table, it will
                     * use a singular of a join table appended with _id
                     *
                     * ex.
                     * products -> product_id
                     */
                    $prefix = rtrim($sortTable, 's');
                    $keyName = $table . '.' . $prefix . '_id';
                }

                $model = $model
                    ->join($sortTable, $keyName, '=', $sortTable . '.id')
                    ->orderBy($sortColumn, $sortedBy)
                    ->addSelect($table . '.*');
            } else {
                $model = $model->orderBy($orderBy, $sortedBy);
            }
        }

        if (isset($filter) && !empty($filter)) {
            if (is_string($filter)) {
                $filter = explode($this->paramSeparator, $filter);
            }

            $model = $model->select($filter);
        }

        if ($with) {
            $with = explode($this->paramSeparator, $with);
            $model = $model->with($with);
        }

        return $model;

    }

    /**
     * 获取 搜索参数 被重置后的值
     * 如:createTime = 2016-04-28 但是数据库中 是 时间戳
     * 需要在 相应的 model 中 覆写 此值 parserCreateTime4Search
     * @param $search
     *
     * @return array
     */
    protected function parserSearchData($search)
    {
        return $this->repository->parserSearchData($this->parserOriginSearchData($search));
    }

    /**
     * 获取 搜索参数 原始值
     * @param $search
     * @return array
     */
    protected function parserOriginSearchData($search)
    {

        $searchData = $this->repository->parserOriginSearchData($search);

        view()->share([
            'searchCondition' => $searchData,
        ]);
        return $searchData;

    }

    /**
     * @param $search
     *
     * @return null
     */
    protected function parserSearchValue($search)
    {

        if (stripos($search, $this->paramSeparator) || stripos($search, $this->keyValueSeparator)) {
            $values = explode($this->paramSeparator, $search);
            foreach ($values as $value) {
                $s = explode($this->keyValueSeparator, $value);
                if (count($s) == 1) {
                    return $s[0];
                }
            }

            return null;
        }

        return $search;
    }


}