<?php
class sf_change_password {

  private $current_password;
  private $new_password;
  private $confirm_new_password;

  function __construct() {
    $this->current_password = "";
    $this->new_password = "";
    $this->confirm_new_password = "";
  }

  static function process ( ) {
    $sentences = sc_sentences::get_sentence_sequency("CHANGE_PASSWORD");
    $page = c_page::get_instance();
    $filter = c_page::getFilter();
    $content = self::get_section( $sentences );
    $page->page->middle .= "<section class='center' id='CHANGE_PASSWORD'>$content</section>";
    $page->page->navigator = $page->render_navigator( "MY_ACCOUNT" );
    $url = sc_page::get_clean_filter();
    $url->page = $filter->page;
    $url = sc_page::convert_object_to_url($url);
    $page->page->navigator .= "<span class='nowrap op4'> >    </span><span class='vmiddle'><a class='glow4' href='/$url'>$sentences->CHANGE_PASSWORD</a></span>";
  }
  public static function get_section( $sentences = null ) {
    if ( $sentences == null ) $sentences = sc_sentences::get_sentence_sequency("CHANGE_PASSWORD");
    $page = c_page::get_instance();
    $config = $page->get_config();
    $filter = c_page::getFilter();


    if ( isset($_SERVER['REMOTE_ADDR']) ) {
      if ( !c_user::verify_block($_SERVER['REMOTE_ADDR']) ) {
        return c_sentences::get_sentence("NETWORK_BLOCKED");
      }
    }
    if ( isset($filter->ldap) ) {
      $query = "SELECT * FROM tb_relation_ldap_user trlu JOIN tb_ldap tl ON tl.ldap_id=trlu_ldap_id WHERE trlu_user_id=".c_user::getID()." AND trlu_ldap_id=".intval(c_sql::escape_string($filter->ldap))."";
      $ldap = c_sql::get_first_object($query);
      if ( empty($ldap) || ($filter->login !== $ldap->trlu_alias) ) {
        if ( isset($_SERVER['REMOTE_ADDR']) ) {
          c_user::insert_block($_SERVER['REMOTE_ADDR']);
        }
        return c_sentences::get_sentence("PERMISSION_DENIED");
      }
    }

    $url = c_page::getFilter();
    $url = c_page::convert_object_to_url($url);

    $return = "";
    $return .= "<h1>$sentences->CHANGE_PASSWORD</h1>";
    $return .= "<form onsubmit='event.preventDefault();' action='/$url'>";
    $return .= "<div class='center'><div class='inline ema50vw center'>";
    $return .= "<p class='rcenter cindent mb1em italic'>$sentences->VALID_PASSWORD</p>";
    $return .= "<div class='inline left'>";
    $return .= "<div class='inline middle'>";
    $return .= "<span class='lowercase middle'>";
    if ( !isset($filter->ldap) ) {
      $return .= "$sentences->CURRENT_PASSWORD: ";
    } else {
      $return .= "$sentences->CURRENT_PASSWORD_FOR \"$ldap->trlu_alias@$ldap->ldap_domain\": ";
    }
    $return .= "</span></div>";
    $return .= "<div class='left mb1em'>";
    $return .= "<input type='password' poe style='min-width: 17em;' id='current_password' name='current_password' autofocus placeholder='$sentences->TYPE_HERE'";
    if ( !isset($ldap) ) {
      $return .= " crypted";
    }
    $return .= " />";
    $return .= "</div>";


    $return .= "<div class='inline middle'>";
    $return .= "<span class='lowercase middle'>";
    if ( !isset($filter->ldap) ) {
      $return .= "$sentences->NEW_PASSWORD: ";
    } else {
      $return .= "$sentences->NEW_PASSWORD_FOR \"$ldap->trlu_alias@$ldap->ldap_domain\": ";
    }
    $return .= "</span></div>";
    $return .= "<div class='left mb1em'>";
    $return .= "<input type='password' poe style='min-width: 17em;' id='new_password' name='new_password' placeholder='$sentences->TYPE_HERE' />";
    $return .= "</div>";


    $return .= "<div class='inline middle'>";
    $return .= "<span class='lowercase middle'>";
    $return .= "$sentences->CONFIRM_NEW_PASSWORD: ";
    $return .= "</span></div>";
    $return .= "<div class='left mb1em'>";
    $return .= "<input type='password' poe style='min-width: 17em;' id='confirm_new_password' name='confirm_new_password' placeholder='$sentences->TYPE_HERE' />";
    $return .= "</div>";

    $return .= "<div class='center'>";
      $return .= "<label class='bold mr1em iglow4 pointer lowercase'>$sentences->CHANGE_PASSWORD<input class='invisible' type='button' default value='change_password' onClick=\"send_ajax_form( this.form, this )\" /></label>";
    $return .= "</div>";
    $return .= "</div>";
    $return .= "<div class='message' id='change_password_message'></div>";
    $return .= "</div>";
    $return .= "</div>";
    $return .= "</form>";
    $url = c_page::get_clean_filter();
    $url->page = "MY_ACCOUNT";
    $url = c_page::convert_object_to_url($url);
    $return .= "<a class='glow4' href='/$url'>$sentences->GO_BACK</a>";
    return $return;
  }
  static function process_ajax ( ) {

    $page = c_page::get_instance();
    $data =  new static;
    $filter = c_page::getFilter();

    if ( isset($_SERVER['REMOTE_ADDR']) ) {
      if ( !c_user::verify_block($_SERVER['REMOTE_ADDR']) ) {
        $page->page->xml = array("element"=>array(array("name"=>"change_password_message","value"=>sc_sentences::get_sentence("NETWORK_BLOCKED"))));
        return; 
      }
    }
    if ( isset($filter->ldap) ) {
      $query = "SELECT * FROM tb_relation_ldap_user trlu JOIN tb_ldap tl ON tl.ldap_id=trlu_ldap_id WHERE trlu_user_id=".c_user::getID()." AND trlu_ldap_id=".intval(c_sql::escape_string($filter->ldap))."";
      $ldap = c_sql::get_first_object($query);
      if ( empty($ldap) || ($filter->login !== $ldap->trlu_alias) ) {
        if ( isset($_SERVER['REMOTE_ADDR']) ) {
          c_user::insert_block();
        }
        $page->page->xml = array("element"=>array(array("name"=>"change_password_message","value"=>sc_sentences::get_sentence("PERMISSION_DENIED"))));
        return; 
      }
    }

    if ( isset($_POST) && !empty($_POST) ) {
      foreach( $_POST AS $index => $value ) {
        if ( isset($data->{$index}) ) $data->{$index} = trim($value);
      }
      switch(@$_POST['f_name']) {
        default:
          break;
        case "change_password":
          if ( isset($filter->ldap) ) {
            $result = sc_ldap::change_password( $ldap->ldap_id, $ldap->trlu_alias, $_POST['current_password'], $_POST['new_password'], $_POST['confirm_new_password'] );
          } else {
           $result = sc_accounts::change_password( $data->current_password, $data->new_password, $data->confirm_new_password );
          }
          if ( defined("AJAX_OK") ) {
            $url = c_page::get_clean_filter();
            $url->page = "MY_ACCOUNT";
            $url = c_page::convert_object_to_url($url);
            $page->page->xml = array("element"=>array(array("name"=>"change_password_message","value"=>$result)),"sleep"=>2000,"loadpage"=>$url);
          } else {
            $page->page->xml = array("element"=>array(array("name"=>"change_password_message","value"=>$result)));
          }
          break;
        case "recover_password":
          break;
      }
    }

  }
}
?>
