<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\Controller;
use App\Repositories\Contracts\ChatRepository;
use App\Repositories\Contracts\ForumRepository;
use Illuminate\Http\Request;

/**
 * Class ShuoshuoController
 * @package App\Http\Controllers\API\V1
 */
class ShuoshuoController extends Controller
{
    protected $chatRepository;
    protected $forumRepository;

    public function __construct(ChatRepository $chatRepository, ForumRepository $forumRepository)
    {
        parent::__construct();
        $this->chatRepository = $chatRepository;
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
        $forumId = intval($request->input('forum_id', 0));
        $lastId = trim($request->input('last_id', 0));
        $pageSize = intval($request->input('page_size', 20));

        $where = [];
        $currentForum = new \stdClass();
        if (!empty($forumId)) {
            $where['forum_id'] = $forumId;
            $currentForum = $this->forumRepository->whereWithParams(['id' => $forumId])->first();
        }

        if (!empty($lastId)) {
            $where['id'] = ['id', '<', $lastId];
        }

        $where['user_id'] = ['user_id', '<>', 1111];

        $chats = $this->chatRepository->whereWithParams($where)->with('user')
            ->withCount(['shuoshuocomments', 'shuoshuoupvotes'])
            ->orderBy('id', 'desc')->paginate($pageSize, ['id', 'content', 'user_id']);

        $this->returnData['data'] = compact('chats', 'currentForum');
        if (count($chats) == 0) {
            $this->markSuccess('没有更多');
            return $this->returnData;
        } else {
            $this->markSuccess('数据获取成功');
        }

        return $this->returnData;
    }

    /**
     * @comment 发表一条
     * @param Request $request
     * @return array
     * @author zzp
     * @date 2018-01-13
     */
    public function postAdd(Request $request)
    {
        $forumId = intval($request->input('forum_id', 0));
        $userId = intval($request->input('user_id', 0));
        $content = trim($request->input('content', ''));


        $flag = true;
        if (empty($userId)) {
            $this->markFailed('9401', 'user_id 必填');
            $flag = false;
        } elseif (empty($forumId)) {
            $this->markFailed('9402', 'forum_id 必填');
            $flag = false;
        } elseif (empty($content)) {
            $this->markFailed('9403', 'content 必填');
            $flag = false;
        }

        if ($flag) {
            $createData = [
                'user_id' => $userId,
                'forum_id' => $forumId,
                'content' => $content
            ];
            $object = $this->chatRepository->create($createData);
            if (count($object) == 0) {
                $this->markSuccess('没有更多收藏');
                return $this->returnData;
            } else {
                $this->returnData['data'] = $object;
                $this->markSuccess('数据获取成功');
            }
        }

        return $this->returnData;
    }
}
