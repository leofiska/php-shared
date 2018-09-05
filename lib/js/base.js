function create_form_from_element( el ) {
  var form = document.createElement("form");
  form.action = window.location;
  var f_name = document.createElement("input");
  f_name.type = "text";
  f_name.name = "f_name";
  if ( el.getAttribute && el.getAttribute("sf") != undefined ) f_name.value = el.getAttribute("sf");
  form.appendChild(f_name);
  if ( el.getAttribute != undefined && el.getAttribute("action") != undefined ) {
    form.setAttribute("action",el.getAttribute("action"));
  }
  if ( el.getAttribute != undefined && el.getAttribute("sop") != undefined && el.getAttribute("sov") != undefined ) {
    var action = document.createElement("input");
    action.name = el.getAttribute("sop");
    action.value = el.getAttribute("sov");
    form.appendChild(action);
  }
  if ( el.getAttribute && el.getAttribute("sol") && el.getAttribute("son") ) {
    var action = document.createElement("input");
    action.name = el.getAttribute("son")+"["+el.getAttribute("sol")+"]";
    action.value = el.value;
    form.appendChild(action);
  } else if ( el.getAttribute && el.getAttribute("son") ) {
    var action = document.createElement("input");
    action.name = el.getAttribute("son");
    action.value = el.value;
    form.appendChild(action);
    var t = document.getElementById(el.getAttribute("son")+"_message");
    if ( t ) {
      t.innerHTML = "";
    }
  }
  return form;
}

function rollback_make_editable( el ) {
  var tmp = document.createElement("span");
  if ( el.getAttribute && el.getAttribute("original") != undefined ) tmp.innerHTML = el.getAttribute("original");
  if ( el.getAttribute && el.getAttribute("c") != undefined ) tmp.className = el.getAttribute("c");
  else tmp.innerHTML = el.value;
  if ( el.id != undefined ) tmp.id = el.id;
  tmp.setAttribute("me","");
  if ( el.nextSibling != undefined ) tmp.setAttribute("sb", "");
  var ces = getComputedStyle(el);
  for(i in ces) {
    switch( ces[i] ) {
      case "letter-spacing":
        tmp.style.letterSpacing = ces.getPropertyValue(ces[i]);
      break;
      case "text-transform":
        tmp.style.textTransform = ces.getPropertyValue(ces[i]);
        break;
      case "font-size":
        tmp.style.fontSize = ces.getPropertyValue(ces[i]);
        break;
      case "font-variant":
        tmp.style.fontVariant = ces.getPropertyValue(ces[i]);
        break;
      case "font-weight":
        tmp.style.fontWeight = ces.getPropertyValue(ces[i]);
        break;
    }
  }
  if ( el.getAttribute && el.getAttribute("sop") != undefined ) tmp.setAttribute("sop",el.getAttribute("sop"));
  if ( el.getAttribute && el.getAttribute("sov") != undefined ) tmp.setAttribute("sov",el.getAttribute("sov"));
  if ( el.getAttribute && el.getAttribute("sol") != undefined ) tmp.setAttribute("sol",el.getAttribute("sol"));
  if ( el.getAttribute && el.getAttribute("son") != undefined ) tmp.setAttribute("son",el.getAttribute("son"));
  if ( el.type != undefined ) tmp.type=el.setAttribute("sot",el.type);
  if ( el.getAttribute && el.getAttribute("size") != undefined ) tmp.setAttribute("size",el.getAttribute("size"));
  if ( el.getAttribute && el.getAttribute("sf") != undefined ) { 
    if ( el.onkeyup != undefined ) tmp.setAttribute("soe",el.getAttribute("sf"));
    if ( el.onblur != undefined ) tmp.setAttribute("sob",el.getAttribute("sf"));
  }
  while( el.nextSibling != undefined ) {
    el.parentNode.removeChild( el.nextSibling );
  }
  el.parentNode.appendChild(tmp);
  el.parentNode.removeChild(el);
}
function make_editable( el ) {
  var tmp = document.createElement("input");
  tmp.value = el.innerHTML;
  tmp.setAttribute("c",el.className);
  if ( el.id != undefined ) tmp.id = el.id;
  var ces = getComputedStyle(el);
  for(i in ces) {
    switch( ces[i] ) {
      case "letter-spacing":
        tmp.style.letterSpacing = ces.getPropertyValue(ces[i]);
      break;
      case "text-transform":
        tmp.style.textTransform = ces.getPropertyValue(ces[i]);
        break;
      case "font-size":
        tmp.style.fontSize = ces.getPropertyValue(ces[i]);
        break;
      case "font-variant":
        tmp.style.fontVariant = ces.getPropertyValue(ces[i]);
        break;
      case "font-weight":
        tmp.style.fontWeight = ces.getPropertyValue(ces[i]);
        break;
    }
  }
  if ( el.getAttribute && el.getAttribute("sop") != undefined ) tmp.setAttribute("sop",el.getAttribute("sop"));
  if ( el.getAttribute && el.getAttribute("sov") != undefined ) tmp.setAttribute("sov",el.getAttribute("sov"));
  if ( el.getAttribute && el.getAttribute("sol") != undefined ) tmp.setAttribute("sol",el.getAttribute("sol"));
  if ( el.getAttribute && el.getAttribute("son") != undefined ) tmp.setAttribute("son",el.getAttribute("son"));
  if ( el.getAttribute && el.getAttribute("sot") != undefined ) tmp.type=el.getAttribute("sot");
  if ( el.getAttribute && el.getAttribute("size") != undefined ) {
    tmp.setAttribute("size",el.getAttribute("size"));
    tmp.className = "text ";
    if ( el.getAttribute && el.getAttribute("sb") != undefined ) {
      var tmp2 = document.createElement("DIV");
      tmp2.className = "disabled_button";
      if ( el.getAttribute && el.getAttribute("son") != undefined ) tmp2.id = "save_"+el.getAttribute("son");
      tmp2.onclick = function ( e ) {
        if ( !e ) var e = window.event;      
        if ( e.target == undefined ) return;
        if ( e.target.className.indexOf("disabled" ) != -1 ) return;
        if ( e.target.previousSibling == undefined || e.target.previousSibling.tagName.toUpperCase() != "INPUT" ) return;
        var form = create_form_from_element( e.target.previousSibling );
        send_ajax_form( form, null );
/*          e.target.setAttribute("original",e.target.value);
          e.target.blur();*/
      };
      tmp2.innerHTML=messages['SAVE'];
      var tmp3 = document.createElement("DIV");
      tmp3.className = "button";
      if ( el.getAttribute && el.getAttribute("son") != undefined ) tmp3.id = "cancel_"+el.getAttribute("son");
      tmp3.onclick = function( e ) {
        if ( !e ) var e = window.event;      
        if ( e.target.previousSibling != undefined && e.target.previousSibling.previousSibling != undefined ) {
          rollback_make_editable( e.target.previousSibling.previousSibling );
        }
      };
      tmp3.innerHTML=messages['CANCEL'];
      var tmp4 = document.createElement("P");
      tmp4.className = "message";
//      tmp4.style.width = el.getAttribute("size");
      if ( el.getAttribute && el.getAttribute("son") != undefined ) tmp4.id = el.getAttribute("son")+"_message";
    }
  } else {
    tmp.className = "text transparent ";
    tmp.setAttribute("af","");
  }
  tmp.setAttribute("original",el.innerHTML);
  if ( el.getAttribute && el.getAttribute("soe") != undefined ) {
    tmp.setAttribute("sf",el.getAttribute("soe"));
    tmp.onkeyup = function ( e ) {
      if ( !e ) var e = window.event;
      var id = e.which ? e.which : e.button;
      switch(id) {
        default:
          if ( e.target.nextSibling.className.indexOf("button") != -1 ) {
            if ( e.target.getAttribute && e.target.getAttribute("original") != undefined && e.target.getAttribute("original") == e.target.value ) {
              e.target.nextSibling.className = "disabled_button";
            } else if ( e.target.getAttribute && e.target.getAttribute("original") != undefined && e.target.getAttribute("original") != e.target.value ) {
              e.target.nextSibling.className = "button";
            }
          }
          break;
        case 27:
          if ( e.target.getAttribute && e.target.getAttribute("original") ) {
            e.target.value = e.target.getAttribute("original");
          }
          rollback_make_editable( e.target );
          break;
        case 13:
          var form = create_form_from_element( e.target );
          send_ajax_form( form, null );
          e.target.blur();
          break;
      }
    }
  }
  if ( el.getAttribute && el.getAttribute("sob") ) {
    tmp.onblur = function ( e ) {
      if ( !e ) var e = window.event;
      var form = create_form_from_element( e.target );
      send_ajax_form( form, null );
    };
  }
  el.parentNode.appendChild(tmp);
  if ( tmp2 != undefined ) el.parentNode.appendChild(tmp2);
  if ( tmp3 != undefined ) el.parentNode.appendChild(tmp3);
  if ( tmp4 != undefined ) el.parentNode.appendChild(tmp4);
  el.parentNode.removeChild(el);
  fit ( tmp );
  tmp.focus();
}
function fit( el, offset, mode ) {
  if ( el == undefined || el == null || el.getAttribute == undefined || el.getAttribute == null ) return;
  if ( mode == undefined || mode == null ) {
  if ( el.className.indexOf("collapsed") != -1 ) return;
//  if ( (el.getAttribute != undefined && el.getAttribute("af") != undefined) && (el.getAttribute("af").toUpperCase() == "BOTH" || el.getAttribute("af").toUpperCase() == "HEIGHT") ) {
    if ( (el.getAttribute != undefined) && (el.getAttribute("af") != undefined) && (el.getAttribute("af") != "") ) {
      mode = el.getAttribute("af").toLowerCase();
    } else {
      mode = "width";
    }
  }
  if ( offset == undefined ) {
    if ( el.getAttribute != undefined && el.getAttribute("offsetY") ) {
      var offset = Array();
      offset['height'] = el.getAttribute("offsetY");
    }
    offset == null;
  }
  if ( el.value != undefined ) {
    var tmp = document.createElement("SPAN");
    tmp.innerHTML = el.value.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/(?:\r\n|\r|\n)/g, '<br />.');
  } else {
    var tmp = document.createElement(el.tagName);
    tmp.style.setProperty("display","inline-block");
    tmp.innerHTML = el.innerHTML;
  }
  switch(mode.toLowerCase()) {
    case "both":
      var h = true;
      var w = true;
      break;
    case "height":
      var h = true;
      var w = false;
      tmp.style.width = el.clientWidth+"px";
      break;
    case "width":
    default:
      var h = false;
      var w = true;
      break;
  }
  offsetX = 0;
  offsetY = 0;
  if ( offset != null ) {
    if ( offset['height'] != null ) offsetY = offset['height'];
    if ( offset['width'] != null ) offsetX = offset['width'];
  }
  if ( el.getAttribute && el.getAttribute("size") != undefined ) {
    return "auto";
  }
  tmp.style.visibility = "hidden";
//  var ces = window.getComputedStyle(el,null);
  var ces = getComputedStyle(el);
//  alert(ces.getPropertyValue("letter-spacing");
  for(i in ces) {
    switch( ces[i] ) {
      case "letter-spacing":
      case "text-transform":
      case "font-size":
      case "font-variant":
      case "font-weight":
      case "padding-top":
      case "padding-bottom":
      case "padding-right":
      case "padding-left":
      case "padding":
        tmp.style.setProperty(ces[i],ces.getPropertyValue(ces[i]));
        break;
    }
  }
  document.body.appendChild(tmp);
  var width = (tmp.offsetWidth) ? tmp.offsetWidth : tmp.scrollWidth;
  var height = (tmp.offsetHeight) ? tmp.offsetHeight : tmp.scrollHeight;
  document.body.removeChild(tmp);
  if ( parseInt(offsetX) != offsetX || parseInt(offsetY) != offsetY ) {
    var t = document.createElement("DIV");
    t.style.display="inline-block";
    if ( h ) t.style.height = offsetY;
    if ( w ) t.style.width = offsetX;
    t.style.visibility = "hidden";
    document.body.appendChild(t);
    if ( h ) offsetY = (t.scrollHeight) ? t.scrollHeight : t.offsetHeight;
    if ( w ) offsetX = (t.scrollWidth) ? t.scrollWidth : t.offsetWidth;
    document.body.removeChild(t);
  }
  if ( h ) {
    el.style.height = offsetY+height+"px";
  }
  if ( w ) {
    el.style.width = offsetX+width+"px";
  }
  return width;
}
function close_panels( ) {
  var panels = document.getElementsByClassName("panel_popup");
  if ( !panels ) return;
  for( i in panels ) {
    if ( panels[i].style != undefined ) close_panel(panels[i]);
  }
}
function show_panel( panel ) {
  var panel = document.getElementById(panel.toLowerCase());
  if ( !panel ) return;
  var offset = Array();
  if ( panel.getAttribute != undefined && panel.getAttribute("offsetX") != undefined ) offset['width'] = panel.getAttribute("offsetX");
  if ( panel.getAttribute != undefined && panel.getAttribute("offsetY") != undefined ) offset['height'] = panel.getAttribute("offsetY");
  panel.parentNode.style.display="block";
  fit( panel.parentNode, offset, "BOTH" );
  if ( panel.getAttribute != undefined && panel.getAttribute("focus") != undefined ) {
    var el = document.getElementById(panel.getAttribute("focus").toLowerCase());
    if ( el ) el.focus();
  }
  toogle_panel_scroll( true );
}
function close_panel( panel ) {
  panel.style.setProperty('height','0px');
  panel.style.setProperty('width','0px');
  toogle_panel_scroll( false );
  setTimeout( hide_element, 200, panel );
}
function hide_element( el ) {
  el.style.setProperty('display','none');
}
function render( ) {
  var bar = document.getElementById("bar");
  var barbg = document.getElementById("barbg");
  var cover = document.getElementById("cover");
  var curtain = document.getElementById("curtain");
  var panels = document.getElementsByClassName("panel_popup");
  var offsetY = ( window.scrollY != undefined ) ? window.scrollY : window.pageYOffset;
  var offsetYp = offsetY / (window.innerHeight-bar.offsetHeight);
  var nav = document.getElementById("navigator");
  var panel = null;
  var protect = null;
  var em = null;
  var cm = null;
  var divs = null;
  var divs2 = null;

  if ( offsets["bar"] == undefined ) offsets["bar"] = parseInt(bar.offsetHeight);
  protect = (offsets["bar"]-offsetY) > parseInt(offsets["bar"]*0.6) ? parseInt((offsets["bar"]-offsetY)) : parseInt(offsets["bar"]*0.6);
  if ( protect > 300 ) return;



  if ( cover ) { 
    if ( offsetYp < 1 && (window.innerHeight-offsetY) > bar.offsetHeight ) {
      barbg.style.top = bar.style.top = parseInt((window.innerHeight/8)*(1-offsetYp))+"px";
      cover.setAttribute("cover","1");
      if ( bar ) bar.style.height = parseInt((1-offsetYp)*(offsets["bar"]*0.4)+(offsets["bar"]*0.6))+"px";
      if ( barbg ) barbg.style.height = bar.style.height;
    } else {
      cover.setAttribute("cover","0");
      bar.style.top = "0px";
      barbg.style.top = "0px";
      if ( bar ) bar.style.height = parseInt(offsets["bar"]*0.6)+"px";
      if ( barbg ) barbg.style.height = bar.style.height;
    }
    if ( nav ) nav.style.top = (bar.offsetHeight+bar.offsetTop)+"px";
  } else {
    bar.style.top = "0px";
    barbg.style.top = "0px";
    if ( bar ) bar.style.height = ( (offsets["bar"]-offsetY) > parseInt(offsets["bar"]*0.6) ? (offsets["bar"]-offsetY) : parseInt(offsets["bar"]*0.6) )+"px";
    if ( barbg ) barbg.style.height = bar.style.height;
    if ( nav ) nav.style.top = bar.style.height;
  }

  if ( curtain ) {
    if ( curtain.getAttribute("done") != "1" ) {
      document.body.style.overflowY = "scroll";
      curtain.setAttribute("done","1");
    }
  }

  em = document.getElementsByClassName("extended_menu");
  if ( em ) {
    for( i=0; em[i] != undefined; i++ ) {
      for( j=0; em[i].childNodes[j] != undefined; j++ ) {
        if ( em[i].childNodes[j].firstChild != undefined && em[i].childNodes[j].childNodes[1] != undefined && em[i].childNodes[j].childNodes[1].firstChild != undefined ) {
          em[i].childNodes[j].childNodes[1].firstChild.style.height = Math.ceil((parseInt(bar.style.height)/2)-(parseInt(em[i].childNodes[j].firstChild.offsetHeight)/2)+1)+"px";
        }
      }
    }
  }
  em = null;
  cm = document.getElementsByClassName("compact_menu");
  if ( cm ) {
    for( i=0; cm[i] != undefined; i++ ) {
      divs = cm[i].getElementsByTagName("DIV");
      if ( !divs ) continue;
      if ( divs[0] == undefined ) continue;
      divs2 = divs[0].getElementsByTagName("DIV");
      if ( !divs2 ) continue;
      if ( divs2[0] == undefined ) continue;
      divs2[0].style.height = Math.ceil(((cm[i].parentNode.offsetHeight-cm[i].offsetHeight)/2)-4)+"px";
      divs = null;
      divs2 = null;
    }
  }
  cm = null;
  if ( panels ) {
    for( i in panels ) {
      panel = panels[i];
      if ( panel.style == undefined ) continue;
      if ( parseInt(panel.offsetTop) != (parseInt(bar.offsetHeight+bar.offsetTop)+1) ) {
        panel.style.top = (parseInt(bar.offsetTop+bar.offsetHeight)+1)+"px";
      }
      panel = null;
    }
  }
  cm = null;
}
document.onkeyup = function( e ) {
  if ( !e ) var e = window.event;
  var id = e.which ? e.which : e.button;
  var el = e.target;
  if ( id != 40 && typeof update_list == "function" && el.list != undefined && el.list.id != undefined ) update_list( el );
  el = null;
  id = null;
  e = null;
};
document.onkeypress = function( e ) {
  if ( !e ) var e = window.event;
  var code = e.keyCode ? e.keyCode : e.which;
  var panel = null;
  if ( e.target == undefined ) return;
  switch(code) {
    case 13:
      if ( e.target.type == "textarea" ) break;
      if ( document.getElementsByClassName("suspend_list").length > 0 ) break;
      if ( !e.target.getAttribute || ( e.target.getAttribute("poe") == undefined ) ) break;
      if ( e.target.form != undefined ) {
        send_ajax_form( e.target.form );
      }
      break;
    case 27:
      panel = document.getElementById("panel_button");
      if ( panel ) {
        if ( panel.checked ) close_panels();
      }
      panel = null;
      break;
  }
  panel = null;
  code = null;
  e = null;
};
function toogle_panel_scroll( mode ) {
  if ( mode == undefined || mode == null ) mode = true;
  var el = document.getElementById("panel_button");
  var cm = document.getElementById("compact_button");
  if ( !el ) return;
  if ( mode ) {
    locks['scroll'] = true;
    el.checked = true;
  } else {
    if ( !cm.checked ) locks['scroll'] = false;
    el.checked = false;
  }
  el = null;
  cm = null;
}
function toogle_scroll() {
  var el = document.getElementById("compact_button");
  var panel = document.getElementById("panel_button");
  if ( !el ) return;
  if ( (!el || !el.checked) && (!panel || !panel.checked) ) {
    locks['scroll'] = false;
  } else {
    locks['scroll'] = true;
  }
  el = null;
  panel = null;
}
function sleep( sleepDuration ){
  var now = new Date().getTime();
  while(new Date().getTime() < now + sleepDuration){ /* do nothing */ } 
  now = null;
}
function load() {
}
function download ( ...urls ) {
  // setTimeout( function() { loadpage(url,false); }, 10 );
}

function loadpage( url, newtab ) {
  if ( !newtab || newtab == undefined ) newtab = false;
  if ( keys_pressed[17] || newtab ) {
    window.open(url,"_blank");
  } else {
    window.open(url,"_self");
  }
}
function hide( el ) {
  var el2 = el;
  while ( (!el2.className || el2.className.indexOf("table_cell") == -1) && el2.parentNode ) {
    el2 = el2.parentNode;
  }
  if ( (!el2.className || el2.className.indexOf("table_cell") == -1 || !el2.childNodes[1] || el2.childNodes[1].id != el.childNodes[1].id) && !locks[el.childNodes[1].id] ) {
    el.childNodes[3].style.display = "none";
  }
  else {
    timers[el.childNodes[1].id] = setTimeout( function () { hide( el ); }, 300 );
  }

  els = null;
  el = null;
}
function trim ( str ) { 
  return str.replace(/^\s+|\s+$/g,"");
}
function set_focus( id ) {
  var el = document.getElementById(id);
  if ( el && el.tagName == "INPUT") {
    el.focus();
  } else {
    el = document.getElementsByName(id);
    if ( el ) {
      for( var i =0; el[i] != undefined; i++ ) {
        if ( el[0].tagName != "INPUT" && el[0].tagName != "TEXTAREA" ) continue;
        el[i].focus();
        break;
      }
    }
  }
  el = null;
}
function strstr (haystack, needle, bool ) {
  var pos = 0;
  haystack += '';
  pos = haystack.indexOf(needle);
  if (pos == -1) {
    return false;
  } else {
    if (bool) {
      return haystack.substr(0, pos);
    } else {
      return haystack.slice(pos+1);
    }
  }
  post = null;
}
function strrstr (haystack, needle, bool) {
  var pos = 0;
  haystack += '';
  pos = haystack.lastIndexOf(needle);
  if (pos == -1) {
    return false;
  } else {
    if (bool) {
      return haystack.substr(0, pos);
    } else {
      return haystack.slice(pos);
    }
  }
  post = null;
}
function select_text( el ) {
  var range = null;
  if ( document.selection ) {
    range = document.body.createTextRange();
    range.moveToElementText(el);
    range.select();
  } else if ( window.getSelection ) {
    range = document.createRange();
    range.selectNodeContents( el );
    window.getSelection().removeAllRanges();
    window.getSelection().addRange( range );
  }
  range = null;
}
function getElementsByAttribute( attr, el ) {
  var m_elements = new Array();
  var a_elements = null;
  if ( el ) {
    var a_elements = el.getElementsByTagName('*');
  } else {
    var a_elements = document.getElementsByTagName('*');
  }
  for ( var i=0; i < a_elements.length; i++ ) {
    if ( a_elements[i].getAttribute(attr) !== null ) {
      m_elements.push(a_elements[i]);
    }
    a_elements[i] = null;
  }
  a_elements.length = 0;
  a_elements = null;
  return m_elements;
}
function toogle_visibility ( el, notify ) {
  if ( el == null ) return;
  if ( el.getAttribute == undefined || el.getAttribute == null ) return;
  var mode = (el.getAttribute("tv") != undefined && el.getAttribute("tv") != null ) ? el.getAttribute("tv") : "";
  if ( el.className == undefined || el.className == null ) return;
  if ( el.className.indexOf("collapsed") != -1 ) {
    el.className = el.className.replace("collapsed","expanded");
    if ( notify != undefined && notify != null ) notify.className += " d90";
    switch(mode) {
      case "height":
        fit(el);
        break;
    }
  } else {
    fit(el);
    el.className = el.className.replace("expanded","collapsed");
    switch(mode) {
      case "height":
        el.style.height = "0px";
        break;
    }
    if ( notify != undefined && notify != null ) notify.className = notify.className.replace(" d90","");
  }
  mode = null;
  delete mode;
}
