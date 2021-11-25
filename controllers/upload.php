<?php

namespace controller;

use app\cacheById;
use maestroerror\imageMe;

class upload {
    public function upload($request) {

        $uri = $request->query['uri'];
        
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
        foreach ($request->files['image'] as $image) {
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