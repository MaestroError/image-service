<?php

namespace maestroerror\Traits;

trait mail {
 // connection func
 public function send_mail($to, $subject, $body, $frname, $fremail) {

   $header = "From: noreply $frname <$fremail>\n";

   $header .= "Reply-To: $frname <$fremail>\r\n";
   $header .= "Return-Path: $frname <$fremail>\r\n";

   $header .= "Organization: Digital-eds\r\n";
   $header .= "MIME-Version: 1.0\r\n";
   $header .= "Content-type: text/html; charset=UTF-8\r\n";
   $header .= "X-Priority: 3\r\n";
   $header .= "X-Mailer: PHP". phpversion() ."\r\n";

  //$body = mb_convert_encoding($body, "UTF-8","AUTO");
  //$subject = mb_convert_encoding($subject, "UTF-8","AUTO");

   if(!mail($to, $subject, $body, $header))
     http_response_code(500);
 }
}
