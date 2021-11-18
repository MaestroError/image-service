<?php

namespace maestroerror;

class ImageMe {

    use \maestroerror\traits\checkSystem;
    use \maestroerror\traits\filesProcessing;
    use \maestroerror\traits\staticHelpers;

    // Upload options
    protected string $max_size = '24M';
    protected int $quality = 85;
      // target directory
    protected string $dir = "images/";
    protected string $tmpDir = "tmp/";
    protected array $allowed = [
      "jpeg",
      "png",
      "webp"
    ];
    public static $allowedOptions = [
      "max_size",
      "quality",
      "dir",
      "useMark",
      "watermark",
      "wOpacity",
      "wPadding",
      "name",
      "identificator",
      "scale"
    ];
      // recived identificator from public ->id()
    protected bool $recivedID = false;
    protected string $defName = "image";
    protected string $sourceType = "";
    protected string $saveExt = "webp";
    protected $scale = false;
    protected bool $useMark = false;
    
    protected string $watermark;
    protected int $wOpacity = 80;
    protected int $wPadding = 5;

    // system info
    protected bool $gd_ext;
    protected bool $fileinfo_ext;
    protected bool $webp_func;
    protected bool $jpeg_func;

    // Main Props
    protected object $resource;
    protected $source;

    // Major file info
    public string $name = "";
    public string $fullName;
      // file full path
    public string $fullPath;
    public string $identificator;
    public string $mimeType = "";
    public string $mimeInfo = "";
    public string $extension = "";
    public string $fileSize;
    public string $width;
    public string $height;
    public string $url;

    // Minor file info
    protected string $string_value;
    protected string $tmp_file;



    // Caching
    protected string $infoFile;
    protected array $images;
    protected array $tmps = [];
    static $JPEG_MIME = "image/jpeg";
    static $WEBP_MIME = "image/webp";

    
    private static $inst;

    public function __construct($source = false, $uploadBulk=true) {

        // checking extensions
        $this->checkMachine();
        if($source) {
          $this->processSource($source, $uploadBulk);
        }
        self::$inst = $this;
        
        $this->checkDir($this->tmpDir);

    }

    public function test() {
      $this->source = NULL;
      $this->string_value = 'NULL';
      print_r($this);
    }

    // set id manually
    public function id($id) {
      $this->recivedID = true;
      $this->identificator = $id;
      return $this;
    }

    // set name
    public function name($name) {
      $this->name = $name;
      return $this;
    }

    // set max size
    public function max($data) {
      $this->max_size = $data;
      return $this;
    }

    // set target dir
    public function targetDir($dir) {
      $this->dir = $dir;
      return $this;
    }

    // set target dir
    public function to($dir) {
      $this->dir = $dir;
      return $this;
    }

    // set quality
    public function q($quality) {
      $this->quality = $quality;
      return $this;
    }

    public function jpeg() {
      $this->saveExt = "jpeg";
      return $this;
    }

    public function webp() {
      $this->saveExt = "webp";
      return $this;
    }
    
    // set watermark
    public function mark($watermark) {
      $this->useMark = true;
      $this->watermark = $watermark;
      return $this;
    }

    public function scale($width = false) {
      if(!$width) {
        $width = $this->scale;
      }
      $this->resource = $this->scaleImg($width);
      $this->processResource($this->resource);
      return $this;
    }

    public function up($ext = false) {
      return $this->uploadFile($ext);
    }

    public function upW() {
      $this->saveExt = "webp";
      return $this->uploadFile("webp");
    }

    public function upJ() {
      $this->saveExt = "jpeg";
      return $this->uploadFile("jpeg");
    }

    // $ext = 'webp'|'jpeg'
    public function save($ext="webp", $dir = false) {
      if(!$dir) {
        $dir = $this->dir;
      }
      $this->checkdir($dir);
      $this->setFullName($dir);
      if ($ext == 'webp') {
        $this->fullPath = $this->createWebp();
      } elseif($ext == 'jpeg') {
        $this->fullPath = $this->createJpeg();
      }
      return $this->fullPath;
    }

    // UPLOAD
    public static function UPLOAD($source, $options = []) {
      self::$inst = new self();
      self::$inst->setOptions($options);
      if(is_array($source)) {
        self::$inst->processSource($source);
      } else {
        return self::$inst->processSource($source)->uploadFile();
      }
    }

    public function optionsFrom($options) {
      self::$inst->setOptions($options);
    }

    // SHOW 
    public static function SHOW($source) {
      self::$inst = new self();
      if(is_array($source)) { throw new \Exception("Sorry, It can't show multiple images in one"); }
      self::$inst->processSource($source)->show_image(self::$inst->mimeInfo, self::$inst->fullPath);
    }
    
    public static function SHOWSTAT($source, $mimeInfo) {
      self::$inst = new self();
      if(is_array($source)) { throw new \Exception("Sorry, It can't show multiple images in one"); }
      self::$inst->show_image($mimeInfo, $source);
    }

    // DOWNLOAD
    public static function DOWNLOAD($source) {
      self::$inst = new self();
      if(is_array($source)) { throw new \Exception("Sorry, It can't Download multiple images in one action"); }
      self::$inst->processSource($source)->download_file(self::$inst->mimeInfo, self::$inst->fullPath, self::$inst->name.".".self::$inst->extension);
    }


    protected function processSource($source, $upload = true) {
      if(is_array($source)) {
        // is soruce is $_FILES
        $firstKey = array_key_first($source);
        if(isset($source[$firstKey]['tmp_name']) && is_array($source[$firstKey]['tmp_name'])) {
          foreach ($source as $key => $arr) {
              $sources[$key] = $this->reArrayFiles($arr);
          }
          // $sources = $this->reArrayFiles($source);
        } else {
          $sources = $source;
        }
        // print_r($_FILES);
        // upload if source is array
        foreach($sources as $source) {
          if(isset($source['tmp_name'])) {
            $imgs = $this->processSource($source['tmp_name'])->name($source['name']);
            if($upload) {
              $imgs->uploadFile();
            }
            return $imgs;
          } else {
            $imgs =  $this->processSource($source);
            if($upload) {
              $imgs->uploadFile();
            }
            return $imgs;
          }
        }
      } else {

        // Source Processing 
        $this->source = $source;
        // if source is resource type object
        if($this->isGDResource($this->source)) {
          $this->sourceType = "resource";
          $this->processResource($this->source);
        } else {
          // if source is file path and image type mime
          $this->mimeInfo = $this->isImagePath($this->source);
          if($this->mimeInfo) {
            $this->sourceType = "file";
            $this->processFile();
          } else {
            // check if source is string value of image
            if(strlen($this->source) > 300) {
              $this->sourceType = "string";
              $this->processString();
            } else {
              throw new \invalidArgumentException("ImageMe: __constructor's \$source argument must be path to image file, resource or array (with images)");
            }
          }
        }
        

      }
      return $this;
    }

    protected function uploadFile($ext=false) {
      // Upload image and Consider all options
      $this->setIdentificator();

      if(!$ext) {
        $ext = $this->saveExt;
      } else {
        $this->saveExt = $ext;
      }
      
      // options:
      $max_size = $this->getByteSizeFromStr($this->max_size);
      if($max_size < $this->fileSize) { throw new \Exception("file is too big. Max $max_size allowed"); }
      
      if(!in_array($this->extension, $this->allowed)) { throw new \Exception("File extension ".$this->extension." not allowed"); }
      
      if($this->useMark) {
        $this->watermark($this->wOpacity, $this->wPadding);
      }

      if($this->scale) {
        $this->scale();
      }

      $this->clearTmps();

      return $this->save($ext);
    }

    /* Sourcing process
    - vipovot namdvili mimetype
    - vipovot namdvili extension
    - vipovot fileSize
    - get identificator
    */

    /* upload process
    - namdvili mimetype da shevadarot dashvebulebs
    - extension shevadarot dashvebulebs
    - set Identificator
    */

    /* Processing */
    protected function processFile() {
      
      // mime Type and extension
      $this->setMimeExt();
      // file size
      $this->fileSize = filesize($this->source);
      // resource
      $this->resource = $this->imagecreatefromfile($this->source);
      // path
      $this->fullPath = $this->getFullPath($this->source);
      // string value
      // $this->string_value = file_get_contents($this->source);
      // name & id
      $this->resolveName();

      $this->setSizeByFile($this->source);
      

    }

    protected function processResource($source) {
      $this->resource = $source;
      $this->string_value = $this->imgResourceToString($this->resource);
      $this->setSizeByString($this->string_value);
      $this->fullPath = $this->createTMP();
    }

    protected function processString() {
      $this->string_value = $this->source;
      $this->resource = imagecreatefromstring($this->source);
      $this->setSizeByString($this->string_value);
      $this->setFilesizeByString();
      $this->fullPath = $this->createTMP();
    }


    /* CREATION */

    protected function createWebp() {
      $pathname = $this->fullName.".webp";
      $webp = imagewebp($this->resource, $pathname, $this->quality);
      if(!$webp) {
        throw new \Exception("Cann't create Webp Image");
      }
      imagedestroy($this->resource);
      return $pathname;
    }

    protected function createJpeg() {
      $pathname = $this->fullName.".jpeg";
      $jpeg = imagejpeg($this->resource, $pathname, $this->quality);
      if(!$jpeg) {
        throw new \Exception("Cann't create Jpeg Image");
      }
      imagedestroy($this->resource);
      return $pathname;
    }

    /* Manipulation */

    // scale
    protected function scaleImg($width) {
      if ($width < $this->width) {
        return imagescale($this->resource, $width);
      } else {
        throw new \InvalidArgumentException("\$width should be less than initial width while scaling");
      }
    }

    // watermark
    protected function watermark($opacity=100, $padding=5) {
      $watermark = $this->imagecreatefromfile($this->watermark);
      $image = $this->resource;
      if(!$image || !$watermark) throw new \InvalidArgumentException("Watermark or image resource not found");
      $watermark_size = getimagesize($this->watermark);
      $watermark_width = $watermark_size[0];
      $watermark_height = $watermark_size[1];
      $dest_x = $image_size[0] - $watermark_width - $padding;
      $dest_y = $image_size[1] - $watermark_height - $padding;
      $this->resource = imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $opacity);
      imagedestroy($image);
      imagedestroy($watermark);
    }

    /* HELPING FUNCTIONS */

    private function checkMachine() {
        $this->gd_ext = $this->check_gd_ext();
        $this->fileinfo_ext = $this->check_fileinfo_ext();
        $this->webp_func = $this->check_imagewebp_func();
        $this->jpeg_func = $this->check_imagejpeg_func();

        if(!$this->gd_ext) {
            // exception if no GD extension loaded
            throw new \Exception("");
        }
        if(!$this->fileinfo_ext) {
            // fileinfo Warning
            trigger_error("", E_USER_WARNING);
        }
        if(!$this->webp_func) {
            // imagewebp Warning
            trigger_error("", E_USER_WARNING);
        }
        if(!$this->jpeg_func) {
            // imagejpeg Warning
            trigger_error("", E_USER_WARNING);
        }
        
    }

    private function clearTmps() {
      if(!empty($this->tmps)) {
        foreach($this->tmps as $tmp) {
          if(file_exists($tmp)) {
            unlink($tmp);
          }
        }
      }
    }

    private function setIdentificator() {
      // easy 10 characters identificator
      if(!$this->recivedID) {
        $this->identificator = $this::generateRandom();
      }
    }

    private function setMimeExt() {
      if ($this->mimeInfo) {
        $arr = explode('/', $this->mimeInfo);
        $this->extension = $arr[1];
        $this->mimeType = $arr[0];
      } else {
        $this->mimeInfo = $this->get_file_mime($this->source);
        $this->mimeType = $this->mimeInfo['type'];
        $this->extension = $this->mimeInfo['ext'];
      }
    }

    private function setFullName($dir=false) {
      if(!$dir) {
        $dir = $this->dir;
      }
      if(isset($this->name)) {
        $name = $this->name;
      } else {
        $name = $this->defName;
      }
      $this->fullName = realpath($dir).DIRECTORY_SEPARATOR.$name."-{".$this->identificator."}";
      if(isset($this->saveExt)) {
        $this->fullName .= "-".$this->saveExt;
      }
      if(isset($this->width)) {
        $this->fullName .= "-".$this->width;
      }
    }

    private function resolveName() {
      if($this->sourceType == "file") {
        $name = basename($this->source);
        // if name is defined by us
        if(strpos($this->source, '{') !== false) {
          $this->identificator = $this::get_string_between($name,"{","}");
          $this->name = explode("-", $name)[0];
        } else {
          $this->name = explode(".", $name)[0];
        }
      }
    }

    // size with file
    private function setSizeByFile($file) {
      $image_size = getimagesize($file);
      $this->width = $image_size[0];
      $this->height = $image_size[1];
    }

    // size with string
    private function setSizeByString($string) {
      $image_size = getimagesizefromstring($string);
      $this->width = $image_size[0];
      $this->height = $image_size[1];
    }

    // filesize with string
    private function setFilesizeByString() {
      $name = $this->createTMP();
      $this->fileSize = filesize($name);
      unlink($name);
    }

    private function createTMP($save = false) {
      $rand = $this->generateRandom();
      $name = realpath($this->tmpDir).DIRECTORY_SEPARATOR.$rand.".txt";
      file_put_contents($name, $this->string_value);
      if (!$save) {
        // if not saving add it to delete list (tmps array)
        $this->tmps[] = $name;
      } else {
        // if saving add it in info tmp_file
        $this->tmp_file = $name;
      }
      
      return $name;
    }

    // resource to string
    private function imgResourceToString($img, $type = "webp") {
      $image = $img;
      // start buffering
      ob_start();
      if ($type == "webp") {
        imagepalettetotruecolor($image);
        imagewebp($image);
      } else {
        imagejpeg($image);
      }
      $contents = ob_get_clean();
      return $contents;
    }

    private function setOptions($options) {
      foreach ($options as $key => $value) {
        if(in_array($key, $this::$allowedOptions)) {
          $this->{$key} = $value;
        }
      }
    }
    

}