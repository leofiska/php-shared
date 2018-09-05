<?php
class sc_page {

  private static $instance;

  public static function get_instance() {
    if ( !is_object(self::$instance) ) {
      self::$instance = new c_page();
      self::$instance->prepare_config();
      self::$instance->prepare();
    }
    return self::$instance;
  }

  public static function getConfig() {
    $page = self::get_instance();
    return $page->get_config();
  }
  public static function getFilter() {
    $page = self::get_instance();
    return $page->get_filter();
  }
  public static function getCloneFilter() {
    $page = self::get_instance();
    return $page->clone_filter();
  }

  public static function get_language() {
    if ( !is_object(self::$instance) ) {
      self::get_instance();
    }
    return self::$instance->config->language;
  }

  public static function getUserAttributes() {
    if ( !is_object(self::$instance) ) {
      self::get_instance();
    }
    return self::$instance->user->get_attributes();
  }
  public static function get_user_id() {
    if ( !is_object(self::$instance) ) {
      self::get_instance();
    }
    return self::$instance->user->get_user_id();
  }

  public static function get_global_id() {
    if ( !is_object(self::$instance) ) {
      self::get_instance();
    }
    return self::$instance->user->get_global_id();
  }

  static function has_panic() {
    $page = self::get_instance();
    if ( isset($page->config->panic) && !empty($page->config->panic) ) return true;
    return false;
  }

  static function convert_object_to_url( $object ) {
    $config = self::getConfig(); 
    $encrypt = new STDClass();
    $encrypt->input = json_encode($object);
    $encrypt->cipher = "aes-256-ctr";
    $encrypt->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encrypt->cipher));
//    $encrypt->iv = substr(hash("md5","F1sCHER-EHNGTrTh"),0,16);
    $encrypt->iv = bin2hex($encrypt->iv);
    $encrypt->iv2 = substr(hash("md5","F1sCHER-EHNGTrTh".$encrypt->iv),0,16);
    $encrypt->password = hash("sha256","FISCHER-ThePowerGuido".$config->{"system|url"}.substr($encrypt->iv2,2,4));
    $encrypt->stage1 = bin2hex(gzcompress($encrypt->input));
    $encrypt->stage2 = openssl_encrypt($encrypt->stage1, $encrypt->cipher, $encrypt->password, $options=0, $encrypt->iv2);
    $encrypt->stage3 = urlencode($encrypt->stage2);
    $encrypt->stage4 = base64_encode($encrypt->iv.$encrypt->stage3);
    $encrypt->output = $encrypt->stage4;

    $separator = rand(50,150);

    if ( strlen($encrypt->output) > $separator ) {
      $tmp = $encrypt->output;
      $encrypt->output = "";
      do {
        if ( !empty($encrypt->output) ) $encrypt->output .= "/";
        $encrypt->output .= substr($tmp,0,$separator);
        $tmp = substr($tmp,$separator,(strlen($tmp)-$separator));
      } while( !empty($tmp) );
    }
    return $encrypt->output;
  }

  static function convert_url_to_object( $url ) {
    if ( empty($url) ) return self::get_clean_filter();
    $config = self::getConfig(); 
    $decrypt = new STDClass();
    $decrypt->input = str_replace("/","",$url);
    $decrypt->cipher = "aes-256-ctr";
//    $decrypt->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($decrypt->cipher));
//    $decrypt->iv = substr(hash("md5","F1sCHER-EHNGTrTh"),0,16);
    $decrypt->stage1 = @base64_decode($decrypt->input);
    $decrypt->iv = substr($decrypt->stage1,0,32);
    $decrypt->iv2 = substr(hash("md5","F1sCHER-EHNGTrTh".$decrypt->iv),0,16);
    $decrypt->password = hash("sha256","FISCHER-ThePowerGuido".$config->{"system|url"}.substr($decrypt->iv2,2,4));
    $decrypt->stage1 = @substr($decrypt->stage1,32,strlen($decrypt->stage1)-32);
    $decrypt->stage2 = @urldecode($decrypt->stage1);
//    print_r($decrypt);
    $decrypt->stage3 = openssl_decrypt($decrypt->stage2, $decrypt->cipher, $decrypt->password, $options=0, $decrypt->iv2);
    $decrypt->stage4 = @gzuncompress(hex2bin($decrypt->stage3));
    $decrypt->output = $decrypt->stage4;
    return json_decode($decrypt->output);
  }

  static function get_host_url() {
    $ssl = isset($_SERVER['HTTPS']) ? "https://" : "http://";
    $url = $ssl.$_SERVER['SERVER_NAME'];
    return $url;
  }
  public static function get_style_replace_attributes() {
    $attribs = array();
    $attribs[] = (object)array("name"=>"font|size","prefix"=>"font-size","replace"=>"[[-FONT-SIZE-]]");
    $attribs[] = (object)array("name"=>"system|background|color","prefix"=>"background-color","replace"=>"[[-BGCOLOR-]]");
    $attribs[] = (object)array("name"=>"bar|background|color","prefix"=>"background-color","replace"=>"[[-BAR-BGCOLOR-]]");
    $attribs[] = (object)array("name"=>"bar|background|-color-with-cover","prefix"=>"background-color","replace"=>"[[-BAR-BGCCOLOR-]]");
    $attribs[] = (object)array("name"=>"bottom|background|color","prefix"=>"background-color","replace"=>"[[-BOTTOM-BGCOLOR-]]");
    $attribs[] = (object)array("name"=>"menu|compact|background|color","prefix"=>"background-color","replace"=>"[[-COMPACTMENU-BGCOLOR-]]");
    $attribs[] = (object)array("name"=>"menu|extended|background|color","prefix"=>"background-color","replace"=>"[[-EXTENDEDMENU-BGCOLOR-]]");
    $attribs[] = (object)array("name"=>"menu|compact|font|color","prefix"=>"color","replace"=>"[[-COMPACTMENU-COLOR-]]");
    $attribs[] = (object)array("name"=>"menu|extended|font|color","prefix"=>"color","replace"=>"[[-EXTENDEDMENU-COLOR-]]");
    $attribs[] = (object)array("name"=>"menu|compact|background|color-with-cover","prefix"=>"background-color","replace"=>"[[-COMPACTMENU-BGCCOLOR-]]");
    $attribs[] = (object)array("name"=>"menu|extended|background|color-with-cover","prefix"=>"background-color","replace"=>"[[-EXTENDEDMENU-BGCCOLOR-]]");
    $attribs[] = (object)array("name"=>"menu|compact|font|color-with-cover","prefix"=>"color","replace"=>"[[-COMPACTMENU-CCOLOR-]]");
    $attribs[] = (object)array("name"=>"menu|extended|font|color-with-cover","prefix"=>"color","replace"=>"[[-EXTENDEDMENU-CCOLOR-]]");
    $attribs[] = (object)array("name"=>"icon|ic-user|border","prefix"=>"border","replace"=>"[[-IC-USER-BORDER-]]");


    $attribs[] = (object)array("name"=>"button|1|background|color","prefix"=>"background-color","replace"=>"[[-BUTTON1-BGCOLOR-]]");
    $attribs[] = (object)array("name"=>"panel-popup|border|color","prefix"=>"border-color","replace"=>"[[-PANEL-POPUP-BORDER-COLOR-]]");
    $attribs[] = (object)array("name"=>"border|1|color","prefix"=>"border-color","replace"=>"[[-BORDER-COLOR-1-]]");
    $attribs[] = (object)array("name"=>"block|1|background|color","prefix"=>"background-color","replace"=>"[[-LEFT-BLOCK-COLOR-1-]]");
    $attribs[] = (object)array("name"=>"input|border|color","prefix"=>"border-color","replace"=>"[[-INPUT-BORDER-COLOR-]]");
    $attribs[] = (object)array("name"=>"panel-popup|close|background|color","prefix"=>"background-color","replace"=>"[[-PANEL-POPUP-CLOSE-BGCOLOR-]]");
    return $attribs;
  }


  static function get_clean_filter() {
    $filter = (object)array(
      "page"=>"",
    );
    return $filter;
  }
  public static function clone_filter( ) {
    $page = self::get_instance();
    return clone $page->filter;
  }

  public static function is_user_logged() {
    $user = sc_user::get_instance();
    return $user->is_logged();
  }
  public static function is_mobile() {
    $config = self::getConfig();
    if ( stristr($config->user_agent,"mobile") ) return true;
    if ( stristr($config->user_agent,"android") ) return true;
    return false;
  }
  public static function is_network_trusted( $str = false) {
    $config = self::getConfig();
    if ( !$str ) {
      return $config->network_trusted;
    } else {
      if ( $config->network_trusted ) return "true";
      else return "false";
    }
    return false;
  }
  public static function is_network_internal( $str = false ) {
    $config = self::getConfig();
    if ( !$str ) {
      return $config->network_internal;
    } else {
      if ( $config->network_internal ) return "true";
      else return "false";
    }
    return false;
  }
  public static function is_network_external( $str = false ) {
    $config = self::getConfig();
    if ( !$str ) {
      if ( !$config->network_internal && !$config->network_trusted ) return true;
      else return false;
    } else {
      if ( !$config->network_internal && !$config->network_trusted ) return "true";
      else return "false";
    }
    return true;
  }


  public static function process() {
    $page = self::get_instance();
    return $page->exec();
  }

  public static function render_menu() {
    $page = self::get_instance();
    $menu = $page->ext_render_vertical_menu();
    $template = "<div><div><div><a href='/'><img src='/pictures/logo.png' /></a></div><div><nav class='extended_menu'>".$menu->extended."</nav><nav class='compact_menu'><input type=\"checkbox\" id='compact_button' onClick=\"toogle_scroll()\"/>".$menu->compact."</nav></div><div>".$menu->panel."</div></div></div>";
    return $template;
  }

  public static function not_found() {
    header("content-type: text/plain");
    header("HTTP/1.0 404 Not Found");
    die(c_sentences::get_sentence("NOT_FOUND"));
  }

  private $config;
  public $page;
  private $data;
  private $user;
  private $filter;
  private $last_modified;
 
  private function __construct() {
    $this->page = new STDClass();
    $this->page->middle = "";
    $this->page->navigator = "";
    $this->page->xml = "";
    $this->page->page = "";
    $this->filter = new STDClass();
  }
  private function prepare_config() {
    

    // connect to database and initiate user
    c_sql::connect();

    // get general configuration from database
    $query = "SELECT jsonb_object_agg(config_name, config_value ) AS config FROM tb_config;";
    if ( !($row = c_sql::get_first_object($query)) ) die("error while fetching configuration");
    $this->config = (object)json_decode($row->config);

    $this->config->network_trusted = false;
    $this->config->network_internal = false;
    $this->config->network_name = "";

    if ( isset($_SERVER['REMOTE_ADDR']) ) {
      $query = "SELECT nz_trusted, nz_internal FROM tb_network_zone WHERE nz_network >> '".c_sql::escape_string($_SERVER['REMOTE_ADDR'])."' AND (nz_internal=true OR nz_trusted=true)";
      if ( ($row = c_sql::get_first_object($query)) ) {
        if ( !empty($row->nz_trusted) && $row->nz_trusted == "t" ) {
          $this->config->network_trusted = true;
        }
        if ( !empty($row->nz_internal) && $row->nz_internal == "t" ) {
          $this->config->network_internal = true;
        }
      }
    } else {
      $this->config->network_internal = true;
    }
    if ( isset($_SERVER['HTTP_USER_AGENT']) ) $this->config->user_agent = $_SERVER['HTTP_USER_AGENT'];
  }
  private function prepare() {
    $language_found = false;

    $this->user = c_user::get_instance( );

    if ( isset($_COOKIE['time']) && !empty($_COOKIE['time']) ) {
      if ( strtotime($_COOKIE['time']) > strtotime($this->config->last_modified) ) {
        $this->last_modified = $_COOKIE['time'];
      } else {
        $this->last_modified = gmdate('D, d M Y H:i:s T',strtotime($this->config->last_modified));
      }
    } else { 
      $this->last_modified = gmdate('D, d M Y H:i:s T',strtotime($this->config->last_modified));
    }
    // if user is logged, get language for user
    if ( !$language_found && $this->user->is_logged() ) {
      $attributes = $this->user->get_attributes();
      $query = "SELECT language_codeset FROM tb_languages WHERE language_wui=true AND (language_code ILIKE '".c_sql::escape_string($attributes->language)."%' OR language_codeset='".c_sql::escape_string($attributes->language)."') LIMIT 1";
      if ( $obj = c_sql::get_first_object($query) ) {
        $this->config->language = $attributes->language;
        $language_found = true;
        $time = time()-365*24*60*60;
        setcookie("language", 0, $time, "/", $_SERVER['SERVER_NAME'], false, false);
      }
    }

    // if no language found, try to locate cookie
    if ( !$language_found && isset($_COOKIE['language']) && !empty($_COOKIE['language']) ) {
      $query = "SELECT language_codeset FROM tb_languages WHERE language_codeset=".c_sql::escape_string($_COOKIE['language'])." AND language_wui=true LIMIT 1";
      if ( $obj = c_sql::get_first_object($query) ) {
        $this->config->language = $obj->language_codeset;
        $language_found = true;
      }
    }

    // detect browser language   
    if ( !$language_found && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {
      $browser_language = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
      foreach($browser_language as &$language) {
        if ( ($pos = strpos($language,';')) ) {
          $language = substr($language,0,$pos);
        }
        // if language is find on database set as broswer language and break on first match
        $query = "SELECT language_codeset FROM tb_languages WHERE language_wui=true AND language_code ILIKE '".c_sql::escape_string($language)."%' LIMIT 1";
        if ( $obj = c_sql::get_first_object($query) ) {
          $this->config->language = $obj->language_codeset;
          $language_found = true;
          break;
        }
      }
      // set browser as language if valid value was found
    }
    if ( !$language_found ) {
      $query = "SELECT language_codeset FROM tb_languages WHERE language_wui=true AND language_code ILIKE '".c_sql::escape_string($this->config->{"system|language|default"})."%' LIMIT 1";
      if ( $obj = c_sql::get_first_object($query) ) {
        $this->config->language = $this->config->{"system|language|default"};
        $language_found = true;
      }
    }
    if ( !$language_found ) {
      $query = "SELECT language_codeset FROM tb_languages WHERE language_wui=true ORDER BY language_codeset ASC LIMIT 1";
      if ( $obj = c_sql::get_first_object($query) ) {
        $this->config->language = $obj->language_codeset;
        $language_found = true;
      }
    }
    if ( isset($browser_language) ) unset($browser_language);
    if ( isset($obj) ) unset($obj);
    if ( isset($this->config->browser_language) ) unset($this->config->browser_language);
    if ( isset($this->config->{"system|language|default"}) ) unset($this->config->{"system|language|default"});

    // get page
    $this->filter = isset($_GET['filter']) && !empty($_GET['filter']) ? trim($_GET['filter']) : false;
    $this->filter = self::convert_url_to_object($this->filter);  
  }

  public function exec() {

    if ( !isset($this->filter) ) $this->filter = new STDClass();

    if ( !isset($this->filter->page) || empty($this->filter->page) ) $this->filter->page = "HOME";

    $user = c_user::get_instance();
    $attrib = $user->get_attributes();

    $function = false;
    $query = "SELECT menu_function,hstore_to_json(menu_info) as menu_info FROM v_menu WHERE menu_alias='".c_sql::escape_string($this->filter->page)."'";
    if ( $obj = c_sql::get_first_object($query) ) {
      $function = $obj->menu_function;
    } else {
      $function = strtolower($this->filter->page);
    }
    if ( !c_user::has_permission(strtoupper($function)) ) {
      if ( c_user::has_permission("POSTS") ) {
        $co = "f_posts";
      } elseif ( isset($_SERVER['HTTP_REFERER']) ) {
        $url = $_SERVER['HTTP_REFERER'];
        header( "Location: $url" );
        die();
      } else {
        self::not_found();
        $url = "/";
        header( "Location: $url" );
        die();
      }
    } else {
      $co = "f_".strtolower($function);
      if ( !class_exists($co) ) {
        $co = "sf_".strtolower($function);
        if ( !class_exists($co) ) {
          if ( c_user::has_permission("POSTS") ) {
            $co = "f_posts";
          } elseif ( isset($_SERVER['HTTP_REFERER']) ) {
            $url = $_SERVER['HTTP_REFERER'];
            header( "Location: $url" );
            die();
          } else {
            self::not_found();
            $url = "/";
            header( "Location: $url" );
            die();
          }
        }
      }
    }
    if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
      if ( !isset($_SERVER["HTTP_REFERER"]) || empty($_SERVER["HTTP_REFERER"]) ) return;
      if ( !strstr($_SERVER["HTTP_REFERER"],$_SERVER["SERVER_NAME"]) ) return;
      if ( $_SERVER["CONTENT_TYPE"] == "application/x-www-form-urlencoded" && !isset($_POST['page_number']) ) {
        define("AJAX",true);
        $co::process_ajax();
        $this->plot_as_ajax();
        return;
      }
    }
    $co::process();
    if ( isset($obj->menu_info) && !empty($obj->menu_info) ) $this->page->cover = self::render_cover( $obj->menu_info );
    $this->plot_as_html();
  }

  public function plot_as_ajax() {
    header( 'content-type: application/xml; charset=utf-8', true );
    $data =  '<?xml version="1.0" encoding="utf-8"?'.'>';
    $data .= "<json>";
    $el = isset($_POST['el']) ? $_POST['el'] : "";
    if ( isset($this->page->xml) ) $data .= "<![CDATA[".json_encode(array("id"=>@$_POST['id'], "result"=>array("content"=>$this->page->xml)) )."]]>";
    $data .= "</json>";
    $md5 = base64_encode(hash("md5",$data));
    header( "Content-MD5: $md5" );
    header( "X-Content-MD5: $md5" );
    echo $data;
  }
  public function plot_as_html() {

    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");

    $attrib = sc_user::getUserAttributes();
    $lang = self::get_language();
    $config = self::get_config();
    $filter = $this->get_filter();


    if ( isset($config->navigator) && !empty($config->navigator) ) {
      $this->page->navigator = $this->render_navigator()." ".$this->page->navigator;
    }
    

    $this->page->menu = $this->render_vertical_menu( $this->get_menu() );
    $this->page->panel = $this->render_horizontal_panel( $this->get_panel() );
    $this->page->bottom = $this->render_bottom();
    if ( !isset($_GET['css']) && empty($_GET['css']) ) $this->config->css = true;
    else $this->config->css = false;
    $this->config->js = true;

    $file = $GLOBALS['config']->shared->dir->lib->html."index.html";
    if ( !is_file($file) || !is_readable($file) ) die("error while fetching base file");
    $handle = @fopen($file,"rb");
    if ( $handle == null || !is_resource($handle) ) die("permission denied while fetching base file");
    $data = fread($handle,filesize($file));
    fclose($handle);
    if ( isset($this->page->panel) && isset($this->page->panel->popup) && !empty($this->page->panel->popup) ) {
      $this->page->panel->popup = "<input type='checkbox' id='panel_button' onclick=\"close_panels()\"><label for='panel_button' id='panel_control'></label>".$this->page->panel->popup;
    }
    if ( isset($this->page->title) ) {
      if ( isset($this->config->{"system|title|$lang"}) ) $data = str_replace("[[-PAGETITLE-]]",$this->page->title." - ".$this->config->{"system|title|$lang"},$data);
      elseif ( isset($this->config->title) ) $data = str_replace("[[-PAGETITLE-]]",$this->page->title." - ".$this->config->title,$data);
    } else {
      if ( isset($this->config->{"system|title|$lang"}) ) $data = str_replace("[[-PAGETITLE-]]",$this->config->{"system|title|$lang"},$data);
      elseif ( isset($this->config->title) ) $data = str_replace("[[-PAGETITLE-]]",$this->config->title,$data);
    }
    $data = $this->config->css ? str_replace("[[-CSS-]]","<link rel='stylesheet' href='/base.css' type='text/css' />\r\n\t<link rel='stylesheet' href='/dynamic.css' type='text/css' />",$data) : str_replace("[[-CSS-]]","",$data);
    $data = $this->config->js ? str_replace("[[-JS-]]","<script type='text/javascript' src='/base.js'></script>\r\n\t<script type='text/javascript' src='/dynamic.js'></script>",$data) : str_replace("[[-JS-]]","",$data);

    $data = isset($this->page->navigator) ? str_replace("[[-NAVIGATOR-]]",$this->page->navigator,$data) : str_replace("[[-NAVIGATOR-]]","",$data);
    $data = isset($this->page->middle) ? str_replace("[[-PRINCIPAL-]]",$this->page->middle,$data) : str_replace("[[-PRINCIPAL-]]","",$data);
    $data = isset($this->page->menu) ? str_replace("[[-LARGE-MENU-]]",$this->page->menu->extended,$data) : str_replace("[[-LARGE-MENU-]]","",$data);
    $data = isset($this->page->menu) ? str_replace("[[-COMPACT-MENU-]]",$this->page->menu->compact,$data) : str_replace("[[-COMPACT-MENU-]]","",$data);
    $data = isset($this->page->cover) ? str_replace("[[-COVER-]]",$this->page->cover,$data) : str_replace("[[-COVER-]]","",$data);

    if ( isset($this->page->panel) ) {
      $data =  isset($this->page->panel->f1) ? str_replace("[[-PANEL-F1-]]",$this->page->panel->f1,$data) : str_replace("[[-PANEL-F1-]]","",$data);
      $data =  isset($this->page->panel->popup) ? str_replace("[[-PANEL-POPUP-]]",$this->page->panel->popup,$data) : str_replace("[[-PANEL-POPUP-]]","",$data);;
    }
    $data = isset($this->page->bottom) ? str_replace("[[-FOOTER-]]",$this->page->bottom,$data) : str_replace("[[-FOOTER-]]","",$data);
    $data = isset($this->config->{"menu|extended|logo"}) ? str_replace("[[-TOP-EXTENDED-LOGO-]]","<img class='pointer' src='/pictures/".$this->config->{"menu|extended|logo"}."' />",$data) : str_replace("[[-TOP-EXTENDED-LOGO-]]","",$data);
    $data = isset($this->config->{"menu|compact|logo"}) ? str_replace("[[-TOP-COMPACT-LOGO-]]","<img class='vmiddle' src='/pictures/".$this->config->{"menu|compact|logo"}."' />",$data) : str_replace("[[-TOP-COMPACT-LOGO-]]","",$data);
//    $data = isset($this->page->page) ? str_replace("[[-PAGE-]]",$this->page->page,$data) : str_replace("[[-PAGE-]]","",$data);
    if ( isset($attrib->hide_on_blur) && !empty($attrib->hide_on_blur) ) $this->page->bottom .= "<input type='hidden' id='hide_on_blur' value='1' />";
    else $this->page->bottom .= "<input type='hidden' id='hide_on_blur' value='0' />";
    if ( isset($attrib->panic) && !empty($attrib->panic) ) $this->page->bottom .= "<input type='hidden' id='panic' value='1' />";
    else $this->page->bottom .= "<input type='hidden' id='panic' value='0' />";
    $md5 = base64_encode(hash("md5",$data));
    header( "Content-MD5: $md5" );
    header( "X-Content-MD5: $md5" );
    echo $data;
  }

  public function get_filter() {
    return $this->filter;
  }
  public function get_config() {
    return $this->config;
  }

  public static function get_referer_filter() {
    $config = self::getConfig();
    if ( !isset($_SERVER["HTTP_REFERER"]) || empty($_SERVER["HTTP_REFERER"]) ) return false;
    if ( !strstr($_SERVER["HTTP_REFERER"],$_SERVER["SERVER_NAME"]) ) return false;
    $pseudo_filter = strstr($_SERVER["HTTP_REFERER"],$config->{"system|url"});
    $pseudo_filter = trim(strchr(stristr(trim($_SERVER["HTTP_REFERER"],"/"),$config->{"system|url"}),"/"),"/");
    if ( empty($pseudo_filter) ) return false;
    $pseudo_filter = @self::convert_url_to_object($pseudo_filter);
    return $pseudo_filter;
  }

  public function get_menu() {
    $permissions = $this->user->get_permissions();
    foreach($permissions as &$permission) $permission = '\'{'.$permission.'}\'';
    $query = "SELECT menu_alias, menu_wui, hstore_to_json(menu_info) as menu_info , menu_sentence->CONCAT(".$this->config->language.") AS menu_sentence, menu_parent FROM v_menu WHERE (menu_permissions @> ".implode(" OR menu_permissions @> ",$permissions).")";
    unset($permissions);
    $result = c_sql::select($query);
    $menu = array();
    while ( $obj = c_sql::fetch_object($result) ) {
      $this->user->add_menu_permission($obj->menu_alias);
      $obj->menu_info = json_decode($obj->menu_info);
      $menu_item = (object)array( "alias"=>$obj->menu_alias, "name"=>$obj->menu_sentence, "wui"=>($obj->menu_wui === "t") ? true : false, "icon"=>isset($obj->menu_info->icon) ? $obj->menu_info->icon : "");
      if ( empty($obj->menu_parent) ) {
        $menu["$obj->menu_alias"] = (object)array("item"=>$menu_item);
      } else {
        $menu["$obj->menu_parent"]->childs[] = $menu_item;
      }
    }
    $menu = array_values($menu);
    return $menu;
  }
  private function create_menu_item( $item, $pic = true ) {
    $url = self::get_clean_filter();
    $url->page = $item->alias;
    $url = self::convert_object_to_url($url);
    if ( empty($pic) || !isset($item->icon) || empty($item->icon) ) {
      $tmp = "<a href='/$url'>$item->name</a>";
    } else {
      if ( strchr($item->icon,".") ) {
        $tmp = "<a href='/$url'><img src='/pictures/".$item->icon."' /></a>";
      } else {
        $tmp = "<a href='/$url' class='$item->icon'></a>";
      }
    }
    $tmp = "<div title='".$item->name."' onClick=\"if ( this.firstChild.firstChild && this.firstChild.firstChild.href ) { location.href = this.firstChild.firstChild.href; }\">"."<span>$tmp</span></div>";
    /*if ( class_exists("f_".strtolower($item->alias)) || class_exists("sf_".strtolower($item->alias)) ) {
      $url = self::get_clean_filter();
      $url->page = $item->alias;
      $url = self::convert_object_to_url($url);
      $tmp = "<a href='/$url'>$tmp</a>";
      $tmp = "<div onClick=\"if ( this.firstChild.firstChild && this.firstChild.firstChild.href ) { location.href = this.firstChild.firstChild.href; }\">"."<span>$tmp</span></div>";
    } else {
      $tmp = "<div class='nolink' onClick=\"if ( this.firstChild.firstChild && this.firstChild.firstChild.href ) { location.href = this.firstChild.firstChild.href; }\">"."<span>$tmp</span></div>";
    }*/
    return $tmp;
  }

  public function ext_render_vertical_menu( ) {
    $out = $this->render_vertical_menu( $this->get_menu() );
    $out->panel = $this->page->panel = $this->render_horizontal_panel( $this->get_panel() );
    return $out;
  }

  public function render_cover ( $menu ) {
    try {
      $menu = json_decode($menu);
    } catch ( Exception $e ) {
      return;
    }

    if ( !isset($menu->cover) || empty($menu->cover) ) return;
    $return = "";
    $return .= "<div id='cover'";
    if ( isset($menu->{"cover|bw"}) && !empty($menu->{"cover|bw"}) ) {
      $return .= " class='abw'";
    }
    $return .= "></div>";
    return $return;
  }
  public function render_navigator ( $alias = "" ) {
    $filter = c_page::getFilter();
    if ( empty($alias) ) {
      $alias = $filter->page;
    }
    $return = "";
    $query = "SELECT menu_alias,menu_sentence->CONCAT(".$this->config->language.") AS menu_sentence FROM v_menu WHERE menu_alias='HOME'";
    $obj = c_sql::get_first_object($query);
    $url = self::get_clean_filter();
    $url->page = $obj->menu_alias;
    $url = self::convert_object_to_url($url);
    $return .= "<span class='vmiddle'><a class='glow4' href='/$url'>$obj->menu_sentence</a></span>";
    if ( $filter->page != "HOME" ) {
      $query = "SELECT menu_parent,menu_alias,menu_sentence->CONCAT(".$this->config->language.") AS menu_sentence FROM v_menu WHERE menu_alias='".c_sql::escape_string($alias)."'";
      if ( $obj = c_sql::get_first_object($query) ) {
        $url = self::get_clean_filter();
        $url->page = $obj->menu_alias;
        $url = self::convert_object_to_url($url);
        $return1 = "<span class='nowrap op4 wspre'> | </span><span class='vmiddle'><a class='glow4' href='/$url'>$obj->menu_sentence</a></span>";
        if ( !empty($obj->menu_parent) ) {
          $query = "SELECT menu_parent,menu_alias,menu_sentence->CONCAT(".$this->config->language.") AS menu_sentence FROM v_menu WHERE menu_alias='".c_sql::escape_string($obj->menu_parent)."'";
          $obj = c_sql::get_first_object($query);
          $url = self::get_clean_filter();
          $url->page = $obj->menu_alias;
          $url = self::convert_object_to_url($url);
          $return .= "<span class='nowrap op4 wspre'> | </span><span class='vmiddle'><a class='glow4' href='/$url'>$obj->menu_sentence</a></span>";
        }
        $return .= $return1;
      }
    }
    return $return;
  }

  public function render_vertical_menu( $menus ) {
    $menu = "";
    foreach($menus as $item ) {
      if ( !isset($item->item->wui) || empty($item->item->wui) ) continue;
      $tmp = self::create_menu_item($item->item);
      if ( isset($item->childs) && !empty($item->childs) ) {
        $tmp2 = "";
        foreach($item->childs as $child) {
          $tmp2 .= self::create_menu_item($child);
        }
        $tmp .= "<div><div></div><div>$tmp2</div></div>";
        unset($tmp2);
      }
      if ( isset($item->item) && isset($item->item->icon) && !empty($item->item->icon) && !strchr($item->item->icon,".") ) {
        $tmp = "<div class='mi' title='".$item->item->name."'>$tmp</div>";
      } else {
        $tmp = "<div>$tmp</div>";
      }
      $menu .= $tmp;
      unset($tmp);
    }
    $return = new STDClass();
    $return->extended = $menu;
    unset($menu);

    $menu = "";
    for ( $i=0; isset($menus[$i]); $i++ ) {
      $item = $menus[$i];
      $tmp = self::create_menu_item($item->item, false);
      if ( isset($item->childs) && !empty($item->childs) ) {
        if ( !empty($menu) ) $tmp = "<hr />".$tmp;
        foreach($item->childs as $child) {
          $tmp .= self::create_menu_item($child, false);
        }
      } elseif( isset($menus[($i-1)]) && isset($menus[($i-1)]->childs) && !empty($menus[($i-1)]->childs) ) {
        if ( !empty($menu) ) $tmp = "<hr />".$tmp;
      }
      $menu .= $tmp;
      unset($tmp);
    }
    $return->compact = "<span></span><span></span><span></span><div>";

    $return->compact .= "<div></div><div>$menu</div>";
    $return->compact .= "</div>";
    unset($tmp);
    return $return;
  }

  private function get_panel() {
    $permissions = $this->user->get_permissions();
    foreach($permissions as &$permission) $permission = '\''.$permission.'\'';
    $query = "SELECT panel_floor, panel_alias, panel_sentence->'".$this->config->language."' AS panel_sentence, panel_icon AS panel_icon FROM v_panel WHERE (panel_permissions = ".implode(" OR panel_permissions = ",$permissions).")";
    unset($permissions);
    $result = c_sql::select($query);
    $panel = array();
    while ( $obj = c_sql::fetch_object($result) ) {
      $t = (object)array("name"=>trim($obj->panel_sentence,"\""), "alias"=>$obj->panel_alias,"floor"=>$obj->panel_floor );
      if ( !empty($obj->panel_icon) ) $t->pic = trim($obj->panel_icon,"\"");
      $panel[] = $t;
      unset($t);
    }
    return $panel;
  }

  private function render_horizontal_panel( $panels ) {
    $return = new STDClass();
    $return->popup = "";
    foreach($panels AS $item) {
      $t = "f".$item->floor;
      if ( !isset($return->{$t}) ) $return->{$t} = "";
      $return->{$t} .= "<div title='".$item->name."' onclick=\"show_panel( '$item->alias"."_popup' )\"  title='".c_sentences::strtolower($item->name)."' >";
      if ( isset($item->pic) && !empty($item->pic) ) {
        if ( strchr($item->pic,".") ) $return->{$t} .= "<img src='/pictures/$item->pic' />";
        else $return->{$t} .= "<div class='$item->pic'></div>";
      } else {
        $return->{$t} .= "$item->name";
      }
      $return->{$t} .= "</div>";
      $co = "f_".strtolower($item->alias);
      if ( !class_exists($co) ) {
        $co = "sf_".strtolower($item->alias);
        if ( !class_exists($co) ) {
          continue;
        }
      }
      $content = $co::get_section();
      $return->popup .= "<div class='panel_popup'><div offsetX='6em' offsetY='4em' focus='f_".c_sentences::strtolower($item->alias)."' id='".c_sentences::strtolower($item->alias)."_popup'>$content</div><div onClick=\"close_panel( this.parentNode )\">close</div></div>";
      unset($t);
    }
    return $return;
  }
  private function render_bottom( ) {

    $page = c_page::get_instance();
    $filter = $page->get_filter();
    $config = c_page::getConfig();
    $sentences = c_sentences::get_sentence_sequency("BOTTOM");


    $bottom = "<div class='table'>";
    $bottom .= "\t<div class='tr'>";
    $bottom .= "\t\t<div class='td'>";
    $bottom .= "\t\t\t<div class='inline'>";
    if ( !isset($config->{"bottom|logo"}) ) {
      $bottom .= "\t\t\t\t<p><img class='signature' src='/pictures/logo.png' /></p>";
    } else {
      $bottom .= "\t\t\t\t<p><img class='signature' src='/pictures/".$config->{"bottom|logo"}."' /></p>";
    }
    $bottom .= "\t\t\t\t<p title='".$page->config->{"contact|address"}."' class='nowrap center pt1em glow4'>";
    $bottom .= "\t\t\t\t".$page->config->{"contact|address"};
    $bottom .= "\t\t\t\t</p>";
    $bottom .= "\t\t\t</div>";
    $bottom .= "\t\t</div>";
    $bottom .= "\t\t<div class='td right top' style='width: 99%;'>";
    $bottom .= "\t\t\t<div class='inline'>";
    $bottom .= "\t\t\t\t<div class='table'>";

    $info[] = "contact|email";
    $info[] = "contact|phone";
    $info[] = "contact|whatsapp";
    $info[] = "contact|skype";

    foreach($info as $tmp) {
      if ( !isset($page->config->{$tmp}) || empty($page->config->{$tmp}) ) continue;
      switch($tmp) {
        case "contact|whatsapp":
          if ( sc_page::is_mobile() ) {
            $bottom .= "\t\t\t\t\t<label onClick=\"loadpage('whatsapp://send?phone=".$page->config->{$tmp}."&text=')\" class='tr glow6 text nowrap lh200' title='".$page->config->{$tmp}."'>\r\n";
          } else {
            $bottom .= "\t\t\t\t\t<label class='tr glow6 text nowrap lh200' title='".$page->config->{$tmp}."'>\r\n";
          }
          break;
        case "contact|phone":
          if ( sc_page::is_mobile() ) {
            $bottom .= "\t\t\t\t\t<label onClick=\"loadpage('tel://".$page->config->{$tmp}."')\" class='tr glow6 text nowrap lh200' title='".$page->config->{$tmp}."'>\r\n";
          } else {
            $bottom .= "\t\t\t\t\t<label class='tr glow6 text nowrap lh200' title='".$page->config->{$tmp}."'>\r\n";
          }
          break;
        case "contact|email":
          if ( sc_page::is_mobile() ) {
            $bottom .= "\t\t\t\t\t<label onClick=\"loadpage('mailto://".$page->config->{$tmp}."')\" class='tr glow6 text nowrap lh200' title='".$page->config->{$tmp}."'>\r\n";
          } else {
            $bottom .= "\t\t\t\t\t<label class='tr glow6 text nowrap lh200' title='".$page->config->{$tmp}."'>\r\n";
          }
          break;
        default:
          $bottom .= "\t\t\t\t\t<label class='tr glow6 text nowrap lh200' title='".$page->config->{$tmp}."'>\r\n";
          break;
      }
      $bottom .= "\t\t\t\t\t\t<div class='td right'>\r\n";
      $bottom .= "\t\t\t\t\t\t\t<img class='icon middle' src='/pictures/".substr($tmp,strpos($tmp,"|")+1,strlen($tmp)).".png'>\r\n";
      $bottom .= "\t\t\t\t\t\t</div>\r\n";
      $bottom .= "\t\t\t\t\t\t<div class='td left pl2px'>\r\n";
      $bottom .= "\t\t\t\t\t\t\t<p class='nowrap ellipsis'>\r\n";
      $bottom .= "\t\t\t\t\t\t\t\t<input af class='transparent middle' type='text' style='width: ".number_format(strlen($page->config->{$tmp})*0.9,0)."ch;' readonly='readonly' value='".$page->config->{$tmp}."' onclick=\"this.select( )\" onfocus=\"this.select( )\" />\r\n";
      $bottom .= "\t\t\t\t\t\t\t</p>\r\n";
      $bottom .= "\t\t\t\t\t\t</div>\r\n";
      $bottom .= "\t\t\t\t\t</label>\r\n";
    }
    $bottom .= "\t\t\t\t</div>";
    $bottom .= "\t\t\t</div>";
    $bottom .= "\t\t</div>";
    $bottom .= "\t</div>";
    $bottom .= "</div>";
    // LANGUAGE BAR


    $header = array();
    $query = "SELECT l1.language_code, l1.language_codeset, l1.language_name->CONCAT(language_codeset) AS name FROM tb_languages l1 WHERE language_wui = true  ORDER BY name ASC;";
    if ( ($result = c_sql::select($query)) && c_sql::num_rows($result) > 1 ) {
      $div = "";
      if ( c_sql::num_rows($result) > 1 ) {
        $div .= "<div id='language_options'>";
        while( $item = c_sql::fetch_object($result) ) {
          if ( $item->language_codeset == $page->config->language ) {
            $language = $item;
          }
          $url = self::get_clean_filter();
          $url->page = "LANGUAGE";
          $url->id = "$item->language_codeset";
          $url = self::convert_object_to_url($url);
          $header[] = "<https://".$_SERVER['SERVER_NAME']."/$url>; rel=\"alternate\"; hreflang=\"".substr($item->language_code,0,2)."\"";
          $div .= "<div onclick=\"loadpage( '$url' )\" class='pointer'><div class='inline middle caps glow4'><div class='inline middle'><img src='/pictures/$item->language_codeset.png' /></div><div class='inline middle'>$item->name</div></div></div>";
        }
        $div .= "</div>";
      } else {
        while( $item = c_sql::fetch_object($result) ) {
          if ( $item->language_codeset == $page->config->language ) {
            $language = $item;
          }
        }
      }
      $bottom .= "<div><div id='language'><div class='caps'><div><div class='inline'><div class='inline middle pr5px'><img class='middle' src='/pictures/$language->language_codeset.png' /></div><div class='inline middle'>$language->name</div></div></div>$div</div></div></div>";
    }
    $header = "Link: ".implode($header,",");
    header( $header );

    if ( isset($div) ) unset($div);
    if ( isset($language) ) unset($language);

    $bottom .= "<div class='table'>";
    $bottom .= "\t<div class='tr'>";
    $bottom .= "\t\t<div class='td left'>";
    $bottom .= "\t\t\t<p class='glow4 nowrap compact'>© ".$page->config->{"system|rights_reserved"}."<p>";
    $bottom .= "\t\t\t<p class='glow4 nowrap compact'>".$sentences->ALL_RIGHTS_RESERVED."<p>";
    $bottom .= "\t\t\t<p class='glow4 nowrap extended'>© ".$page->config->{"system|rights_reserved"}." • ".$sentences->ALL_RIGHTS_RESERVED."<p>";
    $bottom .= "\t\t</div>";
    $bottom .= "\t\t<div class='td right'  style='width: 99%;'>";
    $bottom .= "\t\t\t<p>";
    foreach( $page->config as $index => $value ) {
      if ( !stristr($index,"social|") ) continue;
      $index = str_replace("social|","",$index);
      $bottom .= "<a href='$value' target='_blank'><img class='glow6 bigicon' src='/pictures/".c_sentences::strtolower($index).".png' /></a>";
    }

    $bottom .= "\t\t\t</p>";
    $bottom .= "\t\t</div>";
    $bottom .= "\t</div>";
    $bottom .= "</div>";
    $bottom .= "<div class='center'>";
    if ( isset($config->debug) && !empty($config->debug) ) {
      $bottom .= "<div class='op4'>$sentences->IP_ADDRESS: <input type='text' af class='transparent' value='".$_SERVER['REMOTE_ADDR']."' readonly=readonly onclick=\"this.select( )\" onfocus=\"this.select( )\" />";
      if ( sc_page::is_network_internal() ) $bottom .= " [$sentences->INTERNAL_NETWORK]";
      elseif ( sc_page::is_network_trusted() ) $bottom .= " [$sentences->TRUSTED_NETWORK]";
      $bottom .= "</div>";
    }
    $bottom .= "<div class='inline op8'>";
    $bottom .= "<pre>$sentences->POWERED_BY</pre>";
    $bottom .= "</div>";
    $bottom .= "</div>";
    return $bottom;

  }
  public static function create_page_nav( $filter, $query ) {
    $user = c_user::get_instance();
    $attributes = $user->get_attributes();
    if ( !isset($filter->page_number) || empty($filter->page_number) ) $filter->page_number = 1;
    else $filter->page_number = intval($filter->page_number);
    $pages = new STDClass();
    $pages->total_items = c_sql::count_all($query);
    $pages->max =  $attributes->items_per_page;
    $pages->total_pages = intval($pages->total_items/$pages->max);
    if ( $pages->total_items%$pages->max != 0 ) $pages->total_pages++;
   
    if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
      if ( $filter->page_number > $pages->total_pages ) $filter->page_number = $pages->total_pages;
      if ( isset($_POST['next_page']) ) {
        if ( ++$filter->page_number > $pages->total_pages ) $filter->page_number = $pages->total_pages;
      } elseif ( isset($_POST['last_page']) ) {
        $filter->page_number = $pages->total_pages;
      } elseif ( isset($_POST['previous_page']) ) {
        if ( --$filter->page_number < 1 ) $filter->page_number = 1;
      } elseif ( isset($_POST['first_page']) ) {
        $filter->page_number = 1;
      } elseif ( isset($_POST['page_number']) ) {
        if ( intval($_POST['page_number']) < 1 ) $filter->page_number = 1;
        elseif( intval($_POST['page_number']) > $pages->total_pages ) $filter->page_number = $pages->total_pages;
        else $filter->page_number = intval($_POST['page_number']);
      }
      $url = c_page::convert_object_to_url($filter);
      header( "Location: ".c_page::get_host_url()."/$url" );
      die();
//      print_r($filter);print_r($_POST);die();
    }
     
//    print_r($pages); die();
//    echo $query;die();
     
      if ( $pages->total_pages < 1 ) $pages->total_pages = 1;
      $pages->page = $filter->page_number;
      $pages->page = ($pages->page > $pages->total_pages) || ($pages->page == -1) ? $pages->total_pages : $pages->page;
      $pages->page = $pages->page < 1 ? 1 : $pages->page;
      $filter->page_number = $pages->page;
      $sentences = sc_sentences::get_sentence_sequency("PAGE_COUNTER");
      $output = "";
      $output .= "<form method='post' name='page' action=''>";
        if ( $filter->page_number == 1 ) {
          $output .= "<div class='noselect inline caps disabled'>$sentences->FIRST_PAGE</div> | ";
          $output .= "<div class='noselect inline caps disabled'>$sentences->PREVIOUS_PAGE</div> | ";
        } else {
          $output .= "<label class='noselect inline caps'>$sentences->FIRST_PAGE<input type='submit' name='first_page' class='invisible' /></label> | ";
          $output .= "<label class='noselect inline caps'>$sentences->PREVIOUS_PAGE<input type='submit' name='previous_page' class='invisible' /></label> | ";
        }
        if ( $pages->total_pages < 2 ) {
          $output .= "<select onChange='this.form.submit()' class='caps transparent' disabled='disabled' name='page_number'>";
        //  $output .= "<input type='text' class='transparent caps' disabled='disabled' name='page_number' value='$sentences->PAGE 1' style='width: ".(strlen($sentences->PAGE)+strlen($filter->page)+1)."ch;' /> | ";
        } else {
          $output .= "<select onChange='this.form.submit()' class='caps transparent pointer' name='page_number'>";
//          $output .= "<input type='text' class='transparent pointer caps' name='page_number' value='$sentences->PAGE $filter->page' style='width: ".(strlen($sentences->PAGE)+strlen($filter->page)+1)."ch;' /> | ";
        }
        for( $i=1; $i <= $pages->total_pages; $i++ ) {
          while( strlen($i) < strlen($pages->total_pages) ) $i = "0".$i;
          if ( intval($i) == intval($filter->page_number) ) $output .= "<option selected='selected' value='$i'>$sentences->PAGE $i</option>";
          else $output .= "<option value='$i'>$sentences->PAGE $i</option>";
        }
        $output .= "</select> ";
        if ( $filter->page_number == $pages->total_pages ) {
          $output .= "<div class='noselect inline caps disabled'>$sentences->NEXT_PAGE</div> | ";
          $output .= "<div class='noselect inline caps disabled'>$sentences->LAST_PAGE</div> ";
        } else {
          $output .= "<label class='noselect inline caps'>$sentences->NEXT_PAGE<input type='submit' name='next_page' class='invisible' /></label> | ";
          $output .= "<label class='noselect inline caps'>$sentences->LAST_PAGE<input type='submit' name='last_page' class='invisible' /></label> ";
        }
      $output .= "</form>";
      $pagenav = "<div class='pagetop'><div>$output</div></div>";
      $pagenav .= "<div class='pagebottom'><div>$output</div></div><div></div>";
      return $pagenav;
    }

    public static function create_table( $filter, $sentences, $title = false, $search = false, $titles = false, $orders_by = false, $top_commands = false, $bottom_commands = false, $search_result = false ) {
      $data = new STDClass();
      if ( $search ) {
        $data->search = c_filter::create_filter_element( $filter );
      }
      if ( !empty($title) ) {
        if ( is_object($title) ) {
          $data->title = "<a name='$title->id'>$title->content</a>";
        } else {
          $data->title = $title;
        }
      }
      if ( !empty($top_commands) ) {
        $data->top_commands = "<div class='table_cell right'>";
        foreach( $top_commands->global as $alias => $value ) {
          $id = isset($value->id) && !empty($value->id) ? $value->id : $value->name;
          $type = strstr($id,"_line") ? "type='button' onclick=\"exec_button( this )\" " : "type='submit' ";
          if ( $value->selectable ) {
            $data->top_commands .= "<div class='pointer'><label>".$sentences->{sc_sentences::strtoupper($value->name)}."<input name='command' $type class='invisible' value='$id'></label></div>";
          } else {
            $data->top_commands .= "<div class='cursor disabled'><label>".$sentences->{sc_sentences::strtoupper($value->name)}."<input name='command' $type disabled=disabled class='invisible' value='$id'></label></div>";
          }
          unset($id);
        }
        $data->top_commands .= "</div>";
      }
      if ( !empty($bottom_commands) ) {
        $data->bottom_commands = "<div class='table_cell right'>";
        foreach( $bottom_commands->global as $alias => $value ) {
          $id = isset($value->id) && !empty($value->id) ? $value->id : $value->name;
          $type = strstr($id,"_line") ? "type='button' onclick=\"exec_button( this )\" " : "type='submit' ";
          if ( $value->selectable ) {
            $data->bottom_commands .= "<div class='pointer'><label>".$sentences->{sc_sentences::strtoupper($value->name)}."<input name='command' $type' class='invisible' value='$id'></label></div>";
          } else {
            $data->bottom_commands .= "<div class='cursor disabled'><label>".$sentences->{sc_sentences::strtoupper($value->name)}."<input name='command' $type disabled=disabled class='invisible' value='$id'></label></div>";
          }
          unset($id);
        }
        $data->bottom_commands .= "</div>";
      }
      if ( !empty($titles) ) {
        if ( isset($filter->order_by) ) {
          if ( strstr($filter->order_by,"_ASC") ) {
            $arrow = "/arrow_up.png";
            $order = "DESC";
          } else {
            $arrow = "/arrow_down.png";
            $order = "ASC";
          }
        }
        $url = clone $filter;
        if ( isset($url->page) ) unset($url->page);
        $data->titles = "";
        foreach( $titles AS $value ) {
          if ( is_object($value) ) {
            $width = $value->width;
            $value = $value->alias;
          } else $width=0;
          if ( !empty($value) && isset($filter->order_by) && strstr($filter->order_by,$value) ) {
            $url->order_by = $value."_$order";
            $curl = c_page::convert_object_to_url($url);
            if ( !empty($orders_by) ) {
              $data->titles .= "<div class='table_cell cell_padded pointer' style='min-width: $width"."px;'><a href='/$curl'><img class='arrow' src='$arrow'> ".$sentences->{$value}."</a></div>";
            } else {
              $data->titles .= "<div class='table_cell cell_padded' style='min-width: $width"."px;'><img class='arrow' src='$arrow'> ".$sentences->{$value}."</div>";
            }
          } elseif ( empty($value) ) {
            $data->titles .= "<div class='table_cell' style='width: 20px;'></div>";
          } elseif ( $value == "checkbox" ) {
            $data->titles .= "<div class='table_cell pointer' style='width: 20px;'><input name='selectall' class='noselect pointer' type='checkbox' onClick=\"toogle_select_all( event, this.form )\" /></div>";
          } else {
            $url->order_by = $value."_ASC";
            $curl = sc_page::convert_object_to_url($url);
            if ( !empty($orders_by) ) $data->titles .= "<div class='table_cell cell_padded pointer' style='min-width: $width"."px;'><a href='/$curl'>".$sentences->{$value}."</a></div>";
            else $data->titles .= "<div class='table_cell cell_padded'  style='min-width: $width"."px;'>".$sentences->{$value}."</div>";
          }
        }
      }


      $output = new STDClass();
      $output->start = "<div class='table' style='min-width: 40%;'>";
      $output->end = "";
      if ( isset($data->search) ) {
        $output->start .= "<div class='table_row'>";
          $output->start .= "<div class='table_cell'>";
            $output->start .= $data->search;
          $output->start .= "</div>";
        $output->start .= "</div>";
        if ( $search_result ) {
          $output->start .= "<div class='table_row'>";
            $output->start .= "<div class='table_cell center'>";
              if ( isset($search_result->items) ) {
                $str = str_replace("[LAST]",$search_result->last,str_replace("[FIRST]",$search_result->first,str_replace("[ITEMS]",$search_result->items,str_replace("[SEC]",$search_result->time,$sentences->SEARCH_RESULT))));
                if ( empty($search_result->items) ) $str = substr($str,0,strrpos($str,"."));
              } else {
                $str = str_replace("[SEC]",$search_result->time,$sentences->SEARCH_RESULT);
                $str = substr($str,0,strrpos($str,"."));
              }
              $output->start .= $str;
            $output->start .= "</div>";
          $output->start .= "</div>";
        }
      }
      if ( isset($data->title) ) {
        $output->start .= "<div class='table_row'>";
          $output->start .= "<div class='table_cell noselect caps table_title center'>";
            $output->start .= $data->title;
          $output->start .= "</div>";
        $output->start .= "</div>";
      }
        $output->start .= "<div class='table_row'>";
          $output->start .= "<div class='table_cell'>";
            $output->start .= "<form method='post' action='' name='list'>";
            $output->start .= "<div class='table' style='width: 100%'>";

      if ( isset($data->top_commands) ) {
        $output->start .= "<div class='table_row noselect top_command_bar'>";
          $output->start .= $data->top_commands;
        $output->start .= "</div>";
      }
      $output->start .= "<div class='table_row'>";
        $output->start .= "<div class='table' style='width: 100%;'>";
      if ( isset($data->titles) ) {
         $output->start .= "<div class='table_row table_title noselect caps'>";
            $output->start .= $data->titles;
          $output->start .= "</div>";
      }
        $output->end .= "</div>";
      $output->end .= "</div>";
      if ( isset($data->bottom_commands) ) {
        $output->end .= "<div class='table_row noselect bottom_command_bar'>";
          $output->end .= $data->bottom_commands;
        $output->end .= "</div>";
      }



            $output->end .= "</div>";
            $output->end .= "</form>";
          $output->end .= "</div>";
        $output->end .= "</div>";
      $output->end .= "</div>";
      return $output;
    }

}
?>
