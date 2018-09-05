<?php
$config = new STDClass();
$config->database = new STDCLass();
$config->dir = new STDCLass();
$config->dir->lib = new STDCLass();

#### EDIT BY USER

//salt for password, don`t lose otherwise passwords would not work anymore
$config->passwordsalt = '';

//root path
$config->dir->root = dirname(__DIR__)."/";

//database information
$config->database->host = '[[-DBHOST-]]';
$config->database->name = '[[-DBNAME-]]';
$config->database->username = '[[-DBUSER-]]';
$config->database->password = '[[-DBPASS-]]';


### SYSTEM USAGE OR ADVANCED EDITION
$config->dir->bin = $config->dir->root.'bin/';
$config->dir->content = $config->dir->root.'content/';
$config->dir->cookies = $config->dir->content.'cookies/';
$config->dir->functions = $config->dir->root.'functions/';
$config->dir->log = $config->dir->root.'logs/';
$config->dir->media = $config->dir->content.'media/';
$config->dir->templates = $config->dir->root.'templates/';

$config->dir->lib->css = $config->dir->root.'lib/css/';
$config->dir->lib->external = $config->dir->root.'lib/external/';
$config->dir->lib->html = $config->dir->root.'lib/html/';
$config->dir->lib->js = $config->dir->root.'lib/js/';
$config->dir->lib->php = $config->dir->root.'lib/php/';
$config->dir->exec = getcwd();

foreach (glob($config->dir->lib->php."*.php") as $filename) {
  include_once $filename;
}
foreach (glob($config->dir->functions."*.php") as $filename) {
  include_once $filename;
}
?>
