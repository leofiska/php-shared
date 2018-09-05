function new_line_here( el ) {
  var d1 = create_new_line_here();
  el.parentNode.insertBefore(d1,el);  
  d1 = null;

  var table = create_line();
  el.parentNode.insertBefore(table,el);
  table = null;

  var rl1 = create_resize_line();
  el.parentNode.insertBefore(rl1,el);
  rl1 = null;
}

function new_line_before( el ) {
  var d1 = create_new_line_here();
  el.parentNode.insertBefore(d1,el);
  d1 = null;

  var table = create_line();
  el.parentNode.insertBefore(table,el);
  table = null;

  var rl1 = create_resize_line();
  el.parentNode.insertBefore(rl1,el);
  rl1 = null;
}
var c_element = null;
var r_element = null;
var pr_element = null;
document.onmousedown = function ( e ) {
  if ( !e ) e = window.event;
  if ( e.target.getAttribute != undefined && e.target.getAttribute("cr") != undefined && c_element == null ) {
    c_element = e.target;
    c_element.setAttribute("posX",e.pageX);
    tmp = c_element.previousSibling;
    while ( c_element.className.indexOf("rtable-cell") == -1 ) {
      tmp = tmp.previousSibling;
    }
    cp_element = tmp;
    var w = (cp_element.offsetWidth) ? cp_element.offsetWidth : cp_element.scrollWidth;
//    w = parseInt((w/window.innerWidth)*100);
    cp_element.setAttribute("oriX",w);
    tmp = c_element.nextSibling;
    while ( c_element.className.indexOf("rtable-cell") == -1 ) {
      tmp = tmp.nextSibling;
    }
    cn_element = tmp;
    var w = (cn_element.offsetWidth) ? cn_element.offsetWidth : cn_element.scrollWidth;
//    w = parseInt((w/window.innerWidth)*100);
    cn_element.setAttribute("oriX",w);
    w = null;
    tmp = null;
  };
  if ( e.target.getAttribute != undefined && e.target.getAttribute("class") != undefined && e.target.getAttribute("class") == "rr" && r_element == null ) {
    r_element = e.target;
    tmp_element = r_element.previousSibling;
    for( i=0; i < 2; i++ ) {
      if ( tmp_element == undefined || tmp_element == null || tmp_element.getAttribute == undefined || tmp_element.getAttribute("class") == undefined ) {
        tmp_element = tmp_element.previousSibling;
        continue;
      }
      pr_element = tmp_element;
    }
    tmp_element = null;
    if ( pr_element == null ) {
      r_element = null;
      return;
    }
    r_element.setAttribute("posY",e.pageY);
    var h = (pr_element.scrollHeight) ? pr_element.scrollHeight : pr_element.offsetHeight;
//    var h = parseInt(pr_element.style.height);
    r_element.setAttribute("oriY",h);
  }
};
document.onmousemove  = function ( e ) {
  if ( !e ) e = window.event;
  if ( c_element != null ) {
    var delta = c_element.getAttribute("posX")-e.pageX;
    if ( (((cp_element.getAttribute("oriX")*1)-delta)/window.innerWidth*100) < 10 ) return;
    if ( (((cn_element.getAttribute("oriX")*1)+delta)/window.innerWidth*100) < 10 ) return;
    cp_element.style.width = parseFloat((((cp_element.getAttribute("oriX")*1)-delta)/window.innerWidth*100)).toFixed(2)+"vw";
    cn_element.style.width = parseFloat((((cn_element.getAttribute("oriX")*1)+delta)/window.innerWidth*100)).toFixed(2)+"vw";
  }
  if ( r_element != null ) {
    if ( r_element.getAttribute == undefined || r_element.getAttribute("posY") == undefined ) return; 
    var old_posY = r_element.getAttribute("posY");
    var oriY = r_element.getAttribute("oriY");
    var posY = e.pageY;
    var delta = old_posY-posY;
    var h = oriY-delta;
    pr_element.style.height = h+"px";
  }
};
document.onmouseup  = function ( e ) {
  if ( !e ) e = window.event;
  if ( c_element != null ) {
    c_element.removeAttribute("posX");
    c_element = null;
    cp_element.removeAttribute("oriX");
    cp_element = null;
    cn_element.removeAttribute("oriX");
    cn_element = null;
  }
  if ( r_element != null ) {
    r_element.removeAttribute("posY");
    r_element.removeAttribute("oriY");
    r_element = null;
    pr_element = null;
  }
};
function create_resize_line() {
  var rl1 = document.createElement("DIV");
  rl1.style.setProperty("height","1em");
  rl1.style.setProperty("cursor","row-resize");
  rl1.setAttribute("class","rr");
  return rl1;
}
function create_new_line_here() {
  var d1 = document.createElement("DIV");
  var d2 = document.createElement("DIV");
  var s1 = document.createElement("SPAN");
  d1.setAttribute("class","pointer glow4 center table w100");
  d1.style.setProperty("margin-bottom","1em");
  d1.onclick = function ( e ) {
    if ( !e ) e = window.event;
    new_line_here( e.target );
  };
  d2.setAttribute("class","td middle");
  d2.style.setProperty("pointer-events","none");
  d2.style.setProperty("height","1em");
  d2.style.setProperty("border","1px dashed #a5a5a5");
  d2.style.setProperty("background-color","#f5f5f5");
  s1.innerHTML = "+";
  s1.style.setProperty("box-sizing","border-box");
  d2.appendChild(s1);
  d1.appendChild(d2);
  d2 = null;
  s1 = null;
  return d1;
}
function create_line() {
  var table = document.createElement("DIV");
  table.setAttribute("class","rtable");
  table.style.setProperty("height","100px");
  table.style.setProperty("min-height","1em");
  // command left
  var td0 = document.createElement("DIV");
  td0.style.setProperty("vertical-align","middle");
  td0.style.setProperty("border","1px dashed #a5a5a5");
  td0.style.setProperty("background-color","#f5f5f5");
  td0.style.setProperty("text-align","center");
  td0.innerHTML = "+";
  td0.setAttribute("class","rtable-cell col-cm pointer glow4");

  //content
  var td1 = new_column();

  //command right
  var td2 = td0.cloneNode(true);

  //command action
  td0.onclick = function ( e ) {
    if ( !e ) e = window.event;
    new_column_after ( e.target );
  };
  td2.onclick = function ( e ) {
    if ( !e ) e = window.event;
    new_column_before ( e.target );
  };

  // appends
  table.appendChild(td0);
  table.appendChild(td1);
  table.appendChild(td2);
  td0 = null;
  td1 = null;
  td2 = null;
  return table;
}
function new_column() {
  var text = create_select_type_element();
  var td0 = document.createElement("DIV");
  td0.style.setProperty("vertical-align","top");
  td0.style.setProperty("height","100%");
  td0.setAttribute("class","rtable-cell pblock");
  td0.appendChild(text);
  var tdtop = document.createElement("DIV");
  tdtop.setAttribute("class","top pointer glow4");
  tdtop.innerHTML = "+";
  tdtop.onclick = function ( e ) {
    if ( !e ) e = window.event;
    var el = create_select_type_element();
    e.target.parentNode.insertBefore(el,e.target.parentNode.firstChild);
  };
  var tdbottom = document.createElement("DIV");
  tdbottom.setAttribute("class","bottom pointer glow4");
  tdbottom.innerHTML = "+";
  tdbottom.onclick = function ( e ) {
    if ( !e ) e = window.event;
    var el = create_select_type_element();
    e.target.parentNode.insertBefore(el,e.target.previousSibling);
  };
  td0.appendChild(tdtop);
  td0.appendChild(tdbottom);
  text = null;
  return td0;
}
function create_text_element() {
  var btext = document.createElement("DIV");
  btext.style.setProperty("margin","0em 0.5vw");
  var text = document.createElement("TEXTAREA");
  text.style.setProperty("width","100%");
  text.style.setProperty("resize","vertical");
  text.style.setProperty("box-sizing","border-box");
  text.setAttribute("af","height");
  text.setAttribute("offsetY","1em");
//  btext.appendChild(text);
//  text = null;
//  return btext;
  return text;
}
d_element = null;
function handleDragStart(e) {
  if ( !e ) e = window.event;
  e.dataTransfer.setData('Text', e.target.getAttribute("draggable"));
  var el = e.target;
  for( i=0; i < 5 && (el.getAttribute == undefined || el.getAttribute("draggable") == undefined || el.getAttribute("draggable") == null); i++ ) {
    el = el.parentNode;
  }
  if ( el != null ) {
    d_element = el;
  }
}
function handle_drop ( e ) {
  if ( !e ) e = window.event;
  var el = e.target;
  for( i=0; i < 5 && (el.getAttribute == undefined || el.getAttribute("draggable") == undefined || el.getAttribute("draggable") == null); i++ ) {
    el = el.parentNode;
  }
  if ( el.getAttribute == undefined || el.getAttribute("draggable") == undefined || el.getAttribute("draggable") == null ) return;
  e.stopPropagation();
  e.preventDefault();
  if ( el === d_element ) return;
  if ( e.target.files != undefined || e.dataTransfer.types == "Files" ) {
    load_files( e, e.target.parentNode );
  } else { 
    var pel = d_element.parentNode;
    d_element.parentNode.removeChild(d_element);
    el.parentNode.insertBefore(d_element,el.nextSibling);
    e.target.style.setProperty("border","1px solid #a5a5a5");
    if ( pel.childNodes.length < 3 ) {
      pel.insertBefore(create_select_type_element(),pel.firstChild);
    }
      
    d_element = null;
  }
}
function create_select_type_element() {
  var o = document.createElement("DIV");
  o.style.setProperty("display","block");
  o.style.setProperty("border","1px solid #a5a5a5");
  o.style.setProperty("margin","0em 0.5vw");
  o.style.setProperty("position","relative");
  o.setAttribute("draggable",true);
  o.ondragstart = function ( e ) {
    handleDragStart( e );
  };
  o.ondragover = o.ondragenter = function ( e ) {
    if ( !e ) e = window.event;
    var el = e.target;
    for( i=0; i < 5 && (el.getAttribute == undefined || el.getAttribute("draggable") == undefined || el.getAttribute("draggable") == null); i++ ) {
      el = el.parentNode;
    }
    if ( el.getAttribute == undefined || el.getAttribute("draggable") == undefined || el.getAttribute("draggable") == null ) return;
    e.stopPropagation();
    e.preventDefault();
    if ( el === d_element ) return;
    e.target.style.setProperty("border","1px dashed #000000");
  };
  o.ondragleave = function ( e ) {
    if ( !e ) e = window.event;
    e.target.style.setProperty("border","1px solid #a5a5a5");
  };
  o.ondrop = function ( e ) {
    handle_drop ( e );
  };
  var oo = document.createElement("DIV");
  oo.setAttribute("class","vmiddle");
  oo.style.setProperty("width","100%");
  oo.style.setProperty("box-sizing","border-box");
  oo.style.setProperty("background-image","url(\"/pictures/background.png\")");
  var o1 = document.createElement("DIV");
  o1.style.setProperty("display","inline-block");
  o1.setAttribute("class","iglow4 pointer lowercase vmiddle");
  var img = document.createElement("img");
  img.setAttribute("src","/pictures/paragraph.png");
  img.setAttribute("class","bigicon nohook middle");
  img.setAttribute("border","1px solid #a5a5a5");
  img.setAttribute("alt",messages['paragraph']);
  o1.appendChild(img);
  img = null;
  o1.onclick = function ( e ) {
    if ( !e ) e = window.event;
    var text = create_text_element();
    var p = e.target.parentNode.parentNode;
    p.insertBefore(text,e.target.parentNode);
    p.removeChild(e.target.parentNode);
    p.style.removeProperty("border");
  };
  var o2 = document.createElement("LABEL");
  o2.style.setProperty("display","inline-block");
  o2.setAttribute("class","iglow4 pointer lowercase vmiddle");
  var img = document.createElement("img");
  img.setAttribute("src","/pictures/media.png");
  img.setAttribute("class","bigicon nohook middle");
  img.setAttribute("border","1px solid #a5a5a5");
//  img.setAttribute("alt",messages['paragraph']);
  o2.appendChild(img);
  img = null;
  var img = document.createElement("INPUT");
  img.setAttribute("type","file");
  img.setAttribute("multiple","multiple");
  img.setAttribute("accept","image/*,video/*");
  img.style.setProperty("display","none");
  img.onchange = function ( e ) {
    if ( !e ) e = window.event;
    load_files( e, e.target.parentNode.parentNode.parentNode );
  };
  img.setAttribute("type","file");
  o2.appendChild(img);
  img = null;
  oo.appendChild(o1);
  oo.appendChild(o2);
  o.appendChild(oo);
  o1 = null;
  o2 = null;
  oo = null;
  return o;
}
function prepare_for_new_column( el ) {
  var counter = 0;
  var mw = parseInt(window.innerWidth/10);
  for( i=0; el.childNodes[i] != undefined; i++ ) {
    if ( el.childNodes[i].className.indexOf("pblock") != -1 ) {
      var tw = (el.childNodes[i].offsetWidth) ? el.childNodes[i].offsetWidth : el.childNodes[i].scrollWidth;
      if ( (tw - mw) > mw ) counter++;
    }
  }
  if ( counter == 0 ) return false;
  var w = parseInt(parseInt(window.innerWidth/8)/counter);
//  alert(counter+"|"+w);
  for( i=0; el.childNodes[i] != undefined; i++ ) {
    if ( el.childNodes[i].className.indexOf("pblock") != -1 ) {
      var tw = (el.childNodes[i].offsetWidth) ? el.childNodes[i].offsetWidth : el.childNodes[i].scrollWidth;
      if ( (tw - mw) > mw ) {
        tw = parseInt(tw)-w;
        tw = parseFloat((tw/window.innerWidth*100)).toFixed(2);
        el.childNodes[i].style.width = tw+"vw";
      }
    }
  }
  return true;
}
function new_column_before( el ) {
  var td0 = document.createElement("DIV");
  td0.style.setProperty("border","1px dashed #a5a5a5");
  td0.style.setProperty("background-color","#f5f5f5");
  td0.style.setProperty("text-align","center");
  td0.style.setProperty("width","0.2em");
  td0.style.setProperty("cursor","col-resize");
  td0.setAttribute("class","rtable-cell");
  td0.setAttribute("cr","");
  td0.ondragover = td0.ondragenter = function ( e ) {
    e.target.style.setProperty("border","1px dashed #000000");
  };
  var td1 = new_column();
  if ( prepare_for_new_column( el.parentNode ) ) {
    el.parentNode.insertBefore(td0,el);
    el.parentNode.insertBefore(td1,el);
  }
}
function new_column_after( el ) {
  var td0 = document.createElement("DIV");
  td0.style.setProperty("border","1px dashed #a5a5a5");
  td0.style.setProperty("background-color","#f5f5f5");
  td0.style.setProperty("text-align","center");
  td0.style.setProperty("width","0.2em");
  td0.style.setProperty("cursor","col-resize");
  td0.setAttribute("class","rtable-cell");
  td0.setAttribute("cr","");
  var td1 = new_column();
  if ( prepare_for_new_column( el.parentNode ) ) {
    el.parentNode.insertBefore(td0,el.nextSibling);
    el.parentNode.insertBefore(td1,el.nextSibling);
  }
}

//DND
function load_files ( e, container ) {
  if ( !e ) e = window.event;
  if ( container == undefined || container == null ) container = e.target;
  e.preventDefault();
  e.stopPropagation();
  if ( e.dataTransfer != undefined ) {
    var files = e.dataTransfer.files;
  } else {
    var files = e.target.files;
  }
  var i=-1;
  fr = new Array();
  while ( files[i+1] ) {
    i++;
    var o = document.createElement("DIV");
    o.style.setProperty("display","block");
    o.style.setProperty("margin","0em 0.5vw");
    o.setAttribute("draggable","true");
    o.ondragstart = function ( e ) {
      handleDragStart( e );
    };
    o.ondragover = o.ondragenter = function ( e ) {
      if ( !e ) e = window.event;
      var el = e.target;
      for( i=0; i < 5 && (el.getAttribute == undefined || el.getAttribute("draggable") == undefined || el.getAttribute("draggable") == null); i++ ) {
        el = el.parentNode;
      }
      if ( el.getAttribute == undefined || el.getAttribute("draggable") == undefined || el.getAttribute("draggable") == null ) return;
      e.stopPropagation();
      e.preventDefault();
      if ( el === d_element ) return;
      e.target.style.setProperty("border","1px dashed #000000");
    };
    o.ondragleave = function ( e ) {
      if ( !e ) e = window.event;
      e.target.style.setProperty("border","1px solid #a5a5a5");
    };

    o.ondrop = function ( e ) {
      handle_drop ( e );
    };

    var el2 = document.createElement("DIV");
    el2.id = "file_"+sha256(files[i].name);
//    el2.innerHTML = files[i].name;
    el2.style.color="#444444";
    el2.className="preprogress";
    el2.style.setProperty("position","relative");
    el2.style.setProperty("border","1px solid #a5a5a5");

    var progress = document.createElement("DIV");
    progress.className = "progress";
    progress.style.width="0%";
    progress.setAttribute("filesize",files[i].size);
    progress.setAttribute("filemime",files[i].type);
    progress.setAttribute("filename",files[i].name);

    var progress_text = document.createElement("DIV");
    progress_text.className="progress_text";
    progress_text.innerHTML="0%";

    el2.appendChild(progress);
    el2.appendChild(progress_text);
    o.appendChild(el2);


    container.parentNode.insertBefore(o,container);
    el1 = null; el2 = null; progress = null;
    load_on_page( files[i] );
  }
  container.parentNode.removeChild(container);
}
function load_on_page( file ) {
  fr = new FileReader();
  fr.onload = function ( e ) {
    if ( file.type.indexOf("image/") != -1 ) {
      var element = document.getElementById("file_"+sha256(file.name));
      var image = new Image();
      image.setAttribute("id","img_"+sha256(file.name));
      image.style.setProperty("max-width","100%");
      image.style.setProperty("max-height","100%");
      image.style.setProperty("margin-bottom","4px");
      image.style.setProperty("pointer-events","auto");
      image.src = e.target.result;
      element.parentNode.insertBefore(image,element);
      image = null;
    }
    var post = 'operation=verify_upload_need&name='+encodeURI(file.name)+'&value='+sha256(strstr(e.target.result,",").substring(1));
    var url = window.location;
    var req = newXMLHttpRequest();
    req.open("POST", url, true);
    req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    req.send("method=ajax&id="+(++request_id)+"&"+post);
    req.onload = function ( e ) {
      var pageXML = req.responseXML;
       if ( pageXML != null && pageXML.getElementsByTagName("json") && (page = pageXML.getElementsByTagName("json")[0]) && page && page.firstChild && (json = eval("arr="+page.firstChild.nodeValue)) && json.result != undefined && json.result.content != undefined && json.result.content.element != undefined ) {
         update_elements( json );
       } else {
          send_file_to_server( file, storno_send_file_to_server );
       }
    };
  };
  fr.readAsDataURL(file);
}
function send_file_to_server ( file, storno ) {
  var formData = new FormData();
  formData.append( 'file', file );
  formData.append( 'id', sha256(file.name) );
  var req = new XMLHttpRequest();
  var callbackHandler = getReadyStateHandler(req, storno);
  req.onreadystatechange = callbackHandler;
  req.open("POST", "/upload/", true);
  var callbackHandler = getReadyStateHandler(req, storno);
  req.onload = function() {
    var element = document.getElementById("file_"+sha256(file.name));
    if ( element) {
      var tmp1 = element.getElementsByClassName("progress");
      if ( tmp1[0] ) tmp1[0].style.width = "100%";
      var tmp2 = element.getElementsByClassName("progress_text");
      if ( tmp2[0] ) tmp2[0].innerHTML = "100%";
      tmp1 = null;
      tmp2 = null;
    }
  };
  req.upload.onprogress = function ( e ) {
   if ( e.lengthComputable ) {
      var complete = (e.loaded / e.total * 100 | 0);
      var element = document.getElementById("file_"+sha256(file.name));
      if ( element) {
        var tmp1 = element.getElementsByClassName("progress");
        if ( tmp1[0] ) tmp1[0].style.width = complete+"%";
        var tmp2 = element.getElementsByClassName("progress_text");
        if ( tmp2[0] ) tmp2[0].innerHTML = complete+"%";
        tmp1 = null;
        tmp2 = null;
      }
      element = null;
      complete = null;
    }
  };
  req.send(formData);
}
function storno_send_file_to_server( pageXML ) {
  if ( !pageXML ) return;
  if ( !pageXML.getElementsByTagName("json") ) return;
  var page = pageXML.getElementsByTagName("json")[0];
  if ( !page || !page.firstChild ) return;
  var json = eval("arr="+page.firstChild.nodeValue);
  update_elements( json );
}
