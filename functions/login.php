<?php
class sf_login {

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

  static function get_section () {
    $page = c_page::get_instance();
    $config = $page->get_config();

    $sentences = sc_sentences::get_sentence_sequency("LOGIN");

    $url = c_page::get_clean_filter();
    $url->page = "LOGIN";
    $url = c_page::convert_object_to_url($url);

    $return = "";

    $info = new STDClass();
    $query = "SELECT * FROM tb_ldap";
    if ( c_sql::get_first_object($query) ) $info->encrypt = "";
    else $info->encrypt = "crypted";
    $return .= "<form onsubmit='event.preventDefault();' action='/$url'>";
    $return .= "<div class='center'>";
    $return .= "<h2 class='mb05em'>$sentences->SIGNIN</h2>";
    $return .= "</div>";
    $external_domains = array();
    $help = $sentences->LOGIN_HELP;


    if ( !sc_page::is_network_trusted() && !sc_page::is_network_internal() ) {
        $help .= "\r\n\r\n".$sentences->NO_EXTERNAL_DOMAINS;
    } else {
      $query = "SELECT ldap_domain FROM tb_ldap ORDER BY ldap_domain ASC";
      $result = c_sql::select($query);
      while( ($row = c_sql::fetch_object($result)) ) {
        $external_domains[] = $row->ldap_domain;
      }
      if ( empty($external_domains) ) {
        $help .= "\r\n\r\n".$sentences->NO_EXTERNAL_DOMAINS;
      } else {
        $help .= "\r\n\r\n".$sentences->EXTERNAL_DOMAINS.": ".implode(", ",$external_domains);
      }
    }

    $return .= "<div class='inline middle'><span class='inline lowercase middle'>$sentences->LOGIN_ACCOUNT</span><span class='help' title='$help'>(?)</span>:</p></div>";
    $return .= "<div class='mb1em'>";
      $return .= "<input autocapitalize=off placeholder='$sentences->TYPE_HERE' lowercase type='text' $info->encrypt style='min-width: 17em;' poe name='login' id='f_login' autofocus class='lowercase'>";
    $return .= "</div>";
    $return .= "<p class='lowercase'>$sentences->PASSWORD:</p>";
    $return .= "<div class='mb05em'>";
      $return .= "<input placeholder='$sentences->TYPE_HERE' $info->encrypt type='password' style='min-width: 17em;' poe name='password'>";
    $return .= "</div>";
    $return .= "<div class='center'><div class='inline'>";
    $return .= "<div class='inline middle'>";
      $return .= "<span class='inline middle pr2px'><input class='pointer transparent middle' type='checkbox' id='keep_connected' poe name='keep_connected'><label for='keep_connected'></span>";
      $return .= "<label class='inline middle pointer' for='keep_connected'>$sentences->KEEP_CONNECTED</label>";
      if ( isset($sentences->KEEP_CONNECTED_HELP) ) $return .= "<span class='help' title='$sentences->KEEP_CONNECTED_HELP'>(?)</span>";
    $return .= "</div>";
    $return .= "<div class='center'>";
      $return .= "<label class='bold button iglow4 pointer lowercase'>$sentences->SIGNIN<input default type='button' onClick=\"send_ajax_form( this.form, this)\" class='invisible' value='login'></label>";
    $return .= "</div>";
    $return .= "<p id='login_message' class='message center mh6em'></p>";
    $return .= "<div class='center'><label class='button glow4 pointer'>$sentences->FORGOT_PASSWORD<input type='button' onClick=\"send_ajax_form( this.form, this)\" class='invisible' value='recover_password'></label></div>";
    $return .= "</form>";
    $return .= "</div>";
    $return .= "</div>";
    return $return;
  }
  static function process_ajax ( ) {

    $page = c_page::get_instance();
    $data =  new static;

    if ( isset($_POST) && !empty($_POST) ) {
      if ( !isset($_POST['f_name']) ) return;
      foreach( $_POST AS $index => $value ) {
        if ( isset($data->{$index}) ) $data->{$index} = trim($value);
      }
      switch(@$_POST['f_name']) {
        case "login_form":
          if ( !c_user::has_permission("LOGIN") ) break;
          $data = self::get_section();
          $page->page->xml = array("element"=>array(array("name"=>"login_popup","parent"=>array("display"=>"block"),"parent_offsetX"=>"6em","parent_offsetY"=>"4em","parent_fit"=>"BOTH","value"=>$data)),"focus"=>"f_login");
          
          break;
        case "login":
          if ( !c_user::has_permission("LOGIN") ) break;
          if ( !($result = c_user::login( $data->login, $data->password, $data->keep_connected )) ) {
            //$page->page->xml = array("loadpage"=>"");
            $page->page->xml = array("reload"=>true,"sleep"=>2000);
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
        default:
        case "recover_password":
          if ( !c_user::has_permission("LOGIN") ) break;
          $url = c_page::get_clean_filter();
          $url->page = "RECOVER_PASSWORD";
          $url = c_page::convert_object_to_url($url);
          $page->page->xml = array("loadpage"=>$url);
//          $result = c_user::recover_password( $data->login );
//          $page->page->xml = array("element"=>array(array("name"=>"login_message","value"=>c_sentences::get_sentence($result))));
          break;
      }
    }

  }
}
?>
