<?php

namespace Modules\Oms\Events;

use Modules\Oms\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderPaid
{
    use SerializesModels;

    public $order;

    /**
     * 创建一个事件实例。
     *
     * @param  Order  $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
