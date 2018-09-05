<?php
class sc_ipv6 {

  public static function validate_ipv6( $address ) {
    $address = sc_sentences::strtolower($address);
    $tmp = preg_replace("/[^0-9a-f\:]/","",$address);
    if ( $tmp !== $address ) return false; 

    $tmp = explode(":",$tmp);
    if ( Sizeof($tmp) > 8 ) return false;

    if ( Sizeof($tmp) < 8 ) {

      $cleared = array_values(array_filter($tmp,function($value) { return $value !== ''; }));
      if ( (Sizeof($cleared)+1) != (Sizeof($tmp)) ) return false;
      $filled = array();
      for( $i=0; $i < Sizeof($tmp); $i++ ) {
        if ( trim($tmp[$i]) == "" ) break;
        $missing = $i;
        $filled[] = trim($tmp[$i]);
      }
      for( $i=$missing; $i <= (8-Sizeof($tmp)); $i++ ) {
        $filled[] = "0";
      }
      for( $i=($missing+2); $i < Sizeof($tmp); $i++ ) {
        $filled[] = trim($tmp[$i]);
      }
      
    }
    return implode(":",$filled);
  }

}
?>
