<?php
class sc_log {
  
  private $handle;
  private static $logger;
  private $print_on_screen;
  private $filename;

  
  private function __construct() {
    $this->reopen_log_file();
    $this->print_on_screen = false;
  }
 
  private function reopen_log_file() {
    if ( is_resource($this->handle) ) {
      fclose($this->handle);
    }
    $this->filename = $this->gen_log_name();
    $this->handle = fopen($this->filename,"ab");
  }
  private function gen_log_name() {
    if ( !defined("TYPE") ) {
      $filename = $GLOBALS['config']->dir->log.date("Y-m-d").".log";
    } else {
      $filename = $GLOBALS['config']->dir->log.date("Y-m-d")."_".constant("TYPE").".log";
    }
    return $filename;
  }
 
  private function register( $topic, $level, $log = null, $file_only = false ) {
    if ( $log == null ) {
      $log = $level;
      $level = $topic;
      $topic = "";
    }
    if ( !stristr($this->filename,date("Y-m-d")) ) $this->reopen_log_file();
    $callers = debug_backtrace();
    if ( !isset($callers[2]) || !isset($callers[2]['function']) ) {
      $back = "main";
    } else if ( !isset($callers[2]['class'])) {
      $back = "main.".$callers[2]['function'];
    } else {
      $back = $callers[2]['class'].".".$callers[2]['function'];
    }
    while(strlen($back) < 20 ) $back .= " ";
    $time = (microtime(true)-constant("STARTTIME"))*1000;
    $time = number_format($time,0,"","");
    $t = microtime(true);
    $micro = sprintf("%06d",($t - floor($t)) * 1000000);
    $d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );
    if ( !empty($topic) ) {
      $string = $d->format("H:i:s.u")."\t[".constant("UID")."]\t".$time."ms\t$topic\t$level\t$back\t".$log."\r\n";
    } else {
      $string = $d->format("H:i:s.u")."\t[".constant("UID")."]\t".$time."ms\t$level\t$back\t".$log."\r\n";
    }
    fwrite( $this->handle, $string );
    if ( $this->print_on_screen && !$file_only ) echo "$string";
  }
  private function set_ps( $ps ) {
    $this->print_on_screen = $ps;
  }
  
  function __destruct() {
    if ( !is_resource($this->handle) ) {
      fclose($this->handle);
    }
  }
  
  public static function reg( $topic, $level, $log = null) {
    if ( !is_object(self::$logger) ) {
      self::$logger = new c_log();
    }
    self::$logger->register( $topic, $level, $log, false );
  }
  public static function regf( $topic, $level, $log = null) {
    if ( !is_object(self::$logger) ) {
      self::$logger = new c_log();
    }
    self::$logger->register( $topic, $level, $log, true );
  }

  public static function set_print_on_screen( $ps ) {
    if ( !is_object(self::$logger) ) {
      self::$logger = new c_log();
    }
    self::$logger->set_ps($ps);
  }
}

?>
