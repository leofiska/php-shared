<?php
class sf_edit_account extends sc_function {
  public static function process( ) {

    $page = c_page::get_instance();
    $filter = $page->get_filter();

    self::$filter = false;
    $data = parent::process( $page, $filter );
    if ( !isset($filter->id) ||  !is_array($filter->id) || Count($filter->id) > 1 ) {
      header( "Location: ".sc_page::get_referer_url() );
      die();
    }
    if ( Count($filter->id) < 2 ) {
      $filter->id = intval($filter->id[0]);
      $data->base_where = "user_id=$filter->id";
    }
    $message = array();

    if ( isset($_POST) && !empty($_POST) && isset($_POST['f_name']) && !empty($_POST['f_name']) ) {
      $tmp = new STDClass();
      $query = "";
      if ( !isset($_POST['command']) ) $_POST['command'] = "";
      switch($_POST['f_name']) {
        case "edit_user_auth";
          $ids = array();
          foreach($_POST as $item => $value) {
            if ( (is_int($item)) && ($value == "on") ) {
              $ids[] = c_sql::real_escape_string(trim(intval($item)));
            }
          }
          switch($_POST['command']) {
            case "deactivate":
              if ( empty($ids) ) break 2;
              $query = "UPDATE tb_auth SET auth_enabled=b'0' WHERE 1=1 AND ";
              $out = "";
              foreach($ids as $id) {
                if ( !empty($out) ) $out .= " OR ";
                $out .= "auth_id=$id";
              }
              $query .= "( $out ) AND auth_user_id=".$filter->id;
              break;
          }
          break;
        case "edit_user":
          switch($_POST['command']) {
            case "close":
              $url = sc_page::clone_filter( $filter );
              if ( isset($url->bmenu) ) {
                $url = $url->bmenu;
              } else $url->menu = "users_list";
              $url = sc_page::get_host_url()."/?filter=".sc_page::convert_object_to_url($url);
              header( "Location: $url" );
              die();
              break;
            default:
            case "save":
              $tmp->items_per_page = c_sql::real_escape_string(intval(trim($_POST['items_per_page'])));
              if ( $tmp->items_per_page < 10 ) $tmp->items_per_page = 10;
              if ( $tmp->items_per_page > 200 ) $tmp->items_per_page = 200;
              $tmp->email = c_sql::real_escape_string(trim($_POST['email']));
              $tmp->hashed_email = c_user::generate_hashed_user(hash("sha256",$data->email));
              if ( !sc_email::is_valid_email($tmp->email) ) $message['email'] = sc_sentences::get_sentence("ERROR_VALID_EMAIL");
                if ( empty($message) ) {
                $query = "UPDATE tb_users SET user_primary_email='$tmp->email', user_hashed_primary_email='$tmp->hashed_email',  user_items_per_page='$tmp->items_per_page' WHERE user_id=$filter->id LIMIT 1";
              }
              break;
          }
          break;
          unset($tmp);
      }
      if ( $_POST['command'] == "change_password" ) {
        $url = sc_page::clean_filter($filter);
        $url->bmenu = sc_page::clone_filter( $filter );
        $url->menu = "change_password";
        header( "Location: ".sc_page::get_host_url()."/?filter=".sc_page::convert_object_to_url($url)  );
        die();
      }
      if ( isset($query) && !empty($query) ) c_sql::update($query);
      if ( empty($message) ) {
        header( "Location: ".sc_page::get_referer_url() );
        die();
      }
    }
    if ( empty($filter->id) ) {
      header( "Location: ".sc_page::get_referer_url() );
      die();
    }

      if ( empty($filter->id) ) {
        header( "Location: ".sc_page::get_referer_url() );
        die();
      }
      $query = "SELECT * FROM v_users WHERE vu_user_id=$filter->id AND language_code=".c_user::get_language_id()." LIMIT 1";
      if ( !($result = c_sql::select($query)) || c_sql::num_rows($result) < 1 ) {
        header( "Location: ".sc_page::get_host_url() );
        die();
      }
      $user = c_sql::fetch_object($result);
   
      $query = "SELECT language_original_name FROM tb_languages WHERE language_codeset=$user->vu_language LIMIT 1";
      if ( ($result2 = c_sql::select($query)) && c_sql::num_rows($result2) > 0 ) {
        $lang = c_sql::fetch_object($result2);
        $lang = $lang->language_original_name;
      } else {
        $lang = $user->language_code;
      }
      if ( !empty($user->vu_fullname) ) {
        $page->title .= " - $user->vu_fullname";
      } else {
        $page->title .= " - $user->vu_username";
      }

//    sc_page::kill($message);

      if ( isset($message) && !empty($message) ) {
        $page->ca = sc_page::create_message( $message );
      }

      $table = new sc_table();
      $table->set_border( "left", true );
      $table->set_border( "right", true );
      $table->add_command( "top", "save", self::$sentences->SAVE, true );
      $table->add_command( "top", "close", self::$sentences->CLOSE, true );
      $table->add_command( "bottom", "save", self::$sentences->SAVE, true );
      $table->add_command( "bottom", "close", self::$sentences->CLOSE, true );
      $table->form = "my_account";
      $table->title = self::$sentences->USER_INFORMATIONS;
      sc_table::create_simple_list( $filter, $table );



//    $commands = new STDClass(); $commands->global = array( (object)array("name"=>"save","selectable"=>true ),(object)array("name"=>"cancel","selectable"=>true ) );
//    $table = c_page::create_table( $filter, $sentences, $sentences->USER_INFORMATIONS, false, false, false, $commands, $commands, false );
//    unset($commands);


      $table->a = "";
      $table->a .= "<div class='table_row'>";
        $table->a .= "<div class='table_cell cell_padded right width50 noselect cursor'>".self::$sentences->FULLNAME.": </div>";
        $table->a .= "<div class='table_cell cell_padded left width50'>$user->vu_fullname</div>";
      $table->a .= "</div>";
      $table->a .= "<div class='table_row'>";
        $table->a .= "<div class='table_cell cell_padded right width50 noselect cursor'>".self::$sentences->USERNAME.": </div>";
        $table->a .= "<div class='table_cell cell_padded left width50'>$user->vu_username</div>";
      $table->a .= "</div>";
      $table->a .= "<div class='table_row'>";
        $table->a .= "<div class='table_cell cell_padded right width50 noselect cursor'>".self::$sentences->ITEMS_PER_PAGE.": </div>";
        $table->a .= "<div class='table_cell cell_padded left width50'><input type='number' name='items_per_page'  min='10' max='200' size='3' maxlength='3' onkeypress=\"return validate_number( event )\" value='$user->vu_items_per_page' /></div>";
      $table->a .= "</div>";
      $table->a .= "<div class='table_row'>";
        $table->a .= "<div class='table_cell cell_padded right width50 noselect cursor'>".self::$sentences->EMAIL.": </div>";
        $table->a .= "<div class='table_cell cell_padded left width50'><input type='email' id='email' name='email'  size='".(strlen($user->vu_email)+2)."' maxlength='64' onkeypress=\"return validate_email( event )\" value='$user->vu_email' /></div>";
      $table->a .= "</div>";
      $table->a .= "<div class='table_row'>";
        $table->a .= "<div class='table_cell cell_padded right width50 noselect cursor'>".self::$sentences->PASSWORD.": </div>";
        $table->a .= "<div class='table_cell cell_padded left width50'>$user->vu_password_set</div>";
      $table->a .= "</div>";
      $table->a .= "<input type='hidden' name='f_name' value='edit_user'>";
      $page->ca .= $table->start.$table->a.$table->end;


      $table = new sc_table();
      $table->width = "1200px";
      $table->set_border( "left", true );
      $table->set_border( "right", true );
      $table->add_command( "top", "deactivate", self::$sentences->DEACTIVATE, false );
      $table->form = "edit_user_auth";
      $table->title = self::$sentences->USER_SESSIONS;
      $table->add_header( 'checkbox', '', 30 );
      $table->add_header( 'ipaddress', self::$sentences->IPADDRESS, 100 );
      $table->add_header( 'last_seen', self::$sentences->LAST_SEEN, 150 );
      $table->add_header( 'expires_in', self::$sentences->EXPIRES_IN, 150 );
      $table->add_header( 'user_agent', self::$sentences->USER_AGENT, 400 );


      sc_table::create_simple_list( $filter, $table );



//    $titles = array( "checkbox", "IPADDRESS", "LAST_SEEN", "EXPIRES_IN", "USER_AGENT" );
//    $table = sc_page::create_table( $filter, $sentences, $sentences->USER_SESSIONS, false, $titles, false, $commands, false );
//    unset($commands); unset($titles);

    //$query = "SELECT * FROM v_auth WHERE user_id=".c_user::get_user_id();
    //$result = c_sql::select($query);
      $result = $data->get_result( $page, $filter, $table );
      $table->a = "";
      if ( $result && (c_sql::num_rows($result) > 0) ) {
        while ( $auth = c_sql::fetch_object($result) ) {
          $table->a .= "<label class='table_row table_row_item pointer'>";
            $table->a .= "<div class='left top'><input type='checkbox' name='$auth->auth_id' onClick=\"toogle_select_all( event, this.form )\" /></div>";
            $table->a .= "<div class='left top padding-left10'>$auth->auth_address</div>";
            $table->a .= "<div class='left top padding-left10'>$auth->auth_last_use</div>";
            $table->a .= "<div class='left top padding-left10'>".date("Y-m-d H:i:s",strtotime($auth->auth_last_use)+(30*24*60*60))."</div>";
            $table->a .= "<div class='left top padding-left10'>$auth->auth_agent</div>";
          $table->a .= "</label>";
        }
      }
      $table->a .= "<input type='hidden' name='f_name' value='edit_user_auth'>";
      $table->a .= "<input type='hidden' name='deactivate_any' value='true'>";
      $page->ca .= "<div style='height: 100px;'></div>".$table->start.$table->a.$table->end;
  }

  function __construct() {
    parent::__construct();
    $this->table = "v_auth";
    $this->default_order_by = "EXPIRES_IN_DESC";
    $this->orders_by = array();
    $this->base_where = "user_id=".c_user::get_user_id();
    $this->orders_by["EXPIRES_IN_ASC"] = "ORDER BY auth_last_use ASC, auth_address ASC";
    $this->orders_by["EXPIRES_IN_DESC"] = "ORDER BY auth_last_use DESC, auth_address ASC";
    $this->orders_by["IPADDRESS_ASC"] = "ORDER BY auth_address ASC, auth_last_use ASC";
    $this->orders_by["IPADDRESS_DESC"] = "ORDER BY auth_address DESC, auth_last_use ASC";
    $this->orders_by["LAST_SEEN_ASC"] = "ORDER BY auth_last_use ASC, auth_address ASC";
    $this->orders_by["LAST_SEEN_DESC"] = "ORDER BY auth_last_use DESC, auth_address ASC";
    $this->orders_by["USER_AGENT_ASC"] = "ORDER BY auth_agent ASC, auth_last_use ASC";
    $this->orders_by["USER_AGENT_DESC"] = "ORDER BY auth_agent DESC, auth_last_use ASC";
    $this->order_by = $this->orders_by["EXPIRES_IN_ASC"];
    $this->filters = array();
  }


}
?>
