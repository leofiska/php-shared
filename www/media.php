<?php
  include_once dirname(__DIR__)."/lib/php/init.php";
  define("TYPE","media");

  c_sql::connect();
  c_user::get_instance();

  if ( empty($_GET['img']) ) {
    die();
  }
  switch($_GET['type']) {
    case "original":
      $qry = "media_info";
      break;
    case "medium":
      $qry = "media_medium";
      break;
    case "small":
      $qry = "media_small";
      break;
    default:
      $qry = "media_cover";
      break;
  }
  $query = "SELECT * FROM tb_media WHERE media_alias='".c_sql::escape_string($_GET['img'])."' OR $qry->>'filename' LIKE '".c_sql::escape_string($_GET['img'])."' LIMIT 1";
  $obj = c_sql::get_first_object($query);
  if ( empty($obj) ) {
    c_log::reg("ERROR","could not request object from database");
    die();
  }
  $media = ( !isset($_GET['type'])) ? json_decode($obj->media_info) : json_decode($obj->{$qry});

  if ( stristr($media->mime,"image") ) {
    $path = constant("media")."pictures/".$_GET['type']."/";
  } elseif ( stristr($media->mime,"video") ) {
    $path = constant("media")."videos/original/";
  } else {
    $path = constant("media")."files/";
  }
  $last_modified = strtotime($obj->media_time);
  if ( is_file($path.$media->filename) ) {
    $file = $path.$media->filename;
  } elseif( is_file($path.$media->name) ) {
    $file = $path.$media->name;
  } else die();

  header( "Content-Type: $media->mime" );
  header( "Content-Length: $media->size" );
  header( "Accept-Ranges: 0-".($media->size) );

  if ( stristr($media->mime,"image") && isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $last_modified ) {
    header( "HTTP/1.0 304 Not Modified" );
    header( "Expires: ".gmdate('D, d M Y H:i:s T',time()+5*365*3600) );
    header( "Cache-Control: max-age=604800, public ");
    header( "Pragma: cache" );
  } else {
    if ( stristr($media->mime,"image") ) {
      header( "Last-Modified: ".gmdate('D, d M Y H:i:s T',$last_modified) );
      header( "Expires: ".gmdate('D, d M Y H:i:s T',time()+5*365*3600) );
      header( "Cache-Control: max-age=604800, public ");
      header( "Pragma: cache" );
    } elseif( stristr($media->mime,"video") ) {
      header( "Cache-Control: no-store, no-cache, must-revalidate, max-age=0" );
      header( "Cache-Control: post-check=0, pre-check=0", false );
      header( "Pragma: no-cache" );
    } else {
      header( "Cache-Control: no-store, no-cache, must-revalidate, max-age=0" );
      header( "Cache-Control: post-check=0, pre-check=0", false );
      header( "Pragma: no-cache" );
    }
    $fp = fopen($file,"rb");
    $start = 0;
    $end = $media->size-1;
    $length = $media->size;
    if (isset($_SERVER['HTTP_RANGE'])) {
      $c_start = $start;
      $c_end   = $end;
      list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
      if (strpos($range, ',') !== false) {
          header('HTTP/1.1 416 Requested Range Not Satisfiable');
          header("Content-Range: bytes $start-$end/$size");
          exit;
      }
      if ($range == '-') {
          $c_start = $size - substr($range, 1);
      }else{
          $range  = explode('-', $range);
          $c_start = $range[0];
          $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $media->size;
      }
      $c_end = ($c_end > $end) ? $end : $c_end;
      if ($c_start > $c_end || $c_start > $media->size - 1 || $c_end >= $media->size) {
          header('HTTP/1.1 416 Requested Range Not Satisfiable');
          header("Content-Range: bytes $start-$end/$media->size");
          exit;
      }
      $start  = $c_start;
      $end    = $c_end;
      $length = $end - $start + 1;
      fseek($fp, $start);
      header('HTTP/1.1 206 Partial Content');
    } elseif ( isset($_GET['download']) && !empty($_GET['download']) ) {
      header("Content-disposition: attachment; filename=\"".$media->name."\"");
    }

    header( "Content-Range: bytes $start-$end/$media->size" );

    $buffer = 1024 * 8;
    while( is_resource($fp) && !feof($fp) && ($p = ftell($fp)) <= $end) {
      if ($p + $buffer > $end) {
        $buffer = $end - $p + 1;
      }
      set_time_limit(0);
      echo fread($fp, $buffer);
      flush();
    }
    if ( is_resource($fp) ) fclose($fp);
  }
?>
