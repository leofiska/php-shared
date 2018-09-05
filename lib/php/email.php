<?php
class sc_email {

  public static function is_valid_email( $input ) {
    $letters = "abcdefghijklmnopqrstuvwxyz";
    $numbers = "0123456789";
    $special = "_-.@";
    $valid = $letters.$numbers.$special;
    if ( !sc_sentences::contains_only($valid,$input) ) return false;
    if ( !strchr($input,"@") ) return false;
    $tmp = explode("@",$input);
    if ( Sizeof($tmp) != 2 ) return false;
    return self::is_valid_domain($tmp[1]);
  }

  private static function is_valid_domain( $input ) {
    exec( "nslookup -query=mx $input | grep -i exchanger", $out );
    if ( empty($out) ) return false;
    return true;
  }

  private $from;
  private $to;
  private $bcc;
  private $reply_to;
  private $subject;
  private $signature;
  private $message;
  private $language;
  private $attachments;
  private $html;

  public function __construct() {
    $this->from = "";
    $this->to = array();
    $this->bcc = array();
    $this->reply_to = "";
    $this->subject = "";
    $this->signature = "true";
    $this->message = "";
    $this->attachments = array();
    $this->html = "true";
    $this->language = "";
  }

  public function queue_email() {
    foreach( $this as $index => $value ) {
      if ( !is_string($value) || $index == "html" || $index == "signature" ) continue;
      if ( empty($value) ) $this->{$index} = "NULL";
      if ( $index == "language" ) continue;
      $this->{$index} = "'".c_sql::escape_string($value)."'";
    }
    $query = "INSERT INTO tb_emails ( email_subject, email_from, email_html, email_signature, email_text, email_reply_to, email_language ) VALUES ".
             "( $this->subject, $this->from, $this->html, $this->signature, $this->message, $this->reply_to, $this->language ) RETURNING email_id;";
    c_sql::start_transaction();
    $email_id = c_sql::insert($query);
    $email_id = $email_id->email_id;
    if ( empty($email_id) ) {
      c_sql::rollback();
      return "INTERNAL ERROR";
    }
    $query = "";
    if ( Sizeof($this->to) > 0 ) {
      foreach($this->to as $to) {
        if ( !empty($query) ) $query .= ", ";
        $to->name = c_sql::escape_string($to->name);
        $to->email = c_sql::escape_string($to->email);
        $query .= "( $email_id, '$to->name', '$to->email' )";
      }
      $query = "INSERT INTO tb_email_to ( email_to_email_id, email_to_name, email_to_email ) VALUES $query";
      c_sql::insert($query);
    }
    $query = "";
    if ( Sizeof($this->bcc) > 0 ) {
      foreach($this->bcc as $bcc) {
        if ( !empty($query) ) $query .= ", ";
        $bcc->name = c_sql::escape_string($bcc->name);
        $bcc->email = c_sql::escape_string($bcc->email);
        $query .= "( $email_id, '$bcc->name', '$bcc->email' )";
      }
      $query = "INSERT INTO tb_email_bcc ( email_bcc_email_id, email_bcc_name, email_bcc_email ) VALUES $query";
      c_sql::insert($query);
    }
    c_sql::commit();
    return false;
  }
  
  public function set_signature( $state = true ) {
    if ( empty($state) ) $this->signature = "false";
    else $this->signature = "true";
  }
  public function add_bcc( $name, $email ) {
    if ( !is_string($name) || !is_string($email) ) return;
    $item = new STDClass();
    $item->name = $name;
    $item->email = $email;
    $this->bcc[$email] = $item;
  }
  public function add_to( $name, $email ) {
    if ( !is_string($name) || !is_string($email) ) return;
    $item = new STDClass();
    $item->name = $name;
    $item->email = $email;
    $this->to[$email] = $item;
  }
  public function set_from( $from ) {
    if ( is_string($from) ) $this->from = $from;
  }
  public function set_language( $language_id ) {
    if ( is_string($language_id) ) $this->language = "(SELECT language_id FROM tb_languages WHERE language_codeset=".c_sql::escape_string($language_id).")";
    if ( empty($language_id) ) $this->language = "";
  }
  public function set_reply_to( $reply_to ) {
    if ( is_string($reply_to) ) $this->reply_to = $reply_to;
  }
  public function set_subject( $subject ) {
    if ( is_string($subject) ) $this->subject = $subject;
  }
  public function set_message( $message ) {
    if ( is_string($message) ) $this->message = $message;
  }

}
?>
