<?php

namespace controller;

use app\cacheById;
use maestroerror\imageMe;

class show {
    public function show($id, $format, $width) {

        $cache = $this->findJson($id);
        $jsonData = $cache->data->data;

        // option provided
        if(!empty($format)) {
            // checkings
            if(empty($width)) {
                $width = false;
            }
            if (!$this->checkFormat($format)) { return $this->formatError($format); };

            // process showing
            if(isset($jsonData[$format]["W".$width])) {
                $this->showImage($jsonData[$format]["W".$width]['fullPath'], $jsonData[$format]["mimeInfo"]);
            } else {
                $origin = $this->getOrigin($jsonData);

                if (!$this->checkWidth($width, $origin['width'])) {
                    return $this->widthError($width, $origin['width']);
                }

                $img = $this->createImage($id, $origin["fullPath"], $format, $width);
                // print_r($img->newPath);
                return $this->showImage($img->newPath, "image/$format");
            }
        } else {
            $origin = $this->getOrigin($jsonData);
            return $this->showImage($origin['fullPath'], $origin['mimeInfo']);
        }

    }

    public function test() {
        // test
        echo $id . "-" . $format . "-" . $width;
        echo "<br>";
        echo rand(7, 12);
        echo "<br>";
        echo ord("a");
    }

    // find existing cache info file
    private function findJson($id) {
        return new cacheById($id);
    }

    // get origin
    private function getOrigin($jsonData) {
        if(isset($jsonData['origin'])) {
            return $jsonData['origin'];
        }
    }

    private function checkFormat($format) {
        return ($format == 'jpeg' || $format == 'webp');
    }
    
    private function checkWidth($width, $maxWidth) {
        if(!$width) { return true; }
        return ($width < $maxWidth);
    }
    
    private function showImage($path, $mimeInfo) {
        return imageMe::SHOWSTAT($path, $mimeInfo);
    }

    private function createImage($id, $image, $format, $width = false) {
        return new cacheById($id, [
            "image" => $image,
            "format" => $format,
            "width" => $width
        ]);
    }

    // Errors
    private function formatError($format) {
        $this->json(405,
            [
                "status" => 404,
                "message" => "Extension $format not allowed"
            ]
        );
    }
    
    private function widthError($width, $maxWidth) {
        $this->json(405,
            [
                "status" => 404,
                "message" => "Requested width ($width) is more than width of origin ($maxWidth)"
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