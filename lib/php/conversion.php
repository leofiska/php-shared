<?php
class sc_conversions {
  public static function shorten_bytes( $in ) {
    if ( empty($in) ) return false;
    $shorten = $in;
    $i = 0;

    while ( $shorten > 1024 ) {
      $i++;
      $shorten = $shorten / 1024;
    }

    if ( c_page::get_language() == "1033" ) {
      $shorten = number_format($shorten,2,".",",");
    } else {
      $shorten = number_format($shorten,2,",",".");
    }

    switch($i) {
      case 0:
        $shorten .= " B";
        break;
      case 1:
        $shorten .= " KB";
        break;
      case 2:
        $shorten .= " MB";
        break;
      case 3:
        $shorten .= " GB";
        break;
      case 4:
        $shorten .= " TB";
        break;
    }
    return $shorten;
  }
}
?>
