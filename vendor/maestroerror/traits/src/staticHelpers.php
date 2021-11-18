<?php 
namespace maestroerror\Traits;

trait staticHelpers {

    /* STRINGS */

    static function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    // string ends with
    static function endsWith( $haystack, $needle ) {
        $length = strlen( $needle );
        if( !$length ) {
            return true;
        }
        return substr( $haystack, -$length ) === $needle;
    }
    // string starts with
    static function startsWith( $haystack, $needle ) {
        $length = strlen( $needle );
        return substr( $haystack, 0, $length ) === $needle;
    }
    // check if string is JSON
    static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    // time elapsed string from $date
    static function time_elapsed_string($datetime, $full = false) {
        $now = new \DateTime;
        $ago = new \DateTime($datetime);
        $diff = $now->diff($ago);
    
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
    
        $string = array(
            'y' => "years",
            'm' => "months",
            'w' => "weeks",
            'd' => "days",
            'h' => "hours",
            'i' => "minutes",
            's' => "seconds",
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? ' ' : '');
            } else {
                unset($string[$k]);
            }
        }
    
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . l('ago', 1) : l('now', 1);
    }

    // generate random string 
    // $type: 0 - only Digits, 1|'all' - any character, 2|'abc'|'str' - only letters
    static function generateRandom($length = 10, $type = 1) {
        if ($type == 1 || $type == "all") {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } elseif ($type == 2 || $type == "abc" || $type == "str") {
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } else {
            $characters = '0123456789';
        }
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    // Date from string (HTML date input type)
    static function htmlDateFromString($date) {
        $d=strtotime($date);
        return date("Y-m-d", $d);
    }

    /* END STRINGS */

    
    /* ARRAYS */

    // recursive search in array
    static function in_array_r($needle, $haystack, $strict = false) {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
                return true;
            }
        }
    
        return false;
    }
    

    // Refactor php $_FILES array's some key (like "images"), when uploading multiple files
    static function refactorFiles($files){
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
                    $file_ary[$i][$key] = $files[$key][$i];
                }
            }
        }

        return $file_ary;
    }

    // get function or method arguments (from php 'callables')
    static function getFuncArguments($func){
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

    /* END ARRAYS */

    // HTTP get request with cURL
    static function httpGet($url) {
        $ch = curl_init();
    
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
         //  curl_setopt($ch,CURLOPT_HEADER, false);
    
        $output=curl_exec($ch);
    
        curl_close($ch);
        return $output;
    }

    // get children of $class
    static function get_subclasses($class) {
        $subs = array();
        $classes = get_declared_classes();
        foreach ($classes as $c) {
            if (is_subclass_of($c, $class, true)) {
                $subs[] = $c;
    
            }
        }
        return $subs;
    }

    // Get $className class's methods 
    static function get_only_class_methods($className) {
        $f = new \ReflectionClass($className);
        $methods = array();
        foreach ($f->getMethods() as $m) {
            if ($m->class == $className) {
                $methods[] = $m->name;
            }
        }
        return $methods;
    }

    /* String Type functions (need to be tested): */
    // parse string to OOP array
    static function parse_str_to_oop($str) {
        $arguments = "";
        $oopAr = array();
    
        if (strpos($str, '|')) {
            $exp = explode('|', $str);
            $classMethod = $exp[0];
            unset($exp[0]);
            $arguments = $exp;
        } else {
            $classMethod = $str;
        }
    
        if (strpos($classMethod, ".")) {
            $expAr = explode('.', $classMethod);
            $class = $expAr[0];
            $method = $expAr[1];
        }
    
        if (!empty($arguments)) {
            foreach ($arguments as $arg) {
                $key = '';
                if (strpos($arg, ":")) {
                    $expArg = explode(':', $classMethod);
                    $key = $expArg[0];
                    $argument = $expArg[1];
                } else {
                    $argument = $arg;
                }
                if (strpos($argument, 'his.')) {
                    $expArg = explode('.', $argument);
                    $key = $expArg[0];
                    $argument = $expArg[1];
                } else {
                    $argument = $arg;
                }
                if (!empty($key)) {
                    $args[] = array('arg_name' => $key, 'arg_value' => $argument);
                } else {
                    $args[] = $argument;
                }
            }
        } else {
            $args = $arguments;
        }
    
        if (!empty($class) && !empty($method)) {
            $oopAr['class'] = $class;
            $oopAr['method'] = $method;
            $oopAr['arguments'] = $args;
            return $oopAr;
        } else {
            return FALSE;
        }
    
    }
    
    // use OOP array and return $data returned by class method
    static function get_data_by_oop_str($info) {
        if (!is_array($info)) {
        $oopAr = parse_str_to_oop($info);
        if (check_class_exists($oopAr['class'])) {
          $class = $oopAr['class'];
          $method = $oopAr['method'];
          $args = $oopAr['arguments'];
          // need to test $class or \$class
          $obj = new $class();
          if (method_exists($obj, $method)) {
            $data = $obj->$method($args);
          }
        }
      } else {
        $data = $info;
      }
        return $data;
    }

}