<?php
namespace maestroerror\Traits;

trait filesProcessing {

    protected function checkdir($dir){
        if (!file_exists($dir)) {
          $this->createDir($dir);
        }
      }
    
      protected function createDir($dir){
        mkdir($dir, 0777, true);
      }
    
          /* Renames and moves file */
        protected function move_up_file($path, $suffix, $name, $wfile){
          $this->checkdir($path);
          $file = $path.$name.$suffix;
          move_uploaded_file($wfile, $file);
          return $file;
          }
    
        // creates empty .tmp file and writes file content
        protected function tmp_save($dir, $fullFileName, $identificator=NULL){
              do {
                  $suffix = ".tmp";
                  $this->checkdir($dir);
                  if ($identificator != NULL) {
                    $randname = $identificator;
                  } else {
                    $randname = mt_rand();
                  }
                  $file_info = $this->get_file_mime($fullFileName);
                  $file = $dir.$file_info['type'].".".$file_info['ext'].".".$randname.$suffix;
                  $fp = fopen($file, 'w');
                  fwrite($fp, file_get_contents($fullFileName));
              }
              while(!$fp);
    
              fclose($fp);
              return $file;
          }
    
        // creates empty .tmp file and writes file content
        protected function tmp_save_string($dir, $string, $name="image", $identificator=NULL){
              do {
                  $suffix = ".tmp";
                  $this->checkdir($dir);
                  if ($identificator != NULL) {
                    $randname = $identificator;
                  } else {
                    $randname = generateRandom();
                  }
                  $file = $dir."string.".$name.".".$randname.$suffix;
                  $fp = fopen($file, 'w');
                  fwrite($fp, $string);
              }
              while(!$fp);
    
              fclose($fp);
              return $file;
          }
    
          /* Checks the true mime type of the given file */
          protected function check_if_image($tmpname, $return_mime = 0){
          if (file_exists($tmpname)) {
            $finfo = finfo_open( FILEINFO_MIME_TYPE );
                $mtype = finfo_file( $finfo, $tmpname );
                if(strpos($mtype, 'image/') === 0){
              if ($return_mime == 1) {
                return $mtype;
              } else {
                return true;
              }
                } else {
                    return false;
                }
                finfo_close( $finfo );
          } else {
            return false;
          }
          }
    
        /* Gives image extension */
          protected function get_image_extension($tmpname){
          $mimeType = $this->check_if_image($tmpname, 1);
          if ($mimeType) {
            $arr = explode('/', $mimeType);
            return $arr[1];
          } else {
            // if not image or can't get MIME type
            return FALSE;
          }
          }
    
        /* Gives mime type info */
        protected function get_file_mime($tmpname, $return_string = 0){
          if (file_exists($tmpname)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mtype = finfo_file( $finfo, $tmpname );
                finfo_close( $finfo );
            if ($return_string == 1) {
              return $mtype;
            } else {
              $arr = explode('/', $mtype);
              $info = array('type' => $arr[0], 'ext'=>$arr[1]);
              return $info;
            }
          } else {
            return false;
          }
          }
    
          /* Checks if the image isn't to large */
          protected function check_file_size($tmpname, $max_size_str, $return_size = 0){
              $size_conf = substr($max_size_str, -1);
              $max_size = (int)substr($max_size_str, 0, -1);
    
              switch($size_conf){
                  case 'k':
                  case 'K':
                      $max_size *= 1024;
                      break;
                  case 'm':
                  case 'M':
                      $max_size *= 1024;
                      $max_size *= 1024;
                      break;
                  default:
                      $max_size = 1024000;
              }
          $filesize = filesize($tmpname);
              if($filesize > $max_size){
                  return false;
              } else {
            if ($return_size == 1) {
              return $filesize;
            } else {
              return true;
            }
              }
          }
    
          /* Checks if the image isn't to large */
          public function getByteSizeFromStr($string){
              $size_conf = substr($string, -1);
              $max_size = (int)substr($string, 0, -1);
    
              switch($size_conf){
                  case 'k':
                  case 'K':
                      $max_size *= 1024;
                      break;
                  case 'm':
                  case 'M':
                      $max_size *= 1024;
                      $max_size *= 1024;
                      break;
                  default:
                      $max_size = 1024000;
              }
          return $max_size;
          }
    
    
          /* Re-arranges the $_FILES array */
          protected function reArrayFiles($files){
            $file_ary = array();
            if (!empty($files['name'][0])) {
                if (is_array($files['name'])) {
                    $file_count = count($files['name']);
                } else {
                    $file_count = 1;
                }
                $file_keys = array_keys($files);
    
                for ($i=0; $i<$file_count; $i++) {
                    foreach ($file_keys as $key) {
                        if(is_array($files[$key])) {
                            $file_ary[$i][$key] = $files[$key][$i];
                        } else {
                            $file_ary[$i][$key] = $files[$key];
                        }
                    }
                }
            }
    
            return $file_ary;
          }
    
    
        protected function show_image($contentType, $fullFileName) {
          header("Content-Type: " . $contentType);
          ob_clean();
          flush();
          readfile($fullFileName);
        }
    
        // $img must be resource
        protected function show_image_res($contentType, $img, $type = "jpeg", $quality=100) {
          header("Content-Type: " . $contentType);
          ob_clean();
          flush();
          if ($type == "jpeg") {
            imagejpeg($img, quality:$quality);
          } elseif ($type == "webp") {
            imagewebp($img, quality:$quality);
          }
          imagedestroy($img);
        }
    
        /* Force a download of the file */
          protected function download_file($contentType, $fullFileName, $displayName){
    
              /* Send headers and file to visitor for download */
              header('Content-Description: File Transfer');
              header('Content-Disposition: attachment; filename='.basename($displayName));
              header('Expires: 0');
              header('Cache-Control: must-revalidate');
              header('Pragma: protected');
              header('Content-Length: ' . filesize($fullFileName));
              header("Content-Type: " . $contentType);
              readfile($fullFileName);
          }
    
        // creates image resource from any image or .tmp file
        protected function imagecreatefromfile( $filename ) {
          if (!file_exists($filename)) {
              throw new InvalidArgumentException('File "'.$filename.'" not found.');
          }
          $mime = $this->get_file_mime($filename);
          switch ( strtolower($mime['ext'])) {
              case 'jpeg':
              case 'jpg':
                  return imagecreatefromjpeg($filename);
              break;
    
              case 'png':
                  return imagecreatefrompng($filename);
              break;
    
              case 'gif':
                  return imagecreatefromgif($filename);
              break;
    
              case 'webp':
                  return imagecreatefromwebp($filename);
              break;
    
              case 'tmp':
                  return imagecreatefromstring(file_get_contents($filename));
              break;
    
              default:
                  throw new \InvalidArgumentException('File "'.$filename.'" is not valid jpg, png, webp, tmp or gif image.');
              break;
          }
    
      }
    
      protected function deleteFile($fullFileName, $silent=0) {
        if (file_exists($fullFileName)) {
            unlink($fullFileName);
            return TRUE;
        } else {
          if ($silent == 1) {
            return FALSE;
          } else {
            throw new \InvalidArgumentException('File "'.$fullFileName.'" not exists.');
          }
        }
      }

      protected function isGDResource($file) {
        if(gettype($file) == 'object') {
          if (get_class($file) === "GdImage") {
            return TRUE;
          } else {
            return FALSE;
          }
        }
      }
      protected function isImagePath($file) {
        return $this->check_if_image($file, 1);
      }

      protected function getFullPath($file) {
        return realpath($file);
      }
      
}

/* 
lab nominee left buzz begin stamp such obscure flee fragile general avoid
*/