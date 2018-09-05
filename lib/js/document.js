document.onscroll = function( e ) {
  if ( !e ) var e = window.event;
  var offsetY = null;
  var gotop = null;
  var middle = null;
  var panels = null;
  var i = null;
  var j = null;

  if ( locks['scroll'] == true ) {
    e.preventDefault();
    e.stopPropagation();
    e = null;
    return false;
  }

  offsetY = ( window.scrollY != undefined ) ? window.scrollY : window.pageYOffset;
  gotop = document.getElementById("gotop");
  if ( gotop ) {
    if ( (parseInt(window.innerHeight/offsetY)) < 3 ) {
      gotop.className = gotop.className.replace("gotop_invisible","gotop_visible");
    } else {
      gotop.className = gotop.className.replace("gotop_visible","gotop_invisible");
    }
  }
  gotop = null;
  render(); 
  middle = document.getElementById("principal");
  if ( middle ) {
    panels = document.getElementsByClassName("panel_item");
    for ( i=0; middle.childNodes[i] != undefined; i++ ) {
      if ( middle.childNodes[i] == undefined ) continue;
      if ( (parseInt(middle.childNodes[i].offsetTop) - parseInt(window.innerHeight/2) ) <  parseInt(offsetY) && (parseInt(middle.childNodes[i].offsetTop) + parseInt(window.innerHeight/2) ) >  parseInt(offsetY) ) {
        if ( panels.length == 0 ) continue;
        for( j=0; panels[j] != undefined; j++ ) {
          if ( middle.childNodes[i].id != "" && panels[j].firstChild.href.indexOf(middle.childNodes[i].id) != -1 ) {
            panels[j].className = "panel_item panel_selected";
          } else {
            panels[j].className = "panel_item";
          }
        }
      }
    }
  }
  get_more();
  middle = null;
  panels = null;
  gotop = null;
  offsetY = null;
  j = null;
};
