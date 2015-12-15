<?php

namespace Logitrail\Lib;

class RequestParameterSet {

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $method;

    public function __construct($method, $url) {
        $this->url = $url;
        $this->method = $method;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getMethod() {
        return $this->method;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function setMethod($method) {
        $this->method = $method;
    }

}
