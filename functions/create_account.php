<?php
class sf_create_account {

  static function process ( $page, $filter ) {
    $sentences = sc_sentences::get_sentence_sequency($filter->menu);
    $page->title = $sentences->{strtoupper($filter->menu)};
    $page->ca = "";
    $message = array();
 
    $data = new c_accounts();

    if ( isset($_POST) && !empty($_POST) ) {
      $data->fullname = isset($_POST['fullname']) && !empty($_POST['fullname']) ? c_sql::real_escape_string(trim($_POST['fullname'])) : false;
      $data->username = isset($_POST['username']) && !empty($_POST['username']) ? c_sql::real_escape_string(sc_sentences::strtolower(trim($_POST['username']))) : false;
      $data->email = isset($_POST['email']) && !empty($_POST['email']) ? c_sql::real_escape_string(sc_sentences::strtolower(trim($_POST['email'],' .'))) : false;
      $data->birthdate = isset($_POST['birthdate']) && !empty($_POST['birthdate']) ? c_sql::real_escape_string(trim($_POST['birthdate'])) : false;
//      $data->agree_to_terms = isset($_POST['agree_to_terms']) && !empty($_POST['agree_to_terms']) && $_POST['agree_to_terms'] == "on" ? true : false;
//      $data->country_restrictions = isset($_POST['country_restrictions']) && !empty($_POST['country_restrictions']) && $_POST['country_restrictions'] == "on" ? true : false;
      $message = c_accounts::create_account( $data );
      if ( empty($message) ) {
        $page->ca = "<div style='text-align: center'>".$sentences->SUCCESFULLY_CREATED_ACCOUNT."</div>";
        return;
      }
    }
    $url = sc_page::convert_object_to_url($filter);
    $page->ca = "";
    if ( isset($message) && !empty($message) ) {
      $page->ca .= sc_page::create_message( $message );
    }

    $page->ca .= "<div style='margin: 50px 0px 0px 0px;'>";
    $page->ca .= "<form name='create_account' method='post' action='/?filter=$url' class='center'>";
    $page->ca .= "<div class='center auto caps' style='margin-bottom: 15px;'>$sentences->CREATE_ACCOUNT_MESSAGE</div>";


    $table = new sc_table();
    sc_table::create_simple_list( $filter, $table );
    $table->content = "";
    $table->content .= "<div class='table_row'>";
      $table->content .= "<div class='table_cell noselect cursor right caps bottom' style='padding: 0px 5px;'>$sentences->FULLNAME:</div>";
      $table->content .= "<div class='table_cell left' style='width: 50%; padding: 0px 3px;'><div class='border input'><input type='text' autofocus autocomplete='off' size='32' maxlength='128' name='fullname' class='transparent' value='$data->fullname' /></div></div>";
    $table->content .= "</div>";
    $table->content .= "<div class='table_row'>";
      $table->content .= "<div class='table_cell noselect cursor right caps bottom' style='padding: 0px 5px;'>$sentences->USERNAME:</div>";
      $table->content .= "<div class='table_cell left' style='width: 50%; padding: 0px 3px;'><div class='border input'><input type='text' autocomplete='off' size='24' maxlength='32' name='username' class='transparent' value='$data->username' /></div></div>";
    $table->content .= "</div>";
    $table->content .= "<div class='table_row'>";
      $table->content .= "<div class='table_cell noselect cursor right caps bottom' style='padding: 0px 5px;'>$sentences->EMAIL:</div>";
      $table->content .= "<div class='table_cell left' style='width: 50%; padding: 0px 3px;'><div class='border input'><input type='text' autocomplete='off' size='24' maxlength='32' name='email' class='transparent' value='$data->email' /></div></div>";
    $table->content .= "</div>";
    $table->content .= "<div class='table_row'>";
      $table->content .= "<div class='table_cell noselect cursor right caps bottom' style='padding: 0px 5px;'>$sentences->BIRTHDATE ($sentences->BIRTHDATE_FORMAT):</div>";
      $table->content .= "<div class='table_cell left' style='width: 50%; padding: 0px 3px;'><div class='border input'><input type='date' size='10' autocomplete='off' name='birthdate' min='".date("Y-m-d",$data->max_birthdate)."' max='".date("Y-m-d",$data->min_birthdate)."' class='transparent' value='$data->birthdate' /></div></div>";
    $table->content .= "</div>";
    $page->ca .= $table->start.$table->content.$table->end;

/*    $page->ca .= "<div class='table' style='margin-top: 5px'>";
      $page->ca .= "<div class='table_row'>";
        $page->ca .= "<div class='table_cell auto caps'><input class='transparent pointer' type='checkbox' id='agree_to_terms' name='agree_to_terms'></div>";
        $page->ca .= "<div class='table_cell auto caps pointer noselect'><label class='pointer' for='agree_to_terms'>$sentences->AGREE_TO_TERMS</label></div>";
      $page->ca .= "</div>";
      $page->ca .= "<div class='table_row'>";
        $page->ca .= "<div class='table_cell auto caps'><input class='transparent pointer' type='checkbox' id='country_restrictions' name='country_restrictions'></div>";
        $page->ca .= "<div class='table_cell auto caps pointer noselect'><label class='pointer' for='country_restrictions'>$sentences->COUNTRY_RESTRICTION</label></div>";
      $page->ca .= "</div>";
    $page->ca .= "</div>";
*/
    $page->ca .= "<div class='center auto' style='margin-top: 20px'>";
      $page->ca .= "<label class='pointer button caps'>$sentences->CREATE_ACCOUNT<input id='send' name='send' type='button' onClick=\"send_form( this.form )\" class='invisible' value=''></label>";
      if ( isset($filter->bmenu) && !empty($filter->bmenu) ) $url = $filter->bmenu;
      else $url = sc_page::clean_filter($filter);
      $curl = "/?filter=".sc_page::convert_object_to_url($url);
      $page->ca .= "<label class='pointer button caps' style='margin-left: 5px'><label>$sentences->CANCEL<input type='button' onClick=\"loadpage( '$curl' )\" class='invisible'></label>";
    $page->ca .= "</div>";
    
    $page->ca .= "</form>";
    $page->ca .= "</div>";
  }
}
?>
