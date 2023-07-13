$(document).ready(function () {
    $("div.spanner").addClass("show");
    $("div.overlay").addClass("show");

    if (sessionStorage.getItem("current_page") === null) {
        updateView('dashboard-content-div', "Dashboard");
    } else {
        updateView(sessionStorage.getItem("current_page"), sessionStorage.getItem("current_page_header"));
    }

    $(".nav-link").click(function (event) {
        if(!$(event.target).hasClass(("settings-nav-link"))){
            updateView(event.target.getAttribute("main-content"), event.target.getAttribute("header"));
        }
    });

    $(".settings-nav-link").click(function (event) {
        $(".settings-nav-link").removeClass("active");
        updateSettingsView(event.target.getAttribute("form-to-show"));
        $(event.target).addClass("active");
    });


    $(document).ajaxSend(function(){
        $("div.spanner").addClass("show");
        $("div.overlay").addClass("show");
    });

    $(document).ajaxComplete(function(){
        $("div.spanner").removeClass("show");
        $("div.overlay").removeClass("show");
    });

    $('.nav-links').unbind('click')
    $(".nav-links").click(function (event) {
        event.stopImmediatePropagation();
        $(".headcol").css("position", "absolute");
    });

    $(".mobile-menu-link").click(function (event) {
        $("#offcanvasNavbar").removeClass("show");
    });


});

function updateSettingsView(selectedForm) {
    $(".settings-form").addClass("display-none");
    $("#" + selectedForm).removeClass("display-none");
}

function updateView(selectedDiv, header) {
    $(".main-content").addClass("display-none");
    $("#" + selectedDiv).removeClass("display-none");
    $("#main-content-header").html(header);

    sessionStorage.setItem("current_page", selectedDiv);
    sessionStorage.setItem("current_page_header", header);

    switch (selectedDiv) {
        case "dashboard-content-div":
            generateAllGraphs();
            break;
        case "units-content-div":
            getAllUnits();
            break;
        case "leases-content-div":
            getAllLeases();
            break;
        case "applications-content-div":
            getApplications();
            break;
        default:

    }
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


function myFunction() {
    var x = document.getElementById("myLinks");
    if (x.style.display === "block") {
        x.style.display = "none";
    } else {
        x.style.display = "block";
    }
}
