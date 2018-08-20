<?php

declare(strict_types=1);

namespace Modules\Oms\Events\Listeners;

use Modules\Oms\Events\OrderPaid;

class SubStock
{
    /**
     * 创建事件监听器。
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 处理事件
     *
     * @param  OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        // step01
        $order = $event->order;
        if ($order->order_status != 20) {
            return;
        }

        // 非成品定制和洗护
        $need_send = [21, 22, 30];
        if (in_array($order->order_type, $need_send)) {
            order_flow([
                'order_id'  => $order->id,
                'op_log'    => '订单已经支付，请及时邮寄给平台',
                'op_info'   => [
                    'type' => 'waiting_user_express',
                ],
            ]);
        } else {
            order_flow([
                'order_id'  => $order->id,
                'op_log'    => '订单已经支付',
            ]);
        }

        // 减库存
        foreach ($order->goods as $order_goods) {
            // 只有goods才有库存，和src_goods有点一样了（待测）
            if ($order_goods->goods_type != 'goods') {
                continue;
            }

            $src_goods = $order_goods->src_goods;

            // 私人定制和洗护没有 src_goods
            if ($src_goods) {
                // 允许负数，代表超卖及时联系买家
                $src_goods->stock = $src_goods->stock - $order_goods->goods_num;
                $src_goods->save();
            }
        }
    }
}
