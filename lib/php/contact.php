<?php
class c_contact {
  public static function send_message( $data ) {
    $message = array();
    if ( (!isset($data->email) || empty($data->email)) && (!isset($data->phone) || empty($data->phone)) ) return sc_sentences::get_sentence("ERROR_BLANK_CONTACT_INFO");
    if ( !isset($data->message) || empty($data->message) ) return sc_sentences::get_sentence("ERROR_BLANK_MESSAGE");
    if ( isset($data->email) && !empty($data->email) && !sc_email::is_valid_email($data->email) ) return sc_sentences::get_sentence("ERROR_INVALID_EMAIL");

    if ( !empty($message) ) return $message;

    $text = "Contact: $data->name\r\n".
            "Date: ".date("Y-m-d")."\r\n".
            "Time: ".date("H:i:s")."\r\n".
            "Subject: $data->subject\r\n".
            "E-Mail: $data->email\r\n".
            "Phone: $data->phone\r\n".
            "IP Address: ".$_SERVER['REMOTE_ADDR']."\r\n".
            "\r\n---------------------------------------------\r\n".
            "$data->message";

    $page = c_page::get_instance();
    $config = $page->get_config();

    $email = new sc_email();
    $email->set_from($config->notification_email);
    $email->add_bcc( "Leonardo Fischer", "leonardo@fischers.it" );
    $email->add_to( "$config->name", "$config->email" );
    $email->set_reply_to("$data->name <$data->email>");
    $email->set_subject("Contact");
    $email->set_message(nl2br($text));
    $email->queue_email( $data->email, $data->name, $data->subject, $data->message );

    define("AJAX_OK",true);

    return sc_sentences::get_sentence("MESSAGE_SENT");
  }
}
?>
