<?php

namespace Modules\Base\Models;

use Backpack\CRUD\CrudTrait;

class PayNo extends \BaseModel
{
    use CrudTrait;

    protected $table = 'base_pay_no';
    protected $fillable = ['user_id', 'pay_no', 'pay_type', 'pay_amount'];


    // 关系模型
    public function order()
    {
        return $this->hasOne('Modules\Oms\Models\Order', 'pay_no', 'pay_no');
    }

    // public function d_order()
    // {
    //     return $this->hasOne('Modules\Oms\Models\Order', 'pay_no2', 'pay_no');
    // }

    public function custom_log()
    {
        return $this->hasOne('Modules\Customization\Models\UserCustomLog', 'pay_no', 'pay_no');
    }

    /**
     * 生成支付单编号(两位随机 + 从2000-01-01 00:00:00 到现在的秒数+微秒+会员ID%1000)，该值会传给第三方支付接口
     * 长度 =2位 + 10位 + 3位 + 3位  = 18位
     * 1000个会员同一微秒提订单，重复机率为1/100
     * @return string
     */
    public static function makePayNo($user_id)
    {
        do {
            $pay_no = sprintf('%02d', rand(10, 99))
                    . sprintf('%010d',time() - 946656000)
                    . sprintf('%03d', (float) microtime() * 1000)
                    . sprintf('%03d', (int) $user_id % 1000);

            $exists = self::where('pay_no', $pay_no)->first();
        } while ($exists);

        return $pay_no;
    }
}
