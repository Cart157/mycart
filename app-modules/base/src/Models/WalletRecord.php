<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/4/11
 * Time: 16:05
 */

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;


/**
 * wallet_record表模型
 * Class WalletRecord
 * @package Modules\Base\Models
 */
class WalletRecord extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_wallet_record';


    //walletrecord表与walletlog表之间的关系：一对一,依靠 record_sn 建立关系
    public function record(){
        return $this->hasOne("Modules\Base\Models\WalletLog", 'record_sn');
    }


    //插入一条新数据
    public function insertNewItem( $recordSn, $type, $orderId, $fromUid, $toUid, $money, $remark ){

//        echo 22;
        $this->record_sn = $recordSn;
        $this->order_id = $orderId;
        $this->from_user_id = $fromUid;
        $this->to_user_id = $toUid;
        $this->type = $type;
        $this->money = $money;
        $this->pay_type = 0;
        $this->remark = $remark;
        return $this->save();

    }

    //修改支付状态
    function editPayState($id, $payStatus){

    }

    //修改收款状态
    function editResotStatus($id, $resStatus){

    }

}