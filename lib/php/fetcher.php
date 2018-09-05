<?php
class sc_fetcher {

  private static $sentences;

  public static function add_links( $input ) {
    $links = c_sentences::explode_urls(trim($input));
    if ( ($id = c_page::get_user_id()) ) {
      $id_name = "fetcher_download_user_id";
    } else {
      $id = c_page::get_global_id();
      $id_name = "fetcher_download_global_id";
    }
    $ids = array();
    foreach( $links as $value ) {
      if( empty($value) ) continue;
      $tmp = new STDClass();
      $tmp->original_url = trim($value);
      $tmp->url = c_sentences::clear_url($value);
      $tmp->short_url = trim(strstr($tmp->url,"//"),"/");
      if ( empty($tmp->url) ) continue;
      if ( !c_network::is_url($tmp->short_url) ) continue;
      $query = "SELECT insert_fetcher_download( '".c_sql::escape_string($tmp->url)."', '$id_name', $id )";
      $result = c_sql::insert($query );
      if ( !empty($result) ) $ids[] = $result->insert_fetcher_download;
      if ( $id_name == "fetcher_download_user_id" ) {
        $query = "INSERT INTO tb_excluded_links ( excluded_link, excluded_link_user_id ) VALUES ( '".c_sql::escape_string(c_sentences::clear_url($tmp->url))."', $id )";
        c_sql::insert($query);
      }
    }
    if ( empty($ids) ) return false;
    return $ids;
  }

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
    if ( empty(self::$sentences) ) self::$sentences = c_sentences::get_sentence_sequency("FETCHER");

    $items = array_unique($items);
  
    $return = array();

    $user_id = c_page::get_user_id();
    $global_id = c_page::get_global_id();

    
    $query = "SELECT fetcher_download_id, fetcher_download_items, fetcher_domain_icon, fetcher_domain_alias, fetcher_download_link, fetcher_download_status->>'alias' as status_alias, fetcher_download_status->'sentence'->>'".c_page::get_language()."' AS status, fetcher_download_time  FROM v_fetcher_downloads WHERE ";
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
    while( $row = c_sql::fetch_object($result) ) {
      $return[$row->fetcher_download_id] = self::create_item($row);
    }
    return $return;
  }
  static public function create_item( $row ) {
    $return = "";
    if ( empty(self::$sentences) ) self::$sentences = c_sentences::get_sentence_sequency("FETCHER");
    $items = json_decode($row->fetcher_download_items);
    $date = c_sentences::format_date(strtotime($row->fetcher_download_time));

/*    $return .= "<div class='item_control glow6'>";
    $return .= "<span class='pointer iglow4' onClick=\"send_operation( '', 'refresh_item', '$row->fetcher_download_id' )\" )>".self::$sentences->REFRESH."</span>";
    $return .= "<span class='pointer iglow4' onClick=\"send_operation( '', 'hide', '$row->fetcher_download_id' )\" )>".self::$sentences->HIDE."</span>";
    $return .= "</div>";*/

    $return .= "<div class='rt'>";
      $return .= "<div class='rtr extended'>";
        $return .= "<div class='rtd middle'>";
        if ( !empty($row->fetcher_domain_icon) ) {
          $return .= "<picture><img title='".c_sentences::strtolower($row->fetcher_domain_alias)."' class='icon' src='/pictures/$row->fetcher_domain_icon' /></picture>";
          $video = false;
          $picture = false;
          if ( !empty($items) && is_array($items) ) {
            foreach($items as $item) {
              if ( stristr($item->media_mime,"video") && empty($video) ) {
                $return .= "<picture><img title='".self::$sentences->VIDEO."' class='icon' src='/pictures/video.png' /></picture>";
                $video = true;
              }
              if ( stristr($item->media_mime,"image") && empty($picture) ) {
                $return .= "<picture><img title='".self::$sentences->PICTURE."' class='icon' src='/pictures/picture.png' /></picture>";
                $picture = true;
              }
              if ( !empty($picture) && !empty($video) ) break;
            }
          }
          unset($video);
          unset($picture);
        }
        $return .= "</div>";
        $return .= "<div class='rtd top'>";
          $return .= "<p class='left' style='' title='$row->fetcher_download_link'><input class='transparent' style='font-size: inherit; width: ".strlen($row->fetcher_download_link)."ch;' type='text' onclick=\"this.select()\"  value='$row->fetcher_download_link'></p>";
          $return .= "<p class='left' style=''>".self::$sentences->ADDED." $date</p>";
          $return .= "<p class='left' style=''>$row->status</p>";
        $return .= "</div>";
      $return .= "</div>";
      $return .= "<div class='rtr'>";
        $return .= "<div class='rtd middle'>";
        $return .= "</div>";
        $return .= "<div class='rtd middle'>";
          $return .= "<div class='rt' style='margin-top: 10px;'>";
            if ( is_array($items) && $row->status_alias !== "ERROR" ) {
              foreach($items as $item) {
                $return .= "<div class='rtr'>";
                  $return .= "<div class='rtd top'>";
                    if ( !empty($item->cover_local) ) {
                      $return .= "<picture class='compact'><img class='media' src='/media/medium/$item->cover_local' /></picture>";
                      $return .= "<picture class='extended'><a href='/media/$item->media_local' target='_blank'><img class='media' src='/media/cover/$item->cover_local' /></a></picture>";
                    } else {
                      //$return .= "<picture><a href='/media/$item->media_local' target='_blank'><img class='photo' src='/media/cover/$item->cover_local' /></a></picture>";
                    }
                    $return .= "<div class='compact'>";
                      $return .= "<div class='bp'>";
                        $return .= "<div class='bp-r'>";
                          $return .= "<div class='bp-c glow6'>";
                            $return .= "<span class='caps pointer iglow4' style='margin-right: 1vw' onClick=\"loadpage( '/media/$item->media_local' )\">".self::$sentences->OPEN."</span>";
                          $return .= "</div>";
                          $return .= "<div class='bp-c glow6'>";
                            $return .= "<span class='caps pointer iglow4' style='margin-right: 1vw' onClick=\"loadpage( '/media/get/$item->media_local' )\">".self::$sentences->DOWNLOAD."</span>";
                          $return .= "</div>";
                        $return .= "</div>";
                      $return .= "</div>";
                    $return .= "</div>";
                  $return .= "</div>";
                  $return .= "<div class='rtd top extended' style='padding-left: 5px;'>";
                    $return .= "<p class='left' style=''><input title='".self::$sentences->FOUND_URL."' class='transparent' style='font-size: inherit; width: ".strlen($item->media_url)."ch;' type='text' onclick=\"this.select()\" value='$item->media_url'></p>";
                    if ( isset($item->media_tags) && !empty($item->media_tags) ) $return .= "<p class='left tags caps' style=''>#".implode(" #",$item->media_tags)."</p>";
                    $return .= "<p class='left'>".sc_conversions::shorten_bytes($item->media_size)."</p>";
                    $return .= "<p class='left'><a title='".self::$sentences->DOWNLOAD."' class='link iglow4' href='/media/get/$item->media_local'>$item->media_local</a> $item->media_mime</p>";
                  $return .= "</div>";
                $return .= "</div>";
              }
            }
            unset($items);
          $return .= "</div>";
        $return .= "</div>";
      $return .= "</div>";
    $return .= "</div>";
    if ( $row->status_alias != "DONE" && $row->status_alias != "ERROR" ) {
      $return .= "<input type='hidden' id='$row->fetcher_download_id' name='refresh' value='1' />";
    }
    return $return;
  }
}
?>
