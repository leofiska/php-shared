<?php
  define("TYPE","css");
  include_once __DIR__."/config.php";
  if ( !c_sql::connect() ) die();

  foreach (glob($config->shared->dir->lib->css."*.css") as $filename) {
    $files[] = $filename;
  }
  foreach (glob($config->dir->lib->css."*.css") as $filename) {
    $files[] = $filename;
  }
  $last_modified = 0;
  $output = "";
  
  $query = "SELECT jsonb_object_agg(config_name, config_value ) AS config FROM tb_config;";
  if ( !($row = c_sql::get_first_object($query)) ) die("error while fetching configuration");
  $config = (object)json_decode($row->config);

  foreach($files as $file) {  
    if ( filesize($file) < 1 ) continue;
    $handle = fopen($file,"rb");
    $data = fread($handle,filesize($file));
    fclose($handle);
    if ( $last_modified < strtotime(filemtime($file)) ) $last_modified = strtotime(filemtime($file));
    $output .= $data;
  }
  $output = str_replace("[[-SERVER-]]",$_SERVER['SERVER_NAME'],$output);
  if ( stristr($_SERVER['HTTP_USER_AGENT'],"chrome") ) {
    $output = str_replace("<-BASEFONT->","15px",$output);
  } else {
    $output = str_replace("<-BASEFONT->","14px",$output);
  }


  $attribs = sc_page::get_style_replace_attributes();
  foreach($attribs as $attr) {
    if ( isset($config->{$attr->name}) )  {
      $output = str_replace($attr->replace,"$attr->preffix: ".$config->{$attr->name}.";",$output);
    } else {
      $output = str_replace($attr->replace,"",$output);
    }
  }


  header( "Content-Type:text/css; charset=utf-8" );
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
    header( "Content-Length: ".strlen($data) );
    $md5 = base64_encode(hash("md5",$output));
    header( "Content-MD5: $md5" );
    header( "X-Content-MD5: $md5" );
    echo $output;
  }
?>

