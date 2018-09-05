<?php
  header( "Content-Type:text/javascript; charset=utf-8" );
  define("TYPE","js");
  include_once dirname(__DIR__)."/conf/config.php";
  if ( !c_sql::connect() ) die();

  header( "Cache-Control: no-store, no-cache, must-revalidate, max-age=0" );
  header( "Cache-Control: post-check=0, pre-check=0", false );
  header( "Pragma: no-cache" );
  
  $files = array();
  $filter = sc_page::get_referer_filter();
  if ( !isset($filter) || !isset($filter->page) ) $page = "home";
  else $page = strtolower($filter->page);

  $sentences = c_sentences::get_sentence_sequency("MESSAGES");

  $output = "";
 
  foreach((array)$sentences as $index => $value ) {
    $output .= "messages['$index'] = \"$value\";\r\n";
  }

  $tfiles[] = "dynamic/$page.js";
  foreach( $tfiles as $file ) {
    if ( is_file($config->dir->lib->js.$file) && filesize($config->dir->lib->js.$file) > 0 ) $files[] = $config->dir->lib->js.$file;
    elseif ( is_file($config->shared->dir->lib->js.$file) && filesize($config->shared->dir->lib->js.$file) > 0 ) $files[] = $config->shared->dir->lib->js.$file;
  }
  unset($tfiles);

  foreach($files as $file) {
    if ( filesize($file) < 1 ) continue;
    $handle = fopen($file,"rb");
    $data = fread($handle,filesize($file))."\r\n";
    fclose($handle);
    $output .= $data;
  }
  
  header( "Content-Type:text/javascript; charset=utf-8" );
  header( "Content-Length: ".strlen($output) );
  $md5 = base64_encode(hash("md5",$output));
  header( "Content-MD5: $md5" );
  header( "X-Content-MD5: $md5" );
  echo $output;
?>
