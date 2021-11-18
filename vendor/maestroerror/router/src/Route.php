<?php 
namespace maestroerror;

class Route {
    public $_method;
    public $_uri;
    public $_action;
    public $_params;
    public $_name;
    public $_withRouteInfo = false;
    public $_withRequestInfo = false;
    public $_withArgs;

    public function setParamsGlobally() {
        $params = $this->_params;
        // var_dump($params);
        foreach ($params as $key => $value) {
            $GLOBALS["$key"] = $value;
        }
    }

    public function getParams() {
        $params = $this->_params;
        return $params;
    }

    public function getMethod() {
        return $this->_method;
    }

    public function getUri() {
        return $this->_uri;
    }

    public function getAction() {
        return $this->_action;
    }

    public function withRouteInfo() {
        return $this->_withRouteInfo = true;
    }
    public function withRequestInfo() {
        return $this->_withRequestInfo = true;
    }
}