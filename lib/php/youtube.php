<?php
class sc_youtube {

  public static function fetch( $url ) {

    $video_patterns[] = "<div id='tumblr_video_container_[0-9]+' class='tumblr_video_container' style='.+'><iframe src='([^\']+)'";
    $video_patterns[] = "iframe src='([^\']+)' style='[^\']+' class='embed_iframe tumblr_video_iframe'";

    $tags_patterns[] = "<a href=\"[^\"]+\" class=\"tag\"><span class=\"pound\">\#<\/span>(.+)<\/a>";
    $tags_patterns[] = "<a class=\"tag\" href=\"[^\"]+tagged[^\"]+\"[^\>]*>([^\<]+)<\/a>";

    if ( empty($url) ) return false;

    $urls = array();

    c_log::reg("INFO","fetch: $url");
    $curl = new sc_curl("YOUTUBE");
    $data = $curl->fetch($url);

    $handle = fopen("/tmp/youtube","wb");
    fwrite($handle,$data);
    fclose($handle);

    foreach( $video_patterns as $video_pattern ) {
      if ( !empty($video_found) ) break;
      c_log::reg("INFO","matching video pattern: --$video_pattern--");
      if ( preg_match_all("/".$video_pattern."/i", $data, $output) ) {
        c_log::reg("INFO","match");
        $video_found = true;
        for( $i=0; isset($output[1][$i]); $i++ ) {
          $tmp = new STDClass();
          $tmp->media_url = $output[1][$i];
          $items[] = $tmp; unset($tmp);
        }
        if ( Sizeof($items) > 1 ) {
          c_log::reg("INFO",Sizeof($items)." videos detected");
        } else {
          c_log::reg("INFO",Sizeof($items)." video detected");
        } 
        foreach($items as &$item) {
          $data = $curl->fetch($item->media_url);
          if ( preg_match("/".$video_pattern_3."/i", $data, $output) ) {
            $item->media_url = preg_replace("/^(.+)\/[0-9]{3,4}$/","$1",$output[2]);
            $item->cover_url = $output[1];
            $item->media_tags = isset($tags) ? $tags : "";
          }
        }
      }
    }
    if ( Sizeof($items) == 0 ) {
      c_log::reg("ERROR","could not locate any media");
      return false;
    }
    return $items;
  }
}
?>
