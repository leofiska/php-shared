<?php
define("TYPE","email");

include_once dirname(dirname(__DIR__))."/lib/php/init.php";
c_log::reg("INFO","email service started");
c_log::set_print_on_screen(true);

$stdin = fopen('php://stdin', 'r');
stream_set_blocking( $stdin, false );
stream_set_timeout( $stdin, 1 );
c_sql::connect();

while ( true ) {
  $page = c_page::get_instance();
  $config = $page->get_config();


  $query = "SELECT *
            FROM v_emails
            WHERE email_sent_time IS NULL ORDER BY email_id ASC";
  if ( ($result = c_sql::select($query)) ) {
    while ( $email = c_sql::fetch_object($result) ) {
      c_log::reg("INFO","sending e-mail to $email->email_to");
      if ( !empty($email->language) ) {
       $sentences = c_sentences::get_sentence_sequency("SIGNATURE", false, $email->language); 
      } else {
        //MUST ADD LOCATING THE E-MAIL IN DATABASE TO GET LANGUAGE
        $sentences = c_sentences::get_sentence_sequency("SIGNATURE");
      }
      
      $boundary = hash("md5",time()."--");
      $headers = array();
      if ( !empty($email->email_to) ) $headers[] = "To: ".trim($email->email_to,"{}");
      if ( !empty($email->email_bcc) ) $headers[] = "Bcc: ".trim($email->email_bcc,"{}");
      if ( !empty($email->email_reply_to) ) $headers[] = "Reply-To: ".trim($email->email_reply_to,"{}");
      $headers[] = "From: $email->email_from";
      $headers[] = "Content-Type: multipart/alternative; boundary=$boundary";
      //$headers[] = "Content-Type: text/html; charset=UTF-8";
      $headers[] = "MIME-Version: 1.0";
      if ( !empty($email->email_html) ) {
        $content = "".
                   "--$boundary\r\n".
                   "Content-Type: text/html; charset=UTF-8\r\n\r\n".
                   "<html>\r\n<head>\r\n".
                   "<style type=\"text/css\">\r\n".
                    "@font-face {\r\n".
                    "  font-family: PTSans;\r\n".
                    "  font-weight: normal;\r\n".
                    "  src: url(https://$config->url/fonts/PT_Sans-Web-Regular.ttf);\r\n".
                    "}\r\n".
                    "* {\r\n".
                    "  font-family: \"PTSans\", Segoe UI, Calibri, Verdana, Arial !important;\r\n".
                    "  color: #000000 !important;\r\n".
                    "  text-decoration: none !important;\r\n".
                    "}\r\n".
                    "body {\r\n".
                    "  background-color: #ffffff !important;\r\n".
                    "}\r\n".
                    "a:link, a:hover, a:active, a:visited {\r\n".
                    "  color: inherit !important;\r\n".
                    "  font-weight: inherit !important;\r\n".
                    "  cursor: pointer !important;\r\n".
                    "}\r\n".
                    ".iglow4 {\r\n".
                    "  filter: alpha(opacity=1);\r\n".
                    "  opacity: 1;\r\n".
                    "  transition: opacity 0.4s ease-in 0s !important;\r\n".
                    "  -webkit-transition: opacity 0.4s ease-in 0s !important;\r\n".
                    "  -o-transition: opacity 0.4s ease-in 0s !important;\r\n".
                    "  -moz-transition: opacity 0.4s ease-in 0s !important;\r\n".
                    "}\r\n".
                    ".iglow4:hover {\r\n".
                    "  filter: alpha(opacity=0.4);\r\n".
                    "  opacity: 0.4;\r\n".
                    "}\r\n".
                    "img.bigicon {\r\n".
                    "  height: 20px !important;\r\n".
                    "  width: 20px !important;\r\n".
                    "  margin-right: 3px !important;\r\n".
                    "}\r\n".
                   "</style>\r\n".
                   "</head>\r\n<body>\r\n".
                   "$email->email_text";

                   if ( !empty($email->email_signature) ) {
                     $content .= "<br /><br />\r\n".
                                 "$sentences->YOURS_TRULY,\r\n".
                                 "<div style='height: 10px'></div><div style='display: table;'>\r\n".
                                 "<div style='display: table-row'>\r\n".
                                 "<div style='display: table-cell;  vertical-align: middle;'>\r\n".
                                 "<img style='height: 90px' src='https://$config->url/pictures/signature.png' />\r\n".
                                 "</div>\r\n".
                                 "<div style='display: table-cell; vertical-align: middle;'>\r\n".
                                 "<div style='display: inline-block; margin-left: 20px; padding: 5px 0px 10px 20px; border-left: 2px solid black'>\r\n".
                                 "<div style='font-weight: bold; padding-bottom: 5px;'>$config->name</div>\r\n".
                                 "<div style=''>$sentences->DEVELOPER, <a class='iglow4' href='https://$config->url' target='_blank'>$config->url</a></div>\r\n".
                                 "<div style='padding-bottom: 5px;'>$config->phone</div>\r\n".
                                 "<div><a href='https://$config->url' target='_blank'><img class='bigicon' src='https://$config->url/pictures/signature.png' /></a>\r\n";
                     foreach( $config as $index => $value ) {
                       if ( !stristr($index,"social_") ) continue;
                       $index = str_replace("social_","",$index);
                       $content .= "<a href='$value' target='_blank'><img class='bigicon' src='https://$config->url/pictures/".c_sentences::strtolower($index).".png' /></a>\r\n";
                     }
                   }
                   $content .= "</div>\r\n".
                               "</div>\r\n".
                               "</div>\r\n".
                               "</div>\r\n".
                               "</div>\r\n";
        $content .= "</body>\r\n</html>\r\n";
      } else {
        $content = "\r\n".
                   "--$boundary\r\n".
                   "Content-Type: text/plain\r\n\r\n".
                   str_replace("<br />","\r\n",$email->email_text);
         if ( !empty($email->email_signature) ) {
           $content .= "\r\n\r\n".
                       "$sentences->YOURS_TRULY,\r\n".
                       "$config->name\r\n".
                       "$sentences->DEVELOPER, $config->url".
                       "$config->phone\r\n";
         }
      }
      $content .= "\r\n\r\n--$boundary--\r\n";
      if ( @mail( "", $email->email_subject, $content, implode("\r\n",$headers) ) ) {
        $query = "UPDATE tb_emails SET email_sent_time=now() WHERE email_id=$email->email_id";
        c_sql::select($query);
      }
    }
  }
  //echo c_sql::get_last_error()."\r\n";
  sleep(5);
  $line = trim(fgets($stdin));
  if ( $line == "exit" ) {
    c_log::reg("INFO","exit received");
    break;
  }
}
?>
