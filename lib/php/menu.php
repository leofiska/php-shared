<?php
class sc_menu {


  public $items;
  public $default;

  function __construct() {
    $this->default = "";
    $this->items = array();
  }
  public static function get_new_menu_items() {
    $permissions = c_user::get_permissions();
    if ( empty($permissions) || !is_array($permissions) ) {
      die("error rendering menu");
    }
    $query = "";
    foreach($permissions as $p) {
      if ( empty($p) ) continue;
      if ( !empty($query) ) $query .= " OR";
      $query .= " permission_alias='$p'";
    }
    $query = "WHERE language_codeset='".c_user::get_language_id()."' AND (".$query;
    $query = "SELECT * FROM v_user_menu ".$query;
    $query .= ") GROUP BY menu_id";
    if ( !($result = c_sql::select($query)) ) return false;
    $return = new static();

    while ( $row = c_sql::fetch_object($result) ) {
      if ( empty($row->menu_parent_id) && !isset($return->items[$row->menu_id]) ) {
        $tmp = new STDClass();
        $tmp->name = $row->menu_value;
        $tmp->alias = $row->menu_alias;
        $tmp->default = $row->menu_default;
        $tmp->hidden = $row->menu_hidden;
        if ( empty($return->default) && !empty($tmp->default) ) {
          $return->default = $tmp;
        }
        $return->items[$row->menu_id] = $tmp;
        unset($tmp);
      } else {
        if ( !isset($return->items[$row->menu_parent_id]) ) continue;
        $tmp = new STDClass();
        $tmp->name = $row->menu_value;
        $tmp->alias = $row->menu_alias;
        $tmp->default = $row->menu_default;
        $tmp->hidden = $row->menu_hidden;
        if ( empty($return->default) && !empty($tmp->default) ) {
          $return->default = $tmp;
        }
        if ( !isset($return->items[$row->menu_parent_id]->child) ) $return->items[$row->menu_parent_id]->child = array();
        $return->items[$row->menu_parent_id]->child[] = $tmp;
        unset($tmp);
      }
    }
    return $return;
  }


  public static function render_horizontal_menu( $filter, $menus, $view = false ) {
    $menu = "<div></div>\r\n";
    $menu .= "<div class='horizontal-menu'>\r\n";
    $menu .= "\t<div>\r\n";
    if ( $view && !empty($menus->default) ) $menu .= "\t\t<div>".$menus->default->name."</div>\r\n";
    $menu .= "\t\t<div>\r\n";
    $menu .= "\t\t\t<div>\r\n";
    $menu .= "\t\t\t\t<div>\r\n";
    $menu .= "\t\t\t\t\t<div>\r\n";


    foreach($menus->items AS $item) {
      if ( isset($item->hidden) && !empty($item->hidden) ) continue;
      if ( isset($item->child) && !empty($item->child) ) {
        $menu .= "\t\t\t\t\t\t<div>\r\n";
        $menu .= "\t\t\t\t\t\t\t<div>\r\n";
        $menu .= "\t\t\t\t\t\t\t\t<div>$item->name</div>\r\n";
        $menu .= "\t\t\t\t\t\t\t\t<div>\r\n";
        $menu .= "\t\t\t\t\t\t\t\t\t<div></div>\r\n";
        $menu .= "\t\t\t\t\t\t\t\t\t<div>\r\n";
        foreach( $item->child AS $child ) {
          $url = sc_page::clean_filter($filter);
          $url->menu = $child->alias;
          $url = sc_page::convert_object_to_url($url);
          $menu .= "\t\t\t\t\t\t\t\t\t\t<div>\r\n";
          $menu .= "\t\t\t\t\t\t\t\t\t\t\t<div>\r\n";
          $menu .= "\t\t\t\t\t\t\t\t\t\t\t\t<div onClick=\"loadpage( '/?filter=$url' )\">$child->name</div>\r\n";
          $menu .= "\t\t\t\t\t\t\t\t\t\t\t</div>\r\n";
          $menu .= "\t\t\t\t\t\t\t\t\t\t</div>\r\n";
        }
        $menu .= "\t\t\t\t\t\t\t\t\t</div>\r\n";
        $menu .= "\t\t\t\t\t\t\t\t</div>\r\n";
        $menu .= "\t\t\t\t\t\t\t</div>\r\n";
        $menu .= "\t\t\t\t\t\t</div>\r\n";
      } else {
        $url = sc_page::clean_filter($filter);
        $url->menu = $item->alias;
        $url = sc_page::convert_object_to_url($url);
        $menu .= "\t\t\t\t\t\t<div>\r\n";
        $menu .= "\t\t\t\t\t\t\t<div>\r\n";
        $menu .= "\t\t\t\t\t\t\t\t<div onClick=\"loadpage( '/?filter=$url' )\">$item->name</div>\r\n";
        $menu .= "\t\t\t\t\t\t\t</div>\r\n";
        $menu .= "\t\t\t\t\t\t</div>\r\n";
      }
    }
    $menu .= "\t\t\t\t\t</div>\r\n";
    $menu .= "\t\t\t\t</div>\r\n";
    $menu .= "\t\t\t</div>\r\n";
    $menu .= "\t\t</div>\r\n";
    $menu .= "\t</div>\r\n";
    $menu .= "</div>\r\n";
    return $menu;
  }
  public static function render_vertical_menu( $filter, $menus, $view = false ) {
    $menu = "<div></div>\r\n";
    $menu .= "<div class='vertical-menu'>\r\n";
    $menu .= "\t<div>\r\n";
    if ( $view && !empty($menus->default) ) $menu .= "\t\t<div>".$menus->default->name."</div>\r\n";
    $menu .= "\t\t<div>\r\n";
    $menu .= "\t\t\t<div>\r\n";
    $menu .= "\t\t\t\t<div>\r\n";
    $menu .= "\t\t\t\t\t<div>\r\n";
    foreach($menus->items AS $item) {
      if ( isset($item->hidden) && !empty($item->hidden) ) continue;
      if ( isset($item->child) && !empty($item->child) ) {
        $menu .= "\t\t\t\t\t\t<div>\r\n";
        $menu .= "\t\t\t\t\t\t\t<div>\r\n";
        $menu .= "\t\t\t\t\t\t\t\t<div>$item->name</div>\r\n";
        $menu .= "\t\t\t\t\t\t\t\t<div>\r\n";
        $menu .= "\t\t\t\t\t\t\t\t\t<div></div>\r\n";
        $menu .= "\t\t\t\t\t\t\t\t\t<div>\r\n";
        foreach( $item->child AS $child ) {
          $url = c_page::clean_filter($filter);
          $url->menu = $child->alias;
          $url = c_page::convert_object_to_url($url);
          $menu .= "\t\t\t\t\t\t\t\t\t\t<div onClick=\"loadpage( '/?filter=$url' )\">$child->name</div>\r\n";
        }
        $menu .= "\t\t\t\t\t\t\t\t\t</div>\r\n";
        $menu .= "\t\t\t\t\t\t\t\t</div>\r\n";
        $menu .= "\t\t\t\t\t\t\t</div>\r\n";
        $menu .= "\t\t\t\t\t\t</div>\r\n";
      } else {
        $url = c_page::clean_filter($filter);
        $url->menu = $item->alias;
        $url = c_page::convert_object_to_url($url);
        $menu .= "\t\t\t\t\t\t<div>\r\n";
        $menu .= "\t\t\t\t\t\t\t<div>\r\n";
        $menu .= "\t\t\t\t\t\t\t\t<div onClick=\"loadpage( '/?filter=$url' )\">$item->name</div>\r\n";
        $menu .= "\t\t\t\t\t\t\t</div>\r\n";
        $menu .= "\t\t\t\t\t\t</div>\r\n";
      }
    }
    $menu .= "\t\t\t\t\t</div>\r\n";
    $menu .= "\t\t\t\t</div>\r\n";
    $menu .= "\t\t\t</div>\r\n";
    $menu .= "\t\t</div>\r\n";
    $menu .= "\t</div>\r\n";
    $menu .= "</div>\r\n";
    return $menu;
  }



  public static function get_menu_items() {
    $permissions = c_user::get_permissions();
    if ( empty($permissions) || !is_array($permissions) ) {
      die("error rendering menu");
    }
    $query = "";
    foreach($permissions as $p) {
      if ( empty($p) ) continue;
      if ( !empty($query) ) $query .= " OR";
      $query .= " permission_alias='$p'";
    }
    $query = "WHERE language_codeset='".c_user::get_language_id()."' AND (".$query;
    $query = "SELECT * FROM v_user_menu ".$query;
    $query .= ") GROUP BY menu_id";
    if ( !($result = c_sql::select($query)) ) return false;
    $return = array();
    while ( $row = c_sql::fetch_object($result) ) {
      if ( empty($row->menu_parent_id) ) {
        $return[$row->menu_id] = new STDClass();
        $return[$row->menu_id]->name = $row->menu_value;
        $return[$row->menu_id]->alias = $row->menu_alias;
        $return[$row->menu_id]->default = $row->menu_default;
        $return[$row->menu_id]->hidden = $row->menu_hidden;
      } else {
        if ( !isset($return[$row->menu_parent_id]) ) continue;
        $tmp = new STDClass();
        $tmp->name = $row->menu_value;
        $tmp->alias = $row->menu_alias;
        $tmp->default = $row->menu_default;
        $tmp->hidden = $row->menu_hidden;
        if ( !empty($tmp->default) ) {
          $tmp->default = true;
        }
        $return[$row->menu_parent_id]->child[] = $tmp;
        unset($tmp);
      }
    }
    return $return;
  }
}
?>
