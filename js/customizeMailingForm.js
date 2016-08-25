cj(document).ajaxComplete(
  function(event, xhr, settings) {
    if (settings.url.indexOf("action=getfields") > 0) {
      // remove div "send test e-mail to group"
      cj("div.preview-group").hide();
    
      // remove elements in track response
      cj("#tab-response").children().hide();
    }
  }
);
