<?php
class sc_users {

  private static $sentences;

  static public function create_short_item( $row ) {

    if ( empty(self::$sentences) ) self::$sentences = c_sentences::get_sentence_sequency("USERS");

    //$date = c_sentences::format_date(strtotime($row->post_time));

    $language_id = c_page::get_language();

    $url = c_page::get_clean_filter();
    $url->page = "USERS";
    $url = c_page::convert_object_to_url($url);
    $return = "";
    $return .= "<p class='left caps' style=''>$row->user_nickname</p>";
    return $return;
  }

}
?>
