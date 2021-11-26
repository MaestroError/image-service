<?php 

define("CACHE", realpath(dirname(__FILE__)."/../storage/cache").DIRECTORY_SEPARATOR);
define("CACHEROOTED", "storage".DIRECTORY_SEPARATOR."cache");
define("ORIGINAL", realpath(dirname(__FILE__)."/../storage/original"));
define("ORIGINALROOT", "storage".DIRECTORY_SEPARATOR."original");
define("IMAGES", realpath(dirname(__FILE__)."/../storage/images"));
define("IMAGESROOTED", "storage".DIRECTORY_SEPARATOR."images");

define("MAX_SIZE", "5mb");
define("QUALITY", 100);
define("CORS", "*");
