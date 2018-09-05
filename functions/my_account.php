<?php
class sf_my_account {


  private static $sentences;

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

  static function process ( $allow_registration = false ) {
    $page = c_page::get_instance();
    if ( c_user::has_permission("USER_LOGIN") ) {
      $login = f_login::get_section( $allow_registration);
      $page->page->middle .= "<section class='padded_header' id='LOGIN'>$login</section>";
      return;
    }

    self::$sentences = c_sentences::get_sentence_sequency("MY_ACCOUNT");

    $url = c_page::get_clean_filter();
    $url->page = "MY_ACCOUNT";
    $url = c_page::convert_object_to_url($url);
    $user = c_user::get_instance();
    $attributes = $user->get_attributes();
    $return = "<section class='padded_header' id='my_account'>";
    $return .= "<h1>".self::$sentences->MY_ACCOUNT."</h1>";


    $return .= "<div class='center'><div class='inline'>";
    $return .= "<div class='table fixed'>";

      $return .= "<div class='tr'>";
        $return .= "<div class='td right ew30vw' style='padding-right: 10px'>";
          $return .= self::$sentences->USERNAME.":";
        $return .= "</div>";
        $return .= "<div class='td left cw40ch ew30vw'>";
          if ( isset($attributes->nickname_time) ) {
            $time = strtotime($attributes->nickname_time);
            if ( time() < ($time+(30*24*60*60)) ) {
              $return .= "<span>".c_user::getUsername()."</span>";
            } else {
              $return .= "<span class='pointer iglow4' id='f_username' me sb soe='edit_username' size='16ch' sot='text' sop='attributes[]' sov='username' son='username'>".c_user::getUsername()."</span>";
            }
          } else {
            $return .= "<span class='pointer iglow4' id='f_username' me sb soe='edit_username' size='16ch' sot='text' sop='attributes[]' sov='username' son='username'>".c_user::getUsername()."</span>";
          }
        $return .= "</div>";
        $return .= "<div class='td left middle'>";
        $return .= "</div>";
      $return .= "</div>";
      $return .= "<div class='tr'>";
        $return .= "<div class='td right' style='padding-right: 10px'>";
          $return .= self::$sentences->ITEMS_PER_PAGE.":";
        $return .= "</div>";
        $return .= "<div class='td left'>";
          $return .= "<input min='10' max='200' type='number' name='items_per_page' id='items_per_page' value='$attributes->items_per_page' style='width: ".(strlen($attributes->items_per_page)+1)."em;' class='h1em' onchange='key_change( this )' />";
        $return .= "</div>";
        $return .= "<div class='td left middle'>";
        $return .= "</div>";
      $return .= "</div>";


      if ( c_user::has_permission("CHANGE_PASSWORD") ) {
        $return .= "<div class='tr'>";
          $return .= "<div class='td right' style='padding-right: 10px'>";
            $return .= self::$sentences->PASSWORD.":";
          $return .= "</div>";
          $return .= "<div class='td left'>";
            $url = c_page::get_clean_filter();
            $url->page = "CHANGE_PASSWORD";
            $url = c_page::convert_object_to_url($url);
            $return .= "<a href='/$url' class='pointer iglow4'>".self::$sentences->CHANGE_PASSWORD."</a>";
          $return .= "</div>";
          $return .= "<div class='td left middle'>";
          $return .= "</div>";
        $return .= "</div>";
      }
 
    if ( isset($attributes->autologin) && !empty($attributes->autologin) ) {
  //AUTOLOGIN
      $query = "SELECT * FROM tb_users WHERE user_id='".c_user::getID()."'";
      if ( $obj1 = c_sql::get_first_object($query) ) {
         $return .= "<div class='tr'>";
          $return .= "<div class='td right' style='padding-right: 10px'>";
            $return .= self::$sentences->AUTOLOGINURL.":";
          $return .= "</div>";
          $return .= "<div class='td left'>";
            $url = c_page::get_clean_filter();
            $url->page = "USER_AUTOLOGIN";
            $url->id = c_user::getID();
            $url->uid = $obj1->user_hash;
            $url = c_page::convert_object_to_url($url);
            $return .= "<input style='width: 15em;' readonly type='text' value='https://".$_SERVER['SERVER_NAME']."/$url' class='h1em' onclick=\"this.select()\" onfocus=\"this.select( )\"/>";
          $return .= "</div>";
          $return .= "<div class='td left middle'>";
          $return .= "</div>";
        $return .= "</div>";
      }
      unset($obj1);
    }
//ENDOFAUTOLOGIN

      if ( c_page::has_panic() ) {
        $return .= "<div class='tr'>";
          $return .= "<div class='td right' style='padding-right: 10px'>";
            $return .= self::$sentences->PANIC_ACTION.":";
          $return .= "</div>";
          $return .= "<div class='td left'>";
            if ( isset($attributes->panic) && !empty($attributes->panic) ) {
              $return .= "<input type='checkbox' checked=checked name='panic' onchange=\"save ( this )\" />";
            } else {
              $return .= "<input type='checkbox' name='panic' onchange=\"save ( this )\" />";
            }
          $return .= "</div>";
          $return .= "<div class='td left middle'>";
          $return .= "</div>";
        $return .= "</div>";

        $return .= "<div class='tr'>";
          $return .= "<div class='td right' style='padding-right: 10px'>";
            $return .= self::$sentences->HIDE_ON_BLUR.":";
          $return .= "</div>";
          $return .= "<div class='td left'>";
            if ( isset($attributes->hide_on_blur) && !empty($attributes->hide_on_blur) ) {
              $return .= "<input type='checkbox' checked=checked name='hide_on_blur' onchange=\"save ( this )\" />";
            } else {
              $return .= "<input type='checkbox' name='hide_on_blur' onchange=\"save ( this )\" />";
            }
          $return .= "</div>";
          $return .= "<div class='td left middle'>";
          $return .= "</div>";
        $return .= "</div>";


      }


    $return .= "</div>";
    $return .= "</div></div>";

    $query = "SELECT * FROM tb_relation_ldap_user trlu JOIN tb_ldap tl ON tl.ldap_id=trlu.trlu_ldap_id WHERE trlu_user_id=".c_user::getID()."";
    $result = c_sql::select($query);
    if ( c_sql::num_rows($result) > 0 ) {
      $return .= "<div class='center mt2em'>";
      $return .= "<div class='inline emiw30vw center'>";
      while( $ldap = c_sql::fetch_object($result) ) {
        $return .= "<fieldset class='border p1em center'>";
        $return .= "<legend class='bold left'>$ldap->ldap_domain</legend>";
        $return .= "<div class='inline center'>";
        $return .= "<div class='table'>";
          $return .= "<div class='tr'>";
            $return .= "<div class='td right pr5px'>";
              $return .= self::$sentences->USERNAME.": ";
            $return .= "</div>";
            $return .= "<div class='td left'>";
              $return .= $ldap->trlu_alias;
            $return .= "</div>";
          $return .= "</div>";
          $return .= "<div class='tr'>";
            $return .= "<div class='td right pr5px'>";
              $return .= self::$sentences->PASSWORD.":";
            $return .= "</div>";
            $return .= "<div class='td left'>";
              $url = c_page::get_clean_filter();
              $url->page = "CHANGE_PASSWORD";
              $url->ldap = $ldap->ldap_id;
              $url->login = $ldap->trlu_alias;
              $url = c_page::convert_object_to_url($url);
              $return .= "<a href='/$url' class='pointer iglow4'>".self::$sentences->CHANGE_PASSWORD."</a>";
            $return .= "</div>";
          $return .= "</div>";
        $return .= "</div>";
        $return .= "</div>";
        $return .= "</fieldset>";
      }
      $return .= "</div>";
      $return .= "</div>";
    }
    $return .= "</section>";
    $page->page->middle = $return;
    unset($return);
    return;
  }
  static function process_ajax ( ) {

    $page = c_page::get_instance();
    $data =  new static;

    if ( isset($_POST) && !empty($_POST) ) {
      if ( isset($_POST['operation']) && isset($_POST['value']) ) {
        switch($_POST['operation']) {
          case "hide_on_blur":
          case "panic":
            $value = trim($_POST['value']);
            if ( $value == "true" ) {
              $value = 1;
            } else {
              $value = 0;
            }
            $query = "UPDATE tb_users SET user_attributes=user_attributes || '\"".$_POST['operation']."\"=>\"$value\"'::hstore WHERE user_id=".c_page::get_user_id().";";
            c_sql::select($query);
            $page->page->xml = array("element"=>array(array("name"=>$_POST['operation'],"value"=>$value) ));
            break;
          case "items_per_page":
            $value = intval(trim($_POST['value']));
            if ( $value > 200 ) $value = 200;
            elseif( $value < 10 ) $value = 10;
            $query = "UPDATE tb_users SET user_attributes=user_attributes || '\"items_per_page\"=>\"$value\"'::hstore WHERE user_id=".c_page::get_user_id()." RETURNING user_attributes->'items_per_page' as items_per_page;";
            $obj = c_sql::get_first_object($query);
            if ( isset($obj->items_per_page) ) {
              $page->page->xml = array("element"=>array(array("name"=>"save_items_per_page","value"=>"/pictures/checked.png"),array("name"=>"items_per_page","value"=>"$obj->items_per_page") ));
            }
            break;
        }
        return;
      }
      foreach( $_POST AS $index => $value ) {
        if ( isset($data->{$index}) ) $data->{$index} = trim($value);
      }
      if ( isset($_POST['f_name']) && !empty($_POST['f_name']) ) {
        switch(@$_POST['f_name']) {
          default:
            if ( isset($_SERVER['REMOTE_ADDR']) ) c_user::insert_block($_SERVER['REMOTE_ADDR']);
            break;
          case "edit_username":
            if ( !isset($_POST['username']) ) break;
            $result = sc_accounts::change_username( $_POST['username'] );
            //$page->page->xml = array("element"=>array(array("name"=>"username_message","value"=>$result),array("name"=>"f_username","original"=>$_POST['username']),array("name"=>"save_username","operation"=>"change_class","value"=>"disabled_button")));
            if ( defined("AJAX_OK") ) {
              $page->page->xml = array("element"=>array(array("name"=>"username_message","value"=>$result),array("name"=>"f_username","original"=>$_POST['username']),array("name"=>"save_username","operation"=>"change_class","value"=>"disabled_button"),array("name"=>"f_username","rme"=>true,"sleep"=>"1000")));
            } else {
              $page->page->xml = array("element"=>array(array("name"=>"username_message","value"=>$result)));
            }
            break;
          case "login":
            if ( !c_user::has_permission("USER_LOGIN") ) break;
            if ( !($result = c_user::login( $data->login, $data->password, $data->keep_connected )) ) {
              $page->page->xml = array("loadpage"=>"");
            } else {
              if ( $result == "LDAP" ) {
                $result = sc_ldap::login( c_sentences::strtolower($data->login), $data->password, $data->keep_connected );
                if ( empty($result->error) ) {
                  $page->page->xml = array("loadpage"=>$result->url);
                  break;
                } else {
                  $page->page->xml = array("element"=>array(array("name"=>"login_message","value"=>c_sentences::get_sentence($result->message))));
                  break;
                }
              }
              $page->page->xml = array("element"=>array(array("name"=>"login_message","value"=>c_sentences::get_sentence($result))));
            }
            break;
          case "create_account":
            if ( !c_user::has_permission("CREATE_ACCOUNT") ) break;
            if ( !($result = c_user::create_account( $data->username, $data->email )) ) {
              $page->page->xml = array("reload"=>true);
            } else {
              $page->page->xml = array("element"=>array(array("name"=>"create_account_message","value"=>c_sentences::get_sentence($result))));
            }
            break;
          case "recover_password":
            if ( !c_user::has_permission("LOGIN") ) break;
            $result = c_user::recover_password( $data->login );
            $page->page->xml = array("element"=>array(array("name"=>"recover_password_message","value"=>c_sentences::get_sentence($result))));
            break;
        }
      }
    }
  }
}
?>
