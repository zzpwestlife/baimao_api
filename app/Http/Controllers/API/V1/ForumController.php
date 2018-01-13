<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\Controller;
use App\Models\Forum;
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
     * @comment 论坛列表
     * @param Request $request
     * @return array
     * @author zzp
     * @date 2018-01-10
     */
    public function getIndex(Request $request)
    {
        $keyword = trim($request->input('keyword', ''));

        if (empty($keyword)) {
            $data = $this->forumRepository->all(['id', 'name']);
        } else {
            $data = Forum::where(
                function ($query) use ($keyword) {
                    $query->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('alias', 'like', '%' . $keyword . '%')
                        ->orWhere('alias_abbr', 'like', '%' . $keyword . '%');
                }
            )->get(['id', 'name']);
        }

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
