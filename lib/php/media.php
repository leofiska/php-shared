<?php
class sc_media {

  private static $sentences;

  public static function hide_items ( $items ) {
    if ( is_string($items) ) $items = explode(",",trim($items,","));
    $items = array_unique($items);
    $user_id = c_page::get_user_id();
    $global_id = c_page::get_global_id();

    $query = "UPDATE tb_fetcher_downloads SET fetcher_download_hidden=true WHERE ";
    if ( empty($user_id) ) $query .= "fetcher_download_global_id=$global_id AND fetcher_download_user_id is null";
    else $query .= "fetcher_download_global_id=$global_id OR fetcher_download_user_id=$user_id";
    $or = "";
    foreach( $items as $item ) {
      if ( !empty($or) ) $or .= " OR ";
      $or .= "fetcher_download_id=".c_sql::escape_string($item);
    }
    $query .= " AND ( $or )";
    unset($or);
    $result = c_sql::select($query);
    return $items;
  }
  public static function get_items ( $items ) {
    if ( is_string($items) ) $items = explode(",",trim($items,","));
    if ( empty(self::$sentences) ) self::$sentences = c_sentences::get_sentence_sequency("MEDIA");

    $items = array_unique($items);
  
    $return = array();

    $user_id = c_page::get_user_id();
    $global_id = c_page::get_global_id();

    
    $query = "SELECT * FROM tb_media ORDER BY media_id DESC";
//    if ( empty($user_id) ) $query .= "fetcher_download_global_id=$global_id AND fetcher_download_user_id is null";
//    else $query .= "fetcher_download_global_id=$global_id OR fetcher_download_user_id=$user_id";
//    $or = "";
//    foreach( $items as $item ) {
//      if ( !empty($or) ) $or .= " OR ";
//      $or .= "fetcher_download_id=".c_sql::escape_string($item);
//    }
//    $query .= " AND ( $or )";
//    unset($or);
    $result = c_sql::select($query);
    while( $row = c_sql::fetch_object($result) ) {
      $return[$row->media_id] = self::create_item($row);
    }
    return $return;
  }
  static public function create_item( $row ) {
    $return = "";
    if ( empty(self::$sentences) ) self::$sentences = c_sentences::get_sentence_sequency("MEDIA");
    $items = json_decode($row->media_cover);
    $date = c_sentences::format_date(strtotime($row->media_time));

    $return .= "<div class='item_control glow6'>";
    $return .= "<span class='pointer iglow4' onClick=\"send_operation( '', 'refresh_item', '$row->media_id' )\" )>".self::$sentences->REFRESH."</span>";
    $return .= "</div>";

    $item = json_decode($row->media_info);
    $cover = json_decode($row->media_cover);

    $return .= "<div class='table'>";
      $return .= "<div class='table-row'>";
        $return .= "<div class='table-cell middle'>";
        if ( stristr($item->mime,"video") ) {
          $return .= "<picture><img title='".self::$sentences->VIDEO."' class='icon' src='/pictures/video.png' /></picture>";
        } elseif ( stristr($item->mime,"image") ) {
          $return .= "<picture><img title='".self::$sentences->PICTURE."' class='icon' src='/pictures/picture.png' /></picture>";
        }
        $return .= "</div>";
        $return .= "<div class='table-cell top'>";
          $return .= "<p class='left' style=''>".self::$sentences->ADDED." $date</p>";
        $return .= "</div>";
      $return .= "</div>";
      $return .= "<div class='table-row'>";
        $return .= "<div class='table-cell middle'>";
        $return .= "</div>";
        $return .= "<div class='table-cell middle'>";
          $return .= "<div class='table' style='margin-top: 10px;'>";
            $return .= "<div class='table-row'>";
              $return .= "<div class='table-cell top'>";
                $return .= "<picture><a href='/media/$item->name' target='_blank'><img class='photo' src='/media/cover/$cover->name' /></a></picture>";
              $return .= "</div>";
              $return .= "<div class='table-cell top' style='padding-left: 5px;'>";
                if ( isset($item->media_tags) && !empty($item->media_tags) ) $return .= "<p class='left tags caps' style=''>#".implode(" #",$item->media_tags)."</p>";
                $return .= "<p class='left'>".sc_conversions::shorten_bytes($item->size)."</p>";
                $return .= "<p class='left iglow4' style=''><a title='".self::$sentences->DOWNLOAD."' class='link' href='/media/get/$item->name'>$item->name</a></p>";
              $return .= "</div>";
            $return .= "</div>";
          $return .= "</div>";
        $return .= "</div>";
      $return .= "</div>";
    $return .= "</div>";
    return $return;
  }
}
?>
