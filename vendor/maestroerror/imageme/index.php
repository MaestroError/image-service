<?php

require "vendor/autoload.php";
require "src/ImageMe.php";

use maestroerror\ImageMe;

$img = new ImageMe('irmakiPNG.png');

// default upload
$img->up();

// upload with jpeg() or webp()
$img->scale(400)->name("scaled")->jpeg()->up();

// upload with force to webp with upW() (so jpeg() has no affect)
$img->scale(50)->to("images/icons")->id("TEST123id")->jpeg()->upW();

$img->test();



// ImageMe::UPLOAD("irmaki.jpg");