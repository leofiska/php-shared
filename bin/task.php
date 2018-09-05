<?php
define("TYPE","task_executor");

include_once dirname(__DIR__)."/lib/php/init.php";
c_log::set_print_on_screen(true);
c_sql::connect();

if ( !isset($argv[1]) || empty($argv[1]) ) {
  c_log::reg("INFO","empty ID received" );
  die();
}
$id = preg_replace("/[\D]/","",$argv[1]);

$query = "SELECT *
          FROM tb_tasks tt WHERE task_id=$id";

$obj = c_sql::get_first_object($query);
if ( empty($obj) ) {
  c_log::reg("INFO","object not found with ID: $id" );
  die();
}
c_log::reg("INFO","object found with ID: $id" );

$obj->task_parameters = json_decode($obj->task_parameters);

$query = "INSERT INTO tb_relation_task_status ( trts_task_id, trts_task_status_id ) VALUES ( $obj->task_id, (SELECT task_status_id FROM tb_task_status WHERE task_status_alias='IDENTIFYING' LIMIT 1) )";
c_sql::insert($query);

$co = $obj->task_parameters->class;
if ( !class_exists($co) ) {
  $query = "INSERT INTO tb_relation_task_status ( trts_task_id, trts_task_status_id ) VALUES ( $obj->task_id, (SELECT task_status_id FROM tb_task_status WHERE task_status_alias='ERROR' LIMIT 1) )";
  c_sql::insert($query);
  $update = c_sql::escape_string(json_encode(array("long_string"=>"NOT_IMPLEMENTED")));
  $query = "UPDATE tb_tasks SET task_done=true,task_result='$update' WHERE task_id=$obj->task_id";
  c_sql::update($query);
  c_log::reg("ERROR","class does not exist \"".$obj->task_parameters->class."\" for object ID: $obj->task_id");
  exit;
}
if ( !method_exists($co,$obj->task_parameters->method) ) {
  $query = "INSERT INTO tb_relation_task_status ( trts_task_id, trts_task_status_id ) VALUES ( $obj->task_id, (SELECT task_status_id FROM tb_task_status WHERE task_status_alias='ERROR' LIMIT 1) )";
  c_sql::insert($query);
  $update = c_sql::escape_string(json_encode(array("long_string"=>"NOT_IMPLEMENTED")));
  $query = "UPDATE tb_tasks SET task_done=true,task_result='$update' WHERE task_id=$obj->task_id";
  c_sql::update($query);
  c_log::reg("ERROR","method \"".$obj->task_parameters->method."\" on class does not exist \"".$obj->task_parameters->class."\" for object ID: $obj->task_id");
  exit;
}
$query = "INSERT INTO tb_relation_task_status ( trts_task_id, trts_task_status_id ) VALUES ( $obj->task_id, (SELECT task_status_id FROM tb_task_status WHERE task_status_alias='PROCESSING' LIMIT 1) )";
c_sql::insert($query);
$result = call_user_func($obj->task_parameters->class.'::'.$obj->task_parameters->method,$obj->task_parameters);

if ( !empty($result) ) {
  $update = c_sql::escape_string(json_encode(array("long_string"=>$result)));
  $query = "INSERT INTO tb_relation_task_status ( trts_task_id, trts_task_status_id ) VALUES ( $obj->task_id, (SELECT task_status_id FROM tb_task_status WHERE task_status_alias='DONE' LIMIT 1) )";
  c_sql::insert($query);
  $query = "UPDATE tb_tasks SET task_done=true,task_result='$update' WHERE task_id=$obj->task_id";
  c_sql::update($query);
  c_log::reg("INFO","done object ID: $obj->task_id");
} else {
  $query = "INSERT INTO tb_relation_task_status ( trts_task_id, trts_task_status_id ) VALUES ( $obj->task_id, (SELECT task_status_id FROM tb_task_status WHERE task_status_alias='ERROR' LIMIT 1) )";
  c_sql::insert($query);
  $query = "INSERT INTO tb_relation_task_status ( trts_task_id, trts_task_status_id ) VALUES ( $obj->task_id, (SELECT task_status_id FROM tb_task_status WHERE task_status_alias='RETRYING' LIMIT 1) )";
  c_sql::insert($query);
}
?>
