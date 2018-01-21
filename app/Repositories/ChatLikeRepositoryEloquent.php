<?php

namespace App\Repositories;

use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Contracts\ChatLikeRepository;
use App\Models\ChatLike;
use App\Validators\ChatLikeValidator;

/**
 * Class ChatLikeRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class ChatLikeRepositoryEloquent extends BaseRepositoryEloquent implements ChatLikeRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ChatLike::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
