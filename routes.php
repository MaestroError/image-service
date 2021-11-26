<?php 
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
header("Access-Control-Allow-Origin: *");


print_r($_POST);
echo "<br>";

$router::GET("", function() {
    echo "Home";
});


$router::GET("show/:id/:format?/:width?", [controller\show::class, "show"]);

// upload with formData $_FILES "image"
$router::POST("upload", [controller\upload::class, "upload"])->withRequest();


// file system

// get:uri - return current data
$router::GET("files", [controller\files::class, "explore"])->withRequest();
$router::GET("tree", [controller\files::class, "tree"]);
// post:uri - return current data
$router::POST("files/remove/:name", [controller\files::class, "remove"])->withRequest();
// post:uri - return current data
$router::POST("files/add/:name", [controller\files::class, "add"])->withRequest();
// post:uri - return current data
$router::POST("files/rename/:name/:newName", [controller\files::class, "rename"])->withRequest();

// echo "<pre>";
// print_r($router);
// echo "</pre>";