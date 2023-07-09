$(document).ready(function () {
    $("#form-tenant-login").validate({
        // Specify validation rules
        rules: {}, submitHandler: function () {
            sessionStorage.setItem("tenant_id_number", $('#id-number').val());
            sessionStorage.setItem("tenant_phone_number", $('#phone-number').val());
            getStatementLink();
            getLeaseLink();
        }
    });

    $("#form-tenant-login").submit(function (event) {
        event.preventDefault();
    });

});

let getStatementLink= () =>{
    let url = "/api/tenant/getlease/" + sessionStorage.getItem("tenant_id_number") + "/" + sessionStorage.getItem("tenant_phone_number");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if(data.guid === undefined){
                $("#logged-in-content").addClass("display-none");
                $("#form-tenant-login").removeClass("display-none");
                showToast("Error. Tenant authentication failed");
            }else{
                $("#btn-view-statement").attr("href", "/statement/?guid=" + data.guid);
                $("#logged-in-content").removeClass("display-none");
                $("#form-tenant-login").addClass("display-none");
            }

        },
        error: function (xhr) {

        }
    });
}

let getLeaseLink= () =>{
    let url = "/api/tenant/getleaseDocumentName/" + sessionStorage.getItem("tenant_id_number")  + "/" + sessionStorage.getItem("tenant_phone_number");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if(data.name === undefined){
                $("#logged-in-content").addClass("display-none");
                $("#form-tenant-login").removeClass("display-none");
                showToast("Error. Tenant authentication failed");
            }else{
                $("#btn-download-lease").attr("href", "/api/lease_document/" + data.name);
                $("#logged-in-content").removeClass("display-none");
                $("#form-tenant-login").addClass("display-none");
            }

        },
        error: function (xhr) {

        }
    });
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