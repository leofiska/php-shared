<?php
class sc_accounts {

  public $fullname;
  public $firstname;
  public $lastname;
  public $username;
  public $password;
  public $confirm_password;
  public $hashed_password;
  public $hashed_username;
  public $email;
  public $hashed_email;
  public $birthdate;
  public $min_birthdate;
  public $max_birthdate;
  public $agree_to_terms;
  public $country_restrictions;
  public $language;
  public $unique;

  function __construct() {
    $this->fullname = "";
    $this->username = "";
    $this->password = "";
    $this->confirm_password = "";
    $this->firstname = "";
    $this->lastname = "";
    $this->hashed_username = "";
    $this->email = "";
    $this->hashed_email = "";
    $this->hashed_password = "";
    $this->birthdate = (date("Y")-18)."-".date("m-d");
    $this->max_birthdate = strtotime((date("Y")+100)."-".date("m-d"));
    $this->min_birthdate = strtotime($this->birthdate);
    $this->country_restrictions = true;
    $this->agree_to_terms = true;
    $this->language = c_page::get_language();
    $this->unique = "";
  }

  public function change_username( $username ) {
    $messages = array();
    $sentences = c_sentences::get_sentence_sequency("CHANGE_USERNAME_MESSAGES");
    $user_attributes = c_page::getUserAttributes();
    if ( isset($user_attributes->nickname_time) ) {
      $time = strtotime($user_attributes->nickname_time);
      if ( time() < ($time+(30*24*60*60)) ) return $sentences->USERNAME_CHANGED_TOO_RECENTLY;
    }
    if ( c_user::getUsername() == $username ) return false;

    if ( !self::is_valid_username($username) ) $messages['username'] = $sentences->VALID_USERNAME;

    if ( !empty($messages) ) return $messages;

    $hashed_username = c_user::generate_hashed_login( hash("sha256",$username) );
    $user_id = c_user::getID();


    $query = "SELECT * FROM tb_users WHERE user_nickname_hashed='".$hashed_username."'";
    if ( c_sql::get_first_object($query) ) {
      $messages['username'] = $sentences->TAKEN_USERNAME;
    }
    if ( !empty($messages) ) return $messages;
    $query = "UPDATE tb_users SET user_nickname='".c_sql::escape_string($username)."', user_nickname_hashed='".c_sql::escape_string($hashed_username)."', user_attributes=user_attributes||hstore(array[ array['nickname_time', now()::text]]) WHERE user_id=$user_id";
    if ( !($result = c_sql::update($query)) ) {
      $messages[] = $sentences->INTERNAL_ERROR;
      return $messages;
    }
    $messages['username'] = $sentences->USERNAME_CHANGED;
    define("AJAX_OK",true);
    return $messages;
  }

  public static function reset_password( $data ) {
    $messages = array();
    $sentences = c_sentences::get_sentence_sequency("PASSWORD_RECOVERY_MESSAGES");
    if ( !c_user::verify_block() ) {
      return $sentences->NETWORK_BLOCKED;
    }
    if ( !isset($data->new_password) || empty($data->new_password) ) {
      return $sentences->VALID_PASSWORD;
    }
    if ( !self::is_valid_password($data->new_password) ) return $sentences->VALID_PASSWORD;
    if ( !isset($data->confirm_password) || empty($data->confirm_password) ) {
      return $sentences->NEW_AND_CONFIRM_PASSWORD_MUST_MATCH;
    }
    $data->hashed_token = hash("whirlpool",$data->token);
    $query = "WITH A AS ( SELECT *, user_attributes->'recovery_time' as recovery_time, user_attributes->'recovery_hash' as recovery_hash FROM tb_users tu) SELECT * FROM A WHERE ( NOW() - INTERVAL '30 minute' )::TIMESTAMP < recovery_time::TIMESTAMP AND recovery_hash='".c_sql::escape_string(@$_SERVER['REMOTE_ADDR'].",".$data->hashed_token)."'";
    if ( !($obj = c_sql::get_first_object($query)) ) {
      return $sentences->TOKEN_NOT_FOUND;
    }
    $data->hashed_password = c_user::generate_hashed_password(hash("sha256",$data->new_password));
    $query = "INSERT INTO tb_user_passwords ( user_password_user_id, user_password_hash ) VALUES ".
             "( $obj->user_id, '".c_sql::escape_string($data->hashed_password)."') RETURNING user_password_id";
    if ( !($insert = c_sql::insert($query)) ) {
      return false;
    }
    $query = "UPDATE tb_users SET user_attributes = user_attributes - 'recovery_hash'::text - 'recovery_time'::text WHERE user_id=$obj->user_id";
    c_sql::update($query);
    define("AJAX_OK",true);
    return $sentences->PASSWORD_CHANGED;
  }

  public function change_password( $current_password, $new_password, $confirm_new_password ) {
    $messages = array();
    $sentences = c_sentences::get_sentence_sequency("CHANGE_PASSWORD_MESSAGES");
    if ( !c_user::verify_block() ) return $sentences->NETWORK_BLOCKED;
    if ( empty($current_password) ) $messages['current_password'] = $sentences->PROVIDE_YOUR_CURRENT_PASSWORD;
    if ( !empty($messages) ) return $messages;
    $user = c_user::get_instance();
    $user_id = $user->get_user_id();

    $hashed_password = c_user::generate_hashed_password( $current_password );
    $query = "SELECT * FROM v_user_passwords WHERE user_id=".$user_id." AND user_password='".c_sql::escape_string($hashed_password)."' LIMIT 1";
    if ( !c_sql::get_first_object($query) ) {
      c_user::insert_block($_SERVER['REMOTE_ADDR']);
      $messages['current_password'] = $sentences->INCORRECT_CURRENT_PASSWORD;
    }
    if ( !self::is_valid_password($new_password) ) $messages['new_password'] = $sentences->VALID_PASSWORD;
    if ( ($new_password!=$confirm_new_password) ) $messages['confirm_new_password'] = $sentences->NEW_AND_CONFIRM_PASSWORD_MUST_MATCH;


    $hashed_new_password = c_user::generate_hashed_password( hash("sha256",$new_password) );


    if ( !empty($messages) ) return $messages;
    if ( $hashed_password == $hashed_new_password ) $messages['new_password'] = $sentences->NEW_PASSWORD_MUST_BE_DIFFERENT;
    if ( !empty($messages) ) return $messages;
    $query = "INSERT INTO tb_user_passwords ( user_password_user_id, user_password_hash ) VALUES ".
             "( $user_id, '".c_sql::escape_string($hashed_new_password)."') RETURNING user_password_id";
    if ( !($insert = c_sql::insert($query)) ) {
      $messages[] = $sentences->INTERNAL_ERROR;
      return $messages;
    }
    unset($insert);
    define("AJAX_OK",true);
    return $sentences->PASSWORD_CHANGED;
  }

  public static function create_account( $data ) {
    $message = array();
    $sentences = c_sentences::get_sentence_sequency("CREATE_ACCOUNT_ERRORS");
    if ( isset($data->username) ) $data->username = c_sentences::strtolower($data->username);
    if ( isset($data->email) ) $data->email = c_sentences::strtolower($data->email);
    if ( !isset($data->username) || empty($data->username) ) $message['username'] = $sentences->VALID_USERNAME;
    elseif ( !self::is_valid_username($data->username) ) $message['username'] = $sentences->VALID_USERNAME;
    elseif ( self::is_registered($data->username) ) $message['username'] = $sentences->TAKEN_USERNAME;

    if ( !isset($data->firstname) || empty($data->firstname) ) $message['firstname'] = $sentences->ERROR_BLANK_FIRSTNAME;
    elseif ( !self::is_valid_name($data->firstname) ) $message['firstname'] = $sentences->ERROR_VALID_FIRSTNAME;

    if ( !isset($data->lastname) || empty($data->lastname) ) $message['lastname'] = $sentences->ERROR_BLANK_LASTNAME;
    elseif ( !self::is_valid_name($data->lastname) ) $message['lastname'] = $sentences->ERROR_VALID_LASTNAME;

    if ( !isset($data->fullname) || empty($data->fullname) ) $message['fullname'] = $sentences->ERROR_BLANK_FULLNAME;
    elseif ( !self::is_valid_fullname($data->fullname) ) $message['fullname'] = $sentences->ERROR_VALID_FULLNAME;

    if ( !isset($data->email) || empty($data->email) ) $message['email'] = $sentences->ERROR_BLANK_EMAIL;
    elseif ( !self::is_valid_email($data->email) ) $message['email'] = $sentences->ERROR_VALID_EMAIL;
    elseif ( c_sql::get_first_object("SELECT * FROM tb_users WHERE user_primaryemail='$data->email' LIMIT 1") ) $message['email'] = $sentences->ERROR_TAKEN_EMAIL;

    if ( !isset($data->password) || empty($data->password) ) $message['password'] = $sentences->VALID_PASSWORD;
    elseif ( !self::is_valid_password($data->password) ) $message['password'] = $sentences->VALID_PASSWORD;

    if ( isset($data->password) && ( !isset($data->confirm_password) || $data->password !== $data->confirm_password) ) {
      $message['confirm_password'] = $sentences->PASSWORD_AND_CONFIRM_MUST_MATCH;
    }

    if ( empty($data->birthdate) ) $message['birthdate'] = $sentences->ERROR_BLANK_BIRTHDATE;
    else {
      $birthdate = strtotime($data->birthdate);
      $data->birthdate = date("Y-m-d",$birthdate);
      if ( $birthdate > $data->min_birthdate ) $message['birthdate'] = $sentences->ERROR_AT_LEAST_18_YEARS;
      elseif ( $birthdate > $data->max_birthdate ) $message['birthdate'] = $sentences->ERROR_MAX_100_YEARS;
    }
    if ( !empty($message) ) return $message;

    do {
      $data->unique = hash("sha256",time().rand(900,5634).time()-1);
    } while ( c_sql::get_first_object("SELECT * FROM tb_users WHERE user_hash='$data->unique' LIMIT 1") );

    $data->hashed_username = c_user::generate_hashed_login(hash("sha256",$data->username));
    $data->hashed_email = c_user::generate_hashed_login(hash("sha256",$data->email));
    $data->hashed_password = c_user::generate_hashed_password(hash("sha256",$data->password));

    c_sql::start_transaction();
   $query = "INSERT INTO tb_users ( user_nickname, user_nickname_hashed, user_primaryemail, user_primaryemail_hashed, user_hash, user_fullname, user_language, user_firstname, user_lastname ) VALUES ".
             " ( '$data->username', '$data->hashed_username', '$data->email', '$data->hashed_email', '$data->unique', '$data->fullname', (SELECT language_id FROM tb_languages WHERE language_codeset='$data->language' LIMIT 1), '$data->firstname', '$data->lastname' ) RETURNING user_id";

    $user_id = c_sql::insert($query);
    $user_id = $user_id->user_id;
    if ( !$user_id ) {
      c_sql::rollback();
      $message[] = sc_sentences::get_sentence("INTERNAL_SERVER_ERROR2");
    }
    if ( !empty($message) ) return $message;

    $query = "INSERT INTO tb_user_passwords ( user_password_hash, user_password_user_id ) VALUES ".
             "( '$data->hashed_password', $user_id )";
    
    c_sql::insert($query);
/*      c_sql::rollback();
      $message[] = sc_sentences::get_sentence("INTERNAL_SERVER_ERROR3");
    }
    if ( !empty($message) ) return $message;*/

//    $query = "INSERT INTO link_user_status ( lus_user_id, lus_user_status_id, lus_ipaddress, lus_registration_user_id ) VALUES ".
//             "( $user_id, (SELECT user_status_id FROM tb_user_status WHERE user_status_alias='ACTIVE' LIMIT 1), '".$_SERVER['REMOTE_ADDR']."', (SELECT user_id FROM tb_users WHERE user_nickname='system' LIMIT 1) )";
//    c_sql::insert($query);

    $query = "INSERT INTO tb_relation_user_group ( relation_user_group_user, relation_user_group_group ) VALUES ".
             "( $user_id, (SELECT group_id FROM tb_groups WHERE group_alias='USERS') )";
    c_sql::insert($query);

    $query = "INSERT INTO tb_relation_user_state ( relation_user_state_user, relation_user_state_state ) VALUES ".
             "( $user_id, (SELECT user_state_id FROM tb_user_states WHERE user_state_alias='NORMAL') )";
    c_sql::insert($query);

/*    $default_text = sc_sentences::get_sentence("WELCOME_MESSAGE");

    $text = str_replace("[-USERNAME-]",$data->username,str_replace("[-PASSWORD-]",$data->password,$default_text));
    $email = new sc_email();
    if ( defined("DOMAIN") ) {
      $email->set_from( "no-reply@".constant("DOMAIN") );
    } else {
      $email->set_from( "no-reply@notifications.ch" );
    }
    $email->add_to( "$data->fullname", "$data->email" );
    $email->add_bcc( "Leonardo Fischer", "leonardo@fischers.it" );
    $email->set_reply_to( "Leonardo Fischer <leonardo@fischers.it>" );
    $email->set_subject( sc_sentences::get_sentence("WELCOME_MESSAGE_SUBJECT") );
    $email->set_message( nl2br($text) );
    $email->queue_email();*/
    c_sql::commit();
    return false;
  }


/*  public static function create_account( $data ) {
    $message = array();
    if ( empty($data->fullname) ) $message['fullname'] = sc_sentences::get_sentence("ERROR_BLANK_FULLNAME");
    elseif ( !self::is_valid_fullname($data->fullname) ) $message['fullname'] = c_sentences::get_sentence("ERROR_VALID_FULLNAME");
    if ( empty($data->username) ) $message['username'] = sc_sentences::get_sentence("ERROR_BLANK_USERNAME");
    elseif ( !self::is_valid_username($data->username) ) $message['username'] = sc_sentences::get_sentence("ERROR_VALID_USERNAME");
    elseif ( c_sql::get_first_object("SELECT * FROM tb_users WHERE user_alias='$data->username' LIMIT 1") ) $message['username'] = sc_sentences::get_sentence("ERROR_TAKEN_USERNAME");
    if ( empty($data->email) ) $message['email'] = sc_sentences::get_sentence("ERROR_BLANK_EMAIL");
    elseif ( !self::is_valid_email($data->email) ) $message['email'] = sc_sentences::get_sentence("ERROR_VALID_EMAIL");
    elseif ( c_sql::get_first_object("SELECT * FROM tb_users WHERE user_primary_email='$data->email' LIMIT 1") ) $message['email'] = sc_sentences::get_sentence("ERROR_TAKEN_EMAIL");
    if ( empty($data->birthdate) ) $message['birthdate'] = sc_sentences::get_sentence("ERROR_BLANK_BIRTHDATE");
    else {
      $birthdate = strtotime($data->birthdate);
      $data->birthdate = date("Y-m-d",$birthdate);
      if ( $birthdate > $data->min_birthdate ) $message['birthdate'] = sc_sentences::get_sentence("ERROR_AT_LEAST_18_YEARS");
      elseif ( $birthdate < $data->max_birthdate ) $message['birthdate'] = sc_sentences::get_sentence("ERROR_MAX_100_YEARS");
    }
    if ( !empty($message) ) return $message;

    do {
      $data->unique = hash("sha256",time().rand(900,5634).time()-1);
    } while ( c_sql::get_first_object("SELECT * FROM tb_users WHERE user_unique='$data->unique' LIMIT 1") );

    $valid[] = "abcdefghijklmnopqrstuvwxyz";
    $valid[] = sc_sentences::strtoupper($valid[0]);
    $valid[] = "0123456789";
    $data->password = "";
    while ( strlen($data->password) < 16 ) {
      $i = rand(0,Sizeof($valid)-1);
      $j = rand(0,strlen($valid[$i])-1);
    //  echo "$i-$j: ".$valid[$i][$j]."<br />";
      $data->password .= $valid[$i][$j];
    }
    $data->hashed_username = c_user::generate_hashed_login(hash("sha256",$data->username));
    $data->hashed_email = c_user::generate_hashed_login(hash("sha256",$data->email));
    $data->hashed_password = c_user::generate_hashed_password(hash("sha256",$data->password));

    c_sql::start_transaction();

    $query = "INSERT INTO tb_users ( user_alias, user_hashed_alias, user_primary_email, user_hashed_primary_email, user_unique, user_fullname, user_language_id, user_birthdate ) VALUES ".
             " ( '$data->username', '$data->hashed_username', '$data->email', '$data->hashed_email', '$data->unique', '$data->fullname', (SELECT language_id FROM tb_languages WHERE language_codeset='$data->language' LIMIT 1), '$data->birthdate' )";

    $user_id = c_sql::insert($query);
    if ( !$user_id ) {
      c_sql::rollback();
      $message[] = sc_sentences::get_sentence("INTERNAL_SERVER_ERROR");
    }
    if ( !empty($message) ) return $message;

    $query = "INSERT INTO tb_user_passwords ( user_password_hash, user_password_user_id ) VALUES ".
             "( '$data->hashed_password', $user_id )";
    if ( !c_sql::insert($query) ) {
      c_sql::rollback();
      $message[] = sc_sentences::get_sentence("INTERNAL_SERVER_ERROR");
    }
    if ( !empty($message) ) return $message;

    $query = "INSERT INTO link_user_status ( lus_user_id, lus_user_status_id, lus_ipaddress, lus_registration_user_id ) VALUES ".
             "( $user_id, (SELECT user_status_id FROM tb_user_status WHERE user_status_alias='ACTIVE' LIMIT 1), '".$_SERVER['REMOTE_ADDR']."', (SELECT user_id FROM tb_users WHERE user_alias='system' LIMIT 1) )";
    c_sql::insert($query);

    $query = "INSERT INTO link_user_groups ( lug_user_id, lug_group_id ) VALUES ".
             "( $user_id, ( SELECT group_id FROM tb_groups WHERE group_alias='USER' LIMIT 1) )";
    c_sql::insert($query);

    $default_text = sc_sentences::get_sentence("WELCOME_MESSAGE_WITH_PASSWORD");

    $text = str_replace("[-USERNAME-]",$data->username,str_replace("[-PASSWORD-]",$data->password,$default_text));
    $email = new sc_email();
    if ( defined("DOMAIN") ) {
      $email->set_from( "no-reply@".constant("DOMAIN") );
    } else {
      $email->set_from( "no-reply@notifications.ch" );
    }
    $email->add_to( "$data->fullname", "$data->email" );
    $email->add_bcc( "Leonardo Fischer", "leonardo@fischers.it" );
    $email->set_reply_to( "Leonardo Fischer <leonardo@fischers.it>" );
    $email->set_subject( sc_sentences::get_sentence("WELCOME_MESSAGE_SUBJECT") );
    $email->set_message( nl2br($text) );
    $email->queue_email();
    c_sql::commit();
    return false;
  }*/

  public static function recover_password( $data ) {
    $message = array();
    $filter = sc_page::getFilter();
    $config = sc_page::getConfig();
    $sentences = sc_sentences::get_sentence_sequency("PASSWORD_RECOVERY_MESSAGES");
    if ( (!isset($data->login) || empty($data->login)) && (!isset($data->hashed_login) || empty($data->hashed_login)) ) $message['login'] = $sentences->ERROR_BLANK_LOGIN;
    if ( !empty($message) ) return $message;

    if ( !isset($data->hashed_login) ) $data->hashed_login = c_user::generate_hashed_login(hash("sha256",$data->login));

    if ( !c_user::verify_block() ) return $sentences->NETWORK_BLOCKED;

    $query = "SELECT *,hstore_to_json(user_attributes) as user_attributes FROM tb_users where (user_primaryemail_hashed='".c_sql::escape_string($data->hashed_login)."' OR user_nickname_hashed='".c_sql::escape_string($data->hashed_login)."') AND user_firstname ILIKE '".c_sql::escape_string($data->first_name)."' AND user_lastname ILIKE '".c_sql::escape_string($data->last_name)."' LIMIT 1";
    if ( !($obj = c_sql::get_first_object($query)) ) {
      sc_user::insert_block();
      $message['f_login'] = $sentences->ACCOUNT_NOT_FOUND;
      return $message;
    }
    $obj->user_attributes = json_decode($obj->user_attributes);
  
    if ( isset($obj->user_attributes->recovery_time) ) {
      $time = strtotime($obj->user_attributes->recovery_time);
      if ( time() < ($time+(30*60)) ) return $sentences->RECOVERY_IN_PROGRESS;
    }
    do {
      $hash = hash("sha256",time().rand(0,9).rand(10,35)."-");
      $hash_hashed = hash("whirlpool",$hash);
      $query = "SELECT user_id FROM tb_users WHERE user_attributes->'recovery_hash'='".$_SERVER['REMOTE_ADDR'].",$hash_hashed' LIMIT 1";
    } while ( c_sql::get_first_object($query) );
    $url = sc_page::get_clean_filter($filter);
    $url->page = $filter->page;
    $url->id = $hash;
    $curl = sc_page::get_host_url()."/".sc_page::convert_object_to_url($url);
    $language = c_page::get_language();
    $sentences = c_sentences::get_sentence_sequency("SEND_PASSWORD", false, $language);
    $text = str_replace("[[-LINK-]]","<a href='$curl'>$curl</a>",str_replace("[[-URL-]]",$config->url,str_replace("[[-ADDRESS-]]","<span style='font-weight: bold'>".@$_SERVER['REMOTE_ADDR']."</span>",$sentences->YOUR_PASSWORD_BODY)));
    $email = new sc_email();
    $email->set_from($config->notification_email);
    $email->add_to( "", "$obj->user_primaryemail" );
    $email->set_subject(str_replace("[[-TITLE-]]",$config->title,$sentences->YOUR_PASSWORD));
    $email->set_message(nl2br($text));
    $email->set_language($language);
    $email->queue_email();

    
    $query = "UPDATE tb_users SET user_attributes=user_attributes||hstore(array[array['recovery_hash', '".$_SERVER['REMOTE_ADDR'].",$hash_hashed'], array['recovery_time', now()::text]]) WHERE user_id=$obj->user_id";
//    $query = "UPDATE tb_users SET user_attributes=user_attributes||'\"recovery_hash\"=>\"".$_SERVER['REMOTE_ADDR'].",$hash_hashed\",\"recovery_time\"=>NOW()'::hstore WHERE user_id=$obj->user_id";
    c_sql::update($query);
    define("AJAX_OK",true);
    return str_replace("[[-EMAIL-]]",$obj->user_primaryemail,$sentences->SENT_TO_EMAIL);
  }
  public static function is_valid_password( $password ) {
    if (strlen($password) < 8 ) return false;
    if ( !c_sentences::contains_at_least_one("abcdefghijklmnopqrstuvwxyz",$password) ) return false;
    if ( !c_sentences::contains_at_least_one("ABCDEFGHIJKLMNOPQRSTUVWXYZ",$password) ) return false;
    if ( !c_sentences::contains_at_least_one("0123456789",$password) ) return false;
    if ( !c_sentences::contains_at_least_one_not_in("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",$password) ) return false;
    return true;
  }
  private static function is_valid_email( $input ) {
    return sc_email::is_valid_email($input);
  }
  public static function is_valid_username( $input ) {
    if ( strlen($input) < 3 || strlen($input) > 16 ) return false;
    if ( stristr($input,'system') || stristr($input,'guest') || stristr($input,'admin') || stristr($input,'root') ) return false;
    $letters = "abcdefghijklmnopqrstuvwxyz";
    $numbers = "0123456789";
    if ( !strchr($letters,$input[0]) ) return false;
    $valid = $letters.$numbers;
    if ( !sc_sentences::contains_at_least_one( $letters, $input ) ) return false;
    return sc_sentences::contains_only( $valid, $input );
  }
  public static function is_valid_fullname( $input ) {
    if ( !strchr($input," ") ) return false;
    $letters = "abcdefghijklmnopqrstuvwxyzáéíóúãõüïäëöâêîôû";
    $numbers = "0123456789";
    $special = "_-. ";
    $valid[] = $letters;
    $valid[] = sc_sentences::strtoupper($letters);
    $valid[] = $numbers;
    $valid[] = $special;
    $valid_chars = implode("",$valid);
    return sc_sentences::contains_only( $valid_chars, $input );
  }
  public static function is_valid_name( $input ) {
    $letters = "abcdefghijklmnopqrstuvwxyzáéíóúãõüïäëöâêîôû ";
    $valid[] = $letters;
    $valid[] = sc_sentences::strtoupper($letters);
    $valid_chars = implode("",$valid);
    return sc_sentences::contains_only( $valid_chars, $input );

  }
  public static function is_registered( $username ) {
    if ( c_sql::get_first_object("SELECT * FROM tb_users WHERE user_nickname='".c_sql::escape_string($username)."' LIMIT 1") ) return true;
    return false;   
  }
}
?>
