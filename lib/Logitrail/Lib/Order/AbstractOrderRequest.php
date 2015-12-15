<?php

namespace Logitrail\Lib\Order;

abstract class AbstractOrderRequest {

    /**
     * @var string
     */
    protected $orderId;

    public function getOrderId() {
        return $this->orderId;
    }

    public function setOrderId($orderId) {
        $this->orderId = $orderId;
    }

}
