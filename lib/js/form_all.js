var form_data = new Array();
async function send_ajax_form( form, el ) {
  var found = false;
  var tmpe1 = null;
  var mes = null;
  var post = null;
  var i = null;
  var f_name = null;

  if ( el == undefined || !el ) {
    for ( i=0; form[i]; i++ ) {
      if ( (form[i].getAttribute && form[i].getAttribute("default") != undefined) ) {
        el = form[i];
        break;
      }
      if ( form[i].name != undefined && form[i].name == "f_name" ) {
        el = form[i];
        found = true;
      }
    }
  }
  if ( el == undefined || !el ) return false;
  if ( !found ) {
  tmpel = document.createElement("INPUT");
    tmpel.type='hidden';
    tmpel.name="f_name";
    tmpel.value=el.value;
    form.appendChild(tmpel);
  }
  if ( el.getAttribute != undefined && el.getAttribute("notify") != undefined ) {
    mes = document.getElementById(el.getAttribute("notify"));
  } else {
    mes = document.getElementById(el.value+"_message");
  }
  if ( mes ) mes.innerHTML = messages['PROCESSING'];
  post = "";
  for ( i=0; form[i]; i++ ) {
   if ( !form[i].name || form[i].name == "command" ) continue;
    form[i].style.removeProperty("border-color");
    switch(form[i].type) {
      case "text":
      case "password":
        if ( post != "" ) post += "&";
        post += encodeURI(form[i].name)+"=";
        if ( form[i].getAttribute && form[i].getAttribute("lowercase") != undefined ) {
          form[i].value = form[i].value.toLowerCase();
        } else if ( form[i].getAttribute && form[i].getAttribute("uppercase") != undefined ) {
          form[i].value = form[i].value.toUpperCase();
        }
        if ( form[i].getAttribute && form[i].getAttribute("crypted") != undefined ) {
          post += sha256(form[i].value);
        } else {
          post += encodeURIComponent(form[i].value);
        }
        break;
      case "checkbox":
        if ( form[i].checked == false ) continue;;
        if ( post != "" ) post += "&";
        post += encodeURI(form[i].name)+"="+encodeURI(form[i].checked);
        break;
      case "hidden":
        if ( form[i].value == "" ) continue;
        if ( form[i].name.indexOf("_clear") != -1 || form[i].id == "default" ) continue;
        if ( post != "" ) post += "&";
        post += encodeURI(form[i].name)+"="+encodeURIComponent(form[i].value);
        break;
      default:
        if ( post != "" ) post += "&";
        post += encodeURI(form[i].name)+"="+encodeURIComponent(form[i].value);
        break;
    }
  }
  if ( form.name != undefined ) {
    mes = document.getElementById(form.getAttribute("name")+"_message");
    if ( mes ) mes.innerHTML = messages['SENDING'];
    mes = null;
  }
  if ( form.action != undefined ) {
    send_to_server( post, storno_ajax_form, form.getAttribute("action") );
  } else {
    send_to_server( post, storno_ajax_form );
  }
  f_name = document.getElementsByName("f_name");
  for( i=0; f_name != undefined && f_name[i] != undefined; i++ ) {
    f_name[i].parentNode.removeChild(f_name[i]);
  }
  found = false;
  tmpe1 = null;
  mes = null;
  post = null;
  i = null;
  f_name = null;
  delete found;
  delete tmpe1;
  delete mes;
  delete post;
  delete i;
  delete f_name;
}
async function storno_ajax_form( pageXML ) {
  var slept = false;
  if ( !pageXML ) return;
  if ( !pageXML.getElementsByTagName("json") ) return;
  var page = pageXML.getElementsByTagName("json")[0];
  if ( !page || !page.firstChild ) return;
  var json = eval("arr="+page.firstChild.nodeValue);
//  if ( json.id != request_id ) return;
  if ( request_id < 0 ) request_id = 1;
  if ( json.result != undefined && json.result.content != undefined && json.result.content.loadpage != undefined ) {
    if ( json.result.content.sleep != undefined ) {
      setTimeout( loadpage, json.result.content.sleep, "/"+json.result.content.loadpage );
      slept = true;
    } else {
      loadpage("/"+json.result.content.loadpage );
      return;
    }
  }
  if ( json.result != undefined && json.result.content != undefined && json.result.content.focus != undefined ) {
    setTimeout( set_focus, 300, json.result.content.focus );
  }
  if ( json.result != undefined && json.result.content != undefined && json.result.content.reload != undefined ) {
    if ( json.result.content.sleep != undefined ) {
      setTimeout( location.reload(), json.result.content.sleep );
      slept = true;
    } else {
      location.reload();
      return;
    }
  }
  /*if ( json.result != undefined && json.result.content != undefined && json.result.content.sleep != undefined ) {
    sleep(json.result.content.sleep);
  }*/
  update_elements( json );
  pageXML = null;
  delete pageXML;
  json = null;
  delete json;
  page = null;
  delete page;
}
async function update_elements( json ) {
  var tmp = null;
  var tmp2 = null;
  var i = 0;
  var k = 0;
  var counter = 0;
  var _t = null;
  var t = null;
  var t1 = null;
  var t2 = null;
  var offset = null;

  if ( json.result != undefined && json.result.content != undefined && json.result.content.element != undefined ) {
    for( i=0; json.result.content.element[i] != undefined; i++ ) {
      tmp = document.getElementById(json.result.content.element[i].name);
      if ( tmp ) {
        switch(tmp.tagName.toUpperCase()) {
          case "P":
          case "SECTION":
            if ( typeof json.result.content.element[i].value === 'object' ) {
              tmp.innerHTML = "";
              for( j in json.result.content.element[i].value ) {
                tmp.innerHTML += json.result.content.element[i].value[j]+"<br />";
              }
             } else {
              tmp.innerHTML = json.result.content.element[i].value;
           }
            break;
          case "PRE":
            if ( json.result.content.element[i].operation == undefined || json.result.content.element[i].operation == "replace" ) {
              tmp.innerHTML = json.result.content.element[i].value;
            }else if ( json.result.content.element[i].operation == "append" ) {
              tmp.innerHTML += json.result.content.element[i].value;
            }
            break;
          case "DIV":
          if ( json.result.content.element[i].value != undefined && json.result.content.element[i].operation == undefined || json.result.content.element[i].operation == "replace" ) {
            if ( typeof json.result.content.element[i].value === 'object' ) {
                tmp.innerHTML = "";
                counter = 0;
                for( j in json.result.content.element[i].value ) {
                  _t = document.getElementById(j);
                  if ( _t && counter == 0 ) {
                    if ( _t && _t.form != undefined ) {
                      for ( k=0; _t.form[k]; k++ ) {
                        if ( _t.form[k].getAttribute || _t.form[k].getAttribute('error') == undefined ) continue;
                        _t.form[k].removeAttribute("error");
                      }
                    }
                    _t.setAttribute("error","error");
                  }
                   counter++;
                  t = document.createElement("DIV");
                  t1 = document.createElement("DIV");
                  t.className = "center";
                  if ( document.getElementById(j)) {
                     t1.setAttribute("el",j);
                    t1.className = "inline glow4 pointer";
                    t1.onclick = function (e) {
                      if ( !e ) var e = window.event;
                      t2 = document.getElementById(e.target.getAttribute("el"));
                      if ( t2 ) t2.focus();
                      t2 = null;
                    };
                  }
                  t1.innerHTML = json.result.content.element[i].value[j];
                  t.appendChild(t1);
                  tmp.appendChild(t);
                  t = null;
                  t1 = null;
                }
              } else {
                tmp.innerHTML = json.result.content.element[i].value;
              }
              if ( json.result.content.element[i].parent != undefined ) {
                for( j in json.result.content.element[i].parent ) {
                  tmp.parentNode.style.setProperty(j,json.result.content.element[i].parent[j]);
                }
              }
              if ( json.result.content.element[i].fit != undefined ) {
                fit( tmp, json.result.content.element[i].fit );
              }
              if ( json.result.content.element[i].parent_fit != undefined ) {
                offset = Array();
                offset['width'] = 0;
                offset['height'] = 0;
                if ( json.result.content.element[i].parent_offsetX != undefined ) offset['width'] = json.result.content.element[i].parent_offsetX;
                if ( json.result.content.element[i].parent_offsetY != undefined ) offset['height'] = json.result.content.element[i].parent_offsetY;
                fit( tmp.parentNode, offset, json.result.content.element[i].parent_fit );
                offset = null;
              }
            } else if ( json.result.content.element[i].operation == "change_class" ) {
              tmp.className = json.result.content.element[i].value;
            } else if ( json.result.content.element[i].operation == "add_before" ) {
              if ( json.result.content.element[i].id != undefined ) {
                tmp2 = document.getElementById(json.result.content.element[i].id);
                if ( tmp2 ) {
                  if ( json.result.content.element[i].dd == undefined || json.result.content.element[i].dd == false ) {
                    tmp2.parentNode.parentNode.removeChild(tmp2.parentNode);
                  } else continue;
                }
                tmp2 = null;
              }  
              tmp.innerHTML = json.result.content.element[i].value+tmp.innerHTML;
            } else if ( json.result.content.element[i].operation == "remove_parent" ) {
              if ( tmp.parentNode.nextSibling != undefined ) {
                if ( (window.pageYOffset) > tmp.offsetTop ) {
                  window.scrollBy(0,-(tmp.offsetHeight*1));
                }
                tmp2 = tmp.parentNode.nextSibling;
                tmp2.className = "";
                tmp2.style.marginTop = tmp.parentNode.offsetHeight+"px";
                tmp.parentNode.parentNode.removeChild(tmp.parentNode);
                setTimeout( reset_margin, 10, tmp2 );
                tmp2 = null;
              } else {
                tmp.parentNode.parentNode.removeChild(tmp.parentNode);
              }
            }
            break;
          case "HEADER":
            if ( json.result.content.element[i].operation == undefined || json.result.content.element[i].operation == "replace" ) {
              tmp.innerHTML = json.result.content.element[i].value;
              render();
            }
            break;
          case "IMG":
            tmp.src = json.result.content.element[i].value;
            break;
          case "INPUT":
          case "TEXTAREA":
            if ( json.result.content.element[i].operation != undefined && json.result.content.element[i].operation == "remove" ) {
               tmp.parentNode.removeChild(tmp);
            }
            if ( tmp.type.toLowerCase()=="checkbox" ) {
              if ( json.result.content.element[i].checked != undefined ) {
                tmp.removeAttribute("checked");
                tmp.setAttribute("checked",json.result.content.element[i].checked);
              }
            }
            if ( tmp.getAttribute != undefined && tmp.getAttribute("original") != undefined && json.result.content.element[i].original != undefined ) {
              tmp.setAttribute("original",json.result.content.element[i].original);
            }
            if ( json.result.content.element[i].rme != undefined ) {
              if ( json.result.content.element[i].sleep != undefined ) {
                setTimeout( rollback_make_editable, json.result.content.element[i].sleep, tmp );
              } else {
                rollback_make_editable( tmp );
              }
            }
            if ( tmp.setAttribute && json.result.content.element[i].value != undefined ) {
              tmp.value = json.result.content.element[i].value;
            }
            break;
        }
        if ( json.result.content.element[i].operation != undefined ) {
          switch(json.result.content.element[i].operation) {
            case "remove":
              tmp.parentNode.removeChild(tmp);
              break;
          }
        }
        if ( json.result.content.element[i].set_property != undefined ) {
          for( j in json.result.content.element[i].set_property) {
            tmp.style.setProperty(j,json.result.content.element[i].set_property[j]);
          }
        }
        if ( json.result.content.element[i].set_attribute != undefined ) {
          for( j in json.result.content.element[i].set_attribute) {
            tmp.style.setAttribute(j,json.result.content.element[i].set_attribute[j]);
          }
        }
        if ( json.result.content.element[i].fit != undefined ) {
          if ( tmp.getAttribute != undefined && tmp.getAttribute != null && tmp.getAttribute("af") != undefined ) {
            fit( tmp );
          } else {
            var el = (json.result.content.element[i].fit != "") ? document.getElementById(json.result.content.element[i].fit) : null;
            if ( el ) fit( el );
            else {
              var els = getElementsByAttribute( "af", tmp );
              if ( els.length > 0 ) {
                for (j in els ) {
                  fit( els[j] );
                }
              }
            }
          }
        }
        if ( json.result.content.element[i].remove != undefined ) {
          if ( json.result.content.element[i].sleep == undefined ) {
            tmp.parentNode.removeChild(tmp);
          } else {
            setTimeout(remove_element, json.result.content.element[i].sleep, json.result.content.element[i].name);
          }
        }
      }
      tmp = null;
    }
  }
  tmp = null;
  json = null;
  els = null;
  el = null;
}
async function remove_element( id ) {
  var el = document.getElementById(id);
  if ( !el ) return;
  el.parentNode.removeChild(el);
  el = null;
  delete el;
}
function reset_margin( el ) {
  if ( !el || el == undefined ) return;
  el.className = "margin-top-effect";
  el.style.marginTop = "0px";
  el = null;
  delete el;
}
function clear_form( form ) {
  var i = null;
  var mes = null;

  for ( i=0; form[i]; i++ ) {
   if ( !form[i].name || form[i].type == 'hidden' ) continue;
    switch(form[i].type) {
      case "text":
      case "email":
      case "number":
      case "date":
      case "password":
      case "textarea":
        el = document.getElementById(form[i].name+"_default");
        if ( el && el.value != undefined ) form[i].value = el.value;
        else form[i].value = "";
        form[i].style.visibility = 'visible';
        break;
      case "checkbox":
        form[i].checked = false;
        break;
    }
  }
  if ( form.name != undefined ) {
    mes = document.getElementById(form.getAttribute("name")+"_message");
    if ( mes ) mes.innerHTML = "";
    mes = null;
  }
  mes = null;
  delete mes;
  i = null;
  delete i;
  form = null;
  delete form;
}
function form_key_pressed( e, el ) {
  if ( !e ) e = window.event;
  var code = e.keyCode ? e.keyCode : e.which;
  switch(code) {
    case 27:
      el.blur();
      if ( form_data[el.id] != undefined )  el.value = form_data[el.id];
      el.name = "";
     // alert(form_data[el.id]);
      break;
    default:
      if ( !el.name ) {
        el.name = el.id;
        form_data[el.id] = el.value;
      }
      break;
  }
  code = null;
  e = null;
  el = null;
  delete code;
  delete e;
  delete el;
}
function set_name_as_id( el ) {
  if ( !el.name ) {
    el.name = el.id;
    return true;
  }
  return false;
}
function clear_name( el ) {
  el.name = "";
}
function toogle_select_all( e, form ) {
  if ( !e ) var e = window.event;
  var i = null;
  var allchecked = null;
 
  if ( e.target.name && e.target.name == "selectall" ) {
    for ( i=0; form[i]; i++ ) {
      if ( form[i].type != "checkbox" ) continue;
      if ( form[i].name == undefined || !form[i].name ) continue;
      form[i].checked = e.target.checked;
    }
  } else {
    if ( !e.target.checked ) {
      for ( i=0; form[i]; i++ ) {
        if ( form[i].type != "checkbox" ) continue;
        if ( form[i].name == "selectall" ) form[i].checked = false;
        break;
      }
    } else {
      allchecked = true;
      for ( i=0; form[i]; i++ ) {
        if ( form[i].type != "checkbox" ) continue;
        if ( form[i].name == "selectall" ) continue;
        if ( form[i].checked == false ) {
          allchecked = false;
          break;
        }
      }
      if ( allchecked ) {
        for ( i=0; form[i]; i++ ) {
          if ( form[i].type != "checkbox" ) continue;
          form[i].checked = true;
          break;
        }
      }
    }
  }
  fix_list_selection(form);
  i = null;
  allchecked = null;
  e = null;
  delete i;
  delete e;
  delete allchecked;
  return;
}
function fix_list_selection( form ) {
  var counter = 0;
  for ( var i=0; form[i]; i++ ) {
    if ( form[i].type != "checkbox" ) continue;
    if ( form[i].name == "selectall" ) continue;
    if ( form[i].checked ) {
      counter++;
      if ( form[i].parentNode.parentNode.className.indexOf("item_selected") == -1 ) form[i].parentNode.parentNode.className = form[i].parentNode.parentNode.className += " item_selected";
    } else {
      if ( form[i].parentNode.parentNode.className.indexOf("item_selected") != -1 ) form[i].parentNode.parentNode.className = form[i].parentNode.parentNode.className = form[i].parentNode.parentNode.className.replace("item_selected","");
    }
  }
  for ( var i=0; form[i]; i++ ) {
    if ( form[i].type == "submit" || form[i].type == "button" ) {
      if ( has_name(form, form[i].value+"_always") ) {
        form[i].disabled = false;
        form[i].parentNode.className = "caps";
      } else if ( counter == 1 && has_name(form, form[i].value+"_single") ) {
        form[i].disabled = false;
        form[i].parentNode.className = "caps";
      } else if ( counter > 0 && has_name(form, form[i].value+"_any") ) {
        form[i].disabled = false;
        form[i].parentNode.className = "caps";
      } else if ( counter > 1 && has_name(form, form[i].value+"_multiple") ) {
        form[i].disabled = false;
        form[i].parentNode.className = "caps";
      } else {
        form[i].disabled = true;
        form[i].parentNode.className = "caps disabled";
      }
    }
  }
}
function has_name( form, name ) {
  for ( var i=0; form[i]; i++ ) {
    if ( form[i].type != 'hidden' ) continue;
    if ( form[i].name == name ) return true;
  }
  return false;
}
function validate_number_with_minus( e ) {
  if ( !e ) var e = window.event;
  var id = e.which ? e.which : e.button;
  var el = e.target;
  if ( id >= 48 && id <= 57 ) return true;
  if ( id == 8 ) return true;
  if ( id == 118 ) return true;
  if ( id == 45 ) return true;
  if ( id == undefined ) return true;
  return false;
}
function validate_number( e ) {
  if ( !e ) var e = window.event;
  var id = e.which ? e.which : e.button;
  var el = e.target;
  if ( id >= 48 && id <= 57 ) return true;
  if ( id == 118 ) return true;
  if ( id == 8 ) return true;
  if ( id == undefined ) return true;
  return false;
}
function validate_alphanumeric( e ) {
  if ( !e ) var e = window.event;
  var id = e.which ? e.which : e.button;
  var el = e.target;
  if ( id >= 48 && id <= 57 ) return true;
  if ( id >= 65 && id <= 90 ) return true;
  if ( id >= 97 && id <= 122 ) return true;
  if ( id >= 224 && id <= 252 ) return true;
  if ( id == 118 ) return true;
  if ( id == 8 ) return true;
  if ( id == 32 ) return true;
  if ( id == 46 ) return true;
  if ( id == undefined ) return true;
  return false;
}
function validate_alphabetic( e ) {
  if ( !e ) var e = window.event;
  var id = e.which ? e.which : e.button;
  var el = e.target;
  if ( id >= 65 && id <= 90 ) return true;
  if ( id >= 97 && id <= 122 ) return true;
  if ( id >= 224 && id <= 252 ) return true;
  if ( id == 8 ) return true;
  if ( id == 32 ) return true;
  if ( id == undefined ) return true;
  return false;
}
function validate_alphabetic_with_blank( e ) {
  if ( !e ) var e = window.event;
  var id = e.which ? e.which : e.button;
  var el = e.target;
  if ( id >= 65 && id <= 90 ) return true;
  if ( id >= 97 && id <= 122 ) return true;
  if ( id >= 224 && id <= 252 ) return true;
  if ( id == 8 ) return true;
  if ( id == 32 ) return true;
  if ( id == undefined ) return true;
  return false;
}
function validate_phone( e ) {
  if ( navigator.platform.toLowerCase().indexOf("arm") != -1 ) return true;
  if ( !e ) var e = window.event;
  var id = (e.keyCode ? e.keyCode : e.which);
  if ( id == 116 ) return true;
  if ( id == 39 ) return true;
  if ( id == 37 ) return true;
  if ( id == 36 ) return true;
  if ( id == 35 ) return true;
  if ( id == 27 ) return true;
  if ( id == 16 ) return true;
  if ( id == 13 ) return true;
  if ( id == 9 ) return true;
  var valid = "0123456789";
  var el = e.target;
  var startPos = el.selectionStart;
  var endPos = el.selectionEnd;
  if ( id ==8 || id == 46 || valid.indexOf(String.fromCharCode(id)) != -1 || (id > 48 && valid.indexOf(String.fromCharCode(id-48)) != -1 )) {
    var beforeText = el.value.slice(0,startPos);
    var afterText = el.value.slice(endPos,el.value.length);
    el.value = beforeText+afterText;
  }
  var tmp = el.value;
  if ( id == 8 && startPos == endPos) {
    if ( startPos == 0 ) return false;
    startPos--;
    while( startPos != 0 && valid.indexOf(el.value.charAt(startPos)) == -1 ) {
      startPos--;
    }
    if ( valid.indexOf(el.value.charAt(startPos)) == -1 ) return false;
    tmp = el.value.slice(0,startPos)+el.value.slice(startPos+1,el.value.length);
  } else if ( id == 46 && startPos == endPos ) {
    if ( startPos == el.value.length ) return false;
    while( startPos < (el.value.length-1) && valid.indexOf(el.value.charAt(startPos)) == -1 ) {
      startPos++;
    }
    if ( valid.indexOf(el.value.charAt(startPos)) == -1 ) return false;
    tmp = el.value.slice(0,startPos)+el.value.slice(startPos+1,el.value.length);
  } else if ( id != 8 && id != 46 ) {
    var clicked = String.fromCharCode(id);
    if ( valid.indexOf(clicked) == -1 ) {
      if ( id > 48 ) {
        clicked = String.fromCharCode((id-48));
        if ( valid.indexOf(clicked) == -1 ) return false;
      } else return false;
    }
    el.value = el.value.slice(0,startPos)+clicked+el.value.slice(startPos,el.value.length);
    tmp = el.value;
    if ( startPos == 0 )startPos++;
    if ( startPos == 2 ) startPos = startPos+2;
    if ( el.value.length == 16 && startPos == 9 ) startPos++;
    if ( el.value.length == 15 && startPos == 8 ) startPos++;
    startPos++;
  }
  if ( tmp.length > 0 && tmp.match(/\d/g) != null ) {
    tmp = tmp.match(/\d/g).join("");
  } else {
    tmp = "";
    el.value = tmp;
    return false;
  }
  while( tmp.length < 10 ) {
    tmp += "x";
  }
  if ( tmp.length == 10 ) {
    var tmp2 = "("+tmp.charAt(0)+tmp.charAt(1)+") "+tmp.charAt(2)+tmp.charAt(3)+tmp.charAt(4)+tmp.charAt(5)+"-"+tmp.charAt(6)+tmp.charAt(7)+tmp.charAt(8)+tmp.charAt(9);
  } else {
    var tmp2 = "("+tmp.charAt(0)+tmp.charAt(1)+") "+tmp.charAt(2)+tmp.charAt(3)+tmp.charAt(4)+tmp.charAt(5)+tmp.charAt(6)+"-"+tmp.charAt(7)+tmp.charAt(8)+tmp.charAt(9)+tmp.charAt(10);
  }
  el.value = tmp2;
  el.setSelectionRange(startPos,startPos);
  return false;
}
function validate_email( e ) {
  if ( !e ) var e = window.event;
  var id = e.which ? e.which : e.button;
  var el = e.target;
  if ( id >= 48 && id <= 57 ) return true;
  if ( id >= 64 && id <= 90 ) return true;
  if ( id >= 97 && id <= 122 ) return true;
  if ( id == 8 ) return true;
  if ( id == 46 ) return true;
  if ( id == undefined ) return true;
  return false;
}
function update_list( el ) {
  if ( (el.name == undefined || el.name == "") && (el.id == undefined || el.id == "") ) return;
  var id = el.id != undefined && el.id != "" ? el.id : el.name;
  if ( counters[id] == undefined ) counters[id] = 0;
  counters[id]++;
  setTimeout( send_update_list, 300, el );
}
function send_update_list( el ) {
  var id = el.id != undefined && el.id != "" ? el.id : el.name;
  if ( counters[id] != undefined ) {
    counters[id]--;
    if ( counters[id] > 0 ) return;
  }
  var post = "list="+encodeURI(el.list.id)+"&search="+encodeURI(el.value);
  send_to_server( post, storno_update_list );
}
function storno_update_list( pageXML ) {
  if ( !pageXML ) return;
  if ( !pageXML.getElementsByTagName("json") ) return;
  var page = pageXML.getElementsByTagName("json")[0];
  if ( !page || !page.firstChild ) return;
  var json = eval("arr="+page.firstChild.nodeValue);
  if ( json.id != request_id ) return;
  if ( request_id < 0 ) request_id = 1;
  var el = document.getElementById(json.result.list);
  if ( el != undefined && el.localName == "datalist" ) {
    var j=0;
    el.innerHTML = json.result.content;
    for( i=j; i < json.result.content.length; i++ ) {
    }
  }
}
function suspend_list( e, el ) {
  if ( !e ) e = window.event;
  var code = e.keyCode ? e.keyCode : e.which;
  switch(code) {
    case 13:
      if ( el.parentNode.lastChild != undefined && el.parentNode.lastChild.className=="suspend_list" ) {
        var index = 1;
         for ( i=1; el.parentNode.lastChild.childNodes[i] != undefined; i++ ) {
          if ( el.parentNode.lastChild.childNodes[i].style.backgroundColor != undefined && el.parentNode.lastChild.childNodes[i].style.backgroundColor != "" ) {
            index = i;
            break;
          }
        }
        var simple_selection = true;
        var sel = document.getElementById("selection_"+el.id);
        if ( sel && sel.value=="multiple" ) simple_selection = false;
        if ( simple_selection ) {
          el.value = el.parentNode.lastChild.childNodes[index].innerHTML;
          post = "op=update&field="+encodeURI(el.id)+"&value="+encodeURI(el.value);
          send_update( post, update_storno );
          var save = document.getElementById("save_"+el.id);
          if ( save && save.src.indexOf("unchecked") == -1 ) {
            save.src='/unchecked.png';
          }
          remove_suspend_list( el );
        } else {
          sel.parentNode.parentNode.removeAttribute("style");
          var div = document.createElement("DIV");
          div.onclick = function( e ) {
            if ( !e ) e = window.event;
            remove_multiple_selection( e.target );
          };
          var input = document.createElement("INPUT");
          div.className = "msel";
          input.type = "hidden";
          input.name = el.id+"[]";
          input.value = el.parentNode.lastChild.childNodes[index].innerHTML;
          post = "op=update&field="+encodeURI(input.name)+"&value="+encodeURI(input.value);
          send_update( post, update_storno );
          var save = document.getElementById("save_"+el.id);
          if ( save && save.src.indexOf("unchecked") == -1 ) {
            save.src='/unchecked.png';
          }
          div.innerHTML = el.parentNode.lastChild.childNodes[index].innerHTML;
          div.appendChild(input);
          sel.nextSibling.appendChild(div);
          div = null;
          input = null;
          el.parentNode.lastChild.removeChild(el.parentNode.lastChild.childNodes[index]);
          el.value="";
          el.focus();
          create_suspend_list( el, true );
        }
      }
      break;
    case 38:
      if ( el.parentNode.lastChild != undefined && el.parentNode.lastChild.className=="suspend_list" ) {
        if ( timers[el.id] != undefined && timers[el.id] != null ) {
          clearTimeout(timers[el.id]);
          timers[el.id] = null;
        }
        var index = -1;
        var max = el.parentNode.lastChild.childNodes.length;
        for ( i=(max-1); el.parentNode.lastChild.childNodes[i] != undefined; i-- ) {
          if ( el.parentNode.lastChild.childNodes[i].style.backgroundColor != undefined && el.parentNode.lastChild.childNodes[i].style.backgroundColor != "" ) {
            index = i;
            break;
          }
        }
        if ( index >= 1 ) el.parentNode.lastChild.childNodes[index].style.backgroundColor = "";
        index--;
        if ( index < 1 ) index = (max-1);
        el.parentNode.lastChild.childNodes[index].style.backgroundColor = "#f5f5f5";
      } else {
        create_suspend_list( el, true );
      }
      break;
    case 40:
      if ( el.parentNode.lastChild != undefined && el.parentNode.lastChild.className=="suspend_list" ) {
        if ( timers[el.id] != undefined && timers[el.id] != null ) {
          clearTimeout(timers[el.id]);
          timers[el.id] = null;
        }
        var index = 0;
        var max = el.parentNode.lastChild.childNodes.length;
        for ( i=1; el.parentNode.lastChild.childNodes[i] != undefined; i++ ) {
          if ( el.parentNode.lastChild.childNodes[i].style.backgroundColor != undefined && el.parentNode.lastChild.childNodes[i].style.backgroundColor != "" ) {
            index = i;
            break;
          }
        }
        if ( index >= 1 ) el.parentNode.lastChild.childNodes[index].style.removeProperty("background-color");
        index++;
        if ( index >= max ) index = 1;
        el.parentNode.lastChild.childNodes[index].style.backgroundColor = "#f5f5f5";
      } else {
        create_suspend_list( el, true );
      }
      break;
    default:
      if ( !validate_alphanumeric( e ) ) return;
      if ( counters[el.id] == undefined ) counters[el.id] = 0;
      counters[el.id]++;
      setTimeout( create_suspend_list, 200, el, false );
      break;
  }
}
function create_suspend_list ( el, forced, getall ) {
  if ( forced == undefined ) forced = false;
  if ( getall == undefined ) getall = false;
  el.focus();
  if ( el != document.activeElement ) return;
  var simple_selection = true;
  var sel = document.getElementById("selection_"+el.id);
  if ( sel && sel.value == "multiple" ) {
    simple_selection = false;
  }
  string = null;
  var post = null;
  if ( !forced && counters[el.id] == undefined ) return;
  if ( !forced && counters[el.id] > 1 ) {
    counters[el.id]--;
  } else if ( forced ) {
    if ( el.nextSibling != undefined && el.nextSibling.className=="suspend_list" ) {
      remove_suspend_list( el );
    } else {
      counters[el.id] = 0;
      var list = el.id;
      if ( list.indexOf("[") != -1 ) {
        list = strstr(list,"[",true);
      }
      if ( !getall ) post = "list="+encodeURI(list)+"&search="+encodeURI(el.value)+"&el="+encodeURI(el.id);
      else post = "list="+encodeURI(list)+"&search=&el="+encodeURI(el.id);
    }
  } else {
    counters[el.id] = 0;
    var list = el.id;
    if ( list.indexOf("[") != -1 ) {
      list = strstr(list,"[",true);
    }
    post = "list="+encodeURI(list)+"&search="+encodeURI(el.value)+"&el="+encodeURI(el.id);
  }
  if ( post != null ) {
    if ( !simple_selection && sel ) {
      var els = sel.nextSibling.getElementsByTagName("INPUT");
      var remove = "";
      for( i=0; els[i] && els[i] != undefined; i++ ) {
        if ( remove != "" ) remove += ";";
        remove += els[i].value;
      }
      post += "&remove="+remove;

    }
    send_to_server( post, storno_suspend_list );
  }
  sel = null;
  el.focus();
}
function storno_suspend_list( pageXML ) {
  if ( !pageXML ) return;
  if ( !pageXML.getElementsByTagName("json") ) return;
  var page = pageXML.getElementsByTagName("json")[0];
  if ( !page || !page.firstChild ) return;
  var json = eval("arr="+page.firstChild.nodeValue);
  if ( json.id != request_id ) return;
  if ( request_id < 0 ) request_id = 1;
  if ( !json.el || json.result == undefined || json.result.content == undefined ) return;
  var el = document.getElementById(json.el);
  if ( !el ) return;
  if ( el.nextSibling && el.nextSibling.nextSibling && el.nextSibling.nextSibling.className == "suspend_list" ) el.parentNode.removeChild(el.nextSibling.nextSibling);
  if ( el.nextSibling && el.nextSibling.className == "suspend_list" ) el.parentNode.removeChild(el.nextSibling);
  var div = document.createElement("DIV");
  div.className = "suspend_list";
  div.height = el.clientHeight+"px";
  if ( timers[el.id] != undefined && timers[el.id] != null ) {
    clearTimeout(timers[el.id]);
    timers[el.id] = null;
  }
  if ( document.activeElement != el ) return;
  if ( json.result.content.length < 1 ) return;
  div.onclick = function ( e ) {
    if ( !e ) e = window.event;
    if ( (e.target.parentNode.previousSibling == undefined) && (e.target.parentNode.previousSibling.previousSibling == undefined)) {
      return;
    }
    var tmpel = e.target.parentNode.previousSibling;
    if ( tmpel.tagName != "INPUT" ) {
      tmpel = e.target.parentNode.previousSibling.previousSibling;
      if ( tmpel.tagName != "INPUT" ) return;
    }
    
    if ( e.target.parentNode.firstChild == e.target ) {
      clearTimeout(timers[tmpel.id]);
      timers[tmpel.id] = null;
      tmpel.focus();
      return;
    }
    var simple_selection = true;
    var sel = document.getElementById("selection_"+el.id);
    if ( sel && sel.value=="multiple" ) simple_selection = false;
    if ( simple_selection ) {
      tmpel.value = e.target.innerHTML;
      tmpel.focus();
      post = "op=update&field="+encodeURI(tmpel.id)+"&value="+encodeURI(tmpel.value);
      send_update( post, update_storno );
      var save = document.getElementById("save_"+tmpel.id);
      if ( save && save.src.indexOf("unchecked") == -1 ) {
        save.src='/unchecked.png';
      }
      remove_suspend_list(tmpel);
    } else {
      var lc = tmpel.parentNode.lastChild;
      sel.parentNode.parentNode.removeAttribute("style");
      clearTimeout(timers[tmpel.id]);
      timers[tmpel.id] = null;
      tmpel.focus();
      var div = document.createElement("DIV");
      div.onclick = function( e ) {
        if ( !e ) e = window.event;
        remove_multiple_selection( e.target );
      };
      var input = document.createElement("INPUT");
      div.className = "msel";
      input.type = "hidden";
      input.name = tmpel.id+"[]";
      input.value = e.target.innerHTML;
      post = "op=update&field="+encodeURI(tmpel.id)+"&value="+encodeURI(input.value);
      send_update( post, update_storno );
      var save = document.getElementById("save_"+tmpel.id);
      if ( save && save.src.indexOf("unchecked") == -1 ) {
        save.src='/unchecked.png';
      }
      div.innerHTML = e.target.innerHTML;
      div.appendChild(input);
      sel.nextSibling.appendChild(div);
      div = null;
      input = null;
      e.target.parentNode.removeChild(e.target);
    }
    sel = null;
  };
  div.onmouseout = function ( e ) {
    if ( timers[el.id] != undefined && timers[el.id] != null ) {
      clearTimeout(timers[el.id]);
      timers[el.id] = null;
    }
    timers[el.id] = setTimeout( remove_suspend_list, 5000, el );
  };
  div.onmouseover = function ( e ) {
    clearTimeout(timers[el.id]);
    timers[el.id] = null;
  };
  var tmpdiv = document.createElement("DIV");
  tmpdiv.innerHTML = el.placeholder;
  div.appendChild(tmpdiv);
  tmpdiv = null;
  for( i=0; json.result.content[i]; i++ ) {
    var tmpdiv = document.createElement("DIV");
    tmpdiv.innerHTML = json.result.content[i];
    div.appendChild(tmpdiv);
    tmpdiv = null;   
  }
  el.parentNode.appendChild(div);
  timers[el.id] = setTimeout( remove_suspend_list, 5000, el );
}
function schedule_remove_suspend_list( el ) {
  if ( timers[el.id] != undefined ) clearTimeout(timers[el.id]);
  timers[el.id] = setTimeout( remove_suspend_list, 150, el );
}
function remove_suspend_list( el ) {
  if ( timers[el.id] != undefined ) clearTimeout(timers[el.id]);
  if ( el.nextSibling && el.nextSibling.nextSibling && el.nextSibling.nextSibling.className=="suspend_list" ) el.parentNode.removeChild(el.nextSibling.nextSibling);
  if ( el.nextSibling && el.nextSibling.className=="suspend_list" ) el.parentNode.removeChild(el.nextSibling);
}
function key_change ( el ) {
  if ( !el || el == undefined ) return;
  if ( el.id == undefined ) {
    if ( el.name == undefined ) return;
    else id = el.name;
  } else id = el.id;

  if ( !keys_update[id] || keys_update[id] == undefined || keys_update[id] == null ) keys_update[id] = 0;
  var tmpel = document.getElementById(el.name+"_donotautosave");
  if ( tmpel ) return true;
  keys_update[id]++;
  setTimeout( key_update, 600, id );
}
function key_update( id ) {
  if ( !keys_update[id] ) return;
  keys_update[id]--;
  if ( keys_update[id] < 0 ) keys_update[id] = 0;
  if ( keys_update[id] == 0 ) {
    var el = document.getElementById(id);
    if ( !el ) return;
    if ( el.id == undefined ) {
      post = "operation="+encodeURI(el.name)+"&value="+encodeURI(el.value);
    } else {
      post = "operation="+encodeURI(el.id)+"&value="+encodeURI(el.value);
    }
    send_update( post, storno_ajax_form, window.location );
  }
}
function update_storno( pageXML ) {
  if ( !pageXML ) return;
  if ( !pageXML.getElementsByTagName("json") ) return;
  var page = pageXML.getElementsByTagName("json")[0];
  if ( !page || !page.firstChild ) return;
  var json = eval("arr="+page.firstChild.nodeValue);
  if ( json.id != request_id ) return;
  if ( json.el && json.result ) {
    var save = document.getElementById("save_"+json.el);
    if ( save && save.src.indexOf("unchecked") != -1 ) {
      save.src='/pictures/checked.png';
    }
  }
}
function save( el ) {
  if ( !el ) return;
  var value = "";
  switch(el.type) {
    case "checkbox":
      value = el.checked;
      break;
    default:
      value = el.value;
      break;
  }

  if( el.id == undefined || el.id == "" ) {
    var sel = document.getElementById("save_"+el.name);
    if ( sel ) sel.src = "/pictures/unchecked.png";
    post = "method=ajax&operation="+encodeURI(el.name)+"&value="+encodeURI(value);
  } else {
    var sel = document.getElementById("save_"+el.id);
    if ( sel ) sel.src = "/pictures/unchecked.png";
    post = "method=ajax&operation="+encodeURI(el.id)+"&value="+encodeURI(value);
  }
  send_update( post, storno_ajax_form, window.location );
}
