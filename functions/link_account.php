<?php
class sf_link_account {

  function __construct() {
  }


  public static function process() {
    $sentences = sc_sentences::get_sentence_sequency("LINK_ACCOUNT");
    $page = c_page::get_instance();
    $filter = c_page::getFilter();
    $content = self::get_section( $sentences );
    $page->page->middle .= "<section class='center' id='LINK_ACCOUNT'>$content</section>";
    $page->page->navigator = $page->render_navigator();
    $url = sc_page::get_clean_filter();
    $url->page = $filter->page;
    $url->token = $filter->token;
    $url = sc_page::convert_object_to_url($url);
    $page->page->navigator .= "<span class='nowrap op4'> >    </span><span class='vmiddle'><a class='glow4' href='/$url'>$sentences->LINK_ACCOUNT</a></span>";
  }

  public static function get_section( $sentences = null ) {
    if ( $sentences == null ) $sentences = sc_sentences::get_sentence_sequency("LINK_ACCOUNT");
    $page = c_page::get_instance();
    $filter = c_page::getFilter();
    $user = c_user::get_instance();
    $config = $page->get_config();

    if ( !isset($filter->token) ) {
      c_page::not_found();
    }

    if ( !c_user::verify_block($_SERVER['REMOTE_ADDR']) ) {
      return c_sentences::get_sentence("NETWORK_BLOCKED");
    }
    $token = hash("sha256",sc_user::getGlobalID().$filter->token);
    $query = "SELECT * FROM tb_relation_ldap_user trlu JOIN tb_ldap tl ON trlu.trlu_ldap_id=tl.ldap_id WHERE trlu_token='".c_sql::escape_string($token)."'";
    if ( !($obj = c_sql::get_first_object($query)) ) {
      c_user::insert_block($_SERVER['REMOTE_ADDR']);
      return c_sentences::get_sentence("INVALID_TOKEN");
    }


    if ( $user->is_logged() ) {
      $return .= "OH YEAH";
      return $return;
    }

    $return = "<div class='center'><div class='inline emaw50vw'>";
    if ( !isset($filter->fa) && !isset($filter->ha) ) {
      $return .= "<h1>$sentences->LINK_ACCOUNT</h1>";
      $return .= "<p class='rcenter indent mb1em'>".str_replace("[[-USER-]]",$obj->trlu_alias,str_replace("[[-DOMAIN-]]",$obj->ldap_domain,$sentences->DO_YOU_HAVE_AN_ACCOUNT))."</p>";
      $return .= "<div class='dlist'>";
      $url = c_page::getCloneFilter();
      $url->fa = true;
      $url = c_page::convert_object_to_url($url);
      $return .= "<div><div class='middle pointer' onClick=\"loadpage( '/$url' )\"><p class='uppercase bold'>$sentences->FIRST_ACCESS</p></div></div>";
      $url = c_page::getCloneFilter();
      $url->ha = true;
      $url = c_page::convert_object_to_url($url);
      $return .= "<div><div class='middle pointer' onClick=\"loadpage( '/$url' )\"><p class='uppercase bold'>$sentences->HAVE_ACCOUNT</p></div></div>";
      $return .= "</div>";
      return $return;
    }
    if ( isset($filter->ha) ) {
      $return .= "<h1>$sentences->LINK_ACCOUNT</h1>";
      $return .= "<p class='indent rcenter mb1em'>".str_replace("[[-USER-]]",$obj->trlu_alias,str_replace("[[-DOMAIN-]]",$obj->ldap_domain,$sentences->YOU_HAVE_ACCOUNT_WHICH_ACCOUNT))." </p>";
      $url = c_page::getCloneFilter();
      $url = c_page::convert_object_to_url($url);
      $return .= "<form action='/$url'>";
      $return .= "<div class='inline left'>";
      $return .= "<div class='inline middle'>";
        $return .= "<span class='middle inline lowercase bold' id='login_id'>$sentences->LOGIN_ACCOUNT</span>";
        if ( isset($sentences->LOGIN_HELP) ) $return .= "<span class='help' title='$sentences->LOGIN_HELP'>(?)</span>";
        $return .= "<span class='inlint middle'>:</span>";
      $return .= "</div>";
      $return .= "<div class='mb1em'>";
        $return .= "<input poe style='width: 30ch' type='text' placeholder='$sentences->TYPE_HERE'id='login' name='login' value='' clear autofocus />";
      $return .= "</div>";


      $return .= "<div class='inline middle'>";
        $return .= "<span class='middle inline lowercase bold' id='login_id'>$sentences->PASSWORD</span>";
        if ( isset($sentences->PASSWORD_HELP) ) $return .= "<span class='help' title='$sentences->PASSWORD_HELP'>(?)</span>";
        $return .= "<span class='inlint middle'>:</span>";
      $return .= "</div>";
      $return .= "<div class='mb1em'>";
        $return .= "<input poe style='width: 30ch' type='password' placeholder='$sentences->TYPE_HERE'id='password' name='password' value='' clear />";
      $return .= "</div>";

      $return .= "<div class='center'>";
        $return .= "<label class='bold mr1em iglow4 pointer'>$sentences->LINK<input class='invisible' type='button' default value='link' onClick=\"send_ajax_form( this.form, this )\" /></label>";
      $return .= "</div>";
      $return .= "</div>";
      $return .= "<div class='message' id='link_message'></div>";
      $return .= "</form>";

      $url = c_page::getCloneFilter();
      unset($url->ha);
      $url = c_page::convert_object_to_url($url);
      $return .= "<a class='glow4' href='/$url'>$sentences->BACK_TO_PREVIOUS_QUESTION</a>";
      return $return;
    } elseif ( isset($filter->fa) ) {
      $return .= "<h1>$sentences->CREATE_ACCOUNT</h1>";
      $return .= "<p class='indent justify mb1em'>".str_replace("[[-USER-]]",$obj->trlu_alias,str_replace("[[-DOMAIN-]]",$obj->ldap_domain,$sentences->FIRST_ACCESS_CREATE_ACCOUNT))." </p>";
      $url = c_page::getCloneFilter();
      $url = c_page::convert_object_to_url($url);
      if ( isset($obj->trlu_data) && !empty($obj->trlu_data) ) {
        $data = json_decode($obj->trlu_data);
      }
      if ( isset($obj->trlu_email) ) $obj->trlu_email = explode(",",substr($obj->trlu_email,1,-1));
      $return .= "<form action='/$url'>";
      $return .= "<div class='inline left'>";
      $return .= "<div class='inline middle'>";
        $return .= "<span class='middle inline lowercase bold' id='username_id'>$sentences->USERNAME</span>";
        if ( isset($sentences->USERNAME_HELP) ) $return .= "<span class='help' title='$sentences->USERNAME_HELP'>(?)</span>";
        $return .= "<span class='inlint middle'>:</span>";
      $return .= "</div>";
      $return .= "<div class='mb1em left'>";
      $return .= "<input style='width: 30ch' lowercase class='lowercase middle' poe type='text' placeholder='$sentences->TYPE_HERE' maxlength='16' name='username' value='";
      if ( !sc_accounts::is_registered($obj->trlu_alias) ) {
        $return .= $obj->trlu_alias;
      }
      $return .= "'clear autofocus>";
      $return .= "</div>";

      $return .= "<div class='inline middle left'>";
        $return .= "<span class='lowercase bold inline middle' id='firstname_id'>$sentences->FIRSTNAME</span>";
        if ( isset($sentences->FIRSTNAME_HELP) ) $return .= "<span class='help' title='$sentences->FIRSTNAME_HELP'>(?)</span>";
        $return .= "<span>:</span>";
      $return .= "</div>";
      $return .= "<div class='mb1em'>";
      $return .= "<input poe style='width: 30ch' type='text' placeholder='$sentences->TYPE_HERE' id='firstname' name='firstname' value='";
      if ( isset($data->givenname) && isset($data->givenname[0]) ) $return .= $data->givenname[0];
      $return .= "' clear />";
      $return .= "</div>";


      $return .= "<div class='inline middle left'>";
        $return .= "<span class='lowercase bold inline middle' id='lastname_id'>$sentences->LASTNAME</span>";
        if ( isset($sentences->LASTNAME_HELP) ) $return .= "<span class='help' title='$sentences->LASTNAME_HELP'>(?)</span>";
        $return .= "<span>:</span>";
      $return .= "</div>";
      $return .= "<div class='mb1em left'>";
      $return .= "<input poe style='width: 30ch' type='text' placeholder='$sentences->TYPE_HERE' id='lastname' name='lastname' value='";
      if ( isset($data->sn) && isset($data->sn[0]) ) $return .= $data->sn[0];
      $return .= "' clear />";
      $return .= "</div>";

      $return .= "<div class='inline middle left'>";
        $return .= "<span class='lowercase bold inline middle' id='fullname_id'>$sentences->FULLNAME</span>";
        if ( isset($sentences->FULLNAME_HELP) ) $return .= "<span class='help' title='$sentences->FULL_HELP'>(?)</span>";
        $return .= "<span>:</span>";
      $return .= "</div>";
      $return .= "<div class='left mb1em'>";
      $return .= "<input poe style='width: 30ch' type='text' placeholder='$sentences->TYPE_HERE' id='fullname' name='fullname' value='";
      if ( isset($data->givenname) && isset($data->givenname[0]) && isset($data->sn) && isset($data->sn[0]) ) {
        $return .= $data->givenname[0]." ".$data->sn[0];
      } elseif ( isset($data->cn) && isset($data->cn[0]) ) {
        $return .= $data->cn[0];
      }
      $return .= "' clear />";
      $return .= "</div>";

      
      $return .= "<div class='inline middle left'>";
        $return .= "<span class='lowercase bold inline middle' id='email_id'>$sentences->EMAIL</span>";
        if ( isset($sentences->EMAIL_HELP) ) $return .= "<span class='help' title='$sentences->EMAIL_HELP'>(?)</span>";
        $return .= "<span>:</span>";
      $return .= "</div>";
      $return .= "<div class='left mb1em'>";
      $return .= "<input poe type='text' name='email' id='email' lowercase class='lowercase' style='width: 30ch' value='";
      if ( isset($obj->trlu_email) && isset($obj->trlu_email[0]) ) $return .= $obj->trlu_email[0];
      $return .= "' clear />";
      $return .= "</div>";


      $return .= "<div class='inline middle left'>";
        $return .= "<span class='lowercase bold inline middle' id='password_id'>$sentences->PASSWORD</span>";
        if ( isset($sentences->VALID_PASSWORD) ) $return .= "<span class='help' title='$sentences->VALID_PASSWORD'>(?)</span>";
        $return .= "<span>:</span>";
      $return .= "</div>";
      $return .= "<div class='left mb1em'>";
      $return .= "<input poe style='width: 30ch' type='password' id='password' name='password' value='' clear />";
      $return .= "</div>";


      $return .= "<div class='inline middle left'>";
        $return .= "<span class='lowercase bold inline middle' id='confirm_password_id'>$sentences->CONFIRM_PASSWORD</span>";
        if ( isset($sentences->CONFIRM_PASSWORD_HELP) ) $return .= "<span class='help' title='$sentences->CONFIRM_PASSWORD_HELP'>(?)</span>";
        $return .= "<span>:</span>";
      $return .= "</div>";
      $return .= "<div class='left mb1em'>";
      $return .= "<input poe style='width: 30ch' type='password' id='confirm_password' name='confirm_password' value='' clear />";
      $return .= "</div>";

      $return .= "<div class='center'>";
        $return .= "<label class='bold mr1em iglow4 pointer lowercase'>$sentences->CREATE_ACCOUNT<input class='invisible' type='button' default value='create_account' onClick=\"send_ajax_form( this.form, this )\" /></label>";
      $return .= "</div>";
      $return .= "</div>";
      $return .= "<div class='message' id='create_account_message'></div>";
      $return .= "</form>";

      $url = c_page::getCloneFilter();
      unset($url->fa);
      $url = c_page::convert_object_to_url($url);
      $return .= "<a class='glow4' href='/$url'>$sentences->BACK_TO_PREVIOUS_QUESTION</a>";

      $return .= "</div></div>";
      return $return;
    }


    return $return;
  }
  public static function process_ajax ( ) {
    $page = c_page::get_instance();
    $data =  new static;
    $filter = c_page::getFilter();    
    $sentences = sc_sentences::get_sentence_sequency("LINK_ACCOUNT");

    if ( !c_user::verify_block($_SERVER['REMOTE_ADDR']) ) {
      $page->page->xml = array("element"=>array(array("name"=>"link_message","value"=>c_sentences::get_sentence("NETWORK_BLOCKED"))));
      return;
    }
    $token = hash("sha256",sc_user::getGlobalID().$filter->token);
    $query = "SELECT * FROM tb_relation_ldap_user trlu JOIN tb_ldap tl ON trlu.trlu_ldap_id=tl.ldap_id WHERE trlu_token='".c_sql::escape_string($token)."'";
    if ( !($obj = c_sql::get_first_object($query)) ) {
      c_user::insert_block($_SERVER['REMOTE_ADDR']);
      $page->page->xml = array("element"=>array(array("name"=>"link_message","value"=>c_sentences::get_sentence("INVALID_TOKEN"))));
      return;
    }

    if ( isset($_POST) && !empty($_POST) ) {
      if ( isset($_POST['f_name']) ) {
        switch($_POST['f_name']) {
          case "link":
            if ( !($result = sc_ldap::link_to_existent_account( $filter->token, $_POST['login'], $_POST['password'], $filter->kc )) ) {
              $page->page->xml = array("loadpage"=>"");
            } else {
              $page->page->xml = array("element"=>array(array("name"=>"link_message","value"=>c_sentences::get_sentence(str_replace("LDAP","USER_NOT_FOUND",$result)))));
            }
            break;
          case "create_account":
//            print_r($_POST);
            $messages = array();
            $data = new sc_accounts();
            foreach($_POST as $index => $value) {
              if ( isset($data->{trim(c_sentences::strtolower($index))}) ) $data->{trim(c_sentences::strtolower($index))} = c_sql::escape_string(trim($value));
            }
            if ( !($result = sc_accounts::create_account( $data )) ) {
//              if ( !($result = c_user::login( $data->username, $data->password, $filter->kc )) ) {
              if ( !($result = sc_ldap::link_to_existent_account( $filter->token, $data->username, $data->password, $filter->kc )) ) {
                $page->page->xml = array("loadpage"=>"");
              } else {
                $page->page->xml = array("element"=>array(array("name"=>"create_account_message","value"=>$result)));
              }
            } else {
              $page->page->xml = array("element"=>array(array("name"=>"create_account_message","value"=>$result)));
            }
            break;
          default:
            break;
        }
      }
    }
  }
}
?>
