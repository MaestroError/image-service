<?php 
namespace maestroerror;
class testController {
    public function test($id, $user, $hello, $withArgument) {
        echo __FUNCTION__." <----<br>\n";
        echo $id."::".$user."<br>\n";
        echo "Def: ".$hello."<br>\n";
        echo "with: ".$withArgument."<br>\n";
        echo "with is more important then def <br>";
        print_r(Router::QUERY());
        print_r(Router::PDATA());
    }
}
