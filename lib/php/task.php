<?php
class sc_task {
  public static function update_task_progress( $value ) {
    if ( !defined("TASK_ID") ) return;
    if ( intval($value) !== $value ) return;
    $query = "UPDATE tb_tasks SET task_progress='".c_sql::escape_string(intval($value))."' WHERE task_id='".c_sql::escape_string(intval(constant("TASK_ID")))."'";
    c_sql::update($query);
  }
}
