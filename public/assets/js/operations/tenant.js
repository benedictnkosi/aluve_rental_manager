$(document).ready(function () {
    $("#form-tenant-login").validate({
        // Specify validation rules
        rules: {}, submitHandler: function () {
            sessionStorage.setItem("tenant_id_number", $('#id-number').val());
            sessionStorage.setItem("tenant_phone_number", $('#phone-number').val());
            authenticateTenant();
        }
    });

    $("#form-tenant-login").submit(function (event) {
        event.preventDefault();

    });

    $("#btn-log-call").click(function (event) {
        event.preventDefault();
        logACall();
    });

    $('#onboarding_lease').change(function () {
        uploadSupportingDocuments("Signed Lease", $("#onboarding_lease").prop("files")[0]);
    });

    $('#onboarding_iddoc').change(function () {
        uploadSupportingDocuments("ID Document", $("#onboarding_iddoc").prop("files")[0]);
    });

    $('#onboarding_pop').change(function () {
        uploadSupportingDocuments("Proof OF Payment", $("#onboarding_pop").prop("files")[0]);
    });

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

let authenticateTenant = () => {
    let url = "/public/tenant/get/" + sessionStorage.getItem("tenant_id_number") + "/" + sessionStorage.getItem("tenant_phone_number");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if (data.result_code === 1) {
                $("#logged-in-content").addClass("display-none");
                $("#form-tenant-login").removeClass("display-none");
                showToast("Error. Tenant verification failed");
            } else {
                $("#logged-in-content").removeClass("display-none");
                $("#form-tenant-login").addClass("display-none");
                sessionStorage.setItem("tenant_guid", data.tenant.guid);
                sessionStorage.setItem("application_guid", data.application.uid);

                $(".tenant-div-toggle").addClass("display-none");
                $("." + data.application.status).removeClass("display-none");

                if(data.application.status.localeCompare("accepted") === 0) {
                    getPropertyLeaseToSign();
                }else if(data.application.status.localeCompare("tenant") === 0){
                    getSignedLeaseLink();
                    getStatementLink();
                    getInspectionLink();
                }
            }
        },
        error: function (xhr) {

        }
    });
}

let getPropertyLeaseToSign = () => {
    let url = "/public/tenant/lease_to_sign/" + sessionStorage.getItem("application_guid");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if (data.name === undefined) {
                showToast("Error. Property lease not found. Please contact agent.");
                $("#btn-download-lease-agreement").removeAttr('href');
            } else {
                $("#btn-download-lease-agreement").attr("href", "/public/lease_document/" + data.name);
            }
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
                showToast("Error: Statement link not found. Please contact agent.");
                $("#btn-view-statement").removeAttr('href');
            } else {
                $("#btn-view-statement").attr("href", "/statement/?guid=" + data.guid);
            }

        },
        error: function (xhr) {

        }
    });
}

let getSignedLeaseLink = () => {
    let url = "/public/tenant/getleaseDocumentName/" + sessionStorage.getItem("tenant_id_number") + "/" + sessionStorage.getItem("tenant_phone_number");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if (data.name === undefined) {
                showToast("Error: Signed lease not found. Please contact agent.");
                $("#btn-download-lease").removeAttr('href');
            } else {
                $("#btn-download-lease").attr("href", "/public/lease_document/" + data.name);
            }
        },
        error: function (xhr) {

        }
    });
}

let getInspectionLink = () => {
    let url = "/public/tenant/getlease/" + sessionStorage.getItem("tenant_id_number") + "/" + sessionStorage.getItem("tenant_phone_number");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if (data.guid === undefined) {
                $("#btn-view-Inspection").removeAttr('href');
            } else {

                $("#btn-view-Inspection").attr("href", "/view/inspection/?guid=" + data.guid);
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

function uploadSupportingDocuments(documentType, file_data) {
    let url = "/public/tenant/upload/lease";
    const uid = sessionStorage.getItem("application_guid");
    const form_data = new FormData();
    form_data.append("file", file_data);
    form_data.append("application_guid", uid);
    form_data.append("tenant_guid", sessionStorage.getItem("tenant_guid"));
    form_data.append("document_type", documentType);

    if (file_data === undefined) {
        showToast("Error: Please upload file")
        return;
    }

    const fileSize =file_data.size;
    const fileMb = fileSize / 1024 ** 2;
    if (fileMb >= 5) {
        showToast("Error: Please upload files less than 5mb");
        return;
    }

    $.ajax({
        url: url,
        type: "POST",
        data: form_data,
        dataType: 'script',
        cache: false,
        contentType: false,
        processData: false,
        success: function (response) {
            const jsonObj = JSON.parse(response);
            showToast(jsonObj.result_message);
            if(jsonObj.alldocs_uploaded === true){
                $(".tenant-div-toggle").addClass("display-none");
                $(".lease_uploaded").removeClass("display-none");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showToast(errorThrown);
        }
    });
}