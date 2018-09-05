<?php
class sc_network {

  public static function nslookup( $in ) {
    if ( !preg_match("/([a-zA-Z0-9\.\-]+)/", $in, $out) ) return false;

    $out = $out[1];
    if ( $in !== $out ) return false;
    exec( "nslookup $out | grep -i address | grep -iv \"\\#\"", $output );
    if ( empty($output) ) return false;    
    foreach($output as &$ip) {
      $ip = trim($ip);
      $ip = trim(strrchr($ip," "));
    }
    return $output;
  }

  public static function is_url($in ) {
    if ( strstr($in,"//") ) $in = trim(strstr($in,"//"),"/");
    else $url = trim($in,"/");
    $url = explode("/",$in);
    if ( !isset($url[1]) ) return false;
    $nslookup = self::nslookup($url[0]);
    if ( empty($nslookup) ) return false;
    return true;
  }

  public static function is_valid_ipv4( $in ) {
    if ( sc_ipv4::validate_ipv4($in) ) return true;
    return false;
  }
  public static function is_valid_ipv6( $in ) {
    if ( sc_ipv6::validate_ipv6($in) ) return true;
    return false;
  }
  public static function is_valid_fqdn( $in ) {
    if ( !sc_sentences::contains_at_least_one( ".", $in ) ) return false;
    if ( strstr($in,"..") ) return false;
    $tmp = preg_replace("/[^0-9a-z\.]/","",$in);
    if ( $tmp !== $in ) return false;
    if ( $tmp !== ltrim($tmp,".") ) return false;
    return true;
  }

}
?>
