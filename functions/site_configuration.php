<?php
class sf_site_configuration {
  public static function process() {
    $page = c_page::get_instance();
    $sentences = c_sentences::get_sentence_sequency("SITE_CONFIGURATION");
    

    $page->page->middle .= "<section class='center' id='SITE_CONFIGURATION'>";
    $page->page->middle .= "<h1>$sentences->SITE_CONFIGURATION</h1>";
    if ( c_user::has_permission("SITE_INFORMATION") ) {
      $data = sf_site_information::get_section();
      $page->page->middle .= "$data";
      unset($data);
    }
    $page->page->middle .= "<div style='height: 10vh'></div>";
    if ( c_user::has_permission("SITE_MENU") ) {
      $data = sf_site_menu::get_section();
      $page->page->middle .= "$data";
      unset($data);
    }
    $page->page->middle .= "</section>";
  }
  static function process_ajax ( ) {
    $page = c_page::get_instance();
    $data =  new static;

    if ( isset($_POST) && !empty($_POST) ) {
      if ( isset($_POST['f_name']) ) {
        if ( stristr($_POST['f_name'],"menu") && c_user::has_permission("SITE_MENU") ) {
          sf_site_menu::process_ajax();
        }
        if ( stristr($_POST['f_name'],"site_info") && c_user::has_permission("SITE_INFORMATION") ) {
          sf_site_information::process_ajax();
        }
      }
      if ( isset($_POST['operation']) && isset($_POST['value']) && c_user::has_permission("SITE_CONFIGURATION") ) {
        $value = c_sql::escape_string(trim($_POST['value']));
        $query = "UPDATE tb_config SET config_value='$value' WHERE config_name='".c_sql::escape_string($_POST['operation'])."'";
        $obj = c_sql::select($query);
        $error = c_sql::get_last_error();
        if ( empty($error) ) {
          $page->page->xml = array("element"=>array(array("name"=>"save_".$_POST['operation'],"value"=>"/pictures/checked.png"),array("name"=>$_POST['operation'],"value"=>"$value") ));
        }
        return;
      }
    }
  }
}
?>
