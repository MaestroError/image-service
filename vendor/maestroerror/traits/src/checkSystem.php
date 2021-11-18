<?php 
namespace maestroerror\Traits;

trait checkSystem {
    protected function check_fileinfo_ext(){
        if (!extension_loaded('fileinfo')) {
          // dl() is disabled in the PHP-FPM since php7 so we check if it's available first
          if(function_exists('dl')){
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
              if (!dl('fileinfo.dll')) {
                return false;
              } else {
                return true;
              }
            } else {
              if (!dl('fileinfo.so')) {
                return false;
              } else {
                return true;
              }
            }
          } else {
            return false;
          }
        } else {
          return true;
        }
      }
      
      protected function check_gd_ext(){
        if (!extension_loaded('gd') && !function_exists('gd_info')) {
          // dl() is disabled in the PHP-FPM since php7 so we check if it's available first
          if(function_exists('dl')){
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
              if (!dl('php_gd.dll')) {
                return false;
              } else {
                return true;
              }
            } else {
              if (!dl('gd.so')) {
                return false;
              } else {
                return true;
              }
            }
          } else {
            return false;
          }
        } else {
          return true;
        }
      }
      
      protected function check_imagewebp_func(){
        if (!function_exists('imagewebp')) {
          return false;
        } else {
          return true;
        }
      }
      
      protected function check_imagejpeg_func(){
        if (!function_exists('imagejpeg')) {
          return false;
        } else {
          return true;
        }
      }
}