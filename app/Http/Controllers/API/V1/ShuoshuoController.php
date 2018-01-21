<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\Controller;
use App\Repositories\Contracts\ChatLikeRepository;
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
    protected $chatLikeRepository;

    public function __construct(
        ChatRepository $chatRepository,
        ForumRepository $forumRepository,
        ChatLikeRepository $chatLikeRepository
    ) {
        parent::__construct();
        $this->chatRepository = $chatRepository;
        $this->forumRepository = $forumRepository;
        $this->chatLikeRepository = $chatLikeRepository;
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
        $userId = intval($request->input('user_id', 0));

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

        if (count($chats) == 0) {
            $this->returnData['data'] = compact('chats', 'currentForum');
            $this->markSuccess('没有更多');
            return $this->returnData;
        } else {
            $chatIds = $chats->pluck('id')->toArray();
            $myLikes = [];
            if (!empty($userId)) {
                $myLikes = $this->chatLikeRepository->whereWithParams([
                    'user_id' => $userId,
//                    'deleted_at' => ['deleted_at', 'whereNull', null]
                ])->all(['shuoshuo_id']);
                if (!is_empty($myLikes)) {
                    $myLikes = $myLikes->pluck('shuoshuo_id')->toArray();
                }
            }
            $likedChat = array_intersect($myLikes, $chatIds);
            $returnChats = [];
            foreach ($chats as $chat) {
                if (is_empty($chat->user)) {
                    continue;
                }
                $oneItem = new \stdClass();
                if (in_array($chat->id, $likedChat)) {
                    $oneItem->like_status = 1;
                } else {
                    $oneItem->like_status = 0;
                }

                $oneItem->id = $chat->id;
                $oneItem->content = $chat->content;
                $oneItem->user_id = $chat->user_id;
                $oneItem->username = $chat->user->name;
                $oneItem->fullAvatarUrl = $chat->user->fullAvatarUrl;
                $oneItem->forum_id = $chat->forum_id;
                $oneItem->UpdateTimeForHuman = $chat->UpdateTimeForHuman;
                $oneItem->shuoshuoupvotes_count = $chat->shuoshuoupvotes_count;
                $oneItem->shuoshuocomments_count = $chat->shuoshuocomments_count;

                $returnChats[] = $oneItem;
            }
            $this->returnData['data'] = ['chats' => $returnChats, 'currentForum' => $currentForum];
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

    /**
     * @comment 点赞
     * @param Request $request
     * @return array
     * @author zzp
     * @date 2018-01-20
     */
    public function postLike(Request $request)
    {
        $userId = intval($request->input('user_id', 0));
        $chatLikes = $request->input('chat_likes', '');
        $chatLikes = json_decode($chatLikes, true);


        $flag = true;
        if (empty($userId)) {
            $this->markFailed('9401', 'user_id 必填');
            $flag = false;
        }

        if ($flag) {
            $myChatLikes = $this->chatLikeRepository->whereWithParams([
                'user_id' => $userId,
            ])->all(['shuoshuo_id']);
            $myChatLikes = $myChatLikes->pluck('shuoshuo_id')->toArray();

            // 添加的
            $added = array_diff($chatLikes, $myChatLikes);
            if (count($added)) {
                foreach ($added as $item) {
                    $where = [
                        'user_id' => $userId,
                        'shuoshuo_id' => $item
                    ];
                    $this->chatLikeRepository->create($where);
                }
            }

            // 删除的
            $deleted = array_diff($myChatLikes, $chatLikes);
            if (count($deleted)) {
                foreach ($deleted as $item) {
                    $where = [
                        'user_id' => $userId,
                        'shuoshuo_id' => $item
                    ];
                    $status = $this->chatLikeRepository->whereWithParams($where)->first();
                    if (!is_empty($status)) {
                        $this->chatLikeRepository->delete($status->id);
                    }
                }
            }

            $this->markSuccess('success');
            return $this->returnData;
        }

        return $this->returnData;
    }
}
