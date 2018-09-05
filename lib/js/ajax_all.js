function newXMLHttpRequest() {
  var xmlreq = false;
  if (window.XMLHttpRequest) {
    xmlreq = new XMLHttpRequest();
  } else if (window.ActiveXObject) {
    try {
      xmlreq = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e1) {
      try {
        xmlreq = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (e2) {
      }
    }
  }
  return xmlreq;
}
function getReadyStateHandler(req, responseXmlHandler) {
  return function () {
    var tmps = null;
    if (req.readyState == 4) {
      if (req.status == 200) {
        responseXmlHandler(req.responseXML);
        req.abort();
        delete req;
      }
      if (req.status == 500) {
        req.abort();
        tmps = document.getElementsByClassName("message");
        for (i=0; tmps[i] != undefined; i++ ) {
          if ( tmps[i].innerHTML != undefined && tmps[i].innerHTML == messages['PROCESSING'] ) {
            tmps[i].innerHTML = messages['SOMETHING_HAS_GONE_BAD'];
          }
        }
        tmps = null;
        delete tmps;
      }
    }
  }
}
async function send_to_server( post_data, storno, url ) {
  var req = newXMLHttpRequest();
  var callbackHandler = getReadyStateHandler(req, storno);
  req.onreadystatechange = callbackHandler;
  if ( url != undefined ) {
    req.open("POST", url, true);
  } else {
    req.open("POST", "/", true);
  }
/*  req.ontimeout = function(e) {
    var tmps = document.getElementsByClassName("message");
    for (i=0; tmps[i] != undefined; i++ ) {
      if ( tmps[i].innerHTML != undefined && tmps[i].innerHTML == messages['PROCESSING'] ) {
        tmps[i].innerHTML = messages['SOMETHING_HAS_GONE_BAD'];
      }
    }
  }*/
  req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  req.send("method=ajax&id="+(++request_id)+"&"+post_data);
  req = null;
  delete req;
  callbackHandler = null;
  delete callbackHandler;
}
function send_update( post_data, storno, url ) {
  var req = newXMLHttpRequest();
  var callbackHandler = null;
  if ( url != undefined ) {
    req.open("POST", url, true);
  } else {
    req.open("POST", "/", true);
  }
  if ( storno ) {
    callbackHandler = getReadyStateHandler(req, storno);
    req.onreadystatechange = callbackHandler;
  }
  req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  req.send("method=ajax&id="+(++request_id)+"&"+post_data);
  req = null;
  delete req;
  callbackHandler = null;
  delete callbackHandler;
}
function send_file_to_server( file, id, storno ) {
  var formData = new FormData();
  var req = newXMLHttpRequest();
  var callbackHandler = getReadyStateHandler(req, storno);
  formData.append( 'file', file );
  formData.append( 'id', id );
  req.onreadystatechange = callbackHandler;
  req.open("POST", "/upload_file.php", true);
//  req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  req.onload = function() {
    var progress = document.getElementById('uploadprogress_'+id);
    if ( progress ) progress.value = progress.innerHTML = 100;
    var holder = document.getElementById(id);
    holder.firstChild.style.opacity = "1";
    progress = null;
    holder = null;
    delete progress;
    delete holder;
  };
  req.upload.onprogress = function ( e ) {
    if ( e.lengthComputable ) {
      var complete = ( e.loaded / e.total * 100 | 0 );
      var progress = document.getElementById('uploadprogress_'+id);
      if ( progress) progress.value = progress.innerHTML = complete;
      progress = null;
      complete = null;
      delete progress;
      delete complete;
    }
  };
  req.send(formData);
  req = null;
  delete req;
  callbackHandler = null;
  delete callbackHandler;
  formData = null;
  delete formData;
}
function upload_file_to_server( file ) {
  var formData = tests.formdata ? new FormData() : null;
  formData.append( 'file', file );
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '/upload_photo.php');
  xhr.onload = function() {
    progress.value = progress.innerHTML = 100;
  };
  xhr.upload.onprogress = function ( e ) {
    if ( e.lengthComputable ) {
      var complete = ( e.loaded / e.total * 100 | 0 );
      progress.value = progress.innerHTML = complete;
      compelte = null;
      delete complete;
    }
  };
  xhr.send(formData);
  xhr = null;
  delete xhr;
  callbackHandler = null;
  delete callbackHandler;
  formData = null;
  delete formData;
}
