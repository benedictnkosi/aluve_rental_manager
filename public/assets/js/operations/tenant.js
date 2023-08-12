$(document).ready(function () {
    getTenantInfo();
    getMaintenanceCalls();
    $("#form-tenant-login").submit(function (event) {
        event.preventDefault();
    });

    $("#btn-confirm-maintenance").click(function (event) {
        event.preventDefault();
        closeACall();
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

    $(".statement-close").click(function () {
        $(".statement-card-details").addClass("display-none");
    });

    $(".view-statement-button").click(function () {
        populateStatement(sessionStorage.getItem("lease-guid"));
        getTransactions(sessionStorage.getItem("lease-guid"), "tenant");
    });
});


let logACall = () => {
    const summary = $("#call-summary").val().trim();
    let url = "/api/tenant/maintenance/new";
    const data = {
        summary: summary
    };

    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                getMaintenanceCalls();
                $('#maintenanceModal').modal('toggle');
            }
        }
    });
}
let getMaintenanceCalls = () => {
    let url = "/api/tenant/maintenance/get"
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let html = "";
            if (data.result_code !== undefined) {
                if (data.result_code === 1) {
                    return;
                }
            }
            data.forEach(function (response) {
                const callDate = new Date(response.date_logged);
                const today = new Date();
                // To calculate the time difference of two dates
                const Difference_In_Time = today.getTime() - callDate.getTime();
                // To calculate the no. of days between two dates
                const Difference_In_Days = parseInt(Difference_In_Time / (1000 * 3600 * 24));

                let dateClass = "green-text";
                let cardClass = "";
                if (Difference_In_Days > 7) {
                    dateClass = "red-text";
                    cardClass = "border-left-red";
                }

                let numberOfDays = Difference_In_Days + " days ago";
                if (Difference_In_Days === 0) {
                    numberOfDays = "Today";
                } else if (Difference_In_Days === 1) {
                    numberOfDays = "Yesterday";
                }
                html += '<div class="maintenance-card d-flex w-100 mt-1 ' + cardClass + '">\n' +
                    '                <div class="col-1">\n' +
                    '                  <i class="fa-solid fa-wrench green-text expense-icon m-0"></i>\n' +
                    '                </div>\n' +
                    '                <div class="col-7">\n' +
                    '                  <p class="m-0 fw-normal">' + response.summary + '</p>\n' +
                    '                </div>\n' +
                    '                <div class="col-3">\n' +
                    '                  <p class="m-0 fw-normal">' + response.status.toLocaleString() + '</p>\n' +
                    '                  <p class="m-0 ' + dateClass + '">' + numberOfDays + '</p>\n' +
                    '                </div>\n';
                if (response.status.localeCompare("new") === 0) {
                    html += '                <div class="col-1" style="text-align: right;">\n' +
                        '                  <i class="fa-solid fa-xmark m-0 delete-maintenance-icon red-text"  role="button" maintenance-guid="' + response.uid + '"></i>\n' +
                        '                </div>';
                } else {
                    html += '                <div class="col-1" style="text-align: right;">\n' +
                        '                  <i class="fa-solid fa-check green-text"></i>\n' +
                        '                </div>';
                }

                html += '            </div> ';

            });

            $("#maintenance-div").html(html);

            $(".delete-maintenance-icon").click(function (event) {
                sessionStorage.setItem("maintenance-guid", event.target.getAttribute("maintenance-guid"));
                $('#confirmMaintenanceModal').modal('toggle');
            });

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

            if (data.application.application.status !== undefined) {
                sessionStorage.setItem("application_guid", data.application.application.uid);
                sessionStorage.setItem("lease-guid", data.lease.guid);

                $(".tenant-div-toggle").addClass("display-none");
                $("." + data.application.application.status.replace(" ", "-")).removeClass("display-none");

                if (data.application.application.status.localeCompare("accepted") === 0) {
                    getPropertyLeaseToSign();
                } else if (data.application.application.status.localeCompare("lease uploaded") === 0) {
                    let html = "";
                    data.application.documents.forEach(function (document) {
                        html += '<p><a class="dropdown-item" target="_blank"  style="color: #545151 !important;" href="/api/document/' + document.name + '"><i class="fa-solid fa-file-pdf red-icon me-3"></i><small>' + document.document_type.name + '</small></a></p>\n';
                    });
                    $("#tenant-documents").html(html);
                } else if (data.application.application.status.localeCompare("tenant") === 0) {
                    getSignedLeaseLink();
                    getInspectionLink();
                }

                //load the unit details

                const parking = data.application.application.unit.parking === true ? "1" : "0";
                const children = data.application.application.unit.children_allowed === true ? "1" : "0";

                if (data.lease.start !== undefined) {
                    $("#lease-dates").html(data.lease.start.substring(0, data.lease.start.indexOf("T")) + " to " + data.lease.end.substring(0, data.lease.end.indexOf("T")));
                } else {
                    $('#lease-dates-p').addClass("display-none");
                }
                $("#unit-name").html(data.application.application.unit.name);
                $("#unit-address").html(data.application.application.property.address);
                $("#unit-rent").html("R" + data.application.application.unit.rent.toFixed(2));
                $("#unit-beds").html(data.application.application.unit.bedrooms);
                $("#unit-bathrooms").html(data.application.application.unit.bathrooms);
                $("#unit-max-occupants").html(data.application.application.unit.max_occupants);
                $("#unit-parking").html(parking);
                $("#unit-children").html(children);
                $(".unit-details").removeClass("display-none");
            }

            $('.tenant-container').removeClass("display-none");

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
                if (exisingDocuments === undefined) {
                    html = '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/api/lease_document/' + data.name + '"><i class="fa-solid fa-file-pdf red-icon me-3"></i><small>Property Lease (Download)</small></a></p>\n';
                } else {
                    html = exisingDocuments + '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/api/lease_document/' + data.name + '"><i class="fa-solid fa-file-pdf red-icon me-3"></i><medium>Property Lease (Download)</medium></a></p>\n';
                }
            }

            $("#tenant-documents").html(html);
        },
        error: function (xhr) {

        }
    });
}

let getSignedLeaseLink = () => {
    let url = "/api/tenant/getleaseDocumentName";
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
                if (exisingDocuments === undefined) {
                    html = '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/api/document/' + data.name + '"><i class="fa-solid fa-file-pdf red-icon me-3"></i><medium>Signed Lease</medium></a></p>\n';
                } else {
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
                if (exisingDocuments === undefined) {
                    html = '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/view/inspection/?guid=' + data.lease.guid + '"><i class="fa-solid fa-link  red-icon me-3"></i><medium>View Inspection</medium></a></p>\n';
                } else {
                    html = exisingDocuments + '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/view/inspection/?guid=' + data.lease.guid + '"><i class="fa-solid fa-link  red-icon me-3"></i><medium>View Inspection</medium></a></p>\n';
                }
                $('#tenant-documents').html(html);
            }
        },
        error: function (xhr) {

        }
    });
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

    const fileSize = file_data.size;
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
            if (jsonObj.alldocs_uploaded === true) {
                $(".tenant-div-toggle").addClass("display-none");
                $(".lease-uploaded").removeClass("display-none");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showToast(errorThrown);
        }
    });
}

let closeACall = () => {
    let url = "/api/maintenance/close";
    const data = {
        maintenance_id: sessionStorage.getItem("maintenance-guid"),
    };

    $.ajax({
        url: url,
        type: "put",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                getMaintenanceCalls();
                $('#confirmMaintenanceModal').modal('toggle');
            }
        }
    });
}
