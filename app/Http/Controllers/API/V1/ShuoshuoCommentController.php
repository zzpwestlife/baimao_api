<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\Controller;
use App\Repositories\Contracts\ChatRepository;
use App\Repositories\Contracts\ForumRepository;
use App\Repositories\Contracts\ShuoshuoCommentRepository;
use Illuminate\Http\Request;

/**
 * Class ShuoshuoCommentController
 * @package App\Http\Controllers\API\V1
 */
class ShuoshuoCommentController extends Controller
{
    protected $shuoshuoCommentRepository;
    protected $forumRepository;
    protected $chatRepository;

    public function __construct(
        ShuoshuoCommentRepository $shuoshuoCommentRepository,
        ForumRepository $forumRepository,
        ChatRepository $chatRepository
    ) {
        parent::__construct();
        $this->shuoshuoCommentRepository = $shuoshuoCommentRepository;
        $this->forumRepository = $forumRepository;
        $this->chatRepository = $chatRepository;
    }

    /**
     * @comment 说说评论列表
     * @param Request $request
     * @return array
     * @author zzp
     * @date 2018-01-10
     */
    public function getIndex(Request $request)
    {
        $chatId = intval($request->input('chat_id', 0));

        $where = [];
        if (!empty($chatId)) {
            $chat = $this->chatRepository->whereWithParams(['id' => $chatId])->with('user')->first();
            $where['shuoshuo_id'] = $chatId;

            $comments = $this->shuoshuoCommentRepository->whereWithParams($where)->with(['user', 'parent'])
                ->orderBy('id', 'desc')->all(['id', 'content', 'user_id', 'parent_id', 'updated_at']);

            $this->returnData['data'] = compact('comments', 'chat');
            $this->markSuccess('数据获取成功');
        } else {
            $this->markFailed('1001', 'chat_id 不能为空');
        }

        return $this->returnData;
    }

    /**
     * @comment 添加说说评论
     * @param Request $request
     * @return array
     * @author zzp
     * @date 2018-01-19
     */
    public function postCreate(Request $request)
    {
        $chatId = intval($request->input('chat_id', 0));
        $userId = intval($request->input('user_id', 0));
        $content = trim($request->input('content', ''));
        $flag = true;
        if (empty($chatId)) {
            $this->markFailed('8401', '你评的啥？');
            $flag = false;
        } elseif (empty($userId)) {
            $this->markFailed('8402', '你是谁？');
            $flag = false;
        } elseif (empty($content)) {
            $this->markFailed('8403', '你写的啥？');
            $flag = false;
        }

        if ($flag) {
            $commentData = [
                'user_id' => $userId,
                'shuoshuo_id' => $chatId,
                'content' => $content
            ];
            $this->shuoshuoCommentRepository->create($commentData);
            $this->markSuccess('添加评论成功');
        }
        return $this->returnData;
    }
}
