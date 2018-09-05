<?php
class sc_user {
  private static $instance;

  public static function get_instance() {
    if ( !is_object(self::$instance) ) {
      self::$instance = new self( );
    }
    return self::$instance;
  }

  public static function generate_hashed_login( $user ) {
    return hash("whirlpool","--><".$user."---&");
  }
  public static function generate_hashed_password( $password ) {
    return hash("whirlpool","--><".$password."-@-&");
  }

  public static function has_permission( $permission ) {
    $user = self::get_instance();
    if ( in_array($permission,$user->permissions) ) return true;
    if ( in_array($permission,$user->menu) ) return true;
    return false; 
  }


  public static function getID() {
    $user = self::get_instance();
    return $user->get_user_id();
  }
  public static function getGlobalID() {
    $user = self::get_instance();
    return $user->get_global_id();
  }
  public static function getUsername() {
    $user = self::get_instance();
    return $user->user->nickname;
  }
  public static function getUserAttributes() {
    $user = self::get_instance();
    return $user->attributes;
  }



  private $auth;
  private $permissions;
  private $user;
  private $attributes;
  private $logged;
  private $menu;

  function __construct( $config = null ) {


    $this->logged = false;
    $this->menu = array();
    $this->auth = new STDClass();
    $this->user = new STDClass();
    $this->attributes = new STDClass();
    $this->auth->uid = isset($_COOKIE['uid']) && !empty($_COOKIE['uid']) ? trim($_COOKIE['uid']) : false;
    $this->auth->aid = isset($_COOKIE['aid']) && !empty($_COOKIE['aid']) ? hash("whirlpool",trim($_COOKIE['aid'])) : false;
    $this->auth->gid = isset($_COOKIE['gid']) && !empty($_COOKIE['gid']) ? hash("whirlpool",trim($_COOKIE['gid'])) : false;
    $this->permissions = array();

    // verify if already have the global_id, otherwise create one
    if ( !$this->verify_global_id() ) die("UNABLE TO PROCESS THE REQUEST!");

    // verify if user has valid authentication information
    if ( $this->authenticate_user( ) ) {
      return;
    }
    
    // user has not been authenticate, get guest permissions
    $this->user = false;

    $query = "SELECT group_permissions_internal, group_permissions_trusted, group_permissions_external FROM v_groups WHERE group_alias='GUEST' LIMIT 1";
    $obj = c_sql::get_first_object($query);

    $this->permissions = array();
    if ( sc_page::is_network_internal() ) {
      $this->permissions = array_merge($this->permissions,json_decode($obj->group_permissions_internal));
    }
    if ( sc_page::is_network_trusted() ) {
      $this->permissions = array_merge($this->permissions,json_decode($obj->group_permissions_trusted));
    }
    if ( sc_page::is_network_external() ) {
      $this->permissions = array_merge($this->permissions,json_decode($obj->group_permissions_external));
    }
    $this->permissions = array_values(array_unique($this->permissions));


    $this->attributes->items_per_page = 100;
    
  }

  public function add_menu_permission( $menu ) {
    if ( in_array($menu,$this->menu) ) return true;
    $this->menu[] = $menu;
  }

  private function verify_global_id() {
    if ( empty($this->auth->gid) ) {
      return self::create_global_id();
    }
    $query = "SELECT global_id, global_info FROM v_global_ids WHERE global_hash='".c_sql::escape_string($this->auth->gid)."'";
    if ( !($item = c_sql::get_first_object($query)) ) {
      return self::create_global_id();
    }

    if ( isset($_SERVER['REMOTE_ADDR']) ) $remote_addr = trim($_SERVER['REMOTE_ADDR']); else $remote_addr = "";
    if ( isset($_SERVER['HTTP_USER_AGENT']) ) $user_agent = trim($_SERVER['HTTP_USER_AGENT']); else $user_agent = "";

 
    $this->auth->gid = $item->global_id;
    $item->global_info = json_decode($item->global_info);
   // if ( (trim($item->global_info->ipaddress) != trim($remote_addr)) || (trim($item->global_info->useragent) != trim($user_agent)) ) {
    if ( (trim($item->global_info->useragent) != trim($user_agent)) ) {
      if ( (trim($item->global_info->ipaddress) != trim($remote_addr)) ) {
        echo $item->global_info->ipaddress ." => ".$remote_addr; 
        die("3a");
      } else die("3b");
      $query = "INSERT INTO tb_global_id_ipaddress ( global_id_ipaddress, global_id_ipaddress_global_id, global_id_ipaddress_user_agent ) VALUES ".
               "( '".c_sql::escape_string($remote_addr)."', '".$item->global_id."', '".c_sql::escape_string($user_agent)."' ) RETURNING global_id_ipaddress_id";
      $result = c_sql::insert($query);
      if ( empty($result) ) return false;
    } else {
      $query = "UPDATE tb_global_id_ipaddress SET global_id_ipaddress_lastseen=NOW() WHERE global_id_ipaddress_global_id=$item->global_id AND global_id_ipaddress_id=".$item->global_info->ipaddress_id."";
      c_sql::select($query);
    }
    return true;
  }

  private function create_global_id() {
    if ( !empty($this->auth->guest) ) return;
    //protection for direct accessing page contents (and h2 preload from generating cookies).
    if ( substr($_SERVER['REQUEST_URI'],strlen($_SERVER['REQUEST_URI'])-4,1) == "." || substr($_SERVER['REQUEST_URI'],strlen($_SERVER['REQUEST_URI'])-3,1) == ".") {
      sleep(1);
      header( "Location: https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] );
      die();
    }
    do {
      $gid = hash("sha256",time().rand(0,20));
      $hashed_gid = hash("whirlpool",$gid);
      $query = "SELECT * from tb_global_ids WHERE global_hash='".c_sql::escape_string($hashed_gid)."'";
    } while ( c_sql::get_first_object($query) );

    c_sql::start_transaction();
    $query = "INSERT INTO tb_global_ids ( global_hash ) VALUES ".
             " ( '".c_sql::escape_string($hashed_gid)."' ) RETURNING global_id";
    $result = c_sql::insert($query);
    if ( empty($result) ) {
      c_sql::rollback();
      return false;
    }
    if ( isset($_SERVER['REMOTE_ADDR']) ) $remote_addr = trim($_SERVER['REMOTE_ADDR']); else $remote_addr = "";
    if ( isset($_SERVER['HTTP_USER_AGENT']) ) $user_agent = trim($_SERVER['HTTP_USER_AGENT']); else $user_agent = "";
    $this->auth->gid = $result->global_id;
    $query = "INSERT INTO tb_global_id_ipaddress ( global_id_ipaddress, global_id_ipaddress_global_id, global_id_ipaddress_user_agent ) VALUES ".
             "( '".c_sql::escape_string($remote_addr)."', '".$result->global_id."', '".c_sql::escape_string($user_agent)."' ) RETURNING global_id_ipaddress_id";
    $result = c_sql::insert($query);
    if ( empty($result) ) {
      c_sql::rollback();
      return false;
    }
    c_sql::commit();
    $time = time()+5*365*24*60*60;
    if ( isset($_SERVER['SERVER_NAME']) ) {
      setcookie("gid", $gid, $time, "/", $_SERVER['SERVER_NAME'], false, false);
    }
    return true;
  }

  private function authenticate_user( ) {


    if ( empty($this->auth->uid) || empty($this->auth->aid) ) return false;
    if ( isset($_SERVER['REMOTE_ADDR']) && !self::verify_block($_SERVER['REMOTE_ADDR']) ) { 
      $time = time()-3600;
      setcookie("aid", 0, $time, "/", $_SERVER['SERVER_NAME'], false, false);
      setcookie("uid", 0, $time, "/", $_SERVER['SERVER_NAME'], false, false);
      return false;
    }
    $query = "SELECT * FROM v_users WHERE user_hash='".c_sql::escape_string($this->auth->uid)."' AND user_auth @> '{".c_sql::escape_string($this->auth->aid)."}' LIMIT 1";
    if ( !($item = c_sql::get_first_object($query)) ) {
      $time = time()-3600;
      setcookie("aid", 0, $time, "/", $_SERVER['SERVER_NAME'], false, false);
      setcookie("uid", 0, $time, "/", $_SERVER['SERVER_NAME'], false, false);
      if ( isset($_SERVER['REMOTE_ADDR']) ) self::insert_block($_SERVER['REMOTE_ADDR']);
      return false;
    }
    $query = "SELECT * FROM tb_user_auth WHERE user_auth_hash='".c_sql::escape_string($this->auth->aid)."' AND user_auth_global_id=".$this->auth->gid."";
    if ( !c_sql::get_first_object($query) ) {
      $time = time()-3600;
      setcookie("aid", 0, $time, "/", $_SERVER['SERVER_NAME'], false, false);
      setcookie("uid", 0, $time, "/", $_SERVER['SERVER_NAME'], false, false);
      if ( isset($_SERVER['REMOTE_ADDR']) ) self::insert_block($_SERVER['REMOTE_ADDR']);
      return false;
    }

    $this->attributes = json_decode('{' . str_replace('"=>"', '":"', $item->user_attributes) . '}');
    $language = json_decode($item->user_language);
    $this->attributes->language = $language->codeset;
    $this->logged = true;

    $config = sc_page::getConfig();



    $this->permissions = array();
    if ( sc_page::is_network_internal() ) {
      $this->permissions = array_merge($this->permissions,json_decode($item->user_permissions_internal));
    }
    if ( sc_page::is_network_trusted() ) {
      $this->permissions = array_merge($this->permissions,json_decode($item->user_permissions_trusted));
    }
    if ( sc_page::is_network_external() ) {
      $this->permissions = array_merge($this->permissions,json_decode($item->user_permissions_external));
    }

    $this->permissions = array_values(array_unique($this->permissions));

    $this->user->user_id = $item->user_id;
    $this->user->nickname = $item->user_nickname;
    $this->user->primary_email = $item->user_primaryemail;
    return true;
  }

  public function create_account( $username, $email ) {
    if ( isset($_SERVER['REMOTE_ADDR']) && !self::verify_block($_SERVER['REMOTE_ADDR']) ) {
      if ( isset($_SERVER['REMOTE_ADDR']) ) self::insert_block($_SERVER['REMOTE_ADDR']);
      return 'NETWORK_BLOCKED';
    }
    if ( empty($username) || empty($email) ) return "MISSING_INFORMATION";

    //validate username length
    if ( strlen($username) <= 5 || strlen($username) > 16 ) return "INVALID_USERNAME";

    //validate username against invalid characters
    $test = preg_replace("/\W/","",$username);
    if ( $test !== $username ) return "INVALID_USERNAME";
    unset($test);
  

    $query = "SELECT * FROM tb_users WHERE user_nickname=lower('".c_sql::escape_string($username)."') LIMIT 1";
    if ( c_sql::get_first_object($query) ) return 'USERNAME_ALREADY_TAKEN';
    if ( !sc_email::is_valid_email($email) ) return sc_sentences::get_sentence("ERROR_INVALID_EMAIL");

  
    //create unique hash for user
    do {
      $user_hash = hash("sha256",time().rand(0,20).$username);
      $query = "SELECT * from tb_users WHERE user_hash='".c_sql::escape_string($user_hash)."'";
    } while ( c_sql::get_first_object($query) );

    $password = self::create_password(); 
    $hashed_password = self::generate_hashed_password(hash("sha256",$password));
    $hashed_username = self::generate_hashed_login(hash("sha256",$username));
    $hashed_email = self::generate_hashed_login(hash("sha256",$email));


    $language_id = $language = c_page::get_language();

    //commit user
    c_sql::start_transaction();
    $query = "INSERT INTO tb_users ( user_nickname, user_nickname_hashed, user_primaryemail, user_primaryemail_hashed, user_hash, user_language ) VALUES ".
             "( '".c_sql::escape_string($username)."', '".c_sql::escape_string($hashed_username)."', '".c_sql::escape_string($email)."', '".c_sql::escape_string($hashed_email)."', '".c_sql::escape_string($user_hash)."', (SELECT language_id FROM tb_languages WHERE language_codeset=".c_sql::escape_string($language_id).") ) RETURNING user_id";
    if ( !($insert = c_sql::insert($query)) ) {
      c_sql::rollback();
      return "INTERNAL_ERROR";
    }
    $user_id = $insert->user_id;
    unset($insert);


    //add password
    $query = "INSERT INTO tb_user_passwords ( user_password_user_id, user_password_hash ) VALUES ".
             "( $user_id, '".c_sql::escape_string($hashed_password)."') RETURNING user_password_id";
    if ( !($insert = c_sql::insert($query)) ) {
      c_sql::rollback();
      return "INTERNAL_ERROR";
    }
    unset($insert);

    //set user on group user
    $query = "INSERT INTO tb_relation_user_group ( relation_user_group_user, relation_user_group_group ) VALUES ".
             "( $user_id, (SELECT group_id FROM tb_groups WHERE group_alias='USERS') ) RETURNING relation_user_group_group";
    if ( !($insert = c_sql::insert($query)) ) {
      c_sql::rollback();
      return "INTERNAL_ERROR";
    }
    unset($insert);

    //set user state to confirm email
    $query = "INSERT INTO tb_relation_user_state ( relation_user_state_user, relation_user_state_state ) VALUES ".
             "( $user_id, (SELECT user_state_id FROM tb_user_states WHERE user_state_alias='CONFIRM_EMAIL') ) RETURNING relation_user_state_hash";
    if ( !($insert = c_sql::insert($query)) ) {
      c_sql::rollback();
      return "INTERNAL_ERROR";
    }
    unset($insert);

    c_sql::commit();
    self::send_account( $email, $password, $username );
    self::login( hash("sha256",$username), hash("sha256",$password), false );    
    return false;

  }


  public function has_complexity( $in ) {

    $hl = false;
    $hu = false;
    $hn = false;

    $lowercaseletters = "abcdefghijklmnopqrstuvwxyz";
    $uppercaseletters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $numbers = "0123456789";
    
    for( $j=0; isset($in[$j]); $j++ ) {
      for( $i=0; isset($lowercaseletters[$i]); $i++ ) {
        if ( $in[$j] == $lowercaseletters[$i] ) {
          $hl = true;
          break;
        }
      }
      for( $i=0; isset($uppercaseletters[$i]); $i++ ) {
        if ( $in[$j] == $uppercaseletters[$i] ) {
          $hu = true;
          break;
        }
      }
      for( $i=0; isset($numbers[$i]); $i++ ) {
        if ( $in[$j] == $numbers[$i] ) {
          $hn = true;
          break;
        }
      }
    }
    if ( $hu && $hl && $hn ) return true;
    return false;

  }

  public function create_password( $length = 12 ) {
    $password = "";
    $lowercaseletters = "abcdefghijklmnopqrstuvwxyz";
    $uppercaseletters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $numbers = "0123456789";
    $special = "!@#$*";
    $options[] = $lowercaseletters;
    $options[] = $uppercaseletters;
    $options[] = $numbers;
    $options[] = $special;
    do {
      $i = rand(0,Sizeof($options)-1);
      $password .= $options[$i][rand(0,strlen($options[$i])-1)];
    } while ( strlen($password) < $length );
    return $password;
  }

  public static function send_account( $dest_email, $password, $username ) {
    $page = c_page::get_instance();
    $config = $page->get_config();
    $sentences = c_sentences::get_sentence_sequency("SEND_ACCOUNT");
    $text = str_replace("[[-URL-]]",$config->url,str_replace("[[-PASSWORD-]]","<span style='font-weight: bold'>$password</span>",str_replace("[[-USERNAME-]]","<span style='font-weight: bold'>$username</span>",$sentences->YOUR_ACCOUNT_BODY)));
    $email = new sc_email();
    $email->set_from($config->notification_email);
    $email->add_to( "", "$dest_email" );
    $email->set_subject(str_replace("[[-TITLE-]]",$config->title,$sentences->YOUR_ACCOUNT));
    $email->set_message(nl2br($text));
    $email->set_language(c_page::get_language());
    $email->queue_email();
    return true;
  }

  public static function send_password( $dest_email, $password, $language = false ) {
    $page = c_page::get_instance();
    $config = $page->get_config();
    if ( empty($language) ) $language = c_page::get_language();
    $sentences = c_sentences::get_sentence_sequency("SEND_PASSWORD", false, $language);
    $text = str_replace("[[-URL-]]",$config->url,str_replace("[[-PASSWORD-]]","<span style='font-weight: bold'>$password</span>",$sentences->YOUR_PASSWORD_BODY));
    $email = new sc_email();
    $email->set_from($config->notification_email);
    $email->add_to( "", "$dest_email" );
    $email->set_subject(str_replace("[[-TITLE-]]",$config->title,$sentences->YOUR_PASSWORD));
    $email->set_message(nl2br($text));
    $email->set_language($language);
    $email->queue_email();
    return true;
  }


  public function insert_block( $address = "" ) {
    if ( empty($address) && isset($_SERVER['REMOTE_ADDR']) ) $address = $_SERVER['REMOTE_ADDR'];
    else if ( empty($address) ) return false;
    $query = "SELECT insert_block('".c_sql::escape_string($address)."')";
    c_sql::select($query);
    return true;
  }

  public function verify_block( $address = "" ) {
    if ( empty($address) && isset($_SERVER['REMOTE_ADDR']) ) $address = $_SERVER['REMOTE_ADDR'];
    else if ( empty($address) ) return false;
    $query = "SELECT * FROM tb_blocks WHERE block_ipaddress='".c_sql::escape_string($address)."' AND block_time > NOW()";
    $count = c_sql::count_all($query);
    if ( $count > 5 ) return false;
    return true;
  }

  public function recover_password( $login ) {
    if ( empty($login) ) return "INVALID_LOGIN";
    if ( isset($_SERVER['REMOTE_ADDR']) && !self::verify_block($_SERVER['REMOTE_ADDR']) ) {
      if ( isset($_SERVER['REMOTE_ADDR']) ) self::insert_block($_SERVER['REMOTE_ADDR']);
      return 'NETWORK_BLOCKED';
    }
    $login = c_sql::escape_string(self::generate_hashed_login(trim($login)));
    $query = "SELECT user_id,uid,user_recovery_email,user_language FROM v_user_password_recover WHERE (user_nickname='$login' OR user_primaryemail='$login') LIMIT 1";
    if ( !($item = c_sql::get_first_object($query)) ) {
      if ( isset($_SERVER['REMOTE_ADDR']) ) self::insert_block($_SERVER['REMOTE_ADDR']);
      return 'USER_NOT_FOUND';
    }
    $password = self::create_password();
    $hashed_password = self::generate_hashed_password(hash("sha256",$password));

    //add password
    $query = "INSERT INTO tb_user_passwords ( user_password_user_id, user_password_hash ) VALUES ".
             "( $item->user_id, '".c_sql::escape_string($hashed_password)."') RETURNING user_password_id";
    if ( !($insert = c_sql::insert($query)) ) {
      return "INTERNAL_ERROR";
    }
    unset($insert);
    self::send_password( $item->user_recovery_email, $password, $item->user_language );
    return "NEW_PASSWORD_HAS_BEEN_SENT";
  }
  public function login( $login, $password, $keep_connected = false ) {

    if ( empty($login) || empty($password) ) return "INVALID_LOGIN";
    if ( isset($_SERVER['REMOTE_ADDR']) && !self::verify_block($_SERVER['REMOTE_ADDR']) ) {
      if ( isset($_SERVER['REMOTE_ADDR']) ) self::insert_block($_SERVER['REMOTE_ADDR']);
      return 'NETWORK_BLOCKED';
    }

    $query = "SELECT * FROM tb_ldap";
    if ( ($ldaps = c_sql::select($query)) && c_sql::num_rows($ldaps) > 0 ) {
      $plain_login = c_sentences::strtolower(trim($login));
      $plain_password = trim($password);
      $login = hash("sha256",$login);
      $password = hash("sha256",$password);
    }

    $login = c_sql::escape_string(self::generate_hashed_login(trim($login)));
    $password = c_sql::escape_string(self::generate_hashed_password(trim($password)));
    $query = "SELECT user_id,uid FROM v_user_passwords WHERE (user_nickname='$login' OR user_primaryemail='$login') AND (user_password='$password') LIMIT 1";
    if ( !($item = c_sql::get_first_object($query)) ) {
      if ( c_sql::num_rows($ldaps) > 0 ) return "LDAP";
      if ( isset($_SERVER['REMOTE_ADDR']) ) self::insert_block($_SERVER['REMOTE_ADDR']);
      return 'USER_NOT_FOUND';
    }
    do {
      $aid = hash("sha512",time().rand(0,1000));
      $hashed_aid = hash("whirlpool",$aid);
      $query = "SELECT user_auth_id FROM tb_user_auth WHERE user_auth_hash='".$hashed_aid."' LIMIT 1";
    } while ( c_sql::get_first_object($query));

    $query = "INSERT INTO tb_user_auth ( user_auth_user_id, user_auth_hash, user_auth_global_id ) VALUES ".
             "( $item->user_id, '$hashed_aid', '".c_page::get_global_id()."' )";
    c_sql::insert($query);
    unset($hashed_aid);

    if ( $keep_connected ) {
      $time = time()+365*24*60*60;
    } else {
      $time = 0;
    }
    setcookie("aid", $aid, $time, "/", $_SERVER['SERVER_NAME'], false, false);
    setcookie("uid", $item->uid, $time, "/", $_SERVER['SERVER_NAME'], false, false);
    $user = c_user::get_instance();
    $user->user = new STDClass();
    $user->user->user_id = $item->user_id;
    return false; 
  }
  public static function create_user_cookies( $user_id, $keep_connected = false ) {
    $query = "SELECT user_id,uid FROM v_user_passwords WHERE (user_id='".c_sql::escape_string($user_id)."') LIMIT 1";
    if ( !($item = c_sql::get_first_object($query)) ) return false;
    do {
      $aid = hash("sha512",time().rand(0,1000));
      $hashed_aid = hash("whirlpool",$aid);
      $query = "SELECT user_auth_id FROM tb_user_auth WHERE user_auth_hash='".$hashed_aid."' LIMIT 1";
    } while ( c_sql::get_first_object($query));
    $query = "INSERT INTO tb_user_auth ( user_auth_user_id, user_auth_hash, user_auth_global_id ) VALUES ".
             "( $item->user_id, '$hashed_aid', '".c_page::get_global_id()."' )";
    c_sql::insert($query);
    unset($hashed_aid);

    if ( $keep_connected ) {
      $time = time()+365*24*60*60;
    } else {
      $time = 0;
    }
    setcookie("aid", $aid, $time, "/", $_SERVER['SERVER_NAME'], false, false);
    setcookie("uid", $item->uid, $time, "/", $_SERVER['SERVER_NAME'], false, false);
    return true;
  }

  public function change_password( $current_password, $new_password, $confirm_new_password ) {
    if ( empty($current_password) ) return "PROVIDE_YOUR_CURRENT_PASSWORD";
    if ( empty($new_password) ) return "PROVIDE_YOUR_NEW_PASSWORD";
    if ( strlen($new_password) < 8 ) return "PASSWORD_LENGTH_ERROR";
    if ( !self::has_complexity($new_password) ) return "PASSWORD_COMPLEXITY";
    if ( empty($confirm_new_password) || ($new_password!=$confirm_new_password) ) return "NEW_AND_CONFIRM_PASSWORD_MUST_MATCH";

    $hashed_password = self::generate_hashed_password( $current_password );
    $hashed_new_password = self::generate_hashed_password( hash("sha256",$new_password) );

    if ( $hashed_password == $hashed_new_password ) return "NEW_PASSWORD_MUST_BE_DIFFERENT";

    $user = self::get_instance();
    $user_id = $user->get_user_id();

    $query = "SELECT * FROM v_user_passwords WHERE user_id=".$user_id." AND user_password='".c_sql::escape_string($hashed_password)."' LIMIT 1";
    if ( !c_sql::get_first_object($query) ) {
      return "INCORRECT_CURRENT_PASSWORD";
    }
    $query = "INSERT INTO tb_user_passwords ( user_password_user_id, user_password_hash ) VALUES ".
             "( $user_id, '".c_sql::escape_string($hashed_new_password)."') RETURNING user_password_id";
    if ( !($insert = c_sql::insert($query)) ) {
      return "INTERNAL_ERROR";
    }
    unset($insert);
    return false;
  }

  public function logout() {
    if ( empty($this->auth->uid) || empty($this->auth->aid) ) return false;
    $query = "SELECT * FROM v_users WHERE user_hash='".c_sql::escape_string($this->auth->uid)."' AND user_auth @> '{".c_sql::escape_string($this->auth->aid)."}' LIMIT 1";
    if ( ($item = c_sql::get_first_object($query)) ) {
      $query = "UPDATE tb_user_auth SET user_auth_active=false WHERE user_auth_user_id='$item->user_id' AND user_auth_hash='".c_sql::escape_string($this->auth->aid)."'";
      c_sql::select($query);
//      $query = "DELETE FROM tb_user_auth WHERE user_auth_user_id='$item->user_id' AND user_auth_hash='".c_sql::escape_string($this->auth->aid)."'";
//      c_sql::select($query);
    }
    $time = time()-3600;
    setcookie("aid", 0, $time, "/", $_SERVER['SERVER_NAME'], false, false);
    setcookie("uid", 0, $time, "/", $_SERVER['SERVER_NAME'], false, false);
  }

  public function get_global_id() {
    if ( empty($this->auth) ) return false;
    return $this->auth->gid;
  }
  public function get_user_id() {
    if ( empty($this->user) ) return false;
    return $this->user->user_id;
  }
  public function get_user_info() {
    if ( empty($this->user) ) return false;
    return $this->user;
  }


  public function get_permissions() {
    return $this->permissions;
  }
  public static function get_attributes() {
    $user = self::get_instance();
    return $user->attributes;
  }

  public function is_logged() {
    return $this->logged;
  }
}
?>
