<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/4/11
 * Time: 16:04
 */

namespace Modules\Base\Models;

use Modules\Base\Models\Wallet;

use Backpack\CRUD\CrudTrait;

/**
 * wallet_log 表模型
 * Class WalletLog
 * @package Modules\Base\Models
 */
class WalletLog  extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_wallet_log';

    # 插入新数据
    function insertNewItem($recordSn, $toUid, $money, $remark){
        $oldUserInfo = (new Wallet())->where('user_id', '=', $toUid)->first();
        $this->user_id = $toUid;
        $this->record_sn = $recordSn;
        $this->change_money = $money;
        $this->money = $oldUserInfo->money+$money;
        $this->remark = $remark;
        return $this->save();
    }


    # 查询明细
    public function logList($uid, $number=1){
        return self::where('user_id', '=', $uid)->orderBy('created_at','desc')->skip(10*($number-1))->take(10)->get();
    }
}