$(document).ready(function () {
    if (sessionStorage.getItem("current_page") === null) {
        updateView('dashboard-content-div');
    } else {
        updateView(sessionStorage.getItem("current_page"), sessionStorage.getItem("current_page_header"));
    }

    $(".nav-link").click(function (event) {
        updateView(event.target.getAttribute("main-content"), event.target.getAttribute("header"));
    });

    $(document).ajaxSend(function(){
        $("div.spanner").addClass("show");
        $("div.overlay").addClass("show");
    });

    $(document).ajaxComplete(function(){
        $("div.spanner").removeClass("show");
        $("div.overlay").removeClass("show");
    });
});

function updateView(selectedDiv, header) {
    $(".main-content").addClass("display-none");
    $("#" + selectedDiv).removeClass("display-none");
    $("#main-content-header").html(header);

    sessionStorage.setItem("current_page", selectedDiv);
    sessionStorage.setItem("current_page_header", header);
}

let showToast = (message) =>{
    const liveToast = document.getElementById('liveToast')
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(liveToast)
    if(message.toLowerCase().includes("success")){
        $('#toast-message').html('<div class="alert alert-success" role="alert">'+message+'</div>');
    }else if(message.toLowerCase().includes("fail") || message.toLowerCase().includes("error")){
        $('#toast-message').html('<div class="alert alert-danger" role="alert">'+message+'</div>');
    }else{
        $('#toast-message').html('<div class="alert alert-dark" role="alert">'+message+'</div>');
    }
    toastBootstrap.show();
}

let getURLParameter= (name) =>{
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    return urlParams.get(name);
}
