<?php
  define("TYPE","js");
  include_once __DIR__."/config.php";
  if ( !c_sql::connect() ) die();
  
  $files = array();
  $files[] = $config->shared->dir->lib->js."variables";
  
  foreach (glob($config->shared->dir->lib->js."*.js") as $filename) {
    $files[] = $filename;
  }
  foreach (glob($config->dir->lib->js."*.js") as $filename) {
    $files[] = $filename;
  }


  //$file = constant("js")."public".constant("DIR_SLASH")."base.js";

  $last_modified = 0;
  $output = "";
  
  foreach($files as $file) {
    if ( $last_modified < strtotime(filemtime($file)) ) $last_modified = strtotime(filemtime($file));
    if ( filesize($file) < 1 ) continue;
    $handle = fopen($file,"rb");
    $data = fread($handle,filesize($file))."\r\n";
    fclose($handle);
    $output .= $data;
  }
  $output = str_replace("\r\n","\n",$output);
  $agent = @$_SERVER['HTTP_USER_AGENT'];
  if ( stristr($agent,"MSIE") || stristr($agent,"TRIDENT") || stristr($agent,"SAMSUNGBROWSER")) {
    $output = str_replace("\n","\r\n",$output);
    $output = str_replace("async function","function",$output);
  } else {
    $toutput = explode("\n",$output);
    foreach($toutput as &$i) {
      $i = trim($i);
      if ( isset($i[0]) && isset($i[1]) && $i[0] == "/" && $i[1] == "/" ) $i = "";
    }
    $toutput = array_values(array_filter($toutput));
    $output = implode(" ",$toutput);
    $output = preg_replace("/\/\*[\s\S]*?\*\/|([^\\:]|^)\/\/.*$/m", "$1",$output);
    while( strstr($output,"  ") ) {
      $output = str_replace("  "," ", $output);
    }
  }
  
  header( "Content-Type:text/javascript; charset=utf-8" );
  if ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $last_modified ) {
    header( "HTTP/1.0 304 Not Modified" );
    header( "Expires: ".gmdate('D, d M Y H:i:s T',time()+604800) );
    header( "Cache-Control: max-age=604800, public ");
    header( "Pragma: cache" );
  } else {
    header( "Last-Modified: ".gmdate('D, d M Y H:i:s T',$last_modified) );
    header( "Expires: ".gmdate('D, d M Y H:i:s T',time()+604800) );
    header( "Cache-Control: max-age=604800, public ");
    header( "Pragma: cache" );
    header( "Content-Length: ".strlen($output) );
    $md5 = base64_encode(hash("md5",$output));
    header( "Content-MD5: $md5" );
    header( "X-Content-MD5: $md5" );
    echo $output;
  }
?>
