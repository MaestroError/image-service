<?php

namespace controller;

use app\cacheById;
use maestroerror\imageMe;

class upload {
    public function upload($request) {

        $uri = "/";
        $newName = false;
        if(isset($request->query['uri'])) {
            $uri = $request->query['uri'];
        }
        if(isset($request->query['new_name'])) {
            $newName = $request->query['new_name'];
        }
        
        $options = [
            "max_size" => \MAX_SIZE,
            "quality" => \QUALITY,
            "dir" => \IMAGES.$uri,
        ];
        /*
        $options = [
            "max_size" => "",
            "quality" => "",
            "dir" => "",
            "useMark => """,
            "watermark" => "",
            "wOpacity" => "",
            "wPadding" => "",
            "name" => "",
            "identificator" => "",
            "scale" => ""
        ];
        */
        // $cors = \CORS;
        // header("Access-Control-Allow-Origin: $cors");
        // header('Content-Type: application/json; charset=utf-8');
        // echo json_encode($_FILES);
        // exit;
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
            // you want to allow, and if so:
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 1000');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                // may also be using PUT, PATCH, HEAD etc
                header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization");
            }
            exit(0);
        }
        foreach ($request->files['image'] as $image) {
            if($newName) {
                $exploded = explode(".", $image['name']);
                $image['name'] = $newName.".".$exploded[count($exploded)-1];
            }
            // print_r($image); exit;
            $img = new imageMe([$image], false);
            $max_size = $img->getByteSizeFromStr(\MAX_SIZE);
            if($max_size < $img->fileSize) {
                return $this->sizeError($img->fileSize, $max_size);
            }
            $img->optionsFrom($options);
            cacheById::REGISTER($img);
        }

        $this->json(200, $request->files['image']);
    }

    private function sizeError($size, $maxSize) {
        $this->json(405,
            [
                "status" => 404,
                "message" => "Uploaded file size ($size) is more than allowed ($maxSize)",
                "size" => $size,
                "max_size" => $maxSize
            ]
        );
    }
    

    private function json($status, $data) {
        $cors = \CORS;
        header("Access-Control-Allow-Origin: $cors");
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);
        echo json_encode($data);
    }
}