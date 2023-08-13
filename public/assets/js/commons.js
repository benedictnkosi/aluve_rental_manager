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

function isUserLoggedIn() {
    let url = "/no_auth/me";

    $.ajax({
        url: url,
        type: "GET",
        success: function (data) {
            console.log(data.authenticated);
            if (!data.authenticated) {
                window.location.href = "/logout";
            }
        },
        complete: function (data) {
            console.log(data.status);
            if (data.status.toString().localeCompare("200") !== 0) {
                window.location.href = "/logout";
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            window.location.href = "/logout";
        },
    });
}

let showToast = (message) =>{
    const liveToast = document.getElementById('liveToast')
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(liveToast)
    $('#toast-message').html('<div class="alert" role="alert">'+message+'</div>');
    // if(message.toLowerCase().includes("success")){
    //     $('#toast-message').html('<div class="alert alert-success" role="alert">'+message+'</div>');
    // }else if(message.toLowerCase().includes("fail") || message.toLowerCase().includes("error")){
    //     $('#toast-message').html('<div class="alert alert-danger" role="alert">'+message+'</div>');
    // }else{
    //     $('#toast-message').html('<div class="alert" role="alert">'+message+'</div>');
    // }
    toastBootstrap.show();
}

let populateStatement = (leaseGuid) => {
    let url = "/api/lease/" + leaseGuid;
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (lease) {
            if (lease.result_code !== undefined) {
                if (lease.result_code === 1) {
                    return;
                }
            }

            sessionStorage.setItem("lease-guid", lease.lease_guid);
            sessionStorage.setItem("tenant_guid", lease.tenant_guid);

            $("#statement-property-name").html(lease.property_name);
            $("#statement-property-email").html(lease.property_email);
            $("#statement-property-phone").html(lease.property_phone);
            $("#statement-property-address").html(lease.property_address);

            $("#statement-tenant-name").html(lease.tenant_name);
            $("#statement-tenant-email").html(lease.email);
            $("#statement-tenant-phone").html(lease.phone_number);
            $("#statement-tenant-address").html(
                lease.property_address + ", " + lease.unit_name
            );

            $("#statement-bank-name").html(lease.bank_name);
            $("#statement-bank-account").html(lease.bank_account_number);
            $("#statement-bank-account-type").html(lease.bank_account_type);
            $("#statement-bank-branch").html(lease.bank_branch);

            $("#lease-end-date").html("Lease End Date: " + lease.lease_end);
            $("#lease-start-date").html("Lease Start Date: " + lease.lease_start);
            $(".statement-due").html("Due: " + lease.due);
        },
        error: function (xhr) {},
    });
};

let getTransactions = (leaseGuid, userRole) => {

    let url = "/api/lease/transactions/" + leaseGuid;
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let html = "";
            if (data.result_code !== undefined) {
                if (data.result_code === 1) {
                    $(".statement-due").html("Due: R0.00");
                }
            }else{
                data.forEach(function (transaction) {
                    const delete_button = userRole.localeCompare("landlord") === 0
                        ? '<div class="col-1"><i class="fa-solid fa-trash-can red-text delete-transaction-button" role="button" transaction-id="' +
                        transaction.guid +
                        '"></i></div>'
                        : "";
                    html +=
                        '<div class="transaction-card d-flex w-100 gap-2">\n' +
                        '                                <div class="col-1">\n' +
                        '                                    <i class="fa-solid fa-money-bill-transfer ms-0 green-text"></i>\n' +
                        "                                </div>\n" +
                        '                                <div class="col-6">\n' +
                        '                                    <p class="m-0">' +
                        transaction.description +
                        "</p>\n" +
                        '                                    <p class="m-0 fw-light"><small>' +
                        transaction.date +
                        "</small></p>\n" +
                        "                                </div>\n" +
                        '                                <div class="col-2">\n' +
                        '                                    <p class="m-0 fw-normal">' +
                        transaction.amount +
                        "</p>\n" +
                        "\n" +
                        "                                </div>\n" +
                        '                                <div class="col-2">\n' +
                        '                                    <p class="m-0 fw-normal">' +
                        transaction.balance +
                        "</p>\n" +
                        "                                </div>\n"
                        + delete_button +
                        "                            </div></div>";
                });
            }
            $("#transaction-rows").html(html);
            $(".statement-card-details").removeClass("display-none");
            $(".delete-transaction-button").click(function (event) {
                sessionStorage.setItem(
                    "transaction-id",
                    event.target.getAttribute("transaction-id")
                );
                $("#confirmDeleteTransactionModal").modal("toggle");
            });


        },
        error: function (xhr) {},
    });
};


let getURLParameter= (name) =>{
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    return urlParams.get(name);
}