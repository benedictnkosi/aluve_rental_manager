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

    $("#btn-log-call").click(function (event) {
        event.preventDefault();
        logACall();
    });

    getMaintenanceCalls();

});


let logACall = () => {
    const summary = $("#call-summary").val().trim();
    let url = "/public/maintenance/new";
    const data = {
        id_number: sessionStorage.getItem("tenant_id_number"),
        phone_number: sessionStorage.getItem("tenant_phone_number"),
        summary: summary,
    };

    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                getMaintenanceCalls();
            }
        }
    });
}

let getMaintenanceCalls = () => {
    let url = "/public/maintenance/get/" + sessionStorage.getItem("tenant_id_number") + "/" + sessionStorage.getItem("tenant_phone_number");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let html = "";
            data.forEach(function (call) {
                const d = new Date(call.date_logged);
                if (call.status.localeCompare("resolved") === 0) {
                    html += '<li\n' +
                        '                                    class="list-group-item d-flex justify-content-between align-items-center border-start-0 border-top-0 border-end-0 border-bottom rounded-0 mb-2">\n' +
                        '                                <div class="d-flex align-items-center w-75">\n' +
                        '<s>' + call.summary + '</s>' +
                        '                                </div>' +
                        '<div class="d-flex align-items-center"><i class="bi-calendar-event">' + d.toLocaleDateString () + '</i></div>\n' +
                        '                            </li>';
                } else {
                    html += '<li\n' +
                        '                                    class="list-group-item d-flex justify-content-between align-items-center border-start-0 border-top-0 border-end-0 border-bottom rounded-0 mb-2">\n' +
                        '                                <div class="d-flex align-items-center w-75">\n' +
                        '                                    ' + call.summary + '\n' +
                        '                                </div><div class="d-flex align-items-center"><i class="bi-calendar-event">' + d.toLocaleDateString() + '</i></div>\n' +
                        '                            </li>';
                }

            });

            $('#list-calls').html(html);
        },
        error: function (xhr) {

        }
    });
}

let getStatementLink = () => {
    let url = "/public/tenant/getlease/" + sessionStorage.getItem("tenant_id_number") + "/" + sessionStorage.getItem("tenant_phone_number");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if (data.guid === undefined) {
                $("#logged-in-content").addClass("display-none");
                $("#form-tenant-login").removeClass("display-none");
                showToast("Error. Tenant authentication failed");
            } else {
                $("#btn-view-statement").attr("href", "/statement/?guid=" + data.guid);
                $("#logged-in-content").removeClass("display-none");
                $("#form-tenant-login").addClass("display-none");
            }

        },
        error: function (xhr) {

        }
    });
}

let getLeaseLink = () => {
    let url = "/public/tenant/getleaseDocumentName/" + sessionStorage.getItem("tenant_id_number") + "/" + sessionStorage.getItem("tenant_phone_number");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if (data.name === undefined) {
                $("#logged-in-content").addClass("display-none");
                $("#form-tenant-login").removeClass("display-none");
                showToast("Error. Tenant authentication failed");
            } else {
                $("#btn-download-lease").attr("href", "/api/lease_document/" + data.name);
                $("#logged-in-content").removeClass("display-none");
                $("#form-tenant-login").addClass("display-none");
            }

        },
        error: function (xhr) {

        }
    });
}

let showToast = (message) => {
    const liveToast = document.getElementById('liveToast')
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(liveToast)
    if (message.toLowerCase().includes("success")) {
        $('#toast-message').html('<div class="alert alert-success" role="alert">' + message + '</div>');
    } else if (message.toLowerCase().includes("fail") || message.toLowerCase().includes("error")) {
        $('#toast-message').html('<div class="alert alert-danger" role="alert">' + message + '</div>');
    } else {
        $('#toast-message').html('<div class="alert alert-dark" role="alert">' + message + '</div>');
    }
    toastBootstrap.show();
}