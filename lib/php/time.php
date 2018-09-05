<?php
class sc_time {
  public static function microtime() {
    return microtime(true)*10000;
  }
}
?>
