function get_more() {
  var els = null;
  var post = null;
  var slist = null;
  var list = null;
  var url = null;
  var calc = null;


  var els = document.getElementsByTagName("footer");
  if ( els && els[0] != undefined ) {
    if( Math.ceil(parseInt(window.scrollY)+parseInt(window.innerHeight)) < Math.ceil(els[0].offsetTop*0.8) ) {
      els = null;
      return
    }
  }
  if ( locks['more'] != undefined && locks['more'] == true ) return;
  if ( Math.ceil((window.performance && window.performance.now && window.performance.timing && window.performance.timing.navigationStart ? window.performance.now() + window.performance.timing.navigationStart : Date.now())-2000) < Math.ceil(locks['more']) ) return;
  locks['more'] = true;
  post = "f_name=more";
  els = null;
  url = window.location;
  els = getElementsByAttribute("p");
  for( i in els ) {
    slist = els[i].getElementsByClassName("slist");
    for ( j in slist ) {
      for( k=0; slist[j].childNodes != (undefined&&null) && slist[j].childNodes[k] != (null&&undefined); k++ ) {
        // verify if element has id defined and it is equal to ku,  if so get elements list
        if ( slist[j].childNodes[k].id != (null&&undefined) && slist[j].childNodes[k].id != "" && slist[j].childNodes[k].id == els[i].id.toLowerCase() ) {
          list = "";
          for( l=0; slist[j].childNodes[k].childNodes[l] != (null&&undefined); l++ ) { 
            if ( list != "" ) list += ",";
            list += strstr(slist[j].childNodes[k].childNodes[l].firstChild.id,"_",false);
          }
          post += "&"+els[i].id.toLowerCase()+"="+list;
          if ( els[i].getAttribute("order") != (null&&undefined) ) {
            post += "&"+els[i].id.toLowerCase()+"order="+els[i].getAttribute("order");
          }
          list = null;
        }
      } 
    }
    slist = null;
  }
  delete i;
  delete els;
  if ( post != "f_name=more" ) {
    get_more_content( post, url );
  }
  post = null;
  url = null;
}
async function get_more_content( post_data, url ) {
  var xhr = new XMLHttpRequest();
  var pageXML = null;
  var page = null;
  var json = null;

  xhr.onreadystatechange = function () {
    xhr.onreadystate = null;
    switch(xhr.readyState) {
      case 4:
        if (xhr.status == 200) {
          locks['more'] = window.performance && window.performance.now && window.performance.timing && window.performance.timing.navigationStart ? window.performance.now() + window.performance.timing.navigationStart : Date.now();
          pageXML = xhr.responseXML;
          if ( !pageXML.getElementsByTagName("json") ) return;
          page = pageXML.getElementsByTagName("json")[0];
          if ( !page || !page.firstChild ) return;
          json = eval("arr="+page.firstChild.nodeValue);
          update_elements( json );
          delete json;
          delete page;
          delete pageXML;
          xhr.ontimeout = null;
          xhr.abort();
          delete xhr;
        } else {
          if ( url != undefined ) {
            xhr.open("POST", url, true);
          } else {
            xhr.open("POST", "/", true);
          }
          xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
          xhr.send("method=ajax&"+post_data);
        }
        break;
    }
  };
  xhr.timeout = 1000;
  xhr.ontimeout = function() {
    if ( url != undefined ) {
      xhr.open("POST", url, true);
    } else {
      xhr.open("POST", "/", true);
    }
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("method=ajax&"+post_data);
  };
  if ( url != undefined ) {
    xhr.open("POST", url, true);
  } else {
    xhr.open("POST", "/", true);
  }
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send("method=ajax&"+post_data);
}
