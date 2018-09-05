<?php
class sc_parser {

  private static $lowercase_letters = "abcdefghijklmnopqrstuvwxyz";
  private static $uppercase_letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  private static $lowercase_special_letters = "áéíóúàèìòùãõäëïöïâêîôûç";
  private static $uppercase_special_letters = "ÁÉÍÓÚÀÈÌÒÙÃÕÄËÏÖÜÂÊÎÔÛÇ";
  private static $numbers = "0123456789";

  public static function parse_alphanumeric( $in, $private = "" ) {
    $valid = self::$lowercase_letters.self::$uppercase_letters.self::$lowercase_special_letters.self::$uppercase_special_letters.self::$numbers.$private;
    return self::parse( $in, $valid );
  }

  private static function parse( $in, $valid ) {
    $out = "";
    for( $i=0; isset($in[$i]); $i++ ) {
      if ( !strchr($valid,$in[$i]) ) continue;
      $out .= $in[$i];
    }
    return $out;
  }

}
?>
