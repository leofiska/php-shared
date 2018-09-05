<?php
class sc_psql {

  private static $sql = null;
  private static $logs = false;

  public static function enable_logs() {
    self::$logs = true;
  }

  private static function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
  }

  public static function disconnect( ) {
    if ( self::$sql != null ) self::$sql = null;
    if ( self::$logs ) c_log::regf( "SQL", "INFO","disconnecting to database" );
  }

  public static function reconnect( ) {
    if ( self::$sql != null ) {
      pg_connection_reset(self::$sql);
      if ( self::$logs ) c_log::regf( "SQL", "INFO","reconnected to database" );
    }
  }
  public static function connect() {
    if ( !isset($GLOBALS['config']) || !isset($GLOBALS['config']->database->host) || !isset($GLOBALS['config']->database->name) || !isset($GLOBALS['config']->database->username) || !isset($GLOBALS['config']->database->password) ) return false;
    set_error_handler("self::exception_error_handler");
    if ( self::$sql != null ) return self::reconnect();
    if ( self::$logs ) c_log::regf( "SQL", "INFO","connecting to database" );
    for( $i=0; !is_resource(self::$sql) || self::$sql == null && $i<2; $i++ ) {
      try {
        if ( !empty($GLOBALS['config']->database->name) ) {
          self::$sql = pg_connect("host=".$GLOBALS['config']->database->host." port=5432 dbname=".$GLOBALS['config']->database->name." user=".$GLOBALS['config']->database->username." password=".$GLOBALS['config']->database->password);
        } else {
          self::$sql = pg_connect("host=".$GLOBALS['config']->database->host." port=5432 user=".$GLOBALS['config']->database->username." password=".$GLOBALS['config']->database->password);
        }
      } catch (Exception $e) {
        restore_error_handler();
        if ( self::$logs ) c_log::regf( "SQL", "ERROR","could not connect to database: ".$e->getMessage() );
        return false;
      }
      if ( !is_resource(self::$sql) || self::$sql == null ) {
        if ( self::$logs ) c_log::regf( "SQL", "INFO","could not connect to database: ".pg_last_error(self::$sql) );
        if ( self::$logs ) c_log::regf( "SQL", "INFO","retrying access to database: try $i" );
        usleep(100);
      } else {
        restore_error_handler();
        if ( self::$logs ) c_log::regf( "SQL", "INFO","connected to database: ".$GLOBALS['config']->database->name );
        return true;
      }
    };
    restore_error_handler();
    return false;
  }
  public static function select ( $query ) {
    set_error_handler("self::exception_error_handler");
    if ( self::$sql == null || !is_resource(self::$sql) ) self::connect();
    if( pg_connection_status(self::$sql) === PGSQL_CONNECTION_BAD ) self::reconnect();
    if ( self::$logs ) c_log::regf( "SQL", "DEBUG", "select: $query" );
    try {
      if ( !($result = pg_query(self::$sql,$query)) ) {
        restore_error_handler();
        return false;
      }
    } catch ( Exception $e ) {
      restore_error_handler();
      c_log::regf( "SQL", "ERROR","exception on query\r\n---------------\r\n$query\r\n---------------\r\n".$e->getMessage()."\r\n\r\n" );
      return false;
    }
    restore_error_handler();
    return $result;
  }
  public static function update ( $query ) {
    set_error_handler("self::exception_error_handler");
    if ( self::$sql == null || !is_resource(self::$sql) ) self::connect();
    if( pg_connection_status(self::$sql) === PGSQL_CONNECTION_BAD ) self::reconnect();
    if ( self::$logs ) c_log::regf( "SQL", "DEBUG", "update: $query" );
    try {
      if ( !($result = pg_query(self::$sql,$query)) ) {
        restore_error_handler();
        return false;
      }
    } catch ( Exception $e ) {
      restore_error_handler();
      c_log::regf( "SQL", "ERROR","exception on query\r\n---------------\r\n$query\r\n---------------\r\n".$e->getMessage()."\r\n\r\n" );
      return false;
    }
    restore_error_handler();
    return self::affected_rows($result);
  }
  public static function insert ( $query ) {
    set_error_handler("self::exception_error_handler");
    if ( self::$sql == null || !is_resource(self::$sql) ) self::connect();
    if( pg_connection_status(self::$sql) === PGSQL_CONNECTION_BAD ) self::reconnect();
    if ( self::$logs ) c_log::regf( "SQL", "DEBUG", "insert: $query" );
    try {
      if ( !($result = pg_query(self::$sql,$query)) ) {
        restore_error_handler();
        return false;
      }
    } catch ( Exception $e ) {
      restore_error_handler();
      c_log::regf( "SQL", "ERROR","exception on query\r\n---------------\r\n$query\r\n---------------\r\n".$e->getMessage()."\r\n\r\n" );
      return false;
    }
    restore_error_handler();
    $return = pg_fetch_object($result);
    return $return;
  }
  public static function affected_rows( $result ) {
    return pg_affected_rows($result);
  }
  public static function num_rows( $result ) {
    return pg_num_rows($result);
  }

  public static function fetch_object( $result ) {
    if ( !is_resource($result) ) return false;
    return pg_fetch_object($result);
  }

  public static function get_first_object ( $query ) {
    if ( self::$sql == null || !is_resource(self::$sql) ) self::connect();
    if( pg_connection_status(self::$sql) === PGSQL_CONNECTION_BAD ) self::reconnect();
    if ( self::$logs ) c_log::regf( "SQL", "DEBUG", "select: $query" );
    try {
      if ( !($result = pg_query(self::$sql,$query)) ) {
        return false;
      }
    } catch ( Exception $e ) {
      c_log::regf( "SQL", "ERROR","exception on query\r\n---------------\r\n$query\r\n---------------\r\n".$e->getMessage()."\r\n\r\n" );
      return false;
    }
    $row = pg_fetch_object($result);
    return $row;
  }
  public static function escape_string( $string ) {
    if ( is_array($string) || is_object($string) ) {
      c_log::reg( "SQL", "ERROR", "received not a string: ".print_r($string,true) );
    }
    if ( self::$sql == null || !is_resource(self::$sql) ) self::connect();
    if( pg_connection_status(self::$sql) === PGSQL_CONNECTION_BAD ) self::reconnect();
    $escaped = pg_escape_string( self::$sql, $string );
    return $escaped;
  }

  public static function start_transaction() {
    if ( self::$sql == null || !is_resource(self::$sql) ) self::connect();
    if( pg_connection_status(self::$sql) === PGSQL_CONNECTION_BAD ) self::reconnect();
    pg_query(self::$sql,"START TRANSACTION");
  }
  public static function commit() {
    if ( self::$sql == null || !is_resource(self::$sql) ) self::connect();
    if( pg_connection_status(self::$sql) === PGSQL_CONNECTION_BAD ) self::reconnect();
    pg_query(self::$sql,"COMMIT");
  }
  public static function rollback () {
    if ( self::$sql == null || !is_resource(self::$sql) ) self::connect();
    if( pg_connection_status(self::$sql) === PGSQL_CONNECTION_BAD ) self::reconnect();
    pg_query(self::$sql,"ROLLBACK");
  }
  static function get_last_error ( ) {
    return pg_last_error(self::$sql);
  }

  static function count_all ( $query ) {
    if ( self::$sql == null || !is_resource(self::$sql) ) self::connect();
    if( pg_connection_status(self::$sql) === PGSQL_CONNECTION_BAD ) self::reconnect();
/*    if ( preg_match("/^SELECT .* (FROM.*)(?: OFFSET [0-9]+|)$/Us", $query, $out) ) {
      $query = "SELECT count(*) AS count ".trim($out[1]);
    }
    if ( preg_match("/^SELECT .* (FROM.*)(?: LIMIT [0-9]+|)$/Us", $query, $out) ) {
      $query = "SELECT count(*) AS count ".trim($out[1]);
    }
    if ( preg_match("/^SELECT.*(FROM.*)(?: ORDER BY .+|)$/Us", $query, $out) ) {
      $query = "SELECT count(*) AS count ".trim($out[1]);
    }*/
    $query = preg_replace("/(?:ORDER BY.+|LIMIT.+|OFFSET.+)$/","",$query);
    $query = preg_replace("/^.+(FROM.+)$/","SELECT count(*) AS count $1",$query);


//    echo "Count: ".$query."\r\n";
    if ( !($result = c_sql::select($query)) ) return 0;
    if ( !($row = c_sql::fetch_object($result)) ) return 0;
    return $row->count;
  }

}
?>
