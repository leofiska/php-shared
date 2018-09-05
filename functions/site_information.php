<?php
class sf_site_information {

  function __construct() {
  }



  static function get_section() {

    $page = c_page::get_instance();
    $config = $page->get_config();

    $sentences = sc_sentences::get_sentence_sequency("SITE_INFORMATION");

    $return = "<fieldset class='left inline border p1em'>";
    $return .= "<legend class='uppercase bold'>$sentences->SITE_INFORMATION</legend>";    
    $return .= "<div class='table fixed'>";
    $query = "SELECT * FROM tb_config WHERE config_editable=true ORDER BY config_name ASC";
    if ( !($result = c_sql::select($query)) ) return;
    while ( $row = c_sql::fetch_object($result) ) {   
      $return .= "<div class='tr'>";
        $return .= "<div class='td right pr1em'>";
          $return .= $row->config_name.":";
        $return .= "</div>";
        $return .= "<div class='td left'>";
           $return .= "<span class='pointer iglow4' id='f_$row->config_name' me sb soe='edit_site_info' size='16ch' sot='text' sop='attributes[]' sov='$row->config_name' son='$row->config_name'>".$row->config_value."</span>";
//           $return .= "<input class='h1em' type='text' id='$row->config_name' name='$row->config_name' soe sf='update_config_entry' value='$row->config_value' style='width: 20rem;' onchange='key_change( this )' />";
        $return .= "</div>";
      $return .= "</div>";
    }

    $return .= "</div>";
    $return .= "</fieldset>";

    return $return;
    $return = "<h1>$sentences->SITE_INFORMATION</h1>";
    $return .= "<div class='rtable'>";
    $return .= "<div class='rtd'>";
    $return .= "<p class='center'>$sentences->SITE_INFORMATION_MESSAGE<p>";
//    $return .= "<p class='content_subtitle caps'>Paypal:</p>";
    $return .= "<div class='center'><div class='inline'>";
      $return .= "<div class='table fixed'>";


die("aqui");

        $query = "SELECT * FROM tb_config WHERE config_editable=true ORDER BY config_name ASC";
        if ( !($result = c_sql::select($query)) ) return;
        while ( $row = c_sql::fetch_object($result) ) {   
          $return .= "<div class='tr'>";
            $return .= "<div class='td right pr1em'>";
              $return .= $row->config_name.":";
            $return .= "</div>";
            $return .= "<div class='td left'>";
               $return .= "<input class='h1em' type='text' id='$row->config_name' name='$row->config_name' value='$row->config_value' style='width: 20rem;' onchange='key_change( this )' />";
            $return .= "</div>";
            $return .= "<div class='td left middle'>";
              $return .= "<img src='/pictures/checked.png' id='save_$row->config_name' onClick=\"save( this.parentNode.previousSibling.firstChild )\" class='pointer h1em pl1em' />";
            $return .= "</div>";
          $return .= "</div>";
       }

      $return .= "</div>";
    $return .= "</div></div>";



    $return .= "</div></div>";


    return $return;
  }
  static function process_ajax ( ) {
    $page = c_page::get_instance();
    $data =  new static;

    if ( isset($_POST) && !empty($_POST) ) {
      if ( isset($_POST['f_name']) ) {
        switch($_POST['f_name']) {
          case "edit_site_info":
            if ( !c_user::has_permission("SITE_INFORMATION") ) break;

            if ( !isset($_POST['attributes']) || !is_array($_POST['attributes']) ) break;
              foreach($_POST['attributes'] as $attribute) {
              $value = c_sql::escape_string(trim($_POST[$attribute]));
              $query = "UPDATE tb_config SET config_value='$value' WHERE config_name='".c_sql::escape_string($attribute)."'";
              $obj = c_sql::select($query);
              $error = c_sql::get_last_error();
              if ( empty($error) ) {
                $page->page->xml = array("element"=>array(array("name"=>"$attribute"."_message","value"=>"done"),array("name"=>"f_$attribute","original"=>$_POST[$attribute]),array("name"=>"save_$attribute","operation"=>"change_class","value"=>"disabled_button"),array("name"=>"f_$attribute","rme"=>true,"sleep"=>"1000")));
              }
            }
            break;
          default:
            break;
        }
        //$page->page->xml = array("element"=>array(array("name"=>"bar","value"=>"$menu")) );
//        $page->page->xml = array("reload"=>true);
      }
    }
  }

}
?>
