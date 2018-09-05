<?php
  define("TYPE","root");
  include_once __DIR__."/config.php";
  c_log::set_print_on_screen(false);
  c_sql::enable_logs();

  if ( !c_sql::connect() ) {
    die("please contact system administrator");
  }
  c_page::process();

?>
