<?php
class sc_panel {

  public $items;

  function __construct() {
    $this->items = array();
  }

  public static function get_new_panel_items() {
    c_log::reg("DEBUG", "entered");
    $permissions = c_user::get_permissions();
    if ( empty($permissions) || !is_array($permissions) ) {
      die("error rendering panel");
    }

    $return = new self();

    $query = "SELECT * from tb_languages WHERE language_wui='1' ORDER BY language_original_name ASC";
    if ( ($result = c_sql::select($query)) ) {
      $language = new STDClass();
      $language->alias = "language";
      while ( $row = c_sql::fetch_object($result) ) {
        if ( !isset($language->child) ) $language->child = array();
        $tmp = new STDClass();
        $tmp->name = "$row->language_original_name";
        $tmp->alias = $row->language_codeset;
        $tmp->pic = $row->language_flag;
        $language->child[] = $tmp;
        if ( c_user::get_language_id() == $row->language_codeset ) {
          $language->name = "$row->language_original_name";
          $language->pic = $row->language_flag;
        }
        unset($tmp);
      }
      $return->items[] = $language;
    }
    if ( c_user::is_logged() ) {
      $tmp = new STDClass();
      $tmp->name = sc_sentences::get_sentence("my_account");
      $tmp->alias = "MY_ACCOUNT";
      $return->items[] = $tmp; unset($tmp);
      $tmp = new STDClass();
      $tmp->name = sc_sentences::get_sentence("logout");
      $tmp->alias = "LOGOUT";
      $return->items[] = $tmp; unset($tmp);
    } else {
      $tmp = new STDClass();
      $tmp->name = sc_sentences::get_sentence("my_account");
      $tmp->alias = "LOGIN";
      $return->items[] = $tmp; unset($tmp);
    }
    return $return;
  }
  public static function get_panel_items() {
    c_log::reg("DEBUG", "entered");
    $permissions = c_user::get_permissions();
    if ( empty($permissions) || !is_array($permissions) ) {
      die("error rendering panel");
    }
    $return = array();
    if ( c_user::is_logged() ) {
      c_log::reg("DEBUG", "creating profile panel");
      $tmp = new STDClass();
      $tmp->alias = 'MY_ACCOUNT';
      $tmp->name = c_sentences::get_sentence("my_account");
      $return[] = $tmp;
      unset($tmp);
      $permissions = c_user::get_permissions();
    } else {
      c_log::reg("DEBUG", "user is not logged, requesting guest permissions");
      $permissions = c_user::get_guest_permissions();
    }
    if ( !c_user::is_logged() ) {
      c_log::reg("DEBUG", "creating login panel");
      $login = new STDClass();
      $login->alias = 'login';
      $login->name = c_sentences::get_sentence("my_account");
      $return[] = $login;
      unset($login);
    }
    $query = "SELECT * from tb_languages WHERE language_wui='1' ORDER BY language_original_name ASC";
    c_log::reg("QUERY", $query);
    c_log::reg("DEBUG", "creating language panel");
    if ( !($result = c_sql::select($query)) ) return false;
    $language = new STDClass();
    $language->alias = 'language';
    while ( $row = c_sql::fetch_object($result) ) {
      $tmp = new STDClass();
      //$tmp->name = "$row->language_original_name - $row->language_international_name";
      $tmp->name = "$row->language_original_name";
      $tmp->alias = $row->language_codeset;
      $tmp->pic = $row->language_flag;
      $language->child[] = $tmp;
      if ( c_user::get_language_id() == $row->language_codeset ) {
        $language->name = "$row->language_original_name";
        $language->pic = $row->language_flag;
      }
      unset($tmp);
    }
    $return[] = $language;
    unset($language);
    if ( c_user::is_logged() ) {
      c_log::reg("DEBUG", "creating logout panel");
      $logout = new STDClass();
      $logout->alias = 'logout';
      $logout->name = c_sentences::get_sentence("logout");
      $return[] = $logout;
      unset($logout);
    }
    c_log::reg("DEBUG", "getting out of get_panel");
    return $return;
  }
  public static function render_horizontal_panel( $filter, $panels, $view = false ) {
    $panel = "<div></div>\r\n";
    $panel .= "<div class='horizontal-panel'>\r\n";
    $panel .= "\t<div>\r\n";
    if ( $view && !empty($panels->default) ) $panel .= "\t\t<div>".$panels->default->name."</div>\r\n";
    $panel .= "\t\t<div>\r\n";
    $panel .= "\t\t\t<div>\r\n";
    $panel .= "\t\t\t\t<div>\r\n";
    $panel .= "\t\t\t\t\t<div>\r\n";
    foreach($panels->items AS $item) {
      if ( isset($item->hidden) && !empty($item->hidden) ) continue;
      if ( isset($item->child) && !empty($item->child) ) {
        $panel .= "\t\t\t\t\t\t<div>\r\n";
        $panel .= "\t\t\t\t\t\t\t<div>\r\n";
        $panel .= "\t\t\t\t\t\t\t\t<div>";
        if ( !empty($item->pic) ) $panel .= "<img src='/$item->pic.png' style='width: 25px; margin-right: 3px;'>";
        $panel .= "$item->name</div>\r\n";
        $panel .= "\t\t\t\t\t\t\t\t<div>\r\n";
        $panel .= "\t\t\t\t\t\t\t\t\t<div></div>\r\n";
        $panel .= "\t\t\t\t\t\t\t\t\t<div>\r\n";
        foreach( $item->child AS $child ) {
          $panel .= "\t\t\t\t\t\t\t\t\t\t<div>\r\n";
          $panel .= "\t\t\t\t\t\t\t\t\t\t\t<div>\r\n";
          $panel .= "\t\t\t\t\t\t\t\t<div><label class='noselect pointer bottom'>";
          if ( !empty($child->pic) ) $panel .= "<img src='/$child->pic.png' style='width: 20px'>";
          $panel .= "$child->name<input type='submit' class='invisible' name='$item->alias' value='$child->alias'></label></div>\r\n";
          $panel .= "\t\t\t\t\t\t\t\t\t\t\t</div>\r\n";
          $panel .= "\t\t\t\t\t\t\t\t\t\t</div>\r\n";
        }
        $panel .= "\t\t\t\t\t\t\t\t\t</div>\r\n";
        $panel .= "\t\t\t\t\t\t\t\t</div>\r\n";
        $panel .= "\t\t\t\t\t\t\t</div>\r\n";
        $panel .= "\t\t\t\t\t\t</div>\r\n";
      } else {
        $url = sc_page::clean_filter($filter);
        $url->menu = $item->alias;
        $url = sc_page::convert_object_to_url($url);
        $panel .= "\t\t\t\t\t\t<div>\r\n";
        $panel .= "\t\t\t\t\t\t\t<div>\r\n";
        $panel .= "\t\t\t\t\t\t\t\t<div onClick=\"loadpage( '/?filter=$url' )\">";
        if ( !empty($item->pic) ) $panel .= "<img src='/$item->pic.png' style='width: 20px'>";
        $panel .= "$item->name</div>\r\n";

        $panel .= "\t\t\t\t\t\t\t</div>\r\n";
        $panel .= "\t\t\t\t\t\t</div>\r\n";
      }
    }
    $panel .= "\t\t\t\t\t</div>\r\n";
    $panel .= "\t\t\t\t</div>\r\n";
    $panel .= "\t\t\t</div>\r\n";
    $panel .= "\t\t</div>\r\n";
    $panel .= "\t</div>\r\n";
    $panel .= "</div>\r\n";
    return $panel;
  }
}
?>
