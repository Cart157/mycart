<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/4/12
 * Time: 12:59
 */

namespace Modules\Base\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Base\Libraries\Captcha\Sms\SmsCaptcha;
use Modules\Base\Models\UserProfile;
use Modules\Base\Models\Wallet;
use Modules\Base\Models\WalletLog;
use Modules\Base\Models\WalletRecord;
use Validator;
use JWTAuth;
use Request as Re;


/**
 * 钱包也属于个人信息中的一部分内容：凡是与个人财产相关的内容都应该由钱包管理
 * 钱包操作类，主要功能：开放存钱和取钱接口给外部调用
 * 使用钱包功能首先要初始化钱包！
 * 初始化步骤分两个：简单初始化和完全初始化！区别是完全初始化要绑定支付宝【需要提供与支付宝一致的真实名称，身份证号，支付宝账号】！
 * 钱包主要有两大功能：存入和取出
 * 存入【平台退款，平台收入(例如文章)】需要钱包进行简单的初始化【仅仅需要必要的用户id和金额0化即可】
 * 取出需要提供支付密码
 * 提现需要支付宝相关信息，所以需要完全初始化操作！
 */
class WalletController extends \BaseController
{
//初始化钱包的相关操作==================================================================================================
    /**
     * 简单初始化钱包：如果用户还没有被添加到wallet表，就简单初始化这个用户的钱包
     * @param Request $request
     * @URL post:api/wallet/new
     */
    public function newWallet(Request $request)
    {
        //初始化返回信息$res = [code=200 message=""]
        $jsonMsg = parent::apiFetchedResponse();

        //验证
        $this->validate($request, [
            'uid' => 'required|integer',
        ]);

        if ($request->uid != JWTAuth::user()->id) {

            $jsonMsg['status'] = 403;
            $jsonMsg['message'] = "不是当前用户，非法操作";
            return $jsonMsg;
        }

        try {
            //调用插入新用户达到初始化目的
            $resault = $this->intWallet($request->uid);
            //若设置失败
            if (!$resault) {
                //抛出异常信息
                throw new \Exception("失败");
            }
        } catch (\Exception $e) {
            //利用apiException重新设置返回信息
            $jsonMsg = parent::apiException($e, $jsonMsg);
        }

        return $jsonMsg;
    }
	

    //初始化钱包方法，由于多次用到，所以直接定义，共调用使用
    public function intWallet($uid)
    {
        return (new Wallet())->insertNewUser($uid);
    }


    /**
     * 设置支付密码
     * @param Request $request
     * @return mixed
     * @url post:api/wallet/password   Route::post('wallet/password', 'Wallet\WalletController@setPayPassword');
     * 逻辑：验证用户id，设定的密码，是否存在验证码->验证用户的合法性->验证手机验证码的正确定->设置密码->返回客户端信息
     */
    public function setPayPassword(Request $request)
    {
//        dd(1);
        //初始化返回信息$res = [code=200 message=""]
        $jsonMsg = parent::apiFetchedResponse();

        /**
         * 思路
         * 客户端传递过来uid和密码
         *
         */
        $validator = Validator::make(Re::all(),  [
                'password' => "required|digits:6",
                'mobile' => "required",
                'captcha' => 'required'
            ],[
                'password.digits' => "6位的数字密码",
            ]
        );

        if ($validator->fails()) {
            abort(400, $validator->errors()->first());
        }


        //手机验证
        $captcha = new SmsCaptcha();
        //调用Modules\Base\Libraries\Captcha\Sms\SmsCaptcha\validate($target, $code)验证手机验证码
        $captcha->validate(Re::input('mobile'), Re::input('captcha'));

        try {
            //调用模型的setPayPassword方法设置密码
            $res = (new Wallet())->setPayPassword(JWTAuth::user()->id, $request->password);
            //若设置失败
            if (!$res) {
                //抛出异常信息
                throw new \Exception("失败");
            }
        } catch (\Exception $e) {
            //利用apiException重新设置返回信息
            $jsonMsg = parent::apiException($e, $jsonMsg);
        }

        //返回客户端信息
        return $jsonMsg;
    }


    /**
     * 找回支付密码
     * @param Request $request
     * @return mixed
     * @url post:api/wallet/password   Route::post('wallet/password', 'Wallet\WalletController@setPayPassword');
     * 逻辑：验证用户id，设定的密码，是否存在验证码->验证用户的合法性->验证手机验证码的正确定->设置密码->返回客户端信息
     * api/user/
     */
    public function reSetPayPassword(Request $request)
    {
//        dd(1);
        //初始化返回信息$res = [code=200 message=""]
        $jsonMsg = parent::apiFetchedResponse();

        $uid = JWTAuth::user()->id;

        $newWallet = ( new Wallet() )->where('user_id', $uid)->first();
        if( $newWallet->pay_password_checked == 0 ){
            return error_json(403, "您从未设置过支付密码，请先设置支付密码");
        }

        /**
         * 思路
         * 客户端传递过来uid和密码
         *
         */
        $validator = Validator::make(Re::all(),  [
            'password' => "required|digits:6",
            'mobile' => "required",
            'captcha' => 'required'
        ],[
                'password.digits' => "6位的数字密码",
            ]
        );

        if ($validator->fails()) {
            abort(400, $validator->errors()->first());
        }


        //手机验证
        $captcha = new SmsCaptcha();
        //调用Modules\Base\Libraries\Captcha\Sms\SmsCaptcha\validate($target, $code)验证手机验证码
        $captcha->validate(Re::input('mobile'), Re::input('captcha'));

        try {
            //调用模型的setPayPassword方法设置密码
            $res = $newWallet->setPayPassword($uid, $request->password);
            //若设置失败
            if (!$res) {
                //抛出异常信息
                throw new \Exception("失败");
            }
        } catch (\Exception $e) {
            //利用apiException重新设置返回信息
            $jsonMsg = parent::apiException($e, $jsonMsg);
        }

        //返回客户端信息
        return $jsonMsg;
    }



//钱包展示操作==========================================================================================================

    /**
     * 我的钱包展示页面
     * @param Request $request
     * @return mixed
     * @url get:api/wallet/index
     */
    public function walletIndex(Request $request)
    {
        //初始化返回信息$res = [code=200 message=""]
        $jsonMsg = parent::apiFetchedResponse();

        //主要逻辑：完成用户金币，优惠券，现金等信息的统计，返回给客户端
        /*$this->validate($request, [
                'uid' => "required"
            ]
        );

        if ($request->uid != JWTAuth::user()->id) {
            $jsonMsg['status'] = 403;
            $jsonMsg['message'] = "不是当前用户，非法操作";
            return $jsonMsg;
        }*/

        $validator = Validator::make(Re::all(), [
            'uid' => "required",
        ]);

        if ($validator->fails()) {
//            abort(400, $validator->errors()->first());
            $jsonMsg['status'] = 400;
            $jsonMsg['message'] = "亲，缺少uid参数";
            return $jsonMsg;
        }

        //自动初始化钱包
        if (!((new Wallet())->selfHaveUser(JWTAuth::user()->id))) {
            try {
                //调用插入新用户达到初始化目的
                $resault = $this->intWallet(JWTAuth::user()->id);
                //若设置失败
                if (!$resault) {
                    //抛出异常信息
                    throw new \Exception("失败");
                }
            } catch (\Exception $e) {
                //利用apiException重新设置返回信息
                $jsonMsg = parent::apiException($e, $jsonMsg);
            }
        }


        try {
            //JWTAuth::user()->id 登录之后直接可以获取这个id
            //查询制定用户的金钱数
            $jsonMsg['data']['userCash'] = (new Wallet())->seeCash($request->uid);
            if (!$jsonMsg['data']['userCash']) {
                throw new \Exception("获取现金失败");
            }

            //获取金币数
            $jsonMsg['data']['usercoins'] = (new UserProfile())->find($request->uid, ['coin_num']);
            if (!$jsonMsg['data']['usercoins']) {
                throw new \Exception("获取金币失败");
            }


        } catch (\Exception $e) {
            //利用apiException重新设置返回信息
            $jsonMsg = parent::apiException($e, $jsonMsg);
        }


        $jsonMsg['data']['washCards'] = ['cards_num' => 3];
        $jsonMsg['data']['userStones'] = ['stone_number' => 3000];

        //返回客户端信息
        return $jsonMsg;
    }


//钱包明细==============================================================================================================

    //@URL get: api/wallet/log
    public function walletLogDetail(Request $request)
    {

        if (isset($request->page)) {
            $page = (int)$request->page;
        }
//        dd($page);

        /**
         * 逻辑：
         * 根据用户的id查询
         */
        //初始化返回信息$res = [code=200 message=""]
        $jsonMsg = parent::apiFetchedResponse();

        $uid = JWTAuth::user()->id;

//        dd($uid);

        try {
            $jsonMsg['data'] = (new WalletLog())->logList($uid, $page);
            if (!$jsonMsg['data']) {
                throw new \Exception("没有任何记录");
            }
        } catch (\Exception $e) {
            //利用apiException重新设置返回信息
            $jsonMsg = parent::apiException($e, $jsonMsg);
        }

        //返回客户端信息
        return $jsonMsg;

    }


    /**
     * 钱包余额
     * @param $uid
     * @URL
     */
    public function walletCashLeft()
    {
        //初始化返回信息$res = [code=200 message=""]
        $jsonMsg = parent::apiFetchedResponse();

        $uid = JWTAuth::user()->id;

        try {
            //查询用户钱包余额
            $jsonMsg['data']['userCash'] = (new Wallet())->seeCash($uid);
        } catch (\Exception $e) {
            //利用apiException重新设置返回信息
            $jsonMsg = parent::apiException($e, $jsonMsg);
        }

        return $jsonMsg;
    }

//实名认证==============================================================================================================

    /**
     * 实名认证方法
     * @param Request $request
     * @return mixed
     * @URL POST:
     */
    public function checkTrueName(Request $request)
    {
        //逻辑：接受实名名字和身份证号，请求芝麻认证，如果芝麻认证通过，则返回成功信息，否则返回失败信息
//        dd($request);


        //初始化返回信息$res = [code=200 message=""]
        $jsonMsg = parent::apiFetchedResponse();

        //接受信息并验证
        $validator = Validator::make(Re::all(), [
            'truename' => 'required',
            'id_card' => 'required|',
        ], [
            'truename.required' => '请输入真名',
            'id_card.required' => '请输入身份证号',
        ]);

        if ($validator->fails()) {
            $jsonMsg['status'] = 400;
            $jsonMsg['message'] = $validator->errors()->first();
            return $jsonMsg;
        }

        return $jsonMsg;
    }


//绑定支付宝的相关操作==================================================================================================
    //获取用户手机号
    /**
     * 获取登录用户的手机号
     * @return mixed
     * @URL get: api/user/mobile
     */
    public function getUserMobile()
    {
        //初始化返回信息$res = [code=200 message=""]
        $jsonMsg = parent::apiFetchedResponse();

        try {
            $jsonMsg['data']['mobile'] = JWTAuth::user()->profile->mobile;
        } catch (\Exception $e) {
            $jsonMsg = parent::apiException($e, $jsonMsg);
        }

        return $jsonMsg;
    }

    //判断用户是否已经绑定了支付宝
    //@url wallet/havealipay
    public function haveAliPay()
    {
        //初始化返回信息$res = [code=200 message=""]
        $jsonMsg = parent::apiFetchedResponse();


        /*if ($uid != JWTAuth::user()->id) {

            $jsonMsg['status'] = 403;
            $jsonMsg['message'] = "不是当前用户，非法操作";
            return $jsonMsg;
        }*/

        $uid = JWTAuth::user()->id;

        //user表中有这个用户    and     user_profile表中有alipay的信息    wallet表中有alipay信息

        //base_user表中有该用户
        $haveBaseUser = (new Wallet())->havUser($uid);
        if ($haveBaseUser) {
            //user_profile表有该用户
            $haveUserProfile = UserProfile::find($uid);

            //根据用户id 获取wallet表中的用户
            $walletUser = (new Wallet())->selfHaveUser($uid);

            //判断没有绑定支付宝的条件: user_profile 表中和wallet表中 都没有 alipay_account 值
            if (!$haveUserProfile->alipay_account && !$walletUser->alipay_account) {

                $jsonMsg['data']['bindtype'] = 0;
                $jsonMsg['message'] = '用户还没有绑定支付宝信息';

            } else {
                $jsonMsg['data']['bindtype'] = 1;
                $jsonMsg['message'] = 'done';
                $jsonMsg['data']['alipay_account'] = $haveUserProfile->alipay_account ?: $walletUser->alipay_account;
            }
        } else {

            $jsonMsg['data']['bindtype'] = 0;
            $jsonMsg['message'] = '不存在该用户';

        }

        return $jsonMsg;
    }

    //绑定支付宝

    /**
     * 绑定支付宝
     * @param $uid
     * @url POST ： wallet/newalipay
     */
    public function bindAliPay(Request $request)
    {
        /**
         * 绑定支付宝
         * 基本逻辑流程：用户有效性验证->绑定支付宝验证->接受参数并进行合法性验证->手机验证码验证->获取用户信息,profile信息,wallet信息->更新profile表，更新wallet表->返回信息
         */

        //初始化返回信息$res = [code=200 message=""]
        $jsonMsg = parent::apiFetchedResponse();

        /* if ($uid != JWTAuth::user()->id) {
             $jsonMsg['status'] = 403;
             $jsonMsg['message'] = "不是当前用户，非法操作";
             return $jsonMsg;
         }*/
        //获取当前用户
        $user = JWTAuth::user();

        //获取用户id
        $uid = $user->id;

        //如果已经绑定过就不要再绑定了
        $haveAlipay = $this->haveAliPay();
        if (1 == $haveAlipay['data']['bindtype']) {
            $jsonMsg['status'] = 304;
            $jsonMsg['message'] = "已经绑定过，不要重复绑定";
            return $jsonMsg;
        }


        $validator = Validator::make(Re::all(), [
            'alipay_account' => 'required|confirmed',
            'alipay_realname' => 'required',
//            'mobile' => 'required|digits:11',
            'captcha' => 'required',
        ], [
            'alipay_account.required' => '请输入支付宝账号',
            'alipay_account.confirmed' => '输入的两个账号不一致',
            'alipay_realname.required' => '请输入支付宝账号的实名信息，以便打款时确认您的身份',
//            'mobile.required' => '手机号是必须项',
//            'mobile.digits' => '手机号的格式不正确',
            'captcha.required' => '请输入验证码',
        ]);

        try {
            if ($validator->fails()) {
                $jsonMsg['status'] = 400;
                $jsonMsg['message'] = $validator->errors()->first();
                return $jsonMsg;
            }

            //手机验证
            //获取手机号
            $mobile = $user->profile->mobile;
            $captcha = new SmsCaptcha();
            //调用Modules\Base\Libraries\Captcha\Sms\SmsCaptcha\validate($target, $code)验证手机验证码
            $captcha->validate($mobile, Re::input('captcha'));


            $user_profile = $user->profile;
            $user_wallet = $user->wallet;

            //更新user_proifile表
            $user_profile->alipay_account = Re::input('alipay_account');
            $user_profile->alipay_realname = Re::input('alipay_realname');
            $user_profile->save();

            //更新钱包表
            $user_wallet->alipay_account = $user_profile->alipay_account;
            $user_wallet->alipay_realname = $user_profile->alipay_realname;
            $user_wallet->save();

        } catch (\Exception $e) {
            $jsonMsg = parent::apiException($e, $jsonMsg);
        }

        return $jsonMsg;
    }




//钱包存钱的相关操作====================================================================================================

    /**
     * 向钱包中存钱：根据传入type来标识存钱的原因【退款，其他类型钱包的提现到主钱包】
     * 可能发生的情景:平台订单退款，从小钱包提现，充值
     * @param $type         业务类型：0平台退款  1提现[103 CMS提现   203 主播提现] 2交易 3充值,
     * @param $orderId      提供订单号，若没有订单号设为0,
     * @param $fromUid      付款方，平台设为0,
     * @param $toUid        收款方，这个必须有
     * @param $money        金额，这个必须有
     * @param $remark       备注, 可以有
     * @param $run          要调用的具体的业务方法
     * @URL post:api/wallet/in
     */
    public function saveMoney(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|integer',
            'order_id' => 'required',
            'from_uid' => 'required|integer',
            'to_uid' => 'required|integer',
            'money' => 'required',
            // 'remark' => 'required',
        ]);


        // 业务类型: 0平台退款  1提现【】 2交易 3充值
        $type = $request->type;
        // 订单号: 若是订单退款，应该提供订单号是哪个订单要退款
        $orderId = $request->order_id ?: '';
        // 付款方: 0 代表平台，要么就是某个商家的id
        $fromUid = $request->from_uid ?: 0;
        // 收款方: 这是主角，必不可少！是具体用户的id
        $toUid = $request->to_uid;
        // 钱数: 必要参数
        $money = $request->money ?: '0.0';
        // 备注信息: 非必要但是最好有
        $remark = $request->remark ?: $this->setRemark($type);

        //初始化钱包，为了避免钱包没有初始化
        $this->intWallet($toUid);

        //创建流水sn号
        $recordSn = $this->makeRecordSn();


        //自动调用设定好的业务方法，具体方法在下面找即可
        //dd($toUid);
        $options = [
            'recordSn' => $recordSn,
            'type' => $type,
            'orderId' => $orderId,
            'fromUid' => $fromUid,
            'toUid' => $toUid,
            'money' => $money,
            'remark' => $remark
        ];
        $jsonMsg = self::saveMoneyInWallet($options);

        return $jsonMsg;

    }


    /**
     * 内部小钱包的提现操作
     * @param $type         业务类型：103 CMS文章钱包提现   203 主播钱包提现 303 。。。,
     * @param $orderId      提供订单号，若没有订单号设为0,
     * @param $fromUid      付款方，用户的id,
     * @param $toUid        收款方，用户的id
     * @param $money        金额，这个必须有
     * @param $remark       备注, 可以有
     */
    public function InnerCash($type, $order_id, $from_uid, $to_uid, $money, $remark = '')
    {
        //如果fromuid和touid不同，不是自己的转现操作
        if ($from_uid != $to_uid || $from_uid != JWTAuth::user()->id) {
            $jsonMsg['status'] = 403;
            $jsonMsg['message'] = "不是当前用户，非法操作";
            return $jsonMsg;
        }


        // 业务类型: 0平台退款  1提现【】 2交易 3充值
        $type = $type;
        // 订单号: 若是订单退款，应该提供订单号是哪个订单要退款
        $orderId = $order_id ?: '';
        // 付款方: 0 代表平台，要么就是某个商家的id
        $fromUid = $from_uid ?: 0;
        // 收款方: 这是主角，必不可少！是具体用户的id
        $toUid = $to_uid;
        // 钱数: 必要参数
        $money = $money ?: '0.0';
        // 备注信息: 非必要但是最好有
        $remark = $remark ?: $this->setRemark($type);

        //初始化钱包，为了避免钱包没有初始化
        $this->intWallet($toUid);

        //创建流水sn号
        $recordSn = $this->makeRecordSn();


        //自动调用设定好的业务方法，具体方法在下面找即可
        $options = [
            'recordSn' => $recordSn,
            'type' => $type,
            'orderId' => $orderId,
            'fromUid' => $fromUid,
            'toUid' => $toUid,
            'money' => $money,
            'remark' => $remark
        ];
        $jsonMsg = self::saveMoneyInWallet($options);

        return $jsonMsg;

    }


    /**
     * 存钱进钱包:辅助方法
     * 流程：添加wallet_record表记录->添加wallet_log表记录->修改wallet表记录
     * @param $type         业务类型：0平台退款  1提现 2交易 3充值,
     * @param $orderId      提供订单号，若没有订单号设为0,
     * @param $fromUid      付款方，平台设为0,
     * @param $toUid        收款方，这个必须有
     * @param $money        金额，这个必须有
     * @param $remark       备注, 可以有
     * @return String jsonMsg
     */
    public static function saveMoneyInWallet($options)
    {
        //接受各个参数
        /*$recordSn = $options['recordSn'];
        $type = $options['type'];
        $orderId = $options['orderId'];
        $fromUid = $options['fromUid'];
        $toUid = $options['toUid'];
        $money = $options['money'];
        $remark = $options['remark'];*/
        extract($options);

        //初始化返回信息$res = [code=200 message=""]
        $jsonMsg = parent::apiFetchedResponse();


        //开启事务
        DB::beginTransaction();
        try {
            //添加wallet_record表记录，并验证结果，若果失败，抛出异常
            $res = (new WalletRecord)->insertNewItem($recordSn, $type, $orderId, $fromUid, $toUid, $money, $remark);
            if (!$res) {
                throw new \Exception("WalletRecord表添加失败");
            }

            //添加wallet_log表记录
            $res = (new WalletLog())->insertNewItem($recordSn, $toUid, $money, $remark);
            if (!$res) {
                throw new \Exception("walletlog表添加失败");
            }
//                dd(11);
            //修改wallet表记录
            $res = (new Wallet())->modiefyMoney($toUid, $money);
            if (!$res) {
                throw new \Exception("wallet表添加失败");
            }

            //提交事务
            DB::commit();

            //添加成功后应该把更新之后的数据返回给客户端：uid, 变化的金额，不应该直接查询wallet表然后直接返回！
            $jsonMsg['data'] = Wallet::find($toUid, ['user_id', 'money']);


        } catch (\Exception $e) {
            //事务回滚
            DB::rollback();
            /*echo $e->getMessage();
            echo $e->getCode();*/
            $jsonMsg = parent::apiException($e, $jsonMsg);
        }


        //返回信息
        return $jsonMsg;

    }




//钱包取现等操作========================================================================================================

    /**
     * 从钱包中取钱
     * 可能发生的情景：提现，平台消费抵现，商家退款
     */
    public function drawMoney()
    {
        //某人要从平台的支付宝账户里提现1000元到自己的支付宝账户里
        //允许提现的额度
        //提现的验证：如何保证安全？

    }




//辅助方法==============================================================================================================

    /**
     * 生成指定长度的随机数，默认是8位数
     * @param int $length
     * @return int
     */
    public function generateCode($length = 8)
    {
        return rand(pow(10, ($length - 1)), pow(10, $length) - 1);
    }


    /**
     * 生成walletRecord表中的record_sn字段【流水号】: 格式【YmdHis.8位随机数：例如20180412175543204140】
     * @return string
     */
    public function makeRecordSn()
    {
        //每次都要判断表中是否已经存在生成的sn编号，如果已经存在则重新生成
        do {
            //sn的时间戳
            $snTimestamp = date("YmdHis", time());
            //sn的随机字符串
            $snRandNumber = $this->generateCode();
            //拼接成recordsn
            $recordSn = $snTimestamp . $snRandNumber;
            //查看表中是否已经存在这个编号，有则重新生成
            $exists = WalletRecord::where('record_sn', '=', $recordSn)->first();
        } while ($exists);

        //返回sn号
        return $recordSn;
    }


    /**
     * 根据type类型 设置不同的 备注信息
     * @param $type
     * @return string
     */
    public function setRemark($type)
    {
        switch ($type) {
            case 0:
                return "平台退款";
            case 103:
                return "cms作者提现到钱包";
            case 203:
                return "主播提现到钱包";
            case 303:
                return "。。。";
            default:
                return "钱包存钱操作";
        }
    }


    /**
     * 获取uid的用户信息
     * @param $uid
     * @return mixed
     *
     */
    public function getUserInfo($uid)
    {
        return UserProfile::find($uid);
    }


    /**
     * 获取用户的电话
     * @param $uid
     * @return mixed
     */
    public function getUserMobileByUid($uid)
    {
        return $this->getUserInfo($uid)->mobile;
    }


}