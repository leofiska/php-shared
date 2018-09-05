<?php
class sc_ipv4 {
  public static function calculate_broadcast( $address, $netmask ) {
    $netmask_binary = self::mask_to_binary( $netmask );
    if ( empty($netmask_binary) ) return false;

    $address_binary = self::to_binary($address);
    if ( empty($address_binary) ) return false;

    $f0 = strpos($netmask_binary,"0");

    $broadcast_binary = substr($address_binary,0,$f0);
    while( strlen($broadcast_binary) < 32 ) $broadcast_binary .= "1";
    $broadcast = self::to_dec($broadcast_binary);
    return $broadcast;
  }
  public static function calculate_network( $address, $netmask ) {
    $netmask_binary = self::mask_to_binary( $netmask );
    if ( empty($netmask_binary) ) return false;

    $address_binary = self::to_binary($address);
    if ( empty($address_binary) ) return false;

    $f0 = strpos($netmask_binary,"0");

    $network_binary = substr($address_binary,0,$f0)."".substr($netmask_binary,($f0),(strlen($netmask_binary)-$f0));
    $network = self::to_dec($network_binary);
    return $network;
  }
  public static function mask_to_binary( $address ) {
    if ( !preg_match("/([0-9]{1,3})[\.]([0-9]{1,3})[\.]([0-9]{1,3})[\.]([0-9]{1,3})/",$address,$out) ) return false;
   
    $binary = self::to_binary( $address );

    $first0 = strpos($binary,"0");
    $last1 = strrpos($binary,"1");

    if ( (empty($first0)&& $last1 == 31) || (empty($last1) && $first0 == 31) || (($first0-1)==$last1) ) {
      return $binary;
    } else {
      return false;
    }
  }
  public static function to_binary ( $address ) {
   // if ( !preg_match("/([0-9]{1,3}[\.]?){4}/i",$address,$out) ) return false;
    if ( !preg_match("/([0-9]{1,3})[\.]([0-9]{1,3})[\.]([0-9]{1,3})[\.]([0-9]{1,3})/",$address,$out) ) return false;

    if ( $out[1] < 0 || $out[1] > 255 ) return false;
    if ( $out[2] < 0 || $out[2] > 255 ) return false;
    if ( $out[3] < 0 || $out[3] > 255 ) return false;
    if ( $out[4] < 0 || $out[4] > 255 ) return false;


    $binary = "";
    for($i = 1; $i <= 4; $i++ ) {
      $tmp = decbin($out[$i]);
      while(strlen($tmp) < 8 ) $tmp = "0".$tmp;
      $binary .= $tmp;
      unset($tmp);
    }
    return $binary;
  }
  public static function to_dec( $address ) {
    if ( strlen($address) > 32 ) return false;
    while( strlen($address) < 32 ) $address = "0".$address;
    $conv = bindec(substr($address,0,8)).".".bindec(substr($address,8,8)).".".bindec(substr($address,16,8)).".".bindec(substr($address,24,8));
    return $conv;
  }
  public static function validate_ipv4( $address ) {
    if ( !preg_match("/([0-9]{1,3})[\.]([0-9]{1,3})[\.]([0-9]{1,3})[\.]([0-9]{1,3})/",$address,$out) ) return false;

    if ( $out[1] < 0 || $out[1] > 255 ) return false;
    if ( $out[2] < 0 || $out[2] > 255 ) return false;
    if ( $out[3] < 0 || $out[3] > 255 ) return false;
    if ( $out[4] < 0 || $out[4] > 255 ) return false;

    return $out[1].".".$out[2].".".$out[3].".".$out[4];
  }

}
?>
