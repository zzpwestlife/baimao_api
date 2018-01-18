<?php

namespace App\Repositories;

use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\VerifyCodeRepository;
use App\Models\VerifyCode;
use App\Validators\VerifyCodeValidator;

/**
 * Class VerifyCodeRepositoryEloquent
 * @package namespace App\Repositories;
 */
class VerifyCodeRepositoryEloquent extends BaseRepositoryEloquent implements VerifyCodeRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return VerifyCode::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
