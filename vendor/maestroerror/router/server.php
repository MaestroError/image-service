<?php 

if(isset($argv[1])) {
    $_SERVER['REQUEST_URI'] = $argv[1];
} else {
    $_SERVER['REQUEST_URI'] = "/";
}