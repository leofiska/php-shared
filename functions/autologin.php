<?php
class sf_user_autologin {

  private $user_id;
  private $login;
  private $password;
  private $keep_connected;
  private $username;
  private $email;

  function __construct() {
    $this->user_id = "";
    $this->login = "";
    $this->password = "";
    $this->keep_connected = "";
    $this->username = "";
    $this->email = "";
  }
  static function process ( ) {
    $page = c_page::get_instance();
    $filter = $page->get_filter();
    $query = "SELECT * FROM tb_users WHERE user_id='".c_sql::escape_string($filter->id)."' AND user_hash='".c_sql::escape_string($filter->uid)."'";
    if ( (isset($_SERVER['REMOTE_ADDR']) && c_user::verify_block($_SERVER['REMOTE_ADDR'])) && ($obj = c_sql::get_first_object($query)) ) {
      c_user::create_user_cookies( $obj->user_id, false );
      if ( !empty($obj->user_start_page) ) {
        header( "Location: /$obj->user_start_page" );
        die();
      }
    } else {
      $time = time()-3600;
      setcookie("aid", 0, $time, "/", $_SERVER['SERVER_NAME'], false, false);
      setcookie("uid", 0, $time, "/", $_SERVER['SERVER_NAME'], false, false);
      if ( isset($_SERVER['REMOTE_ADDR']) ) c_user::insert_block($_SERVER['REMOTE_ADDR']);
    }
    header( "Location: /" );
    die();
  }
}
?>
