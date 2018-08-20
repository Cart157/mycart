<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/4/11
 * Time: 16:00
 */

namespace Modules\Base\Models;

use Modules\Base\Models\User;
use Modules\Base\Models\UserProfile;
use Backpack\CRUD\CrudTrait;

/**
 * Wallet 表模型
 * Class Wallet
 * @package Modules\Base\Models
 */
class Wallet extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_wallet';
    /**
     * 指定主键。
     */
    protected $primaryKey = 'user_id';
    /**
     * 指定是否模型应该被戳记时间。
     */
    //    public $timestamps = false;

    //wallet表与walletrecord表之间的关系：一对多,依靠 user_id 建立关系
    public function record(){
        return $this->hasMany("Modules\Base\Models\WalletRecord", 'user_id');
    }


    //查询制定用户的现金数
    public function seeCash($uid)
    {
        // dd(123);
        return self::find($uid, ['money']);
    }


    //设置支付密码
    public function setPayPassword($uid, $password)
    {
        //如果有该用户则设置，否则设置新用户
        $haveUser = $this->selfHaveUser($uid);

        //如果没有这个用户
        if (!$haveUser) {
            //创建新用户
            $this->insertNewUser($uid);
            //并找出该用户的信息
            $haveUser = $this->havUser($uid);
        }


        //设置密码盐
        if (!$haveUser->salt) {
            $haveUser->salt = date("YmdHis", time()) . mt_rand(pow(10, 5), pow(10, 8));
        }

        //加密密码 md5(用户名.密码盐)
        $haveUser->pay_password = md5($password . $haveUser->salt);
        # 05181704
        $haveUser->pay_password_checked = 1;
//        dd(1);
        return $haveUser->save();
    }



    /**
     * 初始化新用户：若user表中存在该账户并且钱包表中没有该用户则初始化
     * 如果user_profile表中存在用户的相关信息，尝试取出关于支付宝的信息填充到wallet表的对应字段
     * 方法流程: user表中存在该用户&&wallet表中没有该用户->查询userProfile表中的用户的支付宝信息->赋值并保存进wallet表中
     * @param $uid
     */
    public function insertNewUser($uid)
    {
        //验证user表中存在该账户
//        $haveUser = User::find((int)$uid);
        $haveUser = $this->havUser($uid);
//        dd($haveUser);
//        dd(count(self::where('user_id', '=', (int)$uid)->get()));

        //查询该用户，若wallet表中没有则初始化
        if ($haveUser && !count(self::where('user_id', '=', (int)$uid)->get())) {
            // 查询用户表中的字段:alipay_account   alipay_realname
            $userInfo = UserProfile::find((int)$uid, ["alipay_account", "alipay_realname"]);

            //设置uid值
            $this->user_id = $uid;

            //判断一下 userProfile 表中存在给用户的信息，有可能不存在，user表中存在的信息不代表附表中一定存在
            if ($userInfo && $userInfo->alipay_account && $userInfo->alipay_realname) {
                //设置支付宝的账号
                $this->alipay_account = $userInfo->alipay_account;
                //设置支付宝的用户名
                $this->alipay_realname = $userInfo->alipay_realname;
                //设置支付宝验证字段为1 表示通过验证
                $this->alipay_checked = 1;
            }


            //保存数据  若成功保存返回true  否则返回null
            return $this->save();
        }

    }



    //修改钱包的钱数  要不要传递过来 钱的 正负类型
    function modiefyMoney($uid, $money)
    {

        //查询老数据
        $user = self::where('user_id', '=', $uid)->first();
        //更新新数据
        $user->money = $user->money + $money;
        //保存数据
        return $user->save();
    }



//助手方法==============================================================================================================
    //判断base_user表中存在指定uid的用户
    public function havUser($uid)
    {
        //判断wallet表中已经存在了该用户
        return User::find($uid);
    }

    //判断base_wallet表中是否存在指定用户
    public function selfHaveUser($uid)
    {
        return self::find($uid);
    }

    //判断是否已经绑定了支付宝
    public function haveAlipay($uid){
        //什么情况下算是绑定了？   baseuser表中有该用户  并且  userprofile表中有该用户的alipay信息   并且 wallet表中没有该用户的信息    ||     baseuser表有该用户 并且 wallet表中有该用户信息
        //什么情况下没有算绑定？   baseuser表中有该用户  并且    userprofile表中没有该用户信息
        //判断base_user表中是否有该用户
        /*if($this->havUser($uid) ){
            //wallet表中是否有用户
            $walletUser = $this->selfHaveUser();
            if($walletUser and $walletUser->)

            }
        }else{
            return fasle;
        }*/
    }

    /**
     * 生成32位支付密码salt
     */
    public function makePaySalt(){
        $number = range (1,50000);
        $salt = md5($number.time() );
        return $salt;
    }

}