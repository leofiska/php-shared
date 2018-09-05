function row_click( e, menu ) {
  var rightclick;
  if ( !e ) var e = window.event;
  if ( e.which ) rightclick = (e.which == 3);
  else if ( e.button ) rightclick = ( e.button == 2 );
  if ( !rightclick ) return;

  float_menu = document.getElementById("float_menu");
  if ( !float_menu ) return;
  var json = eval("arr="+base64_decode(menu));
  var data = "";
  for( var i=0; json[i]; i++) {
    if ( json[i][1] != undefined ) {
      data += "<div><div class='caps pointer noselect left hover' style='width: 100%' onClick=\"loadpage( '"+json[i][1]+"')\">"+json[i][0]+"</div></div>";
    } else {
      data += "<div class='caps cursor noselect left' style='border-bottom: 1px solid #d0d0d0; margin-bottom: 3px; width: 100%;'>"+json[i][0]+"</div>";
    }
  }
  float_menu.innerHTML = data;
  float_menu.style.display = "block";
  var to=0;
  var le=0;
  if ( (e.pageX + float_menu.clientWidth+10) > innerWidth ) {
    le = e.pageX-float_menu.clientWidth-5;
  } else {
    le = e.pageX+5;
  }
  if ( (e.pageY + float_menu.clientHeight+10) > innerHeight ) {
    to = e.pageY-float_menu.clientHeight-5;
  } else {
    to = e.pageY+5;
  }
  float_menu.style.top = to+"px";
  float_menu.style.left = le+"px";
}
