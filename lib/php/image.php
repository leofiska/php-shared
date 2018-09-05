<?php
class sc_image {
  public static function compare( $file1, $file2 ) {
    $img1 = new STDClass();
    $img2 = new STDClass();
    $img1->path = $file1;
    $img2->path = $file2;
    $tmp = @getimagesize($img1->path);
    if ( empty($tmp) ) {
      echo "error: $img1->path [".filesize($img1->path)."]\r\n";
      return false;
    }
    $img1->mime = $tmp['mime'];
    $img1->width = $tmp[0];
    $img1->height = $tmp[1];
    $tmp = @getimagesize($img2->path);
    if ( empty($tmp) ) {
      echo "error: $img2->path [".filesize($img2->path)."]\r\n";
      return false;
    }
    $img2->mime = $tmp['mime'];
    $img2->width = $tmp[0];
    $img2->height = $tmp[1];

    $compare = new STDClass();
    $compare->width = ($img2->width > $img1->width ) ? $img1->width : $img2->width;
    $compare->height = ($img2->height > $img1->height ) ? $img1->height : $img2->height;

    switch($img1->mime) {
      case "image/jpeg":
        $img1->bin = imagecreatefromjpeg($img1->path);
        break;
      case "image/png":
        $img1->bin = imagecreatefrompng($img1->path);
        break;
      case "image/gif":
        $img1->bin = imagecreatefromgif($img1->path);
        break;
    }
    switch($img2->mime) {
      case "image/jpeg":
        $img2->bin = imagecreatefromjpeg($img2->path);
        break;
      case "image/png":
        $img2->bin = imagecreatefrompng($img2->path);
        break;
      case "image/gif":
        $img2->bin = imagecreatefromgif($img2->path);
        break;
    }
    $compare1 = imagecreatetruecolor($compare->width, $compare->height);
    $compare2 = imagecreatetruecolor($compare->width, $compare->height);
    imagecopyresampled($compare1, $img1->bin, 0, 0, 0, 0, $compare->width, $compare->height, $img1->width, $img1->height);
    imagecopyresampled($compare2, $img2->bin, 0, 0, 0, 0, $compare->width, $compare->height, $img2->width, $img2->height);
    $compare = self::image_compare($compare1, $compare2, 7, 7, 7, 1, 5 );
    if ( !isset($compare['ErrorLevel']) ) return true;
    return false;
  }
  private static function image_compare( $image1, $image2, $RTolerance=0, $GTolerance=0, $BTolerance=0, $WarningTolerance=1, $ErrorTolerance=5 ) {
    if ( is_resource($image1) ) {
      $im = $image1;
    } else {
      if ( !$im = imagecreatefrompng($image1) ) {
        trigger_error("Image 1 could not be opened",E_USER_ERROR);
      }
    }
    if ( is_resource($image2) ) {
      $im2 = $image2;
    } else {
      if ( !$im2 = imagecreatefrompng($image2) ) {
        trigger_error("Image 2 could not be opened",E_USER_ERROR);
      }
    }
    $OutOfSpec = 0;
 
    if ( imagesx($im)!=imagesx($im2) ) die("Width does not match.");
    if ( imagesy($im)!=imagesy($im2) ) die("Height does not match.");
 
 
    //By columns
    for ( $width=0; $width <= imagesx($im)-1; $width++ ) {
      for ( $height=0; $height <= imagesy($im)-1; $height++ ) {
        $rgb = imagecolorat($im, $width, $height);
        $r1 = ($rgb >> 16) & 0xFF;
        $g1 = ($rgb >> 8) & 0xFF;
        $b1 = $rgb & 0xFF;
 
        $rgb = imagecolorat($im2, $width, $height);
        $r2 = ($rgb >> 16) & 0xFF;
        $g2 = ($rgb >> 8) & 0xFF;
        $b2 = $rgb & 0xFF;
 
        if ( !($r1 >= $r2-$RTolerance && $r1 <= $r2+$RTolerance) ) {
          $OutOfSpec++;
        }
        if ( !($g1 >= $g2-$GTolerance && $g1 <= $g2+$GTolerance) ) {
          $OutOfSpec++;
        }
        if ( !($b1 >= $b2-$BTolerance && $b1 <= $b2+$BTolerance) ) {
          $OutOfSpec++;
        }
      }
    }
    $TotalPixelsWithColors = (imagesx($im)*imagesy($im))*3;
 
    $RET['PixelsByColors'] = $TotalPixelsWithColors;
    $RET['PixelsOutOfSpec'] = $OutOfSpec;
 
    if ( $OutOfSpec != 0 && $TotalPixelsWithColors != 0 ) {
      $PercentOut = ($OutOfSpec/$TotalPixelsWithColors)*100;
      $RET['PercentDifference']=$PercentOut;
      if ( $PercentOut >= $WarningTolerance ) { //difference triggers WARNINGTOLERANCE%
        $RET['WarningLevel']=TRUE;
      }
      if ( $PercentOut >= $ErrorTolerance ) { //difference triggers ERRORTOLERANCE%
        $RET['ErrorLevel']=TRUE;
      }
    }
    return $RET;
  } 
  public static function create_cover ( $image, $dest ) {
    if ( empty($dest) || empty($image) ) {
      return false;
    }
    if ( !is_file($image) ) return false;
    if ( is_file($dest) ) unlink($dest);
    $tmp = getimagesize($image);

    $img1 = new STDClass();
    $img1->path = $image;
    $img1->mime = $tmp['mime'];
    $img1->width = $tmp[0];
    $img1->height = $tmp[1];
    unset($tmp);

    $img2 = new STDClass();
    $img2->path = $dest;
    $img2->mime = $img1->mime;
    $img2->width = 120;
    $img2->height = intval(($img1->height / $img1->width) * $img2->width);

    switch($img1->mime) {
      case "image/jpeg":
        $img1->bin = imagecreatefromjpeg($img1->path);
        break;
      case "image/png":
        $img1->bin = imagecreatefrompng($img1->path);
        break;
      case "image/gif":
        system( "/usr/bin/convert -resize $img2->width"."x"."$img2->height \"$img1->path\" \"$img2->path\"" );
        //$img1->bin = imagecreatefromgif($img1->path);
        return true;
        break;
    }
    $img2->bin = imagecreatetruecolor($img2->width, $img2->height);
    imagecopyresampled($img2->bin, $img1->bin, 0, 0, 0, 0, $img2->width, $img2->height, $img1->width, $img1->height);

    switch($img2->mime) {
      case "image/jpeg":
        imagejpeg($img2->bin, $img2->path, 85);
        break;
      case "image/png":
        imagesavealpha( $img2->bin, true);
        ImageAlphaBlending( $img2->bin, false );
        imagepng($img2->bin, $img2->path, 3 );
        break;
      case "image/gif":
        imagegif($img2->bin, $img2->path);
        break;
    }
    return true;
  }
  public static function create_small ( $image, $dest ) {
    if ( empty($dest) || empty($image) ) {
      return false;
    }
    if ( !is_file($image) ) return false;
    if ( is_file($dest) ) unlink($dest);
    $tmp = getimagesize($image);

    $img1 = new STDClass();
    $img1->path = $image;
    $img1->mime = $tmp['mime'];
    $img1->width = $tmp[0];
    $img1->height = $tmp[1];
    unset($tmp);

    $img2 = new STDClass();
    $img2->path = $dest;
    $img2->mime = $img1->mime;

    if ( $img1->width <= 540 ) {
      $img2->width = $img1->width;
    } else {
      $img2->width = 540;
    }
    $img2->height = intval(($img1->height / $img1->width) * $img2->width);

    switch($img1->mime) {
      case "image/jpeg":
        $img1->bin = imagecreatefromjpeg($img1->path);
        break;
      case "image/png":
        $img1->bin = imagecreatefrompng($img1->path);
        break;
      case "image/gif":
  //      $img1->bin = file_get_contents($img1->path);
        //$img1->images = explode("\x00\x21\xF9\x04", $img1->bin);
//        $img1->bin = imagecreatefromgif($img1->path);
        system( "/usr/bin/convert -resize $img2->width"."x"."$img2->height \"$img1->path\" \"$img2->path\"" );
        return true;
        break;
    }
    $img2->bin = imagecreatetruecolor($img2->width, $img2->height);
    imagecopyresampled($img2->bin, $img1->bin, 0, 0, 0, 0, $img2->width, $img2->height, $img1->width, $img1->height);

    switch($img2->mime) {
      case "image/jpeg":
        imagejpeg($img2->bin, $img2->path, 85);
        break;
      case "image/png":
        imagesavealpha( $img2->bin, true);
        ImageAlphaBlending( $img2->bin, false );
        imagepng($img2->bin, $img2->path, 3 );
        break;
      case "image/gif":
        imagegif($img2->bin, $img2->path);
        break;
    }
    return true;
  }
  public static function create_medium ( $image, $dest ) {
    if ( empty($dest) || empty($image) ) {
      return false;
    }
    if ( !is_file($image) ) return false;
    if ( is_file($dest) ) unlink($dest);
    $tmp = getimagesize($image);

    $img1 = new STDClass();
    $img1->path = $image;
    $img1->mime = $tmp['mime'];
    $img1->width = $tmp[0];
    $img1->height = $tmp[1];
    unset($tmp);

    $img2 = new STDClass();
    $img2->path = $dest;
    $img2->mime = $img1->mime;

    if ( $img1->width <= 1280 ) {
      $img2->width = $img1->width;
    } else {
      $img2->width = 1280;
    }
    $img2->height = intval(($img1->height / $img1->width) * $img2->width);

    switch($img1->mime) {
      case "image/jpeg":
        $img1->bin = imagecreatefromjpeg($img1->path);
        break;
      case "image/png":
        $img1->bin = imagecreatefrompng($img1->path);
        break;
      case "image/gif":
//        $img1->bin = imagecreatefromgif($img1->path);
        system( "/usr/bin/convert -resize $img2->width"."x"."$img2->height \"$img1->path\" \"$img2->path\"" );
        return true;
        break;
    }
    $img2->bin = imagecreatetruecolor($img2->width, $img2->height);
    imagecopyresampled($img2->bin, $img1->bin, 0, 0, 0, 0, $img2->width, $img2->height, $img1->width, $img1->height);

    switch($img2->mime) {
      case "image/jpeg":
        imagejpeg($img2->bin, $img2->path, 85);
        break;
      case "image/png":
        imagesavealpha( $img2->bin, true);
        ImageAlphaBlending( $img2->bin, false );
        imagepng($img2->bin, $img2->path, 3 );
        break;
      case "image/gif":
        imagegif($img2->bin, $img2->path);
        break;
    }
    return true;
  }
}
?>
