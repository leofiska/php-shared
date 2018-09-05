<?php
class sf_recover_password {

  static function process( ) {
    $page = c_page::get_instance();
    $content = self::get_section();
    $filter = c_page::getFilter();
    $page->page->middle .= "<section class='center' id='".$filter->page."'>$content</section>";
  }

  static function get_section( ) {
    $config = c_page::getConfig();
    $filter = c_page::getFilter();
    $url = c_page::getCloneFilter();
    $url = c_page::convert_object_to_url($url);
    $sentences = sc_sentences::get_sentence_sequency("RECOVER_PASSWORD");
    $return = "<h1>$sentences->PASSWORD_RECOVERY</h1>";
    $return .= "<div class='center'><div class='inline emaw50vw' id='rp'>";
    if ( !isset($filter->id) ) {
      $return .= "<p class='rcenter cindent mb1em'>$sentences->RECOVER_PASSWORD_MESSAGE_1</p>";
      $return .= "<p class='rcenter cindent mb2em'>$sentences->RECOVER_PASSWORD_MESSAGE_2</p>";
      $return .= "<div class='inline left'>";
      $return .= "<form onsubmit='event.preventDefault();' action='$url'>";
      $return .= "<p class='lowercase'>$sentences->LOGIN_ACCOUNT:</p>";
      $return .= "<input class='mb1em ph lowercase' crypted lowercase autofocus type='text' name='login' value='' placeholder='$sentences->TYPE_HERE' poe />";
      $return .= "<p class='lowercase'>$sentences->FIRST_NAME:</p>";
      $return .= "<input class='mb1em ph uppercase' uppercase type='text' name='first_name' value='' placeholder='$sentences->TYPE_HERE' poe />";
      $return .= "<p class='lowercase'>$sentences->LAST_NAME:</p>";
      $return .= "<input class='mb2em ph uppercase' uppercase type='text' name='last_name' value='' placeholder='$sentences->TYPE_HERE' poe />";
      $return .= "<div class='center'>";
      $return .= "<label class='lowercase bold mr1em iglow4 pointer'>$sentences->RECOVER_PASSWORD<input class='invisible' type='button' default value='pr' onClick=\"send_ajax_form( this.form, this )\" /></label>";
      $return .= "</div>";
      $return .= "</form>";
      $return .= "</div>";
      $return .= "<p class='message' id='pr_message'></div>";
      $return .= "</div>";
    } elseif ( c_user::verify_block() ) {
      $hash = hash("whirlpool",$filter->id);
      $query = "WITH A AS ( SELECT *, user_attributes->'recovery_time' as recovery_time, user_attributes->'recovery_hash' as recovery_hash FROM tb_users tu) SELECT * FROM A WHERE ( NOW() - INTERVAL '30 minute' )::TIMESTAMP < recovery_time::TIMESTAMP AND recovery_hash='".c_sql::escape_string(@$_SERVER['REMOTE_ADDR'].",".$hash)."'";
      if ( !($obj = c_sql::get_first_object($query)) ) {
        c_user::insert_block();
        $return .= $sentences->TOKEN_NOT_FOUND;
      } else {
        $return .= "<p class='rcenter cindent mb1em'>$sentences->RECOVER_PASSWORD_MESSAGE_3</p>";
        $return .= "<p class='rcenter cindent mb2em italic'>$sentences->VALID_PASSWORD</p>";
        $return .= "<div class='inline left'>";
        $return .= "<form onsubmit='event.preventDefault();' action='$url'>";
        $return .= "<p class='lowercase'>$sentences->NEW_PASSWORD:</p>";
        $return .= "<input class='mb1em ph' type='password' name='new_password' value='' placeholder='$sentences->TYPE_HERE' poe />";
        $return .= "<p class='lowercase'>$sentences->CONFIRM_PASSWORD:</p>";
        $return .= "<input class='mb2em ph' type='password' name='confirm_password' value='' placeholder='$sentences->TYPE_HERE' poe />";
        $return .= "<div class='center'>";
        $return .= "<label class='lowercase bold mr1em iglow4 pointer'>$sentences->SET_PASSWORD<input class='invisible' type='button' default value='pr2' onClick=\"send_ajax_form( this.form, this )\" /></label>";
        $return .= "</div>";
        $return .= "</form>";
        $return .= "</div>";
        $return .= "<p class='message' id='pr2_message'></div>";
        $return .= "</div>";
      }
    } else {
      $return .= $sentences->NETWORK_BLOCKED;
    }
    $return .= "</div>";
    return $return;
  }
  static function process_ajax ( ) {
    $page = c_page::get_instance();
    $config = c_page::getConfig();
    $filter = c_page::getFilter();

    if ( isset($_POST) && !empty($_POST) ) {
      if ( !isset($_POST['f_name']) ) return;
      $data = new STDClass();
      foreach( $_POST AS $index => $value ) {
        $data->{$index} = trim($value);
      }
      switch(@$_POST['f_name']) {
        case "pr":
          if ( !c_user::has_permission("RECOVER_PASSWORD") ) break;
          $data->hashed_login = c_user::generate_hashed_login($data->login);
          $result = sc_accounts::recover_password( $data );
          if ( defined("AJAX_OK") ) {
            $page->page->xml = array("element"=>array(array("name"=>"rp","value"=>$result)));
          } else  {
            $page->page->xml = array("element"=>array(array("name"=>"pr_message","value"=>$result)));
          }
          break;
        case "pr2":
          if ( !c_user::has_permission("RECOVER_PASSWORD") ) break;
          $data->token = $filter->id;
          $result = sc_accounts::reset_password( $data );
          if ( defined("AJAX_OK") ) {
            $page->page->xml = array("element"=>array(array("name"=>"rp","value"=>$result)));
          } else  {
            $page->page->xml = array("element"=>array(array("name"=>"pr2_message","value"=>$result)));
          }
          break;
        default:
          break;
      }
    }
  }
}
?>
