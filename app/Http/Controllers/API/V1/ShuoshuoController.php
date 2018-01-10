<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\Controller;
use App\Repositories\Contracts\ChatRepository;
use Illuminate\Http\Request;

/**
 * Class ShuoshuoController
 * @package App\Http\Controllers\API\V1
 */
class ShuoshuoController extends Controller
{
    protected $chatRepository;

    public function __construct(ChatRepository $chatRepository)
    {
        parent::__construct();
        $this->chatRepository = $chatRepository;
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
        $forumId = intval($request->input('forum_id', 0));
        $lastId = trim($request->input('last_id', 0));

        $where = [];
        if (!empty($forumId)) {
            $where['forum_id'] = $forumId;
        }

        if (!empty($lastId)) {
            $where['id'] = ['id', '<', $lastId];
        }

        $chats = $this->chatRepository->whereWithParams($where)
            ->withCount(['shuoshuocomments', 'shuoshuoupvotes'])
            ->orderBy('id', 'desc')->paginate();

        if (count($chats) == 0) {
            $this->markSuccess('没有更多');
            return $this->returnData;
        } else {
            $this->returnData['data'] = $chats;
            $this->markSuccess('数据获取成功');
        }

        return $this->returnData;
    }
}
