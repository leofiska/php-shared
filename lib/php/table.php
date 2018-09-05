<?php
class sc_table {

  public $title;
  public $headers;
  private $commands_top_attributes;
  private $commands_bottom_attributes;
  private $commands_top;
  private $commands_bottom;
  public $content;
  public $align;
  public $start;
  public $end;
  public $form;
  private $border;
  public $search;
  public $min_width;
  public $width;
  public $page;

  function __construct() {
    $this->page = "";
    $this->headers = array();
    $this->commands_top_attributes = new STDClass();
    $this->commands_bottom_attributes = new STDClass();
    $this->commands_top = array();
    $this->commands_bottom = array();
    $this->content = "";
    $this->start = "";
    $this->end = "";
    $this->align = "center";
    $this->form = false;
    $this->border = (object)(array("left"=>false,"right"=>false,"top"=>false,"bottom"=>false));
    $this->search = false;
    $this->min_width = "40%";
    $this->width = 0;
  }
  public function set_commands_attribute( $side, $attr, $value ) {
    if ( !isset($this->{"commands_".$side."_attributes"}) ) return false;
    $this->{"commands_".$side."_attributes"}->{$attr} = $value;
  }
  public function set_border( $side, $value ) {
    if ( !is_bool($value) ) return false;
    if ( $side == "all" ) {
        $this->border->top = $value;
        $this->border->bottom = $value;
        $this->border->left = $value;
        $this->border->right = $value;
        return true;
     }
     if ( isset($this->border->{$side}) ) {
       $this->border->{$side} = $value;
       return true;
     }
    return false;
  }
  public function add_header( $id, $name, $width = "" ) {
    $this->headers[] = (object)(array( "id"=>$id, "name"=>$name, "width"=>$width ));
  }
  public function add_command( $side, $id, $name, $selectable = false, $control = false ) {
    if ( !is_bool($selectable) ) return false;
    switch($side) {
      case "all":
        $this->commands_top[$id] = (object)(array("id"=>$id, "name"=>$name, "selectable"=>$selectable, "control"=>$control ));
        $this->commands_bottom[$id] = (object)(array("id"=>$id, "name"=>$name, "selectable"=>$selectable, "control"=>$control ));
        break;
      case "top":
        $this->commands_top[$id] = (object)(array("id"=>$id, "name"=>$name, "selectable"=>$selectable, "control"=>$control ));
        break;
      case "bottom":
        $this->commands_bottom[$id] = (object)(array("id"=>$id, "name"=>$name, "selectable"=>$selectable, "control"=>$control ));
        break;
      default:
        return false;
        break;
    }
  }

  public static function create_simple_list( $filter, $table ) {
    if ( !is_a($table,get_called_class()) ) $message[] = "received invalid object";
    $url = sc_page::get_clean_filter($filter);

    $table->start = "";

    if ( !empty($table->form) ) {


      $url = c_page::convert_object_to_url($filter);

      $table->start .= "<form method='post' action='/$url' name='$table->form' style='text-align: center;'>";
      unset($url);
    }
    $table->start .= "<div class='table $table->align";

    if ( !empty($table->border->left) ) {
      $table->start .= " border-left";
    }
    if ( !empty($table->border->right) ) {
      $table->start .= " border-right";
    }
    if ( !empty($table->border->top) ) {
      $table->start .= " border-top";
    }
    if ( !empty($table->border->bottom) ) {
      $table->start .= " border-bottom";
    }

    // creates the table with width spec
    if ( !empty($table->width) ) {
      $table->start .= "' style='width: $table->width;'>";
    } elseif ( !empty($table->min_width) ) {
      $table->start .= "' style='min-width: $table->min_width;'>";
    } else {
      $table->start .= "'>";
    }

    $table->end = "</div>";

    // add search content is exists
    if ( !empty($table->search) ) {
      $table->start .= "<div>";
      $table->start .= "<div>";
      $table->start .= $table->search;
      $table->start .= "</div>";
      $table->start .= "</div>";
    }

    // add search result content if exists
    if ( !empty($table->search) ) {
      $table->start .= "<div>";
      $table->start .= "<div class='center'>";
      $table->start .= $table->search;
      $table->start .= "</div>";
      $table->start .= "</div>";
    }

    // add title if exists
    if ( isset($table->title) ) {
      $table->start .= "<div>";
      $table->start .= "<div class='caps table_title center' ";
      if ( empty($table->title) ) $table->start .= " style='min-height: 20px; display: block;'";
      $table->start .= ">";
      $table->start .= $table->title;
      $table->start .= "</div>";
      $table->start .= "</div>";
    }

    if ( !empty($table->commands_top) ) {
      $table->start .= "<div class='command_bar'>";
      if ( isset($table->commands_top_attributes->background) && !empty($table->commands_top_attributes->background) ) {
        $table->start .= "<div class='right top_command_bar_background'>";
      } else {
        $table->start .= "<div class='right'>";
      }
      $table->start .= "<div class='table'>";
      $table->start .= "<div>";
      foreach($table->commands_top AS $cmd) {
        $type = stristr($cmd->id,"_line") ? "type='button' onclick=\"send_ajax_form( this.form, this )\" name='$cmd->id'" : "type='button' onclick=\"send_ajax_form( this.form, this )\" name='command' ";
        if ( !empty($cmd->selectable) ) {
          $table->start .= "<div><label class='caps'>$cmd->name<input $type class='invisible' value='$cmd->id' /></label></div>";
        } else {
          $table->start .= "<div><label class='caps disabled'>$cmd->name<input $type disabled=disabled class='invisible' value='$cmd->id' /></label></div>";
        }
      }
      $table->start .= "</div>";
      $table->start .= "</div>";
      $table->start .= "</div>";
      $table->start .= "</div>";
    }

    $table->start .= "<div>";
    $table->start .= "<div>";
    $table->start .= "<div>";
    $table->start .= "<div class='table' style='width: 100%;'>";

    if ( !empty($table->headers) ) {
      $table->start .= "<div class='table_title'>";
      foreach( $table->headers AS $head ) {
        if ( $head->id == "checkbox" ) {
          $table->start .= "<div class='left caps middle' style='width: ".$head->width."px;'><input name='selectall' id='selectall_$table->form' type='checkbox' onClick=\"toogle_select_all( event, this.form )\" /><label class='hlabel' for='selectall_$table->form'></label></div>";
        } else {
          $table->start .= "<div class='left caps top";
          if ( isset($filter->order_by) ) $table->start .= " pointer";
          $table->start .= "' style='padding-left: 10px;";
          if ( !empty($head->width) ) $table->start .= "width: $head->width".";";
          $table->start .= "'";
          if ( isset($filter->order_by) ) {
            $url = sc_page::clone_filter($filter);
            if ( strtoupper($head->id)."_ASC" == $filter->order_by ) {
              $url->order_by = $head->id."_DESC";
              $img = 'pictures/arrow_up.png';
            } elseif ( strtoupper($head->id)."_DESC" == $filter->order_by ) {
              $url->order_by = $head->id."_ASC";
              $img = 'pictures/arrow_down.png';
            } else $url->order_by = $head->id."_ASC";
            $url->order_by = strtoupper($url->order_by);
            $url = sc_page::convert_object_to_url($url);
            $table->start .= "><a href='/$url'>";
            if ( isset($img) ) {
              $table->start .= "<img class='arrow' src='/$img' />";
              unset($img);
            }
          } else {
            $table->start .= ">";
          }
          $table->start .= "$head->name</a></div>";
        }
      }
      $table->start .= "</div>";
    }

    $tmp = "";
    if ( !empty($table->commands_bottom) ) {
      $tmp .= "<div class='command_bar'>";
      if ( isset($table->commands_bottom_attributes->background) && !empty($table->commands_bottom_attributes->background) ) {
        $tmp .= "<div class='right bottom_command_bar_background'>";
      } else {
        $tmp .= "<div class='right'>";
      }
      $tmp .= "<div class='table'>";
      $tmp .= "<div>";
      foreach($table->commands_bottom AS $cmd) {
        $type = stristr($cmd->id,"_line") ? "type='submit' name='$cmd->id'" : "type='submit' name='command' ";
        if ( !empty($cmd->selectable) ) {
          $tmp .= "<div><label class='caps'>$cmd->name<input $type class='invisible' value='$cmd->id' /></label></div>";
        } else {
          $tmp .= "<div><label class='caps disabled'>$cmd->name<input $type disabled=disabled class='invisible' value='$cmd->id' /></label></div>";
        }
      }
      $tmp .= "</div>";
      $tmp .= "</div>";
      $tmp .= "</div>";
      $tmp .= "</div>";
    }



    $table->end = "$tmp$table->end";
    unset($tmp);
    $table->end = "</div></div></div></div>$table->end";





   // c_page::kill($table);
    if ( !empty($table->form) ) {
      $table->end .= "</form>";
    }

  }
}
?>
