<?php
class sf_site_menu {

  function __construct() {
  }

  static function get_section() {
 return;
    $page = c_page::get_instance();
    $config = $page->get_config();

    $sentences = sc_sentences::get_sentence_sequency("SITE_MENU");

    $query = "SELECT language_codeset, language_name->'".c_page::get_language()."' as language_name FROM tb_languages where language_wui=true";
    $result = c_sql::select($query);
    while( $obj = c_sql::fetch_object($result) ) {
      $languages[$obj->language_codeset] = (object)array("code"=>$obj->language_codeset,"name"=>$obj->language_name);
    }
    $query = "SELECT menu_id, menu_alias, hstore_to_json(menu_sentence) as menu_sentence FROM v_menu WHERE menu_parent is null AND (menu_function='posts' OR menu_function IS NULL OR menu_function='')";
    $result = c_sql::select($query);

    $url = c_page::get_clean_filter();
    $url->page = "SITE_CONFIGURATION";
    $url = c_page::convert_object_to_url($url);

    $return = "<div style='display: table; width: 70vw; padding: 0 15vw; height: 60px; background-color: ".$config->{"background-color"}.";'>";
      $return .= "<div style='display: table-row'>";
      $return .= "<div style='display: table-cell; letter-spacing: 1px; color: #e5e5e5; width: 99%; padding: 0 10px;' class='middle uppercase'></div>";
      while( $m1 = c_sql::fetch_object($result) ) {
        $lang = (array)json_decode((str_replace('", "','","',str_replace('": "','":"',$m1->menu_sentence))));
        $langs = array();     
        foreach($lang as $index => $value ) {
          $langs[intval($index)] = $value;
        }
        unset($lang);
        $m1->menu_sentence = $langs;
        unset($langs);
        $return .= "<div style='display: table-cell; letter-spacing: 1px; color: #e5e5e5; padding: 0 10px;' class='middle uppercase'><span soe='update_menu' sop='menu' sov='$m1->menu_id' sol='".c_page::get_language()."' son='name'  class='text uppercase' me>".$m1->menu_sentence[c_page::get_language()]."</span></div>";
      }
      $return .= "</div>";
    $return .= "</div>";

    return $return;

    $return .= "<fieldset class='inline border p1em'>";
    $return .= "<legend class='uppercase bold'>o$sentences->SITE_MENU</legend>";    

    $query = "SELECT language_codeset, language_name->'".c_page::get_language()."' as language_name FROM tb_languages where language_wui=true";
    $result = c_sql::select($query);
    while( $obj = c_sql::fetch_object($result) ) {
      $languages[$obj->language_codeset] = (object)array("code"=>$obj->language_codeset,"name"=>$obj->language_name);
    }

    $query = "SELECT menu_id, menu_alias, hstore_to_json(menu_sentence) as menu_sentence FROM v_menu WHERE menu_parent is null AND (menu_function='posts' OR menu_function IS NULL OR menu_function='')";
    $result = c_sql::select($query);

    $return .= "<fieldset class='border p1em' style='background-color: #f5f5f5;'>";
    $return .= "<legend>".$sentences->FIRST_LEVEL_MENU."</legend>";

    $url = c_page::get_clean_filter();
    $url->page = "SITE_CONFIGURATION";
    $url = c_page::convert_object_to_url($url);


    while( $m1 = c_sql::fetch_object($result) ) {
      $lang = (array)json_decode((str_replace('", "','","',str_replace('": "','":"',$m1->menu_sentence))));
      $langs = array();     
      foreach($lang as $index => $value ) {
        $langs[intval($index)] = $value;
      }
      unset($lang);
      $m1->menu_sentence = $langs;
      unset($langs);



      $return .= "<fieldset class='border p1em m1em' style='background-color: #f0f0f0;'>";
      $return .= "<form onsubmit='event.preventDefault();' action='/$url'>";
      $return .= "<div class='table'>";
          $return .= "<div class='tr'>";
            $return .= "<div class='td right pr2px caps'>";
              $return .= $sentences->MENU_NAME.": ";
            $return .= "</div>";
            $return .= "<div class='bold td left'>";
              $return .= "<input dnas type='text' name='name[".c_page::get_language()."]' value='".$m1->menu_sentence[c_page::get_language()]."' />";
            $return .= "</div>";
          $return .= "</div>";
        $return .= "<div class='tr'>";
          $return .= "<div class='td right pr2px caps'>";
            $return .= "$sentences->TAG_NAME: ";
          $return .= "</div>";
          $return .= "<div class='td left'>";
            $return .= "<input dnas type='text' name='tag' value='".c_sentences::strtolower($m1->menu_alias)."' />";
          $return .= "</div>";
        $return .= "</div>";
        foreach($languages as $language) {
          if ( $language->code == c_page::get_language() ) continue;
          $return .= "<div class='tr'>";
            $return .= "<div class='td right pr2px'>";
              $return .= "$language->name: ";
            $return .= "</div>";
            $return .= "<div class='td left'>";
              $return .= "<input dnas type='text' name='name[$language->code]' value='".$m1->menu_sentence[$language->code]."' />";
            $return .= "</div>";
          $return .= "</div>";
        }
      $return .= "</div>";
      $return .= "<input type='hidden' name='menu' value='$m1->menu_id' />";
      $return .= "<label class='glow4 pointer caps ph1em'>$sentences->UPDATE<input type='button' onClick=\"send_ajax_form( this.form, this)\" class='invisible' value='update_menu' /></label>";
      $return .= "<label class='glow4 pointer caps'>$sentences->DELETE<input type='button' onClick=\"send_ajax_form( this.form, this)\" class='invisible' value='delete_menu' /></label>";
      $return .= "</form>";
      $return .= "<div id='message_$m1->menu_id' class='message'></div>";
      $return .= "</fieldset>";

    }
    $return .= "<fieldset class='border p1em m1em'>";
    $return .= "<form onsubmit='event.preventDefault();' action='/$url'>";
    $return .= "<div class='table'>";
      $return .= "<div class='tr'>";
        $return .= "<div class='td right pr2px caps'>";
          $return .= $sentences->MENU_NAME.": ";
        $return .= "</div>";
        $return .= "<div class='bold td left'>";
          $return .= "<input dnas type='text' name='name[".c_page::get_language()."]' value='' />";
        $return .= "</div>";
      $return .= "</div>";
      $return .= "<div class='tr'>";
        $return .= "<div class='td right pr2px caps'>";
          $return .= "$sentences->TAG_NAME: ";
        $return .= "</div>";
        $return .= "<div class='td left'>";
          $return .= "<input dnas type='text' name='tag' value='' />";
        $return .= "</div>";
      $return .= "</div>";
      foreach($languages as $language) {
        if ( $language->code == c_page::get_language() ) continue;
        $return .= "<div class='tr'>";
          $return .= "<div class='td right pr2px'>";
            $return .= "$language->name: ";
          $return .= "</div>";
          $return .= "<div class='td left'>";
            $return .= "<input dnas type='text' name='name[$language->code]' value='".$m1->menu_sentence[$language->code]."' />";
          $return .= "</div>";
        $return .= "</div>";
      }
    $return .= "</div>";
    $return .= "<label class='glow4 pointer caps'>$sentences->ADD<input type='button' onClick=\"send_ajax_form( this.form, this)\" class='invisible' value='add_menu' /></label>";
    $return .= "</form>";
    $return .= "</fieldset>";


    $return .= "</fieldset>";
  

    $return .= "</fieldset>";

    return $return;
  }
static function process_ajax ( ) {
    $page = c_page::get_instance();
    $data =  new static;
    
    if ( isset($_POST) && !empty($_POST) ) {


      if ( isset($_POST['f_name']) ) {
        switch($_POST['f_name']) {
          case "update_menu":
            $query = "SELECT * FROM tb_menu WHERE menu_id='".c_sql::escape_string($_POST['menu'])."' AND ( menu_function='posts' OR menu_function is null OR menu_function='')";
            $m1 = c_sql::get_first_object($query);
            if ( empty($m1) ) {
              return;
            }
            foreach($_POST['name'] as $index => $value) {
              $query = "UPDATE tb_sentences SET sentence_value=sentence_value||'\"".intval($index)."\"=>\"".c_sql::escape_string($value)."\"'::hstore WHERE sentence_id=$m1->menu_sentence_id";
              c_sql::select($query);
            }
//            $query = "UPDATE tb_menu SET menu_alias='".c_sql::escape_string(c_sentences::strtoupper($_POST['tag']))."' WHERE menu_id=$m1->menu_id";
//            c_sql::select($query);
            break;
          case "add_menu":

            break;
          case "delete_menu":

            break;
        }
        $menu = c_page::render_menu( );
        $page->page->xml = array("element"=>array(array("name"=>"bar","value"=>"$menu")) );
//        $page->page->xml = array("reload"=>true);
      }
    }
  }
}
?>
