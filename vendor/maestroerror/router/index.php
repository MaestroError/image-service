<?php 

// include "server.php";
include "src/Route.php";
include "src/Router.php";

include "testController.php";
include "testFunction.php";

use maestroerror\Router;
use maestroerror\testController;

$router = new Router();

// Set default argument, which you can pass any route
$router->def("hello", "Hello World!");

// Closure (with request & route)
$router::GET("post/:id/slug/author/:user", function ($request, $route, $id, $user) { // -> /post/ID123/slug/author/maestroerror
    echo "closure <----";
    echo $id."::".$user."\n";
    print_r($request::URI()."\n");
    print_r($route->_method."\n");
    print_r($request->query);
})->withRoute()->withRequest();

// Array type
$router::GET("post/:id/author/:user", [testController::class, "test"]) // -> /post/ID123/author/maestroerror

// adding argument without route
->with("withArgument", "Nice to See You!")

// Overriding DEF argument
->with("hello", "Nice to See You!");

// Function type
$router::GET("post/:id?/:user?", "testFunction")->withRoute()->withRequest(); // -> /post/ID123/maestroerror
$router::POST("post/:ident", "testFunction2")->withRoute()->withRequest(); // -> /post/ID123

// Nameing


// string type functions


// $router::GET("", [testController::class, "test"]); 
$router::GET("/", [testController::class, "test"]); 

echo "<pre>";
print_r($router);
echo "</pre>";

// Run. last point of router. (can be called in any other file)
$router->run();

// Set route parameters global, to access them outside of route
// in global scope you can acces also parameters, which defined to another route but deleted via router->getRouteParams function
$router->route->setParamsGlobally();
// echo "\n";
// echo "\n";
// echo $id."::".$user;

print_r($router);