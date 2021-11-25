<?php 
namespace app;
use maestroerror\fileManager;
use maestroerror\imageMe;

class cacheById {

    protected $id;
    
    public fileManager $state;
    public fileJSON $data;
    public $uri;
    public $newPath;

    private static $inst;

    private $cachePath;

    public function __construct($id = false, array $data = []) {
        if(!$id) {
            $this->id = imageMe::generateRandom(rand(7, 12));
        } else {
            $this->id = $id;
        }
        
        // set root folder
        $this->cachePath = \CACHE;
        $this->cacheFromRoot = \CACHEROOTED;


        if (empty($data) && $id) {
            // retrieve cache
            $this->find();
        } elseif(!empty($data) && $id) {
            // create cache
            $this->createImage($data['image'], $data['format'], $data['width']);
        }
        
    }

    // register new image
    public static function REGISTER(imageMe $image) {

      self::$inst = new self();

      $image->id(self::$inst->id)->up();

      $uri = self::$inst->resolveID();
      $file = self::$inst->id.".json";
      $state = new fileManager("", self::$inst->cacheFromRoot);
      $fullPath = \ORIGINAL . DIRECTORY_SEPARATOR . time() . "-" . "{".self::$inst->id."}" . ".webp";
      // save original image
      file_put_contents($fullPath, file_get_contents($image->fullPath));

      $data = [
          "origin" => [
            "fileSize"=> $image->fileSize,
            "fullPath"=> $fullPath,
            "startPosition"=> $image->fullPath,
            "mimeInfo"=> $image->mimeInfo,
            "width"=> $image->width
          ]
        ];
      $state->save($file, $uri, json_encode($data));
      $fullPath = $state::pwd() . DIRECTORY_SEPARATOR . self::$inst->id . '.json';
    //   echo $state::pwd();
      unset($state);
      return $fullPath;
    }

    public function find() {

        $this->uri = $this->resolveID();
        $this->state = new fileManager($this->uri, $this->cacheFromRoot);

        $this->findData();
    }

    private function findData() {
        if ($this->state::pwd() != $this->cachePath) {
            $this->data = new fileJSON($this->state::pwd() . DIRECTORY_SEPARATOR . $this->id . '.json');
        } else {
            throw new \Exception("folder {$this->uri} not found in Cache");
        }
    }

    private function resolveID() {
        $a = strlen($this->id);
        $b = $a * ord($this->id);
        $c = $this->id;
        return $a . DIRECTORY_SEPARATOR . $b . DIRECTORY_SEPARATOR . $c;
    }

    public function createImage($image, $format, $width = false) {

        // init
        $img = new imageMe($image);

        // set
        $this->uri = $this->resolveID();
        $this->state = new fileManager($this->uri, $this->cacheFromRoot);

        // save
        $img->to($this->state::pwd())->id($this->id)->{$format}();
        if($width) {
            $img->scale($width);
        }
        $newPath = $img->up();

        // find json file
        $this->findData();

        // edit & save json file
        if($width) {
            $this->data->data[$format]["W".$width]['fullPath'] = $newPath;
            $this->data->save();
        } else {
            $this->data->data[$format]['fullPath'] = $newPath;
            $this->data->data[$format]['mimeInfo'] = "image/$format";
            $this->data->save();
        }

        $this->newPath = $newPath;
        

    }



}