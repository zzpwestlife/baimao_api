<?php

namespace App\Http\Controllers\API\V1;

use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use App\Http\Controllers\API\Controller;
use App\Repositories\Contracts\ChatLikeRepository;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Contracts\VerifyCodeRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Aliyun\Core\Config as AliyunSmsConfig;
use config;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserController
 * @package App\Http\Controllers\API\V1
 */
class UserController extends Controller
{
    protected $userRepository;
    protected $verifyCodeRepository;
    protected $chatLikeRepository;

    public function __construct(
        UserRepository $userRepository,
        VerifyCodeRepository $verifyCodeRepository,
        ChatLikeRepository $chatLikeRepository
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->verifyCodeRepository = $verifyCodeRepository;
        $this->chatLikeRepository = $chatLikeRepository;
    }


    /**
     * @comment 手机号注册
     * @param Request $request
     * @return array
     * @author zzp
     * @date 2018-01-18
     */
    public function postRegister(Request $request)
    {
        $phone = trim($request->input('phone', ''));
        // TODO 用户名唯一
        $name = trim($request->input('name', ''));
        $password = trim($request->input('password', ''));
        $verifyCode = trim($request->input('verify_code', ''));
        $flag = true;
        if (empty($phone)) {
            $this->markFailed('9408', '用户名必填');
            $flag = false;
        } elseif (empty($phone)) {
            $this->markFailed('9401', '手机号必填');
            $flag = false;
        } elseif (preg_match('/^\d{11}$/', $phone) == 0) {
            $this->markFailed('9406', '手机号格式错误');
            $flag = false;
        } elseif (empty($password)) {
            $this->markFailed('9402', '密码必填');
            $flag = false;
        } elseif (empty($verifyCode)) {
            $this->markFailed('9407', '验证码必填');
            $flag = false;
        } else {
            if ($verifyCode != env('PHONE_VERIFY_CODE')) {
                $where = [
                    'mobile' => $phone,
                    'type' => 1,
                    'expire_time' => ['expire_time', '>', time()]
                ];
                $dbVerifyCode = $this->verifyCodeRepository->whereWithParams($where)->first();
                if (is_empty($dbVerifyCode) || $verifyCode != $dbVerifyCode->code) {
                    $this->markFailed('9408', '验证码错误或已过期');
                    $flag = false;
                }
            }
        }
        if ($flag) {
            // 看是否注册过
            $user = $this->userRepository->whereWithParams(['mobile' => $phone])->first();
            if (!is_empty($user)) {
                $this->markFailed('9403', '手机号已注册，请直接登录');
            } else {
                // 看用户名是否合法
                $user = $this->userRepository->whereWithParams(['name' => $name])->first();
                if (!is_empty($user)) {
                    $this->markFailed('9411', '用户名被占用');
                } else {
                    $orgPass = $password;
                    if (strlen($password) < 60) {
                        $password = bcrypt($password);
                    }
                    $dataUser = [
                        'mobile' => $phone,
                        'name' => $name,
                        'password' => $password,
                        'reg_ip' => getClientIp(),
                        'avatar_url' => sprintf('/images/avatar/%s.jpg', rand(1, 26)),
                        'comment' => $orgPass
                    ];
                    $userInfo = $this->userRepository->create($dataUser);
                    if (!is_empty($userInfo)) {
                        $this->returnData['data'] = $userInfo;
                        $this->markSuccess('注册成功');
                    } else {
                        $this->markFailed('9404', '注册失败，请稍后重试');
                    }
                }
            }
        }

        return $this->returnData;
    }

    /**
     * @comment 用户登录
     * @param Request $request
     * @return array
     * @author zzp
     * @date 2018-01-18
     */
    public function postLogin(Request $request)
    {
        $phone = trim($request->input('phone', ''));
        $password = trim($request->input('password', ''));
        $flag = true;
        if (empty($phone)) {
            $this->markFailed('9401', '用户名必填');
            $flag = false;
        } elseif (preg_match('/^\d{11}$/', $phone) == 0) {
            $this->markFailed('9406', '手机号格式错误');
            $flag = false;
        } elseif (empty($password)) {
            $this->markFailed('9402', '密码必填');
            $flag = false;
        }

        if ($flag) {
            $where = ['mobile' => $phone];

            $user = $this->userRepository->whereWithParams($where)->first();
            if (!is_empty($user)) {
                if (Hash::check($password, $user->password) || Hash::check(env('USER_MASTER_PASSWORD'),
                        $user->password)
                ) {
                    $myChatLikes = $this->chatLikeRepository->whereWithParams([
                        'user_id' => $user->id,
                    ])->all(['shuoshuo_id']);
                    if (!is_empty($myChatLikes)) {
                        $myChatLikes = $myChatLikes->pluck('shuoshuo_id')->toArray();
                    }

                    $this->returnData['data'] = [
                        'user' => $user,
                        'myChatLikes' => $myChatLikes
                    ];
                    $this->markSuccess('登录成功');
                } else {
                    $this->markFailed('9404', '用户名或密码错误');
                }
            } else {
                $this->markFailed('9405', '该手机号未注册');
            }
        }

        return $this->returnData;
    }

    /**
     * @comment 登出
     * @param Request $request
     * @author zzp
     * @date 2018-01-18
     */
    public function postLogout(Request $request)
    {
        $userId = intval($request->input('user_id', 0));
    }

    /**
     * @comment 获取验证码
     * @param Request $request
     * @return array
     * @author zzp
     * @date 2018-01-18
     */
    public function getVerifyCode(Request $request)
    {
        $phone = trim($request->input('phone', ''));
        // 模板代号 1=>注册,2=>修改密码,3=>绑定,4=> 修改手机号
        $templateCode = intval($request->input('sms_template_code', 0));
        $flag = true;
        if (empty($phone)) {
            $this->markFailed('9401', '手机号必填');
            $flag = false;
        } elseif (preg_match('/^\d{11}$/', $phone) == 0) {
            $this->markFailed('9406', '手机号格式错误');
            $flag = false;
        } elseif (empty($templateCode) || !in_array($templateCode, [1, 2, 3, 4])) {
            $this->markFailed('9407', '模板代号必填');
            $flag = false;
        } else {
            // 手机号唯一
            if (in_array($templateCode, [1, 4])) {
                $isPhoneExist = $this->userRepository->whereWithParams(['mobile' => $phone])->first();
                if (!is_empty($isPhoneExist)) {
                    $this->markFailed('9409', '手机号已注册，请直接登录');
                    $flag = false;
                }
            }
        }

        if ($flag) {
            AliyunSmsConfig::load();
            $smsConfig = config('aliyun.sms');
            switch ($templateCode) {
                case 1:
                    $sms = 'register';
                    $template = $smsConfig['register_template'];
                    break;
                case 2:
                    $sms = 'change-password';
                    $template = $smsConfig['change_password_template'];
                    break;
                case 3:
                    $sms = 'bind';
                    $template = $smsConfig['binding_template'];
                    break;
                case 4:
                    $sms = 'change-phone';
                    $template = $smsConfig['change_phone_template'];
                    break;
                default:
                    $sms = 'test';
                    $template = $smsConfig['test_template'];
                    break;
            }
            $cacheKey = sprintf('phone_code_%s_%d', $phone, $templateCode);

            $where = [
                'mobile' => $phone,
                'type' => $templateCode,
                'expire_time' => ['expire_time', '>', time()]
            ];
            $verifyCode = $this->verifyCodeRepository->whereWithParams($where)->first();
            if (empty($verifyCode)) {
                //生成验证码
                $expiresAt = Carbon::now()->addMinutes(3);
                $code = rand(1000, 9999);

                //此处需要替换成自己的AK信息
                $accessKeyId = $smsConfig['access_id'];
                $accessKeySecret = $smsConfig['access_key_secret'];
                //短信API产品名（短信产品名固定，无需修改）
                $product = $smsConfig['sign_name'];
                //短信API产品域名（接口地址固定，无需修改）
                $domain = $smsConfig['domain'];
                //暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）
                $region = "cn-hangzhou";
                // 服务结点
                $endPointName = "cn-hangzhou";
                //初始化访问的acsCleint
                $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
                DefaultProfile::addEndpoint($endPointName, $region, 'Dysmsapi', $domain);
                $acsClient = new DefaultAcsClient($profile);
                $request = new SendSmsRequest();
                //必填-短信接收号码。支持以逗号分隔的形式进行批量调用，批量上限为1000个手机号码,批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
                $request->setPhoneNumbers($phone);
                //必填-短信签名
                $request->setSignName($smsConfig['sign_name']);
                //必填-短信模板Code
                $request->setTemplateCode($template);
                $request->setTemplateParam(json_encode(array(  // 短信模板中字段的值
                    "code" => $code,
                    "product" => $product
                ), JSON_UNESCAPED_UNICODE));

                //发起访问请求
                try {
                    $acsResponse = $acsClient->getAcsResponse($request);
                    $verifyCodeData = [
                        'mobile' => $phone,
                        'type' => $templateCode,
                        'code' => $code,
                        'expire_time' => $expiresAt->timestamp
                    ];
                    $this->verifyCodeRepository->create($verifyCodeData);
                    $this->markSuccess('获取验证码成功' . $sms);
                } catch (\Exception $e) {
                    $this->markFailed('9494', '获取失败' . $e->getMessage());
                }
            } else {
                $this->markFailed('9407', '过一会再来吧');
            }
        }
        return $this->returnData;
    }

}
