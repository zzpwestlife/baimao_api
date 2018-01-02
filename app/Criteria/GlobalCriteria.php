<?php
/**
 * Created by PhpStorm.
 * User: howard
 * Date: 16/4/27
 * Time: 17:48
 */

namespace App\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class GlobalCriteria extends BaseCriteria implements CriteriaInterface
{

    protected $defaultMethod = 'applySort';

    /**
     * 排序
     * User: Howard
     * Date: 2016-05-12
     * @return mixed
     */
    public function applySort()
    {
        $fieldSortable = $this->repository->getFieldSortable();
        if ($fieldSortable) {
            foreach ($fieldSortable as $field => $sort) {
                $this->model = $this->model->orderBy($field, $sort);
            }
        }

        return $this->model;
    }
}