<?php
class c_pic {

  public $name;
  public $id;
  public $width;
  public $height;

  public function __construct() {
    $this->name = "";
    $this->id = "";
    $this->width = "";
    $this->height = "";
  }

  public static function create_pic( $pic, $layer = true ) {
    if ( !is_a($pic,get_called_class()) ) return "";
    $output = "<div ";
    if ( !empty($pic->id) ) $output .= "id='$pic->id' ";
    $output .= "class='pic'>";
    if ( $layer ) $output .= self::create_layer();
    $output .= "<img src='/$pic->name' class='noselect' />";
    $output .= "</div>";
    return $output;
  }

  private static function create_layer() {
    $output = "";
    $output .= "<div class='pic_layer'>";
      $output .= "<div class='pic_control'>";
        $output .= "<div><img src='/edit.png' /></div>";
        $output .= "<div><img src='/trash.png' /></div>";
        $output .= "<div><img src='/add.png' /></div>";
      $output .= "</div>";
    $output .= "</div>";
    return $output;
  }

}
?>
