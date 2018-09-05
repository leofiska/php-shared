<?php
class sc_filter {
  public static function create_filter_element( $filter ) {
    return;
    if ( empty($filter) ) return; 
    $search = "";
    

    if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
      $filter->post = sc_page::convert_post_to_object($_POST);
    }
    if ( isset($filter->post) ) {
      if ( isset($filter->post->search) ) $search = trim($filter->post->search);
    }
  
    $sentences = sc_sentences::get_sentence_sequency( "FILTER" );

    $filters = array();
    for( $i=1; true; $i++ ) {
      $tmp = "";
      $items = (array)sc_sentences::get_sentence_sequency( "FILTER_".$filter->page."_OR_$i" );
      if ( empty($items) ) break;
      asort($items);
      foreach( $items as $alias => $value ) {
        if ( isset($filter->post) && isset($filter->post->{"or_$i|$alias"}) ) {
          $tmp .= "<label class='caps pointer' style='padding-left: 5px;'><input class='pointer' type='checkbox' name='or_$i|$alias' id='or_$i|$alias' checked='checked'><label for='or_$i|$alias'></label> $value</label><br />";
        } else { 
          $tmp .= "<label class='caps pointer' style='padding-left: 5px;'><input class='pointer' type='checkbox' name='or_$i|$alias' id='or_$i|$alias'><label for='or_$i|$alias'></label> $value</label><br />";
        }
      }
      $filters[] = $tmp;
      unset($tmp);
    }
    for( $i=1; true; $i++ ) {
      $tmp = "";
      $items = (array)sc_sentences::get_sentence_sequency( "FILTER_".$filter->page."_AND_$i" );
      if ( empty($items) ) break;
      asort($items);
      foreach( $items as $alias => $value ) {
        if ( isset($filter->post) && isset($filter->post->{"and_$i|$alias"}) ) {
          $tmp .= "<label class='caps pointer' style='padding-left: 5px;'><input class='pointer' type='checkbox' name='and_$i|$alias' id='and_$i|$alias' checked='checked'><label for='and_$i|$alias'></label> $value</label><br />";
        } else { 
          $tmp .= "<label class='caps pointer' style='padding-left: 5px;'><input class='pointer' type='checkbox' name='and_$i|$alias' id='and_$i|$alias'><label for='and_$i|$alias'></label> $value</label><br />";
        }
      }
      $filters[] = $tmp;
      unset($tmp);
    }
    $output = "<div id='filter'>";
      $output .= "<form name='filter' method='post' action=''>";
      $output .= "<div style='display: table;'>";
        $output .= "<div style='display: table-row'>";
          $output .= "<div style='display: table-cell' class='cell_padded'>";
            $output .= "<label class='border input' id='search_box'><img src='/magnifier.png' class='magnifier'><input autofocus autocomplete=off type='search' list='search_suggestions' placeholder='$sentences->SEARCH' id='search' name='search' value='$search' class='transparent'></label>";
            $url = clone $filter;
            if ( isset($url->post) ) unset($url->post);
            if ( isset($url->order_by) ) unset($url->order_by);
            if ( isset($url->page) ) unset($url->page);
            $url = sc_page::get_host_url()."?filter=".sc_page::convert_object_to_url($url);
            $output .= "<div class='center'>";
              $output .= "<label class='hbutton caps'>$sentences->SEARCH<input name='bsearch' type='button' onClick=\"send_form( this.form )\" class='invisible'></label>";
              $output .= "<label class='hbutton caps' style='margin-left: 5px'>$sentences->CLEAR<input type='button' class='invisible' onClick=\"loadpage('$url')\"></label>";
              $query = "SELECT * FROM v_filters WHERE vf_menu_alias='$filter->page'";
              if ( c_sql::count_all($query) > 0 ) {
                $output .= "<label class='hbutton caps' style='display: block; font-size: 70%; margin-left: 5px'>$sentences->ADVANCED_SEARCH<input type='button' class='invisible' onClick=\"change_ef()\"></label>";
              }
            $output .= "</div>";
          $output .= "</div>";
          foreach($filters as $tmp) {
            if ( !empty($tmp) ) $output .= "<div style='display: table-cell' class='cell_padded'>$tmp</div>";
          }
        $output .= "</div>";
      $output .= "</div>";
      $output .= "<input type='hidden' name='bsearch' value=''>";
      $output .= "</form>";
    $output .= "</div>";
    return $output;
  }
  public static function parse( $filter ) {
    $filter->page = intval($_POST['page_number']);
    if ( isset($_POST['next_page']) ) $filter->page++;
    elseif ( isset($_POST['previous_page']) ) $filter->page--;
    elseif ( isset($_POST['last_page']) ) $filter->page = -1;
    elseif ( isset($_POST['first_page']))  $filter->page = 1;

    if ( isset($_POST['bsearch']) ) {
      $post = array();
      foreach( $_POST as $alias => $value ) {
        if ( stristr($alias,"binary") ) continue;
        $post[$alias] = $value;
      }
      $filter->post = (object)$post;
    }
  }
}
?>
