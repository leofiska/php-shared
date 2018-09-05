<?php
define("TYPE","control");
include_once dirname(dirname(__DIR__))."/lib/php/init.php";
c_sql::connect();

unset($argv[0]);

$processes = array();
$stdin = fopen('php://stdin', 'r');
stream_set_blocking( $stdin, false );
stream_set_timeout( $stdin, 1 );
c_log::set_print_on_screen( true );

foreach($argv as $index => $value ) {
  $file = constant("bin")."services/".strtolower($value).".php";
  if ( !is_file($file) ) continue;

  c_log::reg("DEBUG","opening service: $value");
  $ps = new STDClass();
  $ps->cmd = "/usr/bin/php \"$file\"";
  $descriptorspec = array (
     0 => array("pipe", "r"),
     1 => array("pipe", "w"),
     2 => array("pipe", "w")
  );
  $env = array();
  $cwd = constant("bin")."services/";
  $ps->process = proc_open($ps->cmd, $descriptorspec, $ps->pipes, $cwd, $env);
  $processes[$value] = $ps;
}
while ( true ) {
  $line = trim(fgets($stdin));
  if ( $line == "exit" ) {
    c_log::reg("INFO","exit received");
    foreach($processes as $ps) {
      fwrite($ps->pipes[0],"exit");
    }
    foreach( $processes as $alias => $ps ) {
      $time = time();
      while( true ) {
        $status = proc_get_status($ps->process);
        if ( !$status["running"] ) {
          proc_close($ps->process);
          break;
        }
        if ( time() - $time > 10 ) {
          proc_close($ps->process);
          break;
        }
      }
    }
    break;
  }
  foreach( $processes as $alias => $ps ) {
    $status = proc_get_status($ps->process);
//    echo "$alias -> ".print_r($status,true)."\r\n";
    if ( empty($status["running"]) ) {
      c_log::reg("WARNING","$alias is dead, killing");
      unset($processes[$alias]);
      $file = constant("bin")."services/".strtolower($alias).".php";
      if ( !is_file($file) ) continue;

      c_log::reg("DEBUG","opening service: $alias");
      $ps = new STDClass();
      $ps->cmd = "/usr/bin/php \"$file\"";
      $descriptorspec = array (
         0 => array("pipe", "r"),
         1 => array("pipe", "w"),
         2 => array("pipe", "w")
      );
      $env = array();
      $cwd = constant("bin")."services/";
      $ps->process = proc_open($ps->cmd, $descriptorspec, $ps->pipes, $cwd, $env);
      $processes[$alias] = $ps;
    }
  }
  //c_log::reg("INFO","input: \"$line\"");
  sleep(10);
}
?>
