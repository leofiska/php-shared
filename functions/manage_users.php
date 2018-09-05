<?php
class f_manage_users {

  private static $sentences;

  public static function process() {
    $page = c_page::get_instance();
    if ( c_user::has_permission("MANAGE_USERS") ) {
      $content = self::get_section();
      $page->page->middle .= "<section class='center' id='".substr(strtoupper(strchr(get_called_class(),"_")),1)."'>$content</section>";
    }
  }

  public static function get_section( $sentences = null ) {
    if ( $sentences == null ) $sentences = sc_sentences::get_sentence_sequency(substr(strtoupper(strchr(get_called_class(),"_")),1));

    $filter = sc_page::getFilter();
    $return = "";
    $url = c_page::get_clean_filter();
    $url->page = $filter->page;
    $url = c_page::convert_object_to_url($url);
    self::$sentences = $sentences;
//    $return = "<h1>$sentences->USERS</h1>";
    $return .= "<form onsubmit='event.preventDefault();' action='/$url'>";
    $return .= "<div>";
      $return .= "<div class='inline top'>";
        $return .= "<div class='left'>";
          $return .= "<div class='inline pr1em middle'><span class='inline middle lowercase'>$sentences->SEARCH</span>";
          $return .= isset($sentences->SEARCH_HELP) ? "<span class='help' title='$sentences->SEARCH_HELP'>(?)</span>" : "";
          $return .= "<span class='inline middle'>:</span></div>";
        $return .= "</div>";
        $return .= "<div>";
          $return .= "<div class='inline middle'><input autofocus placeholder='$sentences->TYPE_HERE' class='lowercase' lowercase type='text' value='' name='filter' poe style='width: 39ch;' id='address'/></div>";
        $return .= "</div>";
        $return .= "<div>";
          $return .= "<label class='inline pr1em middle'><span class='inline middle iglow4 pointer lowercase'>$sentences->FILTER</span>";
          $return .= isset($sentences->FILTER_HELP) ? "<span class='help' title='$sentences->FILTER_HELP'>(?)</span>" : "";
          $return .= "<input type='button' value='filter' notify='network_message' default class='invisible' onClick=\"send_ajax_form( this.form, this)\"/>";
          $return .= "</label>";
          $return .= "</label>";
        $return .= "</div>";
      $return .= "</div>";
    $return .= "</div>";
    $return .= "</form>";
    $return .= "<div>";
    $return .= "<div class='message' id='users_message'></div>";
    $return .= "</div>";

    $return .= self::create_items( $sentences );
    return $return;
  }


  public static function process_ajax() {
    $page = c_page::get_instance();
    $data =  new static;

    if ( isset($_POST) && !empty($_POST) ) {
      if ( !isset($_POST['f_name']) ) return;
      foreach( $_POST AS $index => $value ) {
        if ( isset($data->{trim($index)}) ) $data->{trim($index)} = trim($value);
      }
      if ( isset($_POST['f_name']) ) {
        switch($_POST['f_name']) {
          case "more":
            $result = "";
            break;
          case "keep_alive":
            $result = array();
            if ( isset($_POST['items']) && !empty($_POST['items']) ) $result = array_merge($result,self::refresh_queue( $_POST['items'] ));
            if ( isset($_POST['users_list']) ) $result = array_merge($result,self::update_items_list( $_POST['task_list'], @$_POST['task_list_order'] ));
            define("AJAX_OK2",true);
            sleep(1);
            break;
          case "add_user":
            self::clear_history( );
            $result[] = array("name"=>"task_list","value"=>"","operation"=>"replace");
            $result[] = array("name"=>"network_message","value"=>"");
            define("AJAX_OK2",true);
            break;
          default:
            //$result = c_scanner::schedule( $_POST['f_name'], $data );
            break;
        }
        if ( defined("AJAX_OK") ) {
          $return = array();
          foreach($result as $index=>$value) {
            $return[] = array("name"=>"task_list","operation"=>"add_before", "fit"=>"", "id"=>"task_$index", "value"=>"<div><div id='task_$index'>$value</div></div>");
          }
          $return[] = array("name"=>"network_message","value"=>c_sentences::get_sentence("TASK_ADDED"));
          $return[] = array("name"=>"address","value"=>"");
          $return = array_reverse($return);
          $page->page->xml = array("element"=>$return);
        } elseif ( defined("AJAX_OK2") ) {
          $page->page->xml = array("element"=>$result);
        } else {
          $page->page->xml = array("element"=>array(array("name"=>"network_message","value"=>$result)));
        }
      }
    }
  }

  public static function create_items( $sentences = null ) {
    if ( $sentences == null ) $sentences = c_sentences::get_sentence_sequency("NETWORK_TOOLS");
    $return = "<div id='USERS_LIST' ku p>";
    $return .= "<div class='slist block_left w100'>";
    $return .= "<div class='top_right panel'>";
    if ( sc_user::has_permission("ADD_USER") ) {
      $return .= "<div class='inline middle'><span soc sf='clear_history' class='vmiddle pointer glow4'>$sentences->ADD_USER</span>";
      $return .= isset($sentences->ADD_USER_HELP) ? "<span class='help' title='$sentences->ADD_USER_HELP'>(?)</span>" : "";
      $return .= "</div>";
    }
    $return .= "</div>";

    $return .= "<div id='task_list'>";
    $attributes = sc_page::getUserAttributes();
//    $query = "SELECT * FROM tb_tasks WHERE task_hidden = false AND task_parameters->>'type'='network' AND (task_global_id='".c_user::getGlobalID()."'";

    $query = "SELECT user_id, user_nickname, user_firstname, user_lastname, user_primaryemail, user_language, user_fullname, hstore_to_json(user_attributes) as user_attributes FROM tb_users tu ORDER BY user_lastname ASC, user_firstname ASC";
    $result = c_sql::select($query);

    while ( ($item = c_sql::fetch_object($result)) ) {
      $return .= self::create_item( $item, $sentences );
    }

    $return .= "</div></div>";
    $return .= "</div>";
    return $return;
  }

  public static function create_item( $item, $sentences = null, $new = false ) {
    if ( empty($item) ) return;
    if ( $sentences == null ) $sentences = c_sentences::get_sentence_sequency(substr(strtoupper(strchr(get_called_class(),"_")),1));
    $item->user_attributes = json_decode($item->user_attributes);
    $return = "<div><div id='user_$item->user_id' class='ph02em pv02em'";
    if ( $new ) {
      $return .= " new_item onmouseover=\"if ( this.removeAttribute('new_item') != undefined ) this.removeAttribute('new_item')\">";
    } else {
      $return .= ">";
    }
    $first_row = "<div class='mw2em center pointer glow4 td pr02em middle' onclick=\"toogle_visibility(this.parentNode.nextSibling.childNodes[1].firstChild, this.firstChild )\" title='$sentences->CLICK_TO_EXPAND_OR_COLLAPSE'>";
      $first_row .= "<div class='shadow colexp";
      if ( defined("AJAX") && !$new ) {
        $first_row .= " d90";
      }
      $first_row .= "'>></div>";
    $first_row .= "</div>";
    $first_row .= "<div class='td middle w100'>";
      $first_row .= "<p>$item->user_lastname, $item->user_firstname</p>";
    $first_row .= "</div>";
    $second_row = "<div class='td'>";
    $second_row .= "</div>";
    $second_row .= "<div class='td middle'>";
    $second_row .= "<div tv='height' af='height' class='pt02em he";
    if ( defined("AJAX") && !$new ) {
      $second_row .= " expanded'";
    } else {
      $second_row .= " collapsed' style='height: 0px;'";
    }    
    $second_row .= ">";
    foreach($item->user_attributes as $index => $attr) {
      $second_row .= "<span>$index: $attr</span>";
    }
    $second_row .= "</div>";
    $second_row .= "</div>";
    $return .= "<div class='table w100'>";
    $return .= "<div class='tr'>";
      $return .= $first_row;
    $return .= "</div>";
    $return .= "<div class='tr'>";
      $return .= $second_row;
    $return .= "</div>";
    $return .= "</div>";
    return $return;
  }

  public static function update_items_list( $task_list, $order ) {
    $return = array();
    $task_list = explode(",",$task_list);
    $max = max($task_list);
    $sentences = c_sentences::get_sentence_sequency("NETWORK_TOOLS");
    switch($order) {
      default:
        $query = "WITH A AS (
                    SELECT * FROM tb_tasks tt WHERE task_id > '".c_sql::escape_string(intval($max))."' AND task_hidden = false AND task_parameters->>'type'='network' AND (tt.task_global_id='".c_user::getGlobalID()."'";
        if ( sc_page::is_user_logged() ) {
          $query .= " OR tt.task_user_id='".c_user::getID()."'";
        }
        $query .= ") ORDER BY task_id ASC LIMIT 20
                  ), B AS (
                    SELECT task_status_id,task_status_alias,sentence_value FROM tb_task_status tts JOIN tb_sentences ts ON tts.task_status_sentence=ts.sentence_id
                  )
                  SELECT *,
                         (SELECT json_build_object('alias',task_status_alias,'sentence',sentence_value) FROM tb_relation_task_status trts JOIN B b ON b.task_status_id=trts.trts_task_status_id WHERE trts.trts_task_id=tt.task_id ORDER BY trts_time DESC LIMIT 1) as task_status
                  FROM A tt";
        break;
    } 
    $result = c_sql::select($query);
    $index = $task_list[0];
    while( $row = c_sql::fetch_object($result) ) {
      $return[] = array("name"=>"task_list","operation"=>"add_before", "dd"=>true, "fit"=>"", "id"=>"task_".$row->task_id,"value"=>"".self::create_task_item($row,$sentences,true)."");
      $index = $row->task_id;
    }

    $objs = "";
    foreach($task_list as $item) {
      if ( !empty($objs) ) $objs .= " OR ";
      $objs .= "task_id=".c_sql::escape_string(intval($item));
    }

    $query = "SELECT * FROM tb_tasks tt WHERE ($objs) AND (task_hidden=true OR ( (tt.task_global_id!='".c_user::getGlobalID()."' OR tt.task_global_id IS NULL)";
    if ( sc_page::is_user_logged() ) {
      $query .= " AND (tt.task_user_id!='".c_user::getID()."' OR tt.task_user_id IS NULL)";
    }
    $query .= ") )";
    $result = c_sql::select($query);
    while( $row = c_sql::fetch_object($result) ) {
      $return[] = array("name"=>"task_$row->task_id","operation"=>"remove_parent","value"=>"");
    }

    return $return;
  }


  public $address;

  function __construct() {
    $this->address = "";
  }

}
?>
