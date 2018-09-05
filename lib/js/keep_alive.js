function get_keep_alive_post_data() {
  var els = document.getElementsByName("refresh");
  var els_to_send = "";
  var post = null;
  var url = null;
  var slist = null;
  var list = null;

  if ( els ) {
    for( i=0; els[i] != undefined; i++ ) {
      els_to_send += els[i].id+",";
    }
  }
  els_to_send.trim(",");
  post = "f_name=keep_alive";
  if ( els_to_send != "" ) {
    post += "&items="+els_to_send;
  }
  els.length = 0;
  els = getElementsByAttribute("ku");
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
    if ( slist ) slist.length = 0;
    slist = null;
  }
  if ( els ) els.length = 0;
  els_to_send = null;
  delete i;
  delete els;
  delete this;
  return post;
}
async function keep_alive() {
  var xhr = new XMLHttpRequest();
  var pageXML = null;
  var page = null;
  var json = null;
  var post_data = null;
  var url = window.location;
  post_data = get_keep_alive_post_data();

  xhr.onreadystatechange = function () {
    switch(xhr.readyState) {
      case 4:
        if (xhr.status == 200) {
          pageXML = xhr.responseXML;
          if ( !pageXML.getElementsByTagName("json") ) return;
          page = pageXML.getElementsByTagName("json")[0];
          if ( !page || !page.firstChild ) return;
          json = eval("arr="+page.firstChild.nodeValue);
          update_elements( json );
          post_data = get_keep_alive_post_data();
          xhr.open("POST", url, true);
          xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
          xhr.send("method=ajax&"+post_data);
        } else {
          xhr.open("POST", url, true);
          xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
          xhr.send("method=ajax&"+post_data);
        }
        break;
    }
  };
  xhr.timeout = 5000;
  xhr.ontimeout = function() {
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("method=ajax&id="+(++request_keep_id)+"&"+post_data);
  };
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send("method=ajax&"+post_data);
}
