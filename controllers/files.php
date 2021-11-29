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
        if(isset($request->post['uri'])) {
            $uri = $request->post['uri'];
        } else {
            $uri = "/";
        }
        $files = $this->openLocation($uri);
        $files->remove($name);

        $this->json(200, $this->unsets($files::ls()));
    }
    public function add($request, $name) {
        // when Content-Type is application/json
        $data = json_decode(file_get_contents('php://input'), true);
        if(isset($data['uri'])) {
            $uri = $data['uri'];
        } else {
            $uri = "";
        }
        print_r($_POST);
        $files = $this->openLocation($uri);
        $files->add($name);
        

        $this->json(200, $this->unsets($files::ls()));
    }
    public function rename($request, $name, $newName) {
        if(isset($request->post['uri'])) {
            $uri = $request->post['uri'];
        } else {
            $uri = "/";
        }
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

/** 
 * momrgvalebebi
 * tetri fanjara erti zomis
 * iyos orive mxares gamchirvale
 * qilis dziri gavasworot kvadrattan
 * qilis gadideba tu shesadzlebelia
 * sataurebi iyos H! da cota tu shevszlebt gavzardot
 * ghilaki iyos erti da igive zomebis vrclad/ukan
 */