<?php

namespace Logitrail\Lib\Order;

use Logitrail\Lib\RequestInterface;
use Logitrail\Lib\RequestParameterSet;

class ConfirmOrderRequest extends AbstractOrderRequest implements RequestInterface {

    /**
     * @return RequestParameterSet
     */
    public function getRequestInfo() {
        return new RequestParameterSet('POST', '/orders/' . $this->getOrderId() . '/_confirm');
    }

}
