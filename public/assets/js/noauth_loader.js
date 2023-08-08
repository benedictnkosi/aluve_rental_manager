$(document).ready(function () {
  $(document).ajaxSend(function(){
    $(".spinner-border").show();
    $(".overlay").show();
    
  });
  
  $(document).ajaxComplete(function(event,xhr,options){
    $(".spinner-border").hide();
   $(".overlay").hide();
    if(xhr.status === 302){
        console.log("location " + xhr.getResponseHeader('location'));
        window.location.href = "/logout";
    }
  });
});

