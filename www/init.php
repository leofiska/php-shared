<?php
$fpath = realpath(".".constant("DIR_SLASH"));
if ( strrchr($fpath,".") == ".php" ) {
  $fpath = str_replace(strrchr($fpath,constant("DIR_SLASH")),"",$fpath);
}
$fpath = substr($fpath,0,strrpos($fpath,constant("DIR_SLASH")));
$shared_path = str_replace(strrchr($fpath,constant("DIR_SLASH")),constant("DIR_SLASH")."shared",$fpath);
define("root",$fpath.constant("DIR_SLASH"));
define("shared_root",$shared_path.constant("DIR_SLASH"));
unset($fpath);
unset($shared_path);
unset($external_path);
include_once constant("root")."lib".constant("DIR_SLASH")."php".constant("DIR_SLASH")."init.php";

#if ( !c_user::validate_login( $uid, $aid ) ) {
#  
#}
?>
