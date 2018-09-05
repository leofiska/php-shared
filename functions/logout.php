<?php
  class sf_user_logout {
    static function process( ) {
      $user = c_user::get_instance();
      $user->logout();
      header( "Location: ".sc_page::get_host_url() );
      die();
    }
  }
?>
