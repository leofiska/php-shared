<?php
  include_once dirname(__DIR__)."/lib/php/init.php";

  c_sql::connect();
  $user = c_user::get_instance();

  $filter = sc_page::get_referer_filter();

  if ( !c_user::has_permission($filter->page) ) {
    echo "NOT AUTHORIZED";
    die();
  }

  $co = "f_".strtolower($filter->page);
  if ( !class_exists($co) ) {
    $co = "sf_".strtolower($filter->page);
    if ( !class_exists($co) ) {
      die("function not implemented");
    }
  }
  $return = $co::upload_file();

  header( 'content-type: application/xml; charset=utf-8', true );
  echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
  echo "<json>";
  echo "<![CDATA[".json_encode(array("result"=>array("content"=>array("element"=>$return))) )."]]>";
  echo "</json>";

/*  print_r($filter);

  foreach( $_FILES as $index => $value ) {
    print_r($value);
  }*/


/*  if ( $user->is_logged() ) {
    print_r($_COOKIES);
    print_r($_FILES);
  } else {
    echo $user->get_global_id();
  }(*/
?>
