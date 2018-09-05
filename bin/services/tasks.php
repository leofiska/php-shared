<?php
include_once dirname(dirname(__DIR__))."/lib/php/init.php";

define("TYPE","fetcher");
c_log::reg("INFO","fetcher service started");

c_log::set_print_on_screen(true);
$stdin = fopen('php://stdin', 'r');
stream_set_blocking( $stdin, false );
stream_set_timeout( $stdin, 1 );

$file = constant("bin")."/task.php";
$processes = array();
while ( true ) {

  $query = "SELECT * 
            FROM tb_tasks 
            WHERE task_done=false 
            ORDER BY task_id ASC 
            LIMIT 10";

//  c_log::reg("INFO",Sizeof($processes)." instances of download running");
  if ( ($result = c_sql::select($query)) && c_sql::num_rows($result) > 0 ) {
    if ( Sizeof($processes) < 10 ) {
      c_log::reg("INFO","query executed, ".c_sql::num_rows($result)." objects received");
      if ( c_sql::num_rows($result) > 0 ) {
        while ( $item = c_sql::fetch_object($result) ) {
          c_log::reg("INFO","starting download of $item->task_id");
          if ( isset($processes[$item->task_id]) ) continue;
          $ps = new STDClass();
          $ps->cmd = "/usr/bin/php \"$file\" $item->task_id";
          $descriptorspec = array (
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
          );
          $env = array();
          $cwd = constant("bin");
          $ps->process = proc_open($ps->cmd, $descriptorspec, $ps->pipes, $cwd, $env);
          $processes[$item->task_id] = $ps;
        }
      }
    }
  }
  //c_log::reg("INFO","looking for dead processes");
  foreach( $processes as $alias => $ps ) {
    $status = proc_get_status($ps->process);
    if ( empty($status["running"]) ) {
      if ( isset($ps->remove) ) {
        //c_log::reg("INFO","removing ID: $ps->remove");
        $query = "UPDATE tb_tasks SET task_done=true WHERE task_id=$ps->remove";
        c_sql::select($query);
      }
      unset($processes[$alias]); continue;
    }
  }

  sleep(2);
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
}
?>
