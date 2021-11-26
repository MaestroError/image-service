<?php

namespace controller;

use app\cacheById;
use maestroerror\fileManager;

class files {
    public function explore($request) {
        $uri = $request->query['uri'];
        $files = $this->openLocation($uri);
        
        $this->json(200, $this->unsets($files::ls()));
    }
    public function tree() {
        $files = new fileManager("", \IMAGESROOTED);
        
        $this->json(200, $files->getTree());
    }
    public function remove($request, $name) {
        $uri = $request->post['uri'];
        $files = $this->openLocation($uri);
        $files->remove($name);

        $this->json(200, $this->unsets($files::ls()));
    }
    public function add($request, $name) {
        // $uri = $request->post['uri'];
        // $files = $this->openLocation($uri);
        $files = $this->openLocation("/");
        $files->add($name);
        

        $this->json(200, $this->unsets($files::ls()));
    }
    public function rename($request, $name, $newName) {
        $uri = $request->post['uri'];
        $files = $this->openLocation($uri);
        $files->rename($name, $newName);
        

        $this->json(200, $this->unsets($files::ls()));
    }

    private function openLocation($uri) {
        $files = new fileManager("", \IMAGESROOTED);
        $files->open($uri);
        return $files;
    }

    private function unsets($files) {
        foreach($files as $key => $file) {
            unset($files[$key]["realPath"]);
            unset($files[$key]["parent"]);
        }
        return $files;
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