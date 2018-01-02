<?php
/**
 * Created by PhpStorm.
 * User: howard
 * Date: 16/5/12
 * Time: 10:48
 */

namespace App\Criteria;

use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class BaseCriteria implements CriteriaInterface
{
    
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;
    protected $model;
    protected $repository;
    protected $method;
    protected $defaultMethod;

    public function __construct(Request $request, $method = null)
    {
        $this->request = $request;
    }

    /**
     * Apply criteria in query repository
     *
     * @param                     $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     * @throws \Exception
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
        
        if (method_exists($this, $this->method)) {
            $model = $this->{$this->method}();
        } else {
            if (method_exists($this, $this->defaultMethod)) {
                $model = $this->{$this->defaultMethod}();
            } else {
                throw new \Exception("方法 {$this->method} 或 默认方法 {$this->defaultMethod} 未找到");
            }
        }
        return $model;
    }
}