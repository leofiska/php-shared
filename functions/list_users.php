<?php
class sf_list_users extends sc_function {

  static function process ( ) {
    $page = c_page::get_instance();
    $filter = $page->get_filter();
    $data = parent::process( $page, $filter );
    $table = new sc_table();
    $table->content = "";
    $table->title = @self::$sentences->LIST_OF_USERS;
    $table->add_command( "top", "edit_line", self::$sentences->EDIT, false );
    $table->form = "users";
    $table->add_header( 'checkbox', '', 30 );
    $table->add_header( 'username', self::$sentences->USERNAME, 200 );
    $table->add_header( 'name', self::$sentences->NAME, 200 );
    $table->add_header( 'email', self::$sentences->EMAIL, 200 );
    $table->add_header( 'last_seen', self::$sentences->LAST_SEEN, 200 );
    $table->add_header( 'status', self::$sentences->STATUS, 100 );
    sc_table::create_simple_list( $filter, $table );

    $result = $data->get_result( $page, $filter, $table );
    if ( ($result) ) {
      while( ($user = c_sql::fetch_object($result)) ) {
        $table->content .= "<label class='table_row table_row_item pointer'>";
        $table->content .= "<div class='left middle'><input class='pointer' type='checkbox' name='id[$user->vu_user_id]' onClick=\"toogle_select_all( event, this.form )\" /></div>";
        $table->content .= "<div class='left top padding-left10'>$user->vu_username</div>";
        $table->content .= "<div class='left top padding-left10'>$user->vu_fullname</div>";
        $table->content .= "<div class='left top padding-left10'>$user->vu_email</div>";
        $table->content .= "<div class='left top padding-left10'>$user->vu_last_seen</div>";
        $table->content .= "<div class='left top padding-left10'>$user->vu_status</div>";
        $table->content .= "</label>";
      }
    }
    $table->content .= "<input type='hidden' name='edit_line_single'>";
    $table->content .= "<input type='hidden' name='f' value='edit_account'>";
    $page->ca .= $table->start.$table->content.$table->end.$table->page;
  }

  function __construct() {
    parent::__construct();
    $this->search_column = "vu_search";
    $this->table = "v_users";
    $this->default_order_by = "NAME_ASC";
    $this->search = "";
    $this->orders_by = array();
    $this->orders_by["NAME_ASC"] = "ORDER BY vu_fullname ASC, vu_last_seen ASC";
    $this->orders_by["NAME_DESC"] = "ORDER BY vu_fullname DESC, vu_last_seen ASC";
    $this->orders_by["USERNAME_ASC"] = "ORDER BY vu_username ASC, vu_last_seen ASC";
    $this->orders_by["USERNAME_DESC"] = "ORDER BY vu_username DESC, vu_last_seen ASC";
    $this->orders_by["EMAIL_ASC"] = "ORDER BY vu_email ASC, vu_last_seen ASC";
    $this->orders_by["EMAIL_DESC"] = "ORDER BY vu_email DESC, vu_last_seen ASC";
    $this->orders_by["LAST_SEEN_ASC"] = "ORDER BY vu_last_seen ASC, vu_fullname ASC";
    $this->orders_by["LAST_SEEN_DESC"] = "ORDER BY vu_last_seen DESC, vu_fullname ASC";
    $this->orders_by["STATUS_ASC"] = "ORDER BY vu_status ASC, vu_last_seen ASC";
    $this->orders_by["STATUS_DESC"] = "ORDER BY vu_status DESC, vu_last_seen ASC";
    $this->order_by = $this->orders_by["NAME_ASC"];
    $this->filters = array();
    $this->filters['or_1'] = "vu_status_alias";
  }
}
?>
