<?php 

require "config/config.php";
require "vendor/autoload.php";

use maestroerror\Router;
use controller\show;

// start routing
$router = new Router();

// accept content-type from axios json request
header("Access-Control-Allow-Headers: Content-Type");
