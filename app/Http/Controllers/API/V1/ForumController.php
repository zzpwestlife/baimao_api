<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\Controller;
use App\Repositories\Contracts\ForumRepository;
use Illuminate\Http\Request;

/**
 * Class ForumController
 * @package App\Http\Controllers\API\V1
 */
class ForumController extends Controller
{
    protected $forumRepository;

    public function __construct(ForumRepository $forumRepository)
    {
        parent::__construct();
        $this->forumRepository = $forumRepository;
    }

    /**
     * @comment 说说列表
     * @param Request $request
     * @return array
     * @author zzp
     * @date 2018-01-10
     */
    public function getIndex(Request $request)
    {

        $data = $this->forumRepository->all(['id', 'name']);

        if (count($data) == 0) {
            $this->markSuccess('没有更多');
            return $this->returnData;
        } else {
            $this->returnData['data'] = $data;
            $this->markSuccess('数据获取成功');
        }

        return $this->returnData;
    }
}
