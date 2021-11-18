<?php
namespace maestroerror;

use maestroerror\Route;

class Router {

    // raw data from PHP Global Variables ($_SERVER, $_POST & etc...)
    protected string $currentUri;
    protected string $currentMethod;
    public array $query = [];
    public array $post = [];
    public array $files = [];
    public array $defaultArguments;

    // Route object for public use
    public Route $route;
    // Registered routes
    public array $routes = [];

    // Options
    protected $withObjects = false;
    protected $allowDefaultArguments = true;
    protected $withRequestInfo = false;

    private static $instance;
    private static $abc = [
        "a",
        "b",
        "c",
        "d",
        "f",
        "g",
        "h",
        "i",
        "j",
        "k",
        "l",
        "m",
        "n",
        "o",
        "p",
        "q",
        "r",
        "s",
        "t",
        "u",
        "v",
        "w",
        "x",
        "y",
        "z"
    ];

    public function __construct() {
        
        $this->route = new Route();
        self::$instance = $this;

        $this->buildCurrentRoute();

        // $this->registerRoute("post/:slug", 1);
        // $this->registerRoute(":id", 2);
        // $this->registerRoute("post/:id/:slug/", 3);
        // $this->registerRoute("post/:id/slug/title", 4);
        // $this->registerRoute("post/:id/slug/contact/:user", function () {
        //     echo $id."---".$user;
        // });


        // $this->run();
    }

    public function registerRoute($uri, $action = "", $methods = ['GET']) {
        $routeSegmented = $this->segmentate($uri, $action, $methods);
        $this->routes = array_merge_recursive($this->routes, $routeSegmented);
        return $this;
    }

    public static function GET($uri, $action = "") {
        self::$instance->registerRoute($uri, $action, ["GET"]);
        return self::$instance;
    }

    public static function POST($uri, $action = "") {
        self::$instance->registerRoute($uri, $action, ["POST"]);
        return self::$instance;
    }
    
    public static function PUT($uri, $action = "") {
        self::$instance->registerRoute($uri, $action, ["PUT"]);
        return self::$instance;
    }

    public static function DELETE($uri, $action = "") {
        self::$instance->registerRoute($uri, $action, ["DELETE"]);
        return self::$instance;
    }

    public static function ANY($uri, $action = "") {
        self::$instance->registerRoute($uri, $action, ["GET", "POST", "PUT", "DELETE", "HEAD"]);
        return self::$instance;
    }

    public function run() {
        // print_r($this);
        
        // check action
        $this->checkCurrentAction();
        $func = $this->route->_action;

        // get parameters from uri (and default ones)
        $params = $this->getRouteParams($func);

        
        // print_r(json_encode($params));
        // print_r($this);

        // call function
        call_user_func_array($func, $params);
        
    }

    public function withRoute() {
        $this->route->withRouteInfo();
        return $this;
    }
    public function withRequest() {
        $this->route->withRequestInfo();
        return $this;
    }

    public function def($argument, $value = "") {
        $this->defaultArguments[$argument] = $value;
        return $this;
    }
    public function with($argument, $value) {
        $this->route->_withArgs[$argument] = $value;
        return $this;
    }

    // static methods for info gathering from controller
    static function URI() {
        return self::$instance->currentUri;
    }
    static function METHOD() {
        return self::$instance->currentMethod;
    }
    static function QUERY() {
        return self::$instance->query;
    }
    static function PDATA() {
        return self::$instance->post;
    }
    static function FILES() {
        return self::$instance->files;
    }
    static function DEFS() {
        return self::$instance->defaultArguments;
    }

    /** Helping functions */
    
    // setting main info like GET parameters, METHOD and URI
    private function setRouteInfo() {
        if(isset($_SERVER['REQUEST_URI'])) {

            // split uri and query string
            $request = explode('?', $_SERVER['REQUEST_URI'], 2);
            $this->currentUri = urldecode($request[0]);

            // set query parameters
            if(!$this->catchGET()) {
                if(isset($request[1])) {
                    $this->query = $this->resolveQueryString($request[1]);
                }
            }

            if (isset($_SERVER['REQUEST_METHOD'])) {
                $this->currentMethod = $_SERVER['REQUEST_METHOD'];
            } else {
                $this->currentMethod = "GET";
            }

        } else {
            throw new \Exception('Server doesn`t provided REQUEST_URI variable');
        }
    }

    private function getObjectFromArray($array) {
        if(!is_array($array)) {throw new \Exception('getObjectFromArray argument must be array!');}
        $object = new \stdClass();
        foreach ($array as $key => $value)
        {
            $object->$key = $value;
        }
        return $object;
    }

    private function resolveQueryString($queryString) {
        $ar = explode('&', $queryString);

        if($this->withObjects) {
            $query = new \stdClass();
            foreach($ar as $i) {
                $i = explode('=', $i);
                if(isset($i[1])) {
                    $query->{$i[0]} = $i[1];
                }
            }
        } else {
            $query = [];
            foreach($ar as $i) {
                $i = explode('=', $i);
                if(isset($i[1])) {
                    $query[$i[0]] = $i[1];
                }
            }
        }
        return $query;
    }

    // catch INFO
    private function catchGET() {
        if(!isset($_GET)) { return false; }
        if(empty($_GET)) { return false; }

        if($this->withObjects) {
            $this->query = $this->getObjectFromArray($_GET);
        } else {
            $this->query = $_GET;
        }
        
        return true;
    }

    private function catchPOST() {
        if(!isset($_POST)) { return false; }
        if(empty($_POST)) { return false; }
        if($this->withObjects) {
            $this->post = $this->getObjectFromArray($_POST);
        } else {
            $this->post = $_POST;
        }
        return true;
    }
    
    private function catchFILES() {
        if(!isset($_FILES)) { return false; }
        if(empty($_FILES)) { return false; }

        foreach ($_FILES as $key => $arr) {
            $filesArray[$key] = $this->reArrayFiles($arr);
        }

        if($this->withObjects) {
            $this->files = $this->getObjectFromArray($filesArray);
        } else {
            $this->files = $filesArray;
        }
        return true;
    }

    // For Traits
    private function reArrayFiles($files){
        $file_ary = array();
        if (!empty($files['name'][0])) {
            if (is_array($files['name'])) {
                $file_count = count($files['name']);
            } else {
                $file_count = 1;
            }
            $file_keys = array_keys($files);

            for ($i=0; $i<$file_count; $i++) {
                foreach ($file_keys as $key) {
                    if(is_array($files[$key])) {
                        $file_ary[$i][$key] = $files[$key][$i];
                    } else {
                        $file_ary[$i][$key] = $files[$key];
                    }
                }
            }
        }

        return $file_ary;
    }

    private function getFuncArguments($func){
        if(is_array($func)) { 
            $f = new \ReflectionMethod($func[0], $func[1]);
        } else {
            $f = new \ReflectionFunction($func);
        }
        $result = array();
        
        foreach ($f->getParameters() as $param) {
            $result[] = $param->name;   
        }
        return $result;
    }
    // end For Traits


    private function segmentate($uri, $action = "", $methods = ['GET']) {
        
        $array = array_filter(explode('/', $uri), 'strlen');

        $segments = [];
        $parameters = [];
        $prevSegment = [];
        $count = count($array);

        $reversedSegments = array_reverse($array);

        // if empty route
        if($count == 0) {
            // Home route
            $reversedSegments = ["_H"];
            $count = 1;
        }
        
        $i = 1;
        $routes = [];
        $OPTparams = 0;
        foreach ($reversedSegments as $segment) {
            $currentSegment = [];
            $segment = (string)$segment;
            // echo $segment."<br>";
            if($i == 1) {
                $prevSegment["_methods"] = $methods;
                // $prevSegment["_action"]["a".$count] = $action;
                foreach ($methods as $method) {
                    $prevSegment["_action"][$method.$count] = $action;
                }
            }
            if (strpos($segment, ':') !== false) {
                $param = str_replace(":", "", $segment);
                if(strpos($param, '?') !== false) {
                    $param = str_replace("?", "", $param);
                    // $prevSegment["_OPTparameter"][$this::$abc[$i].$count] = $param;

                    // where optional parameter can be set
                    for ($it=0; $it < $i; $it++) { 
                        // echo $this::$abc[$i-$it].$count-$it ."-". $param;
                        $prevSegment["_OPTparameter"][$this::$abc[$i-$it].$count-$it] = $param;
                        $OPTparams++;
                    }

                    // where optional parameters could be empty
                    for ($it=0; $it < $count; $it++) { 
                        $prevSegment["_EMPTYparameter"][$this::$abc[$count-$it].$count-$it][] = $param;
                        // $prevSegment["_action"]["a".$count-$it] = $action;
                        foreach ($methods as $method) {
                            $prevSegment["_action"][$method.$count-$it] = $action;
                        }
                    }
                } else {
                    $prevSegment["_parameter"][$this::$abc[$i].$count] = $param;
                    if($OPTparams > 0) {
                        for ($it=1; $it <= $OPTparams-1; $it++) { 
                            $prevSegment["_parameter"][$this::$abc[$i-$it].$count-$it] = $param;
                        }
                    }
                }
            } else {
                
                $currentSegment["HS_".$segment] = $prevSegment;
                
                $prevSegment = $currentSegment;
            }
            $i++;
            
        }
        return $prevSegment;
        
        /* need to get object like:
        [post] => Array
                (
                    [_parameter] => id
                    [slug] => Array
                        (
                        )
                    [_action] => callback()
                    [_methods] => ['get']

                )
        */
        
    }

    

    private function findMatch() {
        $array = array_filter(explode('/', $this->currentUri), 'strlen');
        $count = count($array);

        // if empty route
        if($count == 0) {
            // Home route
            $array = ["_H"];
            $count = 1;
        }

        $act = true;
        $exc = false;
        $action = true;
        $currentSegment = $this->routes;
        
        $i = 1;
        $abc = $count;

        foreach ($array as $segment) {
            $coord = $this::$abc[$abc].$count;
            // var_dump($coord);
            
            // var_dump($coord);
            if(isset($currentSegment["HS_".$segment])) { 
                $currentSegment = $currentSegment["HS_".$segment];
                // set parameters which might to be empty
                if(isset($currentSegment["_EMPTYparameter"][$coord])) {
                    foreach ($currentSegment["_EMPTYparameter"][$coord] as $param) {
                        $this->route->_params[$param] = "";
                    }
                }
            } elseif(isset($currentSegment["_parameter"][$coord])) {
                // if parameter allowed
                $this->route->_params[$currentSegment["_parameter"][$coord]] = $segment;
                // if optional parameter also specified
                if (isset($currentSegment["_OPTparameter"][$coord])) {
                    // if optional parameter specified
                    if(is_array($currentSegment["_OPTparameter"][$coord])) {
                        $this->route->_params[$currentSegment["_OPTparameter"][$coord][0]] = $segment;
                    } else {
                        $this->route->_params[$currentSegment["_OPTparameter"][$coord]] = $segment;
                    }
                }
            } elseif(isset($currentSegment["_OPTparameter"][$coord])) {
                // if optional parameter specified
                if(is_array($currentSegment["_OPTparameter"][$coord])) {
                    $this->route->_params[$currentSegment["_OPTparameter"][$coord][0]] = $segment;
                } else {
                    $this->route->_params[$currentSegment["_OPTparameter"][$coord]] = $segment;
                }
                
            } else {
                $act = false;
                $route = $this->currentUri;
                $exc = "No match found for URI $route";
            }
            if($i == $count) {
                if(!isset($currentSegment["_methods"])) {
                    $act = false;
                    $exc = "No similar route registered";
                } else {
                    if(!in_array($this->currentMethod, $currentSegment["_methods"]) && !in_array("ANY", $currentSegment["_methods"])) {
                        $act = false;
                        $meth = $this->currentMethod;
                        $exc = "Method $meth not allowed on this route";
                    } else {
                        if(isset($currentSegment["_action"][$this->currentMethod.$count])) { 
                            $act = $currentSegment["_action"][$this->currentMethod.$count];
                        } elseif(isset($currentSegment["_action"]["ANY".$count])) {
                            $act = $currentSegment["_action"]["ANY".$count];
                        } else {
                            $act = false;
                            $meth = $this->currentMethod;
                            $exc = "no Action found for this route, some segment is missing or more then expected";
                        }
                    }
                }
            }
            $i++;
            $abc--;
            if(!$act) {
                if ($exc) {
                    throw new \Exception($exc);
                }
            } else {
                // make action or return action
                $action = $act;
            }
        }

        if(!$action) {
            if ($exc) {
                throw new \Exception($exc);
            }
            // answer with 404
        } elseif ($action === true) {
            // Make default action
        } else {
            // make action or return action
            return $action;
        }

    }

    private function buildCurrentRoute() {
        // set main information of current request
        $this->setRouteInfo();
        
        // check if POST data is needed
        if($this->currentMethod == "POST" || $this->currentMethod == "PUT") {
            $this->catchPOST();
            $this->catchFILES();
        }

        $this->route->_method = $this->currentMethod;
        $this->route->_uri = $this->currentUri;

    }

    private function getRouteParams($func) {
        $params = $this->route->getParams();
        $args = $this->getFuncArguments($func);

        // check options for optional parameters
        if($this->route->_withRouteInfo) {
            $params['route'] = $this->route;
        }
        if($this->route->_withRequestInfo) {
            $params['request'] = $this;
        }

        if($this->allowDefaultArguments) {
            // if need to set default arguments
            if(!empty($this->defaultArguments)) {
                foreach($this->defaultArguments as $argument => $value) {
                    if(in_array($argument, $args)) { 
                        $params[$argument] = $value;
                    }
                }
            }
        }

        // if need to set with arguments
        if(!empty($this->route->_withArgs)) {
            foreach($this->route->_withArgs as $argument => $value) {
                if(in_array($argument, $args)) { 
                    $params[$argument] = $value;
                }
            }
        }

        $newParams = [];
        // var_dump($params);
        foreach ($args as $key) {
            if(isset($params[$key])) {
                $newParams[$key] = $params[$key];
            } else {
                throw new \Exception("This action needs parameter $$key, which is neither specified as a route, nor as an optional parameter, nor as a default parameter");
            }
        }

        
        
        if(count($args) == count($newParams)) { return $newParams; }
    }

    private function checkCurrentAction() {
        $action = $this->findMatch();
        // var_dump($action);
        $ext = false;
        if(is_callable($action)) {
            // if action is ready to use callable
            $this->route->_action = $action;
            return true;
        }elseif(!is_callable($action) && is_array($action)) {
            // if action is array type callable but without static method
            if(class_exists($action[0])) {
                $action[0] = new $action[0];
            } elseif(function_exists($action[0])) {
                $action = $action[0];
            } else {
                $route = $this->currentUri;
                $class = $action[0];
                $ext = "Used class ($class) in action for route ($route) doesn't exists";
            }
        } elseif(strpos($action, '.')) {
            // for string type functions "testController.index"

        } else {
            $route = $this->currentUri;
            $ext = "Incorrect action for route ($route)";
        }
        $this->route->_action = $action;

        if($ext) {
            throw new \Exception($ext);
        } else {
            unset($ext);
            unset($action);
        }
    }


}
