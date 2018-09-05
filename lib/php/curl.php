<?php
class sc_curl {
  private $user_agent;
  private $referer;
  private $online;
  private $cookie;
  private $ch;

  public function __construct( $cookie = false ) {
    $this->user_agent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/42.0";
    $this->referer = "";
    $this->online = false;
    $this->cookie = $cookie;
    if ( !empty($cookie) ) c_log::reg("INFO","creating curl instance with cookie: $cookie");
    else c_log::reg("INFO","creating curl instance without cookie");
    $this->ch = curl_init();
  }

  public function clear_html( &$out ) {
    if ( is_array($out) ) {
      for( $i = 1; isset($out[$i]); $i++ ) {
        $out[$i] = str_replace("\\","\\\\",$out[$i]);
        $out[$i] = str_replace("\"","\\\"",$out[$i]);
        foreach($out[$i] as &$string)
          $string = trim(html_entity_decode($string,ENT_COMPAT,"UTF-8"));
      }
    }
    elseif ( is_string($out) ) {
      $out = str_replace("\\","\\\\",$out);
      $out = str_replace("\"","\\\"",$out);
      $out = trim(html_entity_decode($out,ENT_COMPAT,"UTF-8"));
    }
  }

  public function fetch( $url, $post = "" ) {
    $ch = $this->ch;
    c_log::reg("INFO","fetching $url, $post");
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_REFERER, $this->referer);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent );
    curl_setopt($ch, CURLOPT_TIMEOUT, "20");
    if ( !empty($this->cookie) ) {
      curl_setopt($ch, CURLOPT_COOKIEFILE, constant("cookies").$this->cookie );
      curl_setopt($ch, CURLOPT_COOKIEJAR, constant("cookies").$this->cookie );
    }
    if ( !empty($post) ) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      curl_setopt($ch, CURLOPT_POST, 1);
    }
    $data = curl_exec($ch);
    $info = curl_getinfo($ch);
    if ( strlen($data) < 1 ) {
      print_r($info);
      c_log::reg("WARNING","zero length while fetching $url: ".curl_error($ch).", $post");
    }
    if ( !strstr(strrchr($url,"."),"jpg") ) $this->referer = $url;
    return $data;
  }
  public function save_to_file( $url, $file, $post = "" ) {
      if ( ($fd = fopen($url, "r")) && ($sf = fopen($file,"wb")) ) {
        $fsize = @filesize($url);
        $path_parts = pathinfo($url);
        /*$ext = strtolower($path_parts["extension"]);
        switch ($ext) {
          case "pdf":
            header("Content-type: application/pdf");
            header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\"");
            break;
           default;
            header("Content-type: application/octet-stream");
            header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
            break;
        }
        header("Content-length: $fsize");
        header("Cache-control: private");*/
        while(!feof($fd)) {
          $buffer = fread($fd, 2048);
          fwrite($sf,$buffer);
        }
      }
      fclose($fd);
      fclose($sf);
  }
}
?>
