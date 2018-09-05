<?php
class sc_function {

  static protected $sentences;
  static protected $page;
  static protected $filter = true;

  static function process ( ) {
    self::$page = c_page::get_instance();
    self::$filter = self::$page->get_filter();
    if ( isset(self::$filter->{get_called_class()}) ) {
      self::$filter = self::$filter->{get_called_class()};
    } else {
      self::$filter->{get_called_class()} = new STDClass();
      self::$filter = self::$filter->{get_called_class()};
    }
    if ( preg_match("/^f_(.+)$/i", get_called_class(), $out) ) {
      self::$filter->page = strtoupper(trim($out[1]));
    }

    self::$sentences = sc_sentences::get_sentence_sequency(self::$filter->page);
    self::$page->ca = "";

    $data =  new static;

    if ( isset(self::$filter->order_by) && !empty(self::$filter->order_by) ) {
      $data->set_order_by(self::$filter->order_by);
    } else {
      $tmp = $data->get_default_order_by();
      if ( !empty($tmp) )  self::$filter->order_by = $data->get_default_order_by();
      unset($tmp);
    }
    if ( $_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['search']) ) {
      sc_filter::parse( $filter );
      $url = sc_page::get_host_url()."/?filter=".sc_page::convert_object_to_url($filter);
      header( "Location: $url" );
      die();
    } elseif ( $_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['f']) ) {
      $url = sc_page::clean_filter($filter);
      $url->bmenu = sc_page::clone_filter( $filter );
      if ( isset($url->bmenu->post) ) unset($url->bmenu->post);
      if ( isset($_POST['command']) && !strstr($_POST['command'],"_line") ) {
        $url->menu = $_POST['command'];
      } else {
        $url->menu = $_POST['f'];
      }
      if ( isset($_POST['id']) ) {
        if ( is_array($_POST['id']) ) {
          foreach( $_POST['id'] as $id => $state ) {
            $url->id[] = $id;;
          }
        } else {
          $url->id[] = $_POST['id'];
        }
      }
      header( "Location: ".sc_page::get_host_url()."/?filter=".sc_page::convert_object_to_url($url)  );
      die();
    }
    return $data;
  }

  protected $search_column;
  protected $table;
  protected $search;
  protected $orders_by;
  protected $order_by;
  protected $default_order_by;
  protected $filters;
  protected $base_where;

  function __construct() {
    $this->search_column = "";
    $this->table = "";
    $this->search = "";
    $this->order_by = "";
    $this->default_order_by = "";
    $this->orders_by = array();
    $this->filters = array();
    //$this->base_where = "language_code=".c_page::get_language();
    $this->base_where = "1=1";
  }

  protected function set_order_by ( $order_by ) {
    if ( isset($this->orders_by[strtoupper($order_by)]) ) $this->order_by = $this->orders_by[strtoupper($order_by)];
  }

  protected function get_default_order_by() {
    return $this->default_order_by;
  }
  protected function get_query_order_by() {
    return " ".$this->order_by;
  }
  protected function get_query( $page, $filter ) {
    $user = c_user::get_instance();
    $attributes = $user->get_attributes();
    $where = (isset($filter->post) && isset($filter->post->search)) ? $this->get_query_where($filter->post->search, $this->search_column) : "";
    $where .= $this->get_filter_items($filter);
    $query = "SELECT * FROM $this->table WHERE $this->base_where".$where.$this->get_query_order_by();
    $start = ( isset($filter->page) && $filter->page > 1 ) ? ( ($filter->page-1) * $attributes->items_per_page ) : 0;
    $query .= " LIMIT $attributes->items_per_page OFFSET $start";
    return $query;
  }
  protected function get_result( $page, $filter, $table ) {
    $user = c_user::get_instance();
    $attributes = $user->get_attributes();
    $where = (isset($filter->post) && isset($filter->post->search)) ? $this->get_query_where($filter->post->search, $this->search_column) : "";
    $where .= $this->get_filter_items($filter);
    $query = "SELECT * FROM $this->table WHERE $this->base_where".$where.$this->get_query_order_by();
    $start = ( isset($filter->page) && $filter->page > 1 ) ? ( ($filter->page-1) * $attributes->items_per_page ) : 0;
    $query .= " LIMIT $attributes->items_per_page OFFSET $start";
    $table->page = sc_page::create_page_nav( $filter, $query );
    $start_microtime = microtime(true);
    if ( isset($_GET['debug']) && !empty($_GET['debug']) ) die($query);
    $result = c_sql::select($query);
    $this->fill_search_time( self::$page, $filter, $query, $start_microtime );
    return $result;
  }

  protected function get_query_where( $search, $columns, $type = "AND", $exact = false ) {
    if ( empty($search) ) return "";
    if ( empty($columns) ) return "";
    $out = "";
    if ( !is_array($columns) ) $columns = explode(" ",$columns);
    if ( !is_array($search) ) $search = explode(" ",sc_parser::parse_alphanumeric($search, " _-!"));
    $search = array_unique(array_filter($search));
    foreach($search as $str) {
      $tmp = "";
      foreach( $columns as $column ) {
        if ( !empty($tmp) ) $tmp .= " OR ";
        if ( $str[0] == "!" ) {
          if ( empty($exact) ) {
            $tmp .= "$column NOT LIKE '%".c_sql::real_escape_string(trim($str,"!"))."%'";
          } else {
            $tmp .= "$column!='".c_sql::real_escape_string(trim($str,"!"))."'";
          }
        } else {
          if ( empty($exact) ) {
            $tmp .= "$column LIKE '%".c_sql::real_escape_string($str)."%'";
          } else {
            $tmp .= "$column='".c_sql::real_escape_string($str)."'";
          }
        }
      }
      if ( !empty($tmp) && !empty($out) ) $out .= " $type ";
      $out .= "($tmp)";
    }
    if ( !empty($out) ) { 
      return " AND ($out)";
    } else {
      return "";
    }
  }
  protected function get_filter_items( $filter ) {
    if ( !isset($filter->post) ) return "";
    $output = array();
    $out = "";
    foreach($filter->post as $index => $value) {
      if ( !preg_match("/^(:?(and|or)_[0-9]+)\|(.+)$/Ui", $index, $out) ) continue;
      $output[$out[1]][] = $out[3];
    }
    $out = "";
    foreach($output as $index => $value) {
      if ( !isset($this->filters[$index]) ) continue;
      $type = stristr($index,"OR") ? "OR" : "AND";
      if ( !empty($out) ) $out .= " AND ";
      $out .= $this->get_query_where( $value, $this->filters[$index], $type, true );
    }
    if ( !empty($out) ) return "$out";
    else return "";
  }
  protected function fill_search_time( $page, $filter, $query, $start_microtime ) {
    $user = c_user::get_instance();
    $attributes = $user->get_attributes();
    $start = ( $filter->page > 1 ) ? ( ($filter->page-1) * $attributes->items_per_page ) : 0;
    $search_result = new STDClass();
    $search_result->time = $start_microtime;
    $search_result->time = number_format((microtime(true)-$search_result->time),3,".",",");
    $search_result->items = c_sql::count_all($query);
    $search_result->first = ($start+1);
    $search_result->last = ($start+$attributes->items_per_page);
    if ( $search_result->last > $search_result->items ) $search_result->last = $search_result->items;
    if ( isset($search_result->items) ) {
      $str = str_replace("[LAST]",$search_result->last,str_replace("[FIRST]",$search_result->first,str_replace("[ITEMS]",$search_result->items,@str_replace("[SEC]",$search_result->time,self::$sentences->SEARCH_RESULT))));
      if ( empty($search_result->items) ) $str = substr($str,0,strrpos($str,"."));
    } else {
      $str = str_replace("[SEC]",$search_result->time,$sentences->SEARCH_RESULT);
      $str = substr($str,0,strrpos($str,"."));
    }
    $page->ca .= "<div>$str</div>";
  }
}
?>
