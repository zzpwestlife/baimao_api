<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\ShuoshuoCommentRepository;
use App\Models\ShuoshuoComment;
use App\Validators\ShuoshuoCommentValidator;

/**
 * Class ShuoshuoCommentRepositoryEloquent
 * @package namespace App\Repositories;
 */
class ShuoshuoCommentRepositoryEloquent extends BaseRepositoryEloquent implements ShuoshuoCommentRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ShuoshuoComment::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
