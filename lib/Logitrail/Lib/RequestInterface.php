<?php

namespace Logitrail\Lib;

interface RequestInterface {

    /**
     * @return RequestParameterSet
     */
    public function getRequestInfo();
}
