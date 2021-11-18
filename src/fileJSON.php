<?php 
namespace app;

class fileJSON {
    
    protected $filepath;
    protected $txtvalue;

    public $data;

    public function __construct($jsonfile) {
        $this->filepath = $jsonfile;
        $this->txtvalue = file_get_contents($jsonfile);
        $this->data = json_decode($this->txtvalue, true);
        return $this;
    }

    public function getString() {
        return $this->txtvalue;
    }

    public function inject($data) {
        $this->data = $data;
        return $this;
    }

    public function get() {
        return $this->data;
    }

    public function save($path = null) {
        if ($path !== null) {
            $file = $path;
        } else {
            $file = $this->filepath;
        }
        file_put_contents($file, json_encode($this->data));
    }

}