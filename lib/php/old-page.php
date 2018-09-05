<?php
  class very_old_page {
    static function convert_object_to_url( $object ) {
      $url = json_encode($object);
      $url = base64_encode($url);
      return $url;
    }
    
    static function convert_post_to_object( $url ) {
      $filter = new STDClass();
      foreach($_POST as $index => $value ) {
        if ( stristr($index,"binary") ) continue;
        $filter->{$index} = $value;
      }
      return $filter;
    }
    static function convert_url_to_object( $url ) {
      $url = base64_decode($url);
      $object = json_decode($url);
      return $object;
    }
    static function kill( $obj ) {
      echo nl2br(print_r($obj, true));
      die();
    }
    static function get_user_agent() {
      if ( isset($_SERVER['HTTP_USER_AGENT']) ) return $_SERVER['HTTP_USER_AGENT'];
      return "";
    }
    static function get_host_url() {
      $ssl = isset($_SERVER['HTTPS']) ? "https://" : "http://";
      $url = $ssl.$_SERVER['SERVER_NAME'];
      return $url;
    }

    static function is_valid_referer( $url ) {
      if ( stristr($url,self::get_host_url()) ) return true;
      return false;
    }

    static function get_referer_url() {
      if ( isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) ) return $_SERVER['HTTP_REFERER'];
      return self::get_host_url();
    }
    static function get_referer_filter() {
      if ( !isset($_SERVER['HTTP_REFERER']) || empty($_SERVER['HTTP_REFERER']) ) return false;
      if ( !preg_match("/^.+filter=(.+)$/i",$_SERVER['HTTP_REFERER'], $out) ) return false;
      $filter = $out[1]; 
      return self::convert_url_to_object($filter);
    }
 
    static function process( $filter ) {
      $page = new STDCLass();
//      $page->la = nl2br(print_r($filter,true));
      $page->la = "";
      $page->ca = "";
      $page->ef = "";
      $page->ra = "";
      $page->ba = "";
      $page->xml = "";
      $query = "SELECT * FROM v_menu_permissions WHERE menu_alias='$filter->menu' LIMIT 1";
      if ( !($result = c_sql::select($query)) || c_sql::num_rows($result) < 1 ) {
        if ( !c_user::has_permission(strtoupper($filter->menu)) ) {
          header( "Location: ".sc_page::get_host_url() );
          die();
        }
      } else {
        $menu_permissions = c_sql::fetch_object($result);
        if ( !c_user::has_permission(explode(",",$menu_permissions->menu_permissions)) ) {
          header( "Location: ".self::get_host_url() );
          die();      
        }
      }
      $co = "f_".strtolower($filter->menu);
      if ( !class_exists($co) ) {
        $co = "sf_".strtolower($filter->menu);
        if ( !class_exists($co) ) {
          if ( isset($_SERVER['HTTP_REFERER']) ) {
            $url = $_SERVER['HTTP_REFERER'];
          } else {
            $url = self::get_host_url();
          }
          header( "Location: $url" );
          die();
        }
      }
      if ( isset($filter->list) && isset($filter->search) && !empty($filter->list) ) c_list::process( $page, $filter );
      elseif ( !isset($filter->list) ) $co::process( $page, $filter );
      else return false;
      return $page;
    }
    public static function clean_filter( $filter ) {
      $out = clone $filter;
      if ( isset($out->post) ) unset($out->post );
      if ( isset($out->order_by) ) unset($out->order_by );
      if ( isset($out->page) ) unset($out->page );
      if ( isset($out->id) ) unset($out->id );
      if ( isset($out->bmenu) ) unset($out->bmenu);
      if ( isset($out->data) ) unset($out->data);
      if ( isset($out->message) ) unset($out->message);
      return $out;
    }
    public static function clone_filter( $filter ) {
      return clone $filter;
    }
    public static function create_message( $message ) {
      if ( !is_array($message) ) return;
      $output = "<div style='margin: 0px 0px 20px 0px;'>";
      $output .= "<div id='message'>";
      foreach($message as $id => $value ) {
        $output .= "<div class='pointer' onClick=\"set_focus( '$id')\">$value</div>";
      }
      $output .= "</div>";
      $output .= "</div>";
      return $output;

    }
    public static function create_page_nav( $filter, $query ) {
      if ( !isset($filter->page) || empty($filter->page) ) $filter->page = 1;
      else $filter->page = intval($filter->page);
      $pages = new STDClass();
      $pages->total_items = c_sql::count_all($query); 
      $pages->max = c_user::get_items_per_page(); 
      $pages->total_pages = intval($pages->total_items/$pages->max);
      if ( $pages->total_items%$pages->max != 0 ) $pages->total_pages++;
      if ( $pages->total_pages < 1 ) $pages->total_pages = 1;
      $pages->page = $filter->page;
      $pages->page = ($pages->page > $pages->total_pages) || ($pages->page == -1) ? $pages->total_pages : $pages->page;
      $pages->page = $pages->page < 1 ? 1 : $pages->page;
      $filter->page = $pages->page;
      $sentences = sc_sentences::get_sentence_sequency("PAGE_COUNTER");
      $output = "";
      $output .= "<form method='post' name='page' action=''>";
        if ( $filter->page == 1 ) {
          $output .= "<div class='noselect lbutton caps auto disabled'>$sentences->FIRST_PAGE</div> | ";
          $output .= "<div class='noselect lbutton caps auto disabled'>$sentences->PREVIOUS_PAGE</div> | ";
        } else {
          $output .= "<label class='noselect lbutton caps auto'>$sentences->FIRST_PAGE<input type='submit' name='first_page' class='invisible'></label> | ";
          $output .= "<label class='noselect lbutton caps auto'>$sentences->PREVIOUS_PAGE<input type='submit' name='previous_page' class='invisible'></label> | ";
        }
        if ( $pages->total_pages < 2 ) {
          $output .= "<select onChange='this.form.submit()' class='transparent' disabled='disabled'' name='page_number'>";
        } else {
          $output .= "<select onChange='this.form.submit()' class='transparent pointer' name='page_number'>";
        }
        for( $i=1; $i <= $pages->total_pages; $i++ ) {
          while( strlen($i) < strlen($pages->total_pages) ) $i = "0".$i;
          if ( intval($i) == intval($filter->page) ) $output .= "<option selected='selected' value='$i'>$sentences->PAGE $i</option>";
          else $output .= "<option value='$i'>$sentences->PAGE $i</option>";
        }
        $output .= "</select> ";
        if ( $filter->page == $pages->total_pages ) {
          $output .= "<div class='noselect lbutton caps auto disabled'>$sentences->NEXT_PAGE</div> | ";
          $output .= "<div class='noselect lbutton caps auto disabled'>$sentences->LAST_PAGE</div> ";
        } else {
          $output .= "<label class='noselect lbutton caps auto'>$sentences->NEXT_PAGE<input type='submit' name='next_page' class='invisible'></label> | ";
          $output .= "<label class='noselect lbutton caps auto'>$sentences->LAST_PAGE<input type='submit' name='last_page' class='invisible'></label> ";
        }

      $output .= "</form>";

      $pagenav = "<div class='pagetop'><div>$output</div></div>";
      $pagenav .= "<div class='pagebottom'><div>$output</div></div><div></div>";
      return $pagenav;
    }
    public static function create_table( $filter, $sentences, $title = false, $search = false, $titles = false, $orders_by = false, $top_commands = false, $bottom_commands = false, $search_result = false ) {
      $data = new STDClass();
      if ( $search ) {
        $data->search = c_filter::create_filter_element( $filter );
      }
      if ( !empty($title) ) {
        if ( is_object($title) ) {
          $data->title = "<a name='$title->id'>$title->content</a>";
        } else {
          $data->title = $title;
        }
      }
      if ( !empty($top_commands) ) {
        $data->top_commands = "<div class='table_cell right'>";
        foreach( $top_commands->global as $alias => $value ) {
          $id = isset($value->id) && !empty($value->id) ? $value->id : $value->name;
          $type = strstr($id,"_line") ? "type='button' onclick=\"exec_button( this )\" " : "type='submit' ";
          if ( $value->selectable ) {
            $data->top_commands .= "<div class='pointer'><label>".$sentences->{sc_sentences::strtoupper($value->name)}."<input name='command' $type class='invisible' value='$id'></label></div>";
          } else {
            $data->top_commands .= "<div class='cursor disabled'><label>".$sentences->{sc_sentences::strtoupper($value->name)}."<input name='command' $type disabled=disabled class='invisible' value='$id'></label></div>";
          }
          unset($id);
        }
        $data->top_commands .= "</div>";
      }
      if ( !empty($bottom_commands) ) {
        $data->bottom_commands = "<div class='table_cell right'>";
        foreach( $bottom_commands->global as $alias => $value ) {
          $id = isset($value->id) && !empty($value->id) ? $value->id : $value->name;
          $type = strstr($id,"_line") ? "type='button' onclick=\"exec_button( this )\" " : "type='submit' ";
          if ( $value->selectable ) {
            $data->bottom_commands .= "<div class='pointer'><label>".$sentences->{sc_sentences::strtoupper($value->name)}."<input name='command' $type' class='invisible' value='$id'></label></div>";
          } else {
            $data->bottom_commands .= "<div class='cursor disabled'><label>".$sentences->{sc_sentences::strtoupper($value->name)}."<input name='command' $type disabled=disabled class='invisible' value='$id'></label></div>";
          }
          unset($id);
        }
        $data->bottom_commands .= "</div>";
      }
      if ( !empty($titles) ) {
        if ( isset($filter->order_by) ) {
          if ( strstr($filter->order_by,"_ASC") ) {
            $arrow = "/arrow_up.png";
            $order = "DESC";
          } else {
            $arrow = "/arrow_down.png";
            $order = "ASC";
          }
        }
        $url = clone $filter;
        if ( isset($url->page) ) unset($url->page);
        $data->titles = "";
        foreach( $titles AS $value ) {
          if ( is_object($value) ) {
            $width = $value->width;
            $value = $value->alias;
          } else $width=0;
          if ( !empty($value) && isset($filter->order_by) && strstr($filter->order_by,$value) ) {
            $url->order_by = $value."_$order";
            $curl = c_page::convert_object_to_url($url);
            if ( !empty($orders_by) ) {
              $data->titles .= "<div class='table_cell cell_padded pointer' onClick=\"loadpage('/?filter=$curl')\" style='min-width: $width"."px;'><img class='arrow' src='$arrow'> ".$sentences->{$value}."</div>";
            } else {
              $data->titles .= "<div class='table_cell cell_padded' style='min-width: $width"."px;'><img class='arrow' src='$arrow'> ".$sentences->{$value}."</div>";
            }
          } elseif ( empty($value) ) {
            $data->titles .= "<div class='table_cell' style='width: 20px;'></div>";
          } elseif ( $value == "checkbox" ) {
            $data->titles .= "<div class='table_cell pointer' style='width: 20px;'><input name='selectall' class='noselect pointer' type='checkbox' onClick=\"toogle_select_all( event, this.form )\" /></div>";
          } else {
            $url->order_by = $value."_ASC";
            $curl = sc_page::convert_object_to_url($url);
            if ( !empty($orders_by) ) $data->titles .= "<div class='table_cell cell_padded pointer' onClick=\"loadpage('/?filter=$curl')\" style='min-width: $width"."px;'>".$sentences->{$value}."</div>";
            else $data->titles .= "<div class='table_cell cell_padded'  style='min-width: $width"."px;'>".$sentences->{$value}."</div>";
          }
        }
      }


      $output = new STDClass();
      $output->start = "<div class='table' style='min-width: 40%;'>";
      $output->end = "";
      if ( isset($data->search) ) {
        $output->start .= "<div class='table_row'>";
          $output->start .= "<div class='table_cell'>";
            $output->start .= $data->search;
          $output->start .= "</div>";
        $output->start .= "</div>";
        if ( $search_result ) {
          $output->start .= "<div class='table_row'>";
            $output->start .= "<div class='table_cell center'>";
              if ( isset($search_result->items) ) {
                $str = str_replace("[LAST]",$search_result->last,str_replace("[FIRST]",$search_result->first,str_replace("[ITEMS]",$search_result->items,str_replace("[SEC]",$search_result->time,$sentences->SEARCH_RESULT))));
                if ( empty($search_result->items) ) $str = substr($str,0,strrpos($str,"."));
              } else {
                $str = str_replace("[SEC]",$search_result->time,$sentences->SEARCH_RESULT);
                $str = substr($str,0,strrpos($str,"."));
              }
              $output->start .= $str;
            $output->start .= "</div>";
          $output->start .= "</div>";
        }
      }
      if ( isset($data->title) ) {
        $output->start .= "<div class='table_row'>";
          $output->start .= "<div class='table_cell noselect caps table_title center'>";
            $output->start .= $data->title;
          $output->start .= "</div>";
        $output->start .= "</div>";
      }
        $output->start .= "<div class='table_row'>";
          $output->start .= "<div class='table_cell'>";
            $output->start .= "<form method='post' action='' name='list'>";
            $output->start .= "<div class='table' style='width: 100%'>";
      
      if ( isset($data->top_commands) ) {
        $output->start .= "<div class='table_row noselect top_command_bar'>";
          $output->start .= $data->top_commands;
        $output->start .= "</div>";
      }
      $output->start .= "<div class='table_row'>";
        $output->start .= "<div class='table' style='width: 100%;'>";
      if ( isset($data->titles) ) {
          $output->start .= "<div class='table_row table_title noselect caps'>";
            $output->start .= $data->titles;
          $output->start .= "</div>";
      }
        $output->end .= "</div>";
      $output->end .= "</div>";
      if ( isset($data->bottom_commands) ) {
        $output->end .= "<div class='table_row noselect bottom_command_bar'>";
          $output->end .= $data->bottom_commands;
        $output->end .= "</div>";
      }


              
            $output->end .= "</div>";
            $output->end .= "</form>";
          $output->end .= "</div>";
        $output->end .= "</div>";
      $output->end .= "</div>";
      return $output;
    }
  }
?>
