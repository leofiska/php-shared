<?php
class sc_sentences {

  public static $url_pattern = '\$\+\!\*\"'."\'".'\(\)\w\:\/\-\ \.\%\=\?\,\;';

  public static function get_sentence_sequency( $alias, $array = false, $language = false ) {
    if ( empty($language) ) {
      $language = c_page::get_language();
    }
    if ( is_array($alias) ) {
      $tmp = "";
      foreach($alias as $item) {
        if ( !empty($tmp) ) $tmp .= " OR ";
        $tmp .= "page_alias='".c_sql::escape_string($item)."'";
      }
      $alias = "($tmp)";
      unset($tmp);
    } else {
      $alias = "page_alias='".c_sql::escape_string($alias)."'";
    }

    $query = "SELECT sentence_alias, sentence_value->CONCAT($language) AS sentence_value FROM v_sentences_page WHERE $alias";
    $result = c_sql::select($query);

    $return = array();
    while ( $row = c_sql::fetch_object($result) ) {
      $return[$row->sentence_alias] = $row->sentence_value;
    }
    if ( !empty($array) ) return $return;
    return (object)$return;
  }
  public static function get_sentence( $alias, $lang = false ) {
    $language = c_page::get_language();
    $alias = c_sql::escape_string($alias);
    $query = "SELECT sentence_alias, sentence_value->CONCAT($language) AS sentence_value FROM tb_sentences WHERE sentence_alias='$alias'";
    if ( $item = c_sql::get_first_object($query) ) return $item->sentence_value;
    return $alias;
  }
  public static function clear_url( $in, $full = true) {
    $url = trim(preg_replace("/[^".self::$url_pattern."]/","",$in));
    if ( strstr($url,"//") ) $output = trim(strstr($url,"//"),"/"); else $output = trim($url,"/");
    if ( strstr($url,"https://") ) $output = "https://".$output; else $output = "http://".$output;
    return $output;
  }
  public static function explode_urls($in) {
//    return explode("\n",preg_replace("/[^".self::$url_pattern."]/","\n",$in));
    $tmp = str_replace("\n\n","\n",implode("\nhttps://",explode("https://",$in)));
    $tmp = str_replace("\n\n","\n",implode("\nhttp://",explode("http://",$tmp)));
    return array_unique(array_filter(explode("\n",preg_replace("/[^".self::$url_pattern."]/","\n",$tmp))));
  }

  public static function create_search_string( $in, $all = false ) {
    if ( is_array($in) ) {
      $in = trim(implode(" ",$in));
    }
    $in = explode(" ",trim(self::strtolower($in)));
    $out = "";
    $mout = "";
    foreach($in as $item) {
      if ( strstr($out,$item) ) continue;
      if ( !empty($out) ) $out .= " ";
      if ( !empty($mout) ) $mout .= " ";
      $out .= $item;
      if ( strstr($mout,metaphone($item)) ) continue;
      $mout .= metaphone($item);
    }
    if ( !empty($out) ) {
      if ( $all ) $out .= " ".$mout;
      else $out = $mout;
    }
  
    return $out;
  }

  public static function strtolower( $string ) {
    $convert_to = array(
      "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u",
      "v", "w", "x", "y", "z", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï",
      "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "а", "б", "в", "г", "д", "е", "ё", "ж",
      "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы",
      "ь", "э", "ю", "я", "ç"
    );
    $convert_from = array(
      "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U",
      "V", "W", "X", "Y", "Z", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï",
      "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж",
      "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ъ",
      "Ь", "Э", "Ю", "Я", "Ç"
    );
    return str_replace($convert_from, $convert_to, $string);
  }
  public static function strtoupper( $string ) {
    $convert_from = array(
      "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u",
      "v", "w", "x", "y", "z", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï",
      "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "а", "б", "в", "г", "д", "е", "ё", "ж",
      "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы",
      "ь", "э", "ю", "я", "ç"
    );
    $convert_to = array(
      "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U",
      "V", "W", "X", "Y", "Z", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï",
      "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж",
      "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ъ",
      "Ь", "Э", "Ю", "Я", "Ç"
    );
    return str_replace($convert_from, $convert_to, $string);
  }


  static function contains_at_least_one_not_in( $pattern, $string ) {
    for( $i=0; $i<strlen($string);$i++ ) {
      if ( !strchr($pattern,$string[$i]) ) return true;
    }
    return false;
  }
  static function contains_at_least_one( $pattern, $string ) {
    for( $i=0; $i<strlen($pattern);$i++ ) {
      if ( strchr($string,$pattern[$i]) ) return true;
    }
    return false;
  }
  static function contains_only( $pattern, $string ) {
    for( $i=0; $i<strlen($string);$i++ ) {
      if ( !strchr($pattern,$string[$i]) ) return false;
    }
    return true;
  }

  public static function format_date( $date ) {
    switch(c_page::get_language() ) {
      case "1046":
        $output = date("d-m-Y H:i:s",$date);
        break;
      default:
        $output = date("m-d-Y h:i:s A",$date);
        break;
    }
    return $output;
  }

}
?>
