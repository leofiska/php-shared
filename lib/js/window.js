window.onload = function ( e ) {
  render();
  var els = document.getElementsByTagName("INPUT");
  for ( i in els ) {
    if ( !els[i].getAttribute ) continue;
    if ( els[i].value == "" ) continue;
    if ( els[i].getAttribute("af") != undefined ) {
      fit(els[i]);
    }
  }
};
window.onkeydown = function( e ) {
  if ( !e ) var e = window.event;
  var id = e.which ? e.which : e.button;
  keys_pressed[id] = true;
  if ( id >= 112 && id <= 123 && id != 116 ) {
    e.preventDefault();
    e.stopPropagation();
    return false;
  }
  switch(id) {
    case 67:
      if ( (keys_pressed[17] != undefined && keys_pressed[17] == true) && (keys_pressed[16] != undefined && keys_pressed[16] == true) ) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
      break;
    case 85:
      if ( keys_pressed[17] != undefined && keys_pressed[17] == true ) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
      break; 
    case 116:
      if ( keys_pressed[17] == undefined || keys_pressed[17] == false ) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
      break; 
  }
};
window.onkeyup = function( e ) {
  if ( !e ) var e = window.event;
  var id = e.which ? e.which : e.button;
  keys_pressed[id] = false;
  var el = e.target;
  if ( el.getAttribute && el.getAttribute("af") != undefined ) fit( el );
  if ( !el || !el.id || el.id == undefined ) return true;
  if ( !keys_update[el.id] || keys_update[el.id] == undefined || keys_update[el.id] == null ) keys_update[el.id] = 0;
  switch(id) {
    case 38:
    case 40:
      if ( !el.getAttribute || el.getAttribute("as") == undefined ) return true;
      keys_update[el.id]++;
      setTimeout( key_update, 600, el.id );
      break;
    case 188:
    case 190:
    case 59:
      if ( el.name && (el.name.indexOf("tags") != -1) ) {
        save( el );
        el.value = "";
        break;
      }
    default:
      if ( !el.getAttribute || el.getAttribute("as") == undefined ) return true;
      var save_el = document.getElementById("save_"+el.id);
      if ( save_el ) {
        save_el.src = "/pictures/unchecked.png";
      }
      keys_update[el.id]++;
      setTimeout( key_update, 600, el.id );
      break;
    case 13:
      if ( !el.getAttribute || el.getAttribute("soe") == undefined ) return true;
      save( el );
      break;
  }
};
window.onblur = function(e) {
  if ( !e ) var e = window.event;
  var el = document.getElementById("hide_on_blur");
  if ( el && el.value == "1" ) document.body.style.visibility = "hidden";
  keys_pressed = new Array();
  float_menu = document.getElementById("float_menu");
  if ( float_menu ) {
    float_menu.style.display = 'none';
    float_menu.innerHTML = "";
  }
};
window.onclick = function( e ) {
  if ( !e ) var e = window.event;
  if ( e.target.getAttribute != undefined && e.target.getAttribute("me") != undefined ) make_editable( e.target );
  if ( e.target.getAttribute != undefined && e.target.getAttribute("soc") != undefined ) {
    var form = create_form_from_element( e.target );
    send_ajax_form( form, null );
    form = null;
  }
};
window.ondblclick = function( e ) {
  if ( !e ) var e = window.event;
  if ( e.target.tagName == "INPUT" || e.target.tagName == "TEXTAREA" ) return;
  var el = document.getElementById("panic");
  if ( el && el.value == "1" ) {
    if ( document.body.style.visibility == undefined || document.body.style.visibility != "hidden" ) {
      document.body.style.visibility = "hidden";
      panic = true;
    } else {
      document.body.style.visibility = "visible";
      panic = false;
    }
  } else {
    document.body.style.visibility = "visible";
  }
};
window.onresize = function(e) {
  var bar = document.getElementById("bar");
  var barbg = document.getElementById("barbg");
  if ( bar ) bar.style.removeProperty('height');
  if ( barbg ) barbg.style.removeProperty('height');
  offsets["bar"] = parseInt(bar.offsetHeight);
  render(); 
  float_menu = document.getElementById("float_menu");
  if ( float_menu ) {
    float_menu.style.display = 'none';
    float_menu.innerHTML = "";
  }
  var el = document.getElementById("compact_button");
  if ( el && el.checked ) {
    el.checked = false;
    toogle_scroll();
  }
};

window.oncontextmenu = function( e ) {
  if ( !e ) var e = window.event;
  if ( e.target != undefined && e.target.type != undefined && (e.target.type == "text" || e.target.type == "email" || e.target.type == "textarea") ) return true;
  var el = e.target;
  for (i=0; i<10 && el && el != undefined && (typeof el.getAttribute === "function"); i++ ) {
    if ( el.getAttribute("rclickable") != null ) return true;
    el = el.parentNode;
  }
  return false;
};
window.setTimeout = function( fRef, mDelay ) {
  if ( typeof(fRef) == "function" ) {
    var argu = Array.prototype.slice.call(arguments,2);
    var f = (function(){ fRef.apply(null, argu); });
    return _st(f, mDelay);
  }
  return _st(fRef,mDelay);
};
window.ontouchmove = document.ontouchmove = window.onwheel = document.onwheel = function( e ) {
  if ( locks['scroll'] == true ) {
    e.preventDefault();
    e.stopPropagation();
    return false;
  }
};
window.requestAnimationFrame = window.requestAnimationFrame
    || window.mozRequestAnimationFrame
    || window.webkitRequestAnimationFrame
    || window.msRequestAnimationFrame
    || function(f){return setTimeout(f, 1000/60)};

window.cancelAnimationFrame = window.cancelAnimationFrame
    || window.mozCancelAnimationFrame
    || function(requestID){clearTimeout(requestID)};
