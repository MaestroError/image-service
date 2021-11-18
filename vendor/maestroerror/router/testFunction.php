<?php 

function testFunction($request, $route, $id, $user) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([__FUNCTION__]);
}

function testFunction2($request, $route, $ident, $hello) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([__FUNCTION__]);
}
