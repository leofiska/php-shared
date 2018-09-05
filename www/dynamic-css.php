<?php
  define("TYPE","js");
  include_once dirname(__DIR__)."/conf/config.php";
  if ( !c_sql::connect() ) die();

  header( "Cache-Control: no-store, no-cache, must-revalidate, max-age=0" );
  header( "Cache-Control: post-check=0, pre-check=0", false );
  header( "Pragma: no-cache" );
  header( "Content-Type:text/css; charset=utf-8" );

  $site_config = $config;
  $config = sc_page::getConfig();

  $files = array();
  $output = "";
  $fonts = "";
  $default_font = "";
  foreach($config as $index => $value ) {
    if ( !preg_match("/^font\|(.+)$/i",$index,$out) ) continue;
    $fonts .= "@font-face {
    font-family: ".$out[1].";
    font-weight: normal;
    src: url(/fonts/$value);
}\r\n";
  }
  if ( isset($config->{"system|font"}) ) {
    $default_font = "font-family: ".$config->{"system|font"}." !important;";
  }
  $filter = sc_page::get_referer_filter();

  if ( !isset($filter) || !isset($filter->page) ) $page = "home";
  else $page = strtolower($filter->page);

  $tfiles[] = "dynamic/all.css";
  $tfiles[] = "dynamic/$page.css";

  foreach( $tfiles as $file ) {
    if ( is_file($site_config->dir->lib->css.$file) && filesize($site_config->dir->lib->css.$file) > 0 ) $files[] = $site_config->dir->lib->css.$file;
    elseif ( is_file($site_config->shared->dir->lib->css.$file) && filesize($site_config->shared->dir->lib->css.$file) > 0 ) $files[] = $site_config->shared->dir->lib->css.$file;
  }
  unset($tfiles);

  if ( empty($files) ) {
    header( "Content-Length: ".strlen($output) );
    echo "";
    die();
  }

  foreach($files as $file) {
    if ( filesize($file) < 1 ) continue;
    $handle = fopen($file,"rb");
    $data = fread($handle,filesize($file))."\r\n";
    fclose($handle);
    $output .= str_replace("[[-DEFAULT-FONT-]]",$default_font,str_replace("[[-FONT-FACE-]]",$fonts,$data));
  }

  $attribs = sc_page::get_style_replace_attributes();
  foreach($attribs as $attr) {
    if ( isset($config->{$attr->name}) )  {
      $output = str_replace($attr->replace,"$attr->prefix: ".$config->{$attr->name}.";",$output);
    } else {
      $output = str_replace($attr->replace,"",$output);
    }
  }
  $output = trim($output);
  if ( isset($config->{"menu|font"}) ) {
    $output .= "\r\nbody > header {\r\n  font-family: ".$config->{"menu|font"}." !important;\r\n}";
  }
  header( "Content-Length: ".strlen($output) );
  echo $output;
?>
