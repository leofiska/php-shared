<?php
class sf_contact {

  public $name;
  public $email;
  public $phone;
  public $subject;
  public $message;


  function __construct() {
    $this->name = "";
    $this->phone = "";
    $this->email = "";
    $this->subject = "";
    $this->message = "";
  }

  static function process ( ) {

  }

  static function process_ajax ( ) {
    $page = c_page::get_instance();
    $data =  new static;
    $filter = c_page::get_referer_filter();

    if ( isset($_POST) && !empty($_POST) && isset($_POST['f_name']) && !empty($_POST['f_name'])  ) {
      foreach( $_POST AS $index => $value ) {
        if ( isset($data->{$index}) ) $data->{$index} = trim($value);
      }
      switch($_POST['f_name']) {
        case "contact":
          $message = c_contact::send_message( $data );
          if ( defined("AJAX_OK") && !empty(constant("AJAX_OK")) ) {
            $page->page->xml = array("element"=>array(array("name"=>"contact_message","value"=>$message)));
          } else {
            $page->page->xml = array("element"=>array(array("name"=>"contact_message","value"=>$message)));
          }
          break;
      }
    }
  }


  static function get_section() {

    $page = c_page::get_instance();
    $config = $page->get_config();

    $sentences = sc_sentences::get_sentence_sequency("CONTACT");

    $return = "<h1>$sentences->CONTACT</h1>";
    $return .= "<div class='inline'>";
    $return .= "<div class='rtable'>";
    $return .= "<div class='rtable-cell top'>";
    $return .= "<p class='rcenter indent'>$sentences->CONTACT_MESSAGE<p>";
    $return .= "</div>";
    $return .= "<div class='rtable-cell' id='contact'>";

    $url = c_page::get_clean_filter();
    $url->page = "CONTACT";
    $url = c_page::convert_object_to_url($url);

    $return .= "<form name='contact' action='/$url'><div>";

    $filter = false;

    $main_table = new sc_table();
    sc_table::create_simple_list( $filter, $main_table );

    $table = new sc_table();
    sc_table::create_simple_list( $filter, $table );

    $main_table->content = "";
    if ( isset($config->{"contact|phone"}) && !empty($config->{"contact|phone"}) ) {
      $main_table->content .= "<div class='tr'>";
        $main_table->content .= "<div class='td left top'><h2 style='margin-top: 15px;' class='lowercase'>".sc_sentences::strtolower($sentences->PHONE).":</h2></div>";
      $main_table->content .= "</div>";
      $main_table->content .= "<div class='tr'>";
        $main_table->content .= "<div class='td cursor left middle'><label style='margin-left: 20px;'><img src='/pictures/whatsapp.png' style='width: 15px; height: 15px' /><img src='/pictures/phone.png' style='width: 15px; height: 15px; margin-left: 3px;' /><input af type='text' class='transparent cursor' style='padding: 0px; margin: 0px; margin-left: 5px; width: ".strlen($config->{"contact|phone"})."ch' value='".$config->{"contact|phone"}."' readonly='readonly' onclick=\"this.select()\" /></label></div>";
      $main_table->content .= "</div>";
    }
    $main_table->content .= "<div class='tr'>";
      $main_table->content .= "<div class='td left top'><h2 style='margin-top: 15px;' class='lowercase'>".sc_sentences::strtolower($sentences->EMAIL).":</h2></div>";
    $main_table->content .= "</div>";
    $main_table->content .= "<div class='tr'>";
      $main_table->content .= "<div class='td cursor left middle'><label style='margin-left: 20px;'><img src='/pictures/email.png' style='width: 15px; height: 15px; margin-left: 18px;' /><input type='text' class='transparent cursor' af style='margin: 0px; margin-left: 5px; width: ".strlen($config->{"contact|email"})."ch;' value='".$config->{"contact|email"}."' readonly='readonly' onclick=\"this.select()\"></label></div>";
    $main_table->content .= "</div>";
    $main_table->content .= "<div class='tr'>";
      $main_table->content .= "<div class='td left top'><h2 style='margin-top: 15px;' class='lowercase'>".sc_sentences::strtolower($sentences->FORM).":</h2></div>";
    $main_table->content .= "</div>";

    $table->content = "";
    $url = sc_page::convert_object_to_url($filter);

    $table->content .= "<div class='tr'>";
      $table->content .= "<div class='td extended right top bold lowercase' style='padding: 4px 3px 1px 10px;'>$sentences->NAME: </div>";
      $table->content .= "<div class='td left' style='width: 30vw; padding: 3px 3px;'><input placeholder='$sentences->NAME' type='text' autocapitalize='words' autocomplete='off' name='name' class='w100' value='' /></div>";
    $table->content .= "</div>";
    $table->content .= "<div class='tr'>";
      $table->content .= "<div class='td extended right top lowercase' style='padding: 4px 3px 1px 10px;'>$sentences->SUBJECT: </div>";
      $table->content .= "<div class='td left' style='padding: 3px 3px;'><input type='text' placeholder='$sentences->SUBJECT' autocomplete='off' name='subject' class='w100' value='' /></div>";
    $table->content .= "</div>";
    $table->content .= "<div class='tr'>";
      $table->content .= "<div class='td right extended top lowercase' style='padding: 4px 3px 1px 10px;'>$sentences->PHONE: </div>";
      $table->content .= "<div class='td left' style='padding: 3px 3px;'><input type='tel' placeholder='(xx) xxxxx-xxxx' onkeydown=\"return validate_phone( event )\" autocomplete='off' maxlength='31' name='phone' class='w100' value='' /></div>";
    $table->content .= "</div>";
    $table->content .= "<div class='tr'>";
      $table->content .= "<div class='td right extended top bold lowercase' style='padding: 4px 3px 1px 10px;'>$sentences->EMAIL: </div>";
      $table->content .= "<div class='td left' style='padding: 3px 3px;'><input placeholder='$sentences->EMAIL' type='email' autocomplete='off' name='email' class='w100' value='' /></div>";
    $table->content .= "</div>";
    $table->content .= "<div class='tr'>";
      $table->content .= "<div class='td right extended top bold lowercase' style='padding: 4px 3px 1px 10px;'>$sentences->MESSAGE: </div>";
      $table->content .= "<div class='td left' style='padding: 3px 3px;'><textarea placeholder='$sentences->MESSAGE' style='min-width: 300px; min-height: 120px' name='message' class='w100'></textarea></div>";
    $table->content .= "</div>";

    $main_table->content .= "<div class='tr'>";
      $main_table->content .= "<div class='td left middle'><div style='margin-left: 20px; display: inline-block; width: 400px;'>$table->start$table->content$table->end</div></div>";
    $main_table->content .= "</div>";
    $main_table->content .= "<div class='tr'>";
      $main_table->content .= "<div class='td center top' style='padding-top: 10px;'><div style='display: inline-block; width: 400px;' class='center'>";
        $main_table->content .= "<div class='bp'>";
          $main_table->content .= "<div class='bp-r'>";
            $main_table->content .= "<div class='bp-c glow6'>";
              $main_table->content .= "<label class='pointer iglow4' style='margin-right: 1vw'>".$sentences->SEND."<input onClick=\"send_ajax_form( this.form, this)\" type='button' class='invisible' value='contact'></label>";
            $main_table->content .= "</div>";
            $main_table->content .= "<div class='bp-c glow6'>";
              $main_table->content .= "<label class='pointer iglow4' type='button'>".$sentences->CLEAR."<input type='button' class='invisible' onClick=\"clear_form( this.form )\" value='contact'></label>";
            $main_table->content .= "</div>";
          $main_table->content .= "</div>";
        $main_table->content .= "</div>";
      $main_table->content .= "</div></div></div>";
      $main_table->content .= "<div class='tr'>";
        $main_table->content .= "<div class='td center top' style='padding-top: 10px;'><div style='display: inline-block; width: 400px;' class='center'>";
        $main_table->content .= "<div id='contact_message' class='message'></div>";
    $main_table->content .= "</div></div></div>";
    $return .= $main_table->start.$main_table->content.$main_table->end;
    $return .= "</div></form>";
    $return .= "</div></div></div>";


    return $return;
  }


}
?>
