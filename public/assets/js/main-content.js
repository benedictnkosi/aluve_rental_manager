$(document).ready(function () {
    if (sessionStorage.getItem("current_page") === null) {
        updateView('dashboard-content-div');
    } else {
        updateView(sessionStorage.getItem("current_page"));
    }

    $(".nav-link").click(function (event) {
        updateView(event.target.getAttribute("main-content"), event.target.getAttribute("header"));
    });

});

function updateView(selectedDiv, header) {
    $(".main-content").addClass("display-none");
    $("#" + selectedDiv).removeClass("display-none");
    $("#main-content-header").html(header);

    sessionStorage.setItem("current_page", selectedDiv);
}

let showMessage = (response, divName) =>{
    $('#' + divName).removeClass("display-none");

    $('#' + divName).html(response.result_message)
    if (response.result_code === 0) {
        $('#' + divName).addClass("alert-success");
        $('#' + divName).removeClass("alert-danger");
    }else {
        $('#' + divName).addClass("alert-danger");
        $('#' + divName).removeClass("alert-success");
    }
}

let hideMessage = () =>{
    $('.alert').addClass("display-none");
}

let showToast = (message) =>{
    const liveToast = document.getElementById('liveToast')
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(liveToast)
    $('#toast-message').html(message);
    toastBootstrap.show()
}
