<?php
class sc_ldap {

  private static $attributes = array( 'mail','telephonenumber','givenname','sn','distinguishedname', 'cn', 'description' );

  public static function create_incomplete_link_reference( $ldap_id, $login, $password, $data, $keep_connected = false ) {
    $return = new STDClass();
    $hashed_password = c_sql::escape_string(c_user::generate_hashed_password(hash("sha256",trim($password))));
    $url = c_page::get_clean_filter();
    $url->page = "LINK_ACCOUNT";
    $url->kc = $keep_connected;
    do {
      $url->token = hash("sha256",time()-rand(1,5000));
      $token_hash = hash("sha256",sc_user::GetGlobalID().$url->token);
    } while ( c_sql::get_first_object("SELECT * FROM tb_relation_ldap_user WHERE trlu_token='".c_sql::escape_string($token_hash)."'") );
    if ( !isset($data->mail) || empty($data->mail) ) {
      $query = "INSERT INTO tb_relation_ldap_user ( trlu_ldap_id, trlu_user_id, trlu_alias, trlu_password, trlu_token, trlu_data ) VALUES ".
               "( $ldap_id, null, '".c_sql::escape_string($login)."', '".$hashed_password."', '".$token_hash."', '".c_sql::escape_string(json_encode($data))."' )";
    } else {
      $emails = $data->mail;
      unset($data->mail);
      $query = "INSERT INTO tb_relation_ldap_user ( trlu_ldap_id, trlu_user_id, trlu_alias, trlu_password, trlu_token, trlu_email, trlu_data ) VALUES ".
               "( $ldap_id, null, '".c_sql::escape_string($login)."', '".$hashed_password."', '".$token_hash."', $emails, '".c_sql::escape_string(json_encode($data))."' )";
    }
    c_sql::insert($query);
    $url = c_page::convert_object_to_url($url);
    $return->error = false;
    $return->url = "$url";
    return $return;
  }

  public static function update_incomplete_link_reference( $ldap_id, $login, $password, $data, $keep_connected = false ) {
    $return = new STDClass();
    $query = "SELECT * FROM tb_relation_ldap_user WHERE ".
             "trlu_alias='".c_sql::escape_string($login)."' AND ".
             "trlu_ldap_id=$ldap_id ";
    $hashed_password = c_sql::escape_string(c_user::generate_hashed_password(hash("sha256",trim($password))));
    $url = c_page::get_clean_filter();
    $url->page = "LINK_ACCOUNT";
    $url->kc = $keep_connected;
    do {
      $url->token = hash("sha256",time()-rand(1,5000));
      $token_hash = hash("sha256",sc_user::getGlobalID().$url->token);
    } while ( c_sql::get_first_object("SELECT * FROM tb_relation_ldap_user WHERE trlu_token='".c_sql::escape_string($token_hash)."'") );
    if ( !isset($data->mail) || empty($data->mail) ) {
      $query = "UPDATE tb_relation_ldap_user SET ".
               "trlu_password='".c_sql::escape_string($hashed_password)."', ".
               "trlu_data='".c_sql::escape_string(json_encode($data))."', ".
               "trlu_token='".c_sql::escape_string($koen_hash)."' ".
               "WHERE trlu_ldap_id=$ldap_id AND trlu_alias='".c_sql::escape_string($login)."'";
    } else {
      $emails = $data->mail;
      unset($data->mail);
      $query = "UPDATE tb_relation_ldap_user SET ".
               "trlu_password='".c_sql::escape_string($hashed_password)."', ".
               "trlu_data='".c_sql::escape_string(json_encode($data))."', ".
               "trlu_token='".c_sql::escape_string($token_hash)."', ".
               "trlu_email=ARRAY( select distinct unnest(trlu_email||$emails)) " .
               "WHERE trlu_ldap_id=$ldap_id AND trlu_alias='".c_sql::escape_string($login)."'";
    }
    c_sql::insert($query);
    $url = c_page::convert_object_to_url($url);
    $return->error = false;
    $return->url = "$url";
    return $return;
  }

  public static function get_user_info( $domain, $login, $password, $attributes, $search, $server = array(), $dn = "" ) {
    $return = new STDClass();
    if ( !($ldap = self::validate_user( $domain, $login, $password, $server, $dn )) ) {
      return false;
    }
    if ( is_array($dn) ) {
      foreach($dn as $base_dn) {
        $base_dn = substr($base_dn,strpos($base_dn,",")+1,strlen($base_dn));
        $search = ldap_search($ldap, $base_dn, str_replace("[[-USER-]]", $login, $search), $attributes);
       if ( !empty($search) ) break;
      }
    } else {
      $base_dn = "";
      $dc = explode(".", $domain);
      foreach($dc as $_dc) {
        $base_dn .= "dc=".$_dc.",";
      }
      $base_dn = substr($base_dn, 0, -1);
      $search = ldap_search($ldap, $base_dn, str_replace("[[-USER-]]", $login,$search), $attributes);
    }
    $data = ldap_get_entries($ldap, $search);
    foreach($attributes as $attr) {
      $return->{$attr} = array();
      if ( isset($data[0][$attr]) ) {
        for ( $i=0; isset($data[0][$attr][$i]); $i++ ) {
          $return->{$attr}[] = trim($data[0][$attr][$i]);
        }
      }
    }
    $fe = ldap_first_entry($ldap,$search);
    $return->dn = ldap_get_dn($ldap,$fe);
    //print_r($return); die();
    return $return;
  }

  public static function login( $login, $password, $keep_connected = false ) {
    $config = sc_page::getConfig();

    $return = new STDClass();
    $return->error = true;
    if ( ($pos = strpos($login,"@")) ) {
      $domain = substr($login,$pos+1,strlen($login));
      $login = substr($login,0,$pos);
      unset($pos);
    } elseif ( ($pos = strpos($login,"\\")) ) {
      $domain = substr($login,0,$pos);
      $login = substr($login,$pos+1,strlen($login));
      unset($pos);
    }

    if ( isset($domain) && !empty($domain) ) {
      $query = "SELECT * FROM tb_ldap WHERE ldap_trusted=true AND ldap_aka @> '{\"".c_sql::escape_string(c_sentences::strtolower($domain))."\"}' ORDER BY ldap_order ASC";
      unset($domain);
    } elseif ( sc_page::is_network_trusted() || sc_page::is_network_internal() ) {
      $query = "SELECT * FROM tb_ldap WHERE ldap_trusted=true ORDER BY ldap_order ASC";
    } else {
      $return->message = "USER_NOT_FOUND";
      return $return;
    }
    $ldaps = c_sql::select($query);
    while( $ldap = c_sql::fetch_object($ldaps) ) {
      if ( (($pos = strpos($login,"@")) > 0) && ($domain = substr($login,($pos+1),(strlen($login)-1))) ) {
        $aka = explode(",",substr($ldap->ldap_aka,1,-1));
        if ( !in_array($domain,$aka) ) {
          continue;
        }
        $login = substr($login,0,$pos);
      }
      if ( !empty($ldap->ldap_server) ) $ldap->ldap_server = explode(",",substr($ldap->ldap_server,1,-1));
      if ( !empty($ldap->ldap_auth_mask) ) {
        $ldap->ldap_auth_mask = explode("\",\"",substr($ldap->ldap_auth_mask,1,-1));
        foreach($ldap->ldap_auth_mask as &$t) $t = trim($t,"\"");
      }
      if ( ($ldap_con = sc_ldap::validate_user( $ldap->ldap_domain, $login, $password, $ldap->ldap_server, $ldap->ldap_auth_mask )) ) {
        $query = "SELECT * FROM tb_relation_ldap_user WHERE ".
                 "trlu_alias='".c_sql::escape_string($login)."' AND ".
                 "trlu_ldap_id=$ldap->ldap_id ";
        if ( ($obj = c_sql::get_first_object($query)) ) {
          if ( !empty($obj->trlu_user_id) ) {
            $data = sc_ldap::get_user_info( $ldap->ldap_domain, $login, $password, self::$attributes, $ldap->ldap_search, $ldap->ldap_server, $ldap->ldap_auth_mask );
            $emails = "";
            foreach($data->mail as $item) {
              if ( !empty($emails) ) $emails .= ", ";
              $emails .= "'".c_sql::escape_string($item)."'";            
            }
            $emails = "ARRAY[".$emails."]";
            $hashed_password = c_sql::escape_string(c_user::generate_hashed_password(hash("sha256",trim($password))));
            if ( empty($emails) ) {
              $query = "UPDATE tb_relation_ldap_user SET ".
                       "trlu_password='".c_sql::escape_string($hashed_password)."' ".
                       "WHERE trlu_alias='".c_sql::escape_string($login)."' AND trlu_ldap_id=$ldap->ldap_id";
            } else {
              $query = "UPDATE tb_relation_ldap_user SET ".
                       "trlu_password='".c_sql::escape_string($hashed_password)."', ".
                       "trlu_email=ARRAY( select distinct unnest(trlu_email||$emails)) " .
                       "WHERE trlu_alias='".c_sql::escape_string($login)."' AND trlu_ldap_id=$ldap->ldap_id";
            }
            c_sql::select($query);
            if ( c_user::create_user_cookies( $obj->trlu_user_id, $keep_connected ) ) {
              $return->error = false;
              $return->url = "";
              return $return;
            }
            //LOCAL ACCOUNT WAS FOUND, NEED TO UPDATE PASSWORD
          } else {
            $data = sc_ldap::get_user_info( $ldap->ldap_domain, $login, $password, self::$attributes, $ldap->ldap_search, $ldap->ldap_server, $ldap->ldap_auth_mask );
            $emails = "";
            foreach($data->mail as $item) {
              $query = "SELECT * FROM tb_users tu LEFT JOIN tb_relation_ldap_user trlu ON trlu.trlu_user_id=tu.user_id WHERE user_primaryemail='".c_sql::escape_string($item)."' OR trlu_email @>'{\"".c_sql::escape_string($item)."\"}'";
              if ( ($user = c_sql::get_first_object($query)) ) {
                if ( self::link_account( $user->user_id, $ldap->ldap_id, $login, $data, $password ) && c_user::create_user_cookies( $user->user_id, $keep_connected ) ) {
                  $return->error = false;
                  $return->url = "";
                  return $return;
                }
              }
              if ( !empty($emails) ) $emails .= ", ";
              $emails .= "'".c_sql::escape_string($item)."'"; 
            }
            $emails = "ARRAY[".$emails."]";
            $data->mail = $emails;
            return self::update_incomplete_link_reference( $ldap->ldap_id, $login, $password, $data, $keep_connected );
          }
        } else {
          // LOCAL ACCOUNT WAS NOT FOUND
          $data = sc_ldap::get_user_info( $ldap->ldap_domain, $login, $password, self::$attributes, $ldap->ldap_search, $ldap->ldap_server, $ldap->ldap_auth_mask );
          $emails = "";
          foreach($data->mail as $item) {
            $query = "SELECT * FROM tb_users tu LEFT JOIN tb_relation_ldap_user trlu ON trlu.trlu_user_id=tu.user_id WHERE user_primaryemail='".c_sql::escape_string($item)."' OR trlu_email @>'{\"".c_sql::escape_string($item)."\"}'";
            if ( ($user = c_sql::get_first_object($query)) ) {
              if ( self::link_account( $user->user_id, $ldap->ldap_id, $login, $data, $password ) && c_user::create_user_cookies( $user->user_id, $keep_connected ) ) {
                $return->error = false;
                $return->url = "";
                return $return;
              }
            }
            if ( !empty($emails) ) $emails .= ", ";
            $emails .= "'".c_sql::escape_string($item)."'";            
          }
          $emails = "ARRAY[".$emails."]";
          $data->mail = $emails;
          return self::create_incomplete_link_reference( $ldap->ldap_id, $login, $password, $data, $keep_connected );
        }
        break;
      }
    }
    if ( !empty($return->error) ) {
      c_user::insert_block();
      $return->message = "USER_NOT_FOUND";
    }
    return $return;
  }
  public static function validate_user( $domain, $user, $password, $server = array(), $dn ) {
    if ( is_string($server) ) {
      $server = explode(",",$server);
    }
    if ( !is_array($dn) ) {
      $dn = "";
    }
    if ( (($pos = strpos($user,"@")) > 0) && ($username = substr($user,0, $pos)) ) {
      $user = $username;
    }
    $auth[] = $user."@".$domain;
    if ( !empty($dn) ) {
      foreach($dn as $_dn) {
        $tmp = explode(".",$domain);
        $dd = "";
        foreach($tmp as $t) {
          if ( !empty($dd) ) {
            $dd .= ", ";
          }
          $dd .= "dc=$t";
        }
        $auth[] = str_replace("[[-USER-]]",$user,str_replace("[[-DC-]]",$dd,$_dn));
      }
    }
/*    $info = new STDClass();
    $info->dn = $dn;
    $info->auth = $auth;
    $info->server = $server;
    if ( $domain == "devlan" ) {
      print_r($info);
    }*/
    if ( empty($server) ) {
      $ldap = @ldap_connect("ldap://".$domain);
      if ( !$ldap ) return false;
      ldap_start_tls ( $ldap );
      ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
      ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, 1);
      foreach($auth as $_auth) {
        if ( (ldap_bind($ldap, $_auth, $password)) ) return $ldap;
        else c_log::reg("LDAP","ERROR","issue logging user \"$user\" on \"ldap://$domain\": ".ldap_error($ldap));
      }
    } else {
      foreach($server as $srv) {
        $ldap = @ldap_connect($srv);
        if ( !$ldap ) return false;
        @ldap_start_tls ( $ldap );
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, 1);
        foreach($auth as $_auth) {
          if ( (ldap_bind($ldap, $_auth, $password)) ) return $ldap;
          else c_log::reg("LDAP","ERROR","issue logging user \"$user\" on \"ldap://$srv\": ".ldap_error($ldap));
        }
      }
    }
    return false;
  }

  public static function link_account( $user_id, $ldap_id, $login, $data="", $password = '' ) {
    if ( !isset($data->mail) || empty($data->mail) ) {
      $emails = array();
    } else $emails = $data->mail;
    if ( !empty($password) ) {
      $hashed_password = c_sql::escape_string(c_user::generate_hashed_password(hash("sha256",trim($password))));
    } else {
      $hashed_password = "";
    }
    if ( !empty($emails) ) {
      $nemails = "";
      foreach($emails as $item) {
        if ( !empty($nemails) ) $nemails .= ", ";
        $nemails .= "'".c_sql::escape_string($item)."'";            
      }
      $nemails = "ARRAY[".$nemails."]";
    }

    // LOOK FOR ALL ACCOUNTS WITH THESE E-MAILS
    if ( !empty($emails) ) {
      $query = "SELECT * FROM tb_relation_ldap_user WHERE ".
               "(trlu_email && $nemails ) OR ".
               "(trlu_alias='".c_sql::escape_string($login)."' AND trlu_ldap_id=$ldap_id) ";
    } else {
      $query = "SELECT * FROM tb_relation_ldap_user WHERE ".
               "trlu_alias='".c_sql::escape_string($login)."' AND ".
               "trlu_ldap_id=$ldap_id ";
    }
    $result = c_sql::select($query);
    while ( ($obj = c_sql::fetch_object($result)) ) {
      $emails = array_unique(array_merge($emails,explode(",",substr($obj->trlu_email,1,-1))));
      if ( $obj->trlu_ldap_id==$ldap_id ) {
        $found = true;
        if ( !isset($nemails) || empty($nemails) ) {
          $query = "UPDATE tb_relation_ldap_user SET ".
                   "trlu_user_id=$user_id, ".
                   "trlu_token=null ".
                   "WHERE trlu_ldap_id=$obj->trlu_ldap_id AND trlu_alias='".c_sql::escape_string($login)."'";
        } else {
          $query = "UPDATE tb_relation_ldap_user SET ".
                   "trlu_user_id=$user_id, ".
                   "trlu_token=null, ".
                   "trlu_email=ARRAY( select distinct unnest(trlu_email||$nemails)) " .
                   "WHERE trlu_ldap_id=$obj->trlu_ldap_id AND trlu_alias='".c_sql::escape_string($login)."'";
        }
      }
    }
    if ( !isset($found) || empty($found) ) {
      if ( !empty($nemails) ) {
        if ( isset($data) && !empty($data) ) {
          if ( isset($data->mail) ) unset($data->mail);
          $query = "INSERT INTO tb_relation_ldap_user ( trlu_ldap_id, trlu_user_id, trlu_alias, trlu_token, trlu_email, trlu_password, trlu_data ) VALUES ".
                   "( $ldap_id, $user_id, '".c_sql::escape_string($login)."', null, $nemails, '".c_sql::escape_string($hashed_password)."', '".c_sql::escape_string(json_encode($data))."' )";
        } else {
          $query = "INSERT INTO tb_relation_ldap_user ( trlu_ldap_id, trlu_user_id, trlu_alias, trlu_token, trlu_password, trlu_data ) VALUES ".
                 "( $ldap_id, $user_id, '".c_sql::escape_string($login)."', null, '".c_sql::escape_string($hashed_password)."', '".c_sql::escape_string(json_encode($data))."' )";
        }
      } else {
          $query = "INSERT INTO tb_relation_ldap_user ( trlu_ldap_id, trlu_user_id, trlu_alias, trlu_token, trlu_password ) VALUES ".
                 "( $ldap_id, $user_id, '".c_sql::escape_string($login)."', null, '".c_sql::escape_string($hashed_password)."' )";
      }
    }
    c_sql::select($query);
    do {
      $all_emails = $emails;
      $nemails = "";
      foreach($emails as $item) {
        if ( !empty($nemails) ) $nemails .= ", ";
        $nemails .= "'".c_sql::escape_string($item)."'";            
      }
      $nemails = "ARRAY[".$nemails."]";
      $query = "UPDATE tb_relation_ldap_user SET ".
               "trlu_user_id=$user_id, ".
               "trlu_token=null ".
               "WHERE trlu_email && $nemails ";
      c_sql::select($query);
      $query = "SELECT * FROM tb_relation_ldap_user WHERE ".
               "(trlu_email && $nemails ) ";
      $result = c_sql::select($query);
      while( $obj = c_sql::fetch_object($result) ) {
        $all_emails = array_unique(array_merge($all_emails,explode(",",substr($obj->trlu_email,1,-1))));
      }
    } while ( $all_emails != $emails );
    return true;
  }
  public static function link_to_existent_account( $token, $login, $password, $keep_connected ) {
    if ( !c_user::verify_block() ) {
      return c_sentences::get_sentence("NETWORK_BLOCKED");
    }
    $token = hash("sha256",sc_user::getGlobalID().$token);
    $query = "SELECT * FROM tb_relation_ldap_user trlu JOIN tb_ldap tl ON trlu.trlu_ldap_id=tl.ldap_id WHERE trlu_token='".c_sql::escape_string($token)."'";
    if ( !($ldap = c_sql::get_first_object($query)) ) {
      c_user::insert_block();
      return c_sentences::get_sentence("INVALID_TOKEN");
    }

    if ( ($result = c_user::login( $login, $password, $keep_connected )) ) return $result;
    self::link_account( c_user::getID(), $ldap->ldap_id, $ldap->trlu_alias ); 
  }
  public static function change_password( $ldap_id, $login, $current_password, $new_password, $confirm_new_password ) {
    $messages = array();
    $sentences = c_sentences::get_sentence_sequency("CHANGE_PASSWORD_MESSAGES");

    $query = "SELECT * FROM tb_relation_ldap_user trlu JOIN tb_ldap tl ON tl.ldap_id=trlu_ldap_id WHERE trlu_user_id=".c_user::getID()." AND trlu_ldap_id=".intval(c_sql::escape_string($ldap_id))."";
    $ldap = c_sql::get_first_object($query);
    if ( empty($ldap) || ($login !== $ldap->trlu_alias) ) {
      $messages['current_password'] = $sentences->PROVIDE_YOUR_CURRENT_PASSWORD;
    }
    if ( !empty($messages) ) return $messages;

    if ( $current_password === $new_password ) $messages['new_password'] = $sentences->SAME_PASSWORD;
    if ( !empty($messages) ) return $messages;
  
    if ( empty($current_password) ) $messages['current_password'] = $sentences->PROVIDE_YOUR_CURRENT_PASSWORD;
    if ( !sc_accounts::is_valid_password($new_password) ) $messages['new_password'] = $sentences->VALID_PASSWORD;
    if ( ($new_password!=$confirm_new_password) ) $messages['confirm_new_password'] = $sentences->NEW_AND_CONFIRM_PASSWORD_MUST_MATCH;

    if ( !empty($messages) ) return $messages;

    $hashed_password = c_user::generate_hashed_password( $current_password );
    $hashed_new_password = c_user::generate_hashed_password( hash("sha256",$new_password) );

    if ( $hashed_password == $hashed_new_password ) $messages['new_password'] = $sentences->NEW_PASSWORD_MUST_BE_DIFFERENT;

    $user_id = c_user::getID();

    if ( (($pos = strpos($login,"@")) > 0) && ($domain = substr($login,($pos+1),(strlen($login)-1))) ) {
      $aka = explode(",",substr($ldap->ldap_aka,1,-1));
      if ( !in_array($domain,$aka) ) {
        $messages['current_password'] = $sentences->INVALID_CURRENT_PASSWORD;
        return $messages;
      }
      $login = substr($login,0,$pos);
    }
    if ( !empty($ldap->ldap_server) ) $ldap->ldap_server = explode(",",substr($ldap->ldap_server,1,-1));
    if ( !empty($ldap->ldap_smbpasswd) ) $ldap->ldap_smbpasswd = explode(",",substr($ldap->ldap_smbpasswd,1,-1));
    if ( !empty($ldap->ldap_auth_mask) ) {
      $ldap->ldap_auth_mask = explode("\",\"",substr($ldap->ldap_auth_mask,1,-1));
      foreach($ldap->ldap_auth_mask as &$t) $t = trim($t,"\"");
    }
    if ( !($ldap_con = sc_ldap::validate_user( $ldap->ldap_domain, $login, $current_password, $ldap->ldap_server, $ldap->ldap_auth_mask )) ) {
      $messages['current_password'] = $sentences->INVALID_CURRENT_PASSWORD;
      return $messages;
    }
    $data = json_decode($ldap->trlu_data);
    if ( isset($data->dn) ) {
      if ( is_array($data->dn) ) {
        $dn = "";
        foreach($data->dn as $_dn) {
          if ( empty($_dn) ) $dn .= " ";
          else $dn .= $_dn;
        }
        $auth[] = $dn;
      } else $auth[] = $data->dn;
    }
    if ( !empty($ldap->ldap_auth_mask) ) {
      foreach($ldap->ldap_auth_mask as $_dn) {
        $tmp = explode(".",$ldap->ldap_domain);
        $dd = "";
        foreach($tmp as $t) {
          if ( !empty($dd) ) {
            $dd .= ", ";
          }
          $dd .= "dc=$t";
        }
        $auth[] = str_replace("[[-USER-]]",$login,str_replace("[[-DC-]]",$dd,$_dn));
      }
    }
    $auth = array_unique($auth);
    $entry = array();
//    $newpassword = "\"" . $new_password . "\"";
//    $len = strlen($newpassword);
//    $newpass = "";
//    for ($i = 0; $i < $len; $i++) $newpass .= "{$newpassword{$i}}\000";
//    $entry["unicodePwd"] = $newpass;

    $entry['UnicodePwd'] = iconv("UTF-8", "UTF-16LE", '"' . $new_password . '"');
//    $userdata["unicodepwd"] = $new_password;
//    die($dn);
    //$result = ldap_mod_replace($ldap_con, $dn, $entry);
//    print_r($entry);
    foreach($auth as $mask) {
      $result = ldap_mod_replace($ldap_con, str_replace("[[-USER-]]",$login,$mask), $entry);
      if ($result) {
        $messages[] = $sentences->PASSWORD_CHANGED;
        c_log::reg("LDAP","INFO","password has been changed for user \"$login\": ".ldap_error($ldap_con));
        define("AJAX_OK",true);
        break;
      } else {
        c_log::reg("LDAP","ERROR","issue changing password for user \"$login\": ".ldap_error($ldap_con));
        //$messages['current_password'] = $sentences->PASSWORD_UNCHANGED;
       // $messages[] = ldap_error($ldap_con);
      }
    }
    if ( !defined("AJAX_OK") ) {
      foreach($auth as $mask) {
        $entry = array();
        $entry['userpassword'] = "{MD5}".base64_encode(pack("H*",md5($new_password)));
        $result = ldap_mod_replace($ldap_con, str_replace("[[-USER-]]",$login,$mask), $entry);
        if ($result) {
          $messages[] = $sentences->PASSWORD_CHANGED;
          c_log::reg("LDAP","INFO","password has been changed for user \"$login\": ".ldap_error($ldap_con));
          define("AJAX_OK",true);
          break;
        } else {
          c_log::reg("LDAP","ERROR","issue changing password for user \"$login\": ".ldap_error($ldap_con));
          $messages[] = $sentences->PASSWORD_UNCHANGED.": ".ldap_error($ldap_con);
        }
      }
    }
    if ( defined("AJAX_OK") && !empty($ldap->ldap_smbpasswd) ) {
      foreach($ldap->ldap_smbpasswd as $server) {
        $conn = ssh2_connect ( $server, 22 );
        if ( ssh2_auth_password ( $conn, $login, $new_password ) ) {
          $cmd = "(printf '".c_sql::escape_string($current_password)."\n'; printf '".c_sql::escape_string($new_password)."\n'; printf '".c_sql::escape_string($new_password)."\n' ) | smbpasswd -s";
          $stream = ssh2_exec($conn, $cmd );
          $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
          stream_set_blocking($stream, true);
          stream_set_blocking($errorStream, true);
          $data = str_replace("\r\n","\n",stream_get_contents($stream));
          $data_error = str_replace("\r\n","\n",stream_get_contents($errorStream));
          if ( !stristr($data,"password changed for user $login") && !stristr($data_error,"password changed for user $login") ) {
            $messages[] = "$sentences->COULD_NOT_SET_PASSWORD_FOR $server";
          }
          $stream = ssh2_exec($conn, "history -c" );
          @fclose($stream);
          @@fclose($errorStream);
        }
      }
    }
    return $messages;
  }
}
?>
