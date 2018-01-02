<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use Helpers;

    protected $repository;
    protected $returnData;

    public function __construct()
    {
        $this->returnData = [
            'code' => 1,
            'msg' => '操作失败',
            'data' => new \stdClass(),
        ];
        $this->logRequest();
        header(sprintf("Current-Server:%s", env('CURRENT_SERVER')));
    }

    /**
     * 标记为 成功
     * @param $message
     * User: Howard
     * Date: 2016-05-11
     */
    protected function markSuccess($message = '操作成功')
    {
        $this->returnData['code'] = 200;
        $this->returnData['msg'] = $message;
    }

    /**
     * 标记为 失败
     * @param $code
     * @param string $message
     * User: Howard
     * Date: 2016-05-11
     */
    protected function markFailed($code, $message = '')
    {
        $this->returnData['code'] = $code;
        if ($message) {
            $this->returnData['msg'] = $message;
        }
    }

    /**
     * @comment api 访问日志，入库
     * @author zzp
     * @date 2017-08-02
     */
    protected function logRequest()
    {
        $route = request()->route();

        $uri = $route->getCompiled()->getStaticPrefix();
        $calledClass = get_called_class();
        $apiVersion = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
        switch ($apiVersion) {
            case 'application/vnd.cj.v2+json':
                $apiVersion = 'v2';
                break;
            case 'application/vnd.cj.v3+json':
                $apiVersion = 'v3';
                break;
            default:
                $apiVersion = 'v1';
                break;
        }

        $columnId = isset($_REQUEST['column_id']) ? $_REQUEST['column_id'] : 0;
        $uuid = isset($_REQUEST['udid']) ? $_REQUEST['udid'] : '';
        $bundleId = isset($_REQUEST['bundleId']) ? $_REQUEST['bundleId'] : '';
        $appVersion = isset($_REQUEST['appVer']) ? $_REQUEST['appVer'] : '';
        $username = isset($_REQUEST['username']) ? $_REQUEST['username'] : '';
        $userId = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
        $lastId = isset($_REQUEST['last_id']) ? $_REQUEST['last_id'] : 0;
        $ip = getClientIp();

        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $url = 'http://' . $host . $uri;

        $projectName = 'unknown';
        if (stripos($calledClass, 'DudaoAPI')) {
            $projectName = 'dudao';
        } elseif (stripos($calledClass, 'API')) {
            $projectName = 'api';
        }
        $objectId = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
        $eventId = strtolower(sprintf('%s_%s_%s', $projectName, $apiVersion, str_replace('/', '_', $uri)));
//        if (in_array($uri, [
//            "/controller/action",
//        ])) {
//            $objectId = $route->getParameter('id');
//        }

        switch ($eventId) {
            case 'api_v1_live_home':
                $objectId = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : 0;
                break;
            case 'api_v1_live_fragment':
                $objectId = isset($_REQUEST['subtopic_id']) ? $_REQUEST['subtopic_id'] : 0;
                break;
            case 'api_v1_user_third-party-user':
                $username = isset($_REQUEST['screen_name']) ? $_REQUEST['screen_name'] : 0;
                break;
            case 'api_v1_magazine_home':
                $tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : '';
                $eventId .= '_4_' . $tab;
                break;
        }

        $nextMonth = Carbon::now();
        $suffix = $nextMonth->year . sprintf("%02d", $nextMonth->month);
        $tableName = 'api_request_log_' . $suffix;
        $date = Carbon::now()->toDateTimeString();
        $extra = [];
        if ($page > 1) {
            $extra['page'] = $page;
        }
        if (!empty($lastId)) {
            $extra['last_id'] = $lastId;
        }
        if (!empty($extra)) {
            $extra = json_encode($extra);
        } else {
            $extra = '';
        }


        $logData = [
            'event_id' => $eventId,
            'user_id' => $userId,
            'username' => $username,
            'bundle_id' => $bundleId,
            'app_version' => $appVersion,
            'uuid' => $uuid,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'column_id' => $columnId,
            'object_id' => $objectId,
            'url' => $url,
            'extra' => $extra,
            'created_at' => $date,
            'updated_at' => $date,
        ];

        DB::table($tableName)->insert($logData);
    }
}
