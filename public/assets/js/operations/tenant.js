$(document).ready(function () {
    getTenantInfo();

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
    let url = "/api/maintenance/new";
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
    let url = "/api/maintenance/get/" + sessionStorage.getItem("tenant_id_number") + "/" + sessionStorage.getItem("tenant_phone_number");
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

let getTenantInfo = () => {
    let url = "/api/tenant/get";
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if (data.application.result_code === 1 && data.lease.result_code === 1) {
                return;
            }
            
            if(data.application.application.status !== undefined){
                sessionStorage.setItem("application_guid", data.application.application.uid);
                $(".tenant-div-toggle").addClass("d-none");
                $("." + data.application.application.status.replace(" ", "-")).removeClass("d-none");

                if(data.application.application.status.localeCompare("accepted") === 0) {
                    getPropertyLeaseToSign();
                }else if(data.application.application.status.localeCompare("lease uploaded") === 0){
                    let html = "";
                    data.application.documents.forEach(function (document) {
                        html += '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/api/document/' + document.name + '"><i class="fa-solid fa-file-pdf red-icon me-3"></i><small>'+document.document_type.name+'</small></a></p>\n';
                    });
                    $("#tenant-documents").html(html);
                }else if(data.application.application.status.localeCompare("tenant") === 0){
                    getSignedLeaseLink();
                    getStatementLink();
                    getInspectionLink();
                }

                //load the unit details

                const parking = data.application.application.unit.parking === true ? "1" : "0";
            const children = data.application.application.unit.children_allowed === true ? "1" : "0";

                $("#unit-name").html(data.application.application.unit.name);
            $("#unit-address").html(data.application.application.property.address);
            $("#unit-rent").html("R" + data.application.application.unit.rent.toFixed(2) );
            $("#unit-beds").html(data.application.application.unit.bedrooms );
            $("#unit-bathrooms").html(data.application.application.unit.bathrooms );
            $("#unit-max-occupants").html(data.application.application.unit.max_occupants );
            $("#unit-parking").html(parking);
            $("#unit-children").html(children);
            $(".unit-details").show();
            }

            $('.tenant-container').show();
        
        },
        error: function (xhr) {

        }
    });
}

let getPropertyLeaseToSign = () => {
    let url = "/api/tenant/lease_to_sign/" + sessionStorage.getItem("application_guid");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if (data.name === undefined) {
                showToast("Error. Property lease not found. Please contact agent.");
                $("#btn-download-lease-agreement").removeAttr('href');
            } else {
                const exisingDocuments = $('#tenant-documents').html();
                if(exisingDocuments === undefined){
                    html = '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/no_auth/lease_document/' + data.name + '"><i class="fa-solid fa-file-pdf red-icon me-3"></i><small>Property Lease (Download)</small></a></p>\n';
                }else{
                    html = exisingDocuments + '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/no_auth/lease_document/' + data.name + '"><i class="fa-solid fa-file-pdf red-icon me-3"></i><medium>Property Lease (Download)</medium></a></p>\n';
                }
            }

            $("#tenant-documents").html(html);
        },
        error: function (xhr) {

        }
    });
}

let getStatementLink = () => {
    let url = "/api/tenant/get";
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if (data.lease.guid === undefined) {
                showToast("Error: Statement link not found. Please contact agent.");
                $("#btn-view-statement").removeAttr('href');
            } else {
                const exisingDocuments = $('#tenant-documents').html();
                if(exisingDocuments === undefined){
                    html =  '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/statement/?guid=' + data.lease.guid + '"><i class="fa-solid fa-link  red-icon me-3"></i><medium>View Statement</medium></a></p>\n';
                }else{
                    html = exisingDocuments + '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/statement/?guid=' + data.lease.guid + '"><i class="fa-solid fa-link  red-icon me-3"></i><medium>View Statement</medium></a></p>\n';
                }
                $('#tenant-documents').html(html);
            }

        },
        error: function (xhr) {

        }
    });
}

let getSignedLeaseLink = () => {
    let url = "/api/tenant/getleaseDocumentName" ;
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if (data.name === undefined) {
                showToast("Error: Signed lease not found. Please contact agent.");
                $("#btn-download-lease").removeAttr('href');
            } else {
                const exisingDocuments = $('#tenant-documents').html();
                if(exisingDocuments === undefined){
                    html = '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/api/document/' + data.name + '"><i class="fa-solid fa-file-pdf red-icon me-3"></i><medium>Signed Lease</medium></a></p>\n';
                }else{
                    html = exisingDocuments + '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/api/document/' + data.name + '"><i class="fa-solid fa-file-pdf red-icon me-3"></i><medium>Signed Lease</medium></a></p>\n';
                }
                $('#tenant-documents').html(html);
            }
        },
        error: function (xhr) {

        }
    });
}

let getInspectionLink = () => {
    let url = "/api/tenant/get";
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if (data.lease.guid === undefined) {
                $("#btn-view-Inspection").removeAttr('href');
            } else {
                const exisingDocuments = $('#tenant-documents').html();
                if(exisingDocuments === undefined){
                    html =  '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/view/inspection/?guid=' + data.lease.guid + '"><i class="fa-solid fa-link  red-icon me-3"></i><medium>View Inspection</medium></a></p>\n';
                }else{
                    html = exisingDocuments + '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/view/inspection/?guid=' + data.lease.guid + '"><i class="fa-solid fa-link  red-icon me-3"></i><medium>View Inspection</medium></a></p>\n';
                }
                $('#tenant-documents').html(html);
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
    let url = "/api/tenant/upload/lease";
    const uid = sessionStorage.getItem("application_guid");
    const form_data = new FormData();
    form_data.append("file", file_data);
    form_data.append("application_guid", uid);
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
                $(".tenant-div-toggle").addClass("d-none");
                $(".lease-uploaded").removeClass("d-none");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showToast(errorThrown);
        }
    });
}