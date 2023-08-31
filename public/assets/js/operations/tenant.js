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

    getApplications();

    $("#onboarding_lease").change(function () {
        uploadLeaseDocuments(
            "Signed Lease",
            $("#onboarding_lease").prop("files")[0]
        );
    });

    $("#onboarding_iddoc").change(function () {
        uploadLeaseDocuments(
            "ID Document",
            $("#onboarding_iddoc").prop("files")[0]
        );
    });

    $("#onboarding_pop").change(function () {
        uploadLeaseDocuments(
            "Proof OF Payment",
            $("#onboarding_pop").prop("files")[0]
        );
    });

    $('#bank_statement').change(function () {
        uploadIncomeDocuments("statement", $("#bank_statement").prop("files")[0]);
    });

    $('#application_payslip').change(function () {
        uploadIncomeDocuments("payslip", $("#application_payslip").prop("files")[0]);
    });

    $('#co_bank_statement').change(function () {
        uploadIncomeDocuments("co_statement", $("#co_bank_statement").prop("files")[0]);
    });

    $('#co_application_payslip').change(function () {
        uploadIncomeDocuments("co_payslip", $("#co_application_payslip").prop("files")[0]);
    });

    $(".statement-close").click(function () {
        $(".statement-card-details").addClass("display-none");
    });

    $(".view-statement-button").click(function () {
        populateStatement(sessionStorage.getItem("lease-guid"));
        getTransactions(sessionStorage.getItem("lease-guid"), "tenant");
    });

    $(".application-details-close").click(function () {
        $(".closable-div").addClass("display-none");
        $(".application-card").removeClass("display-none");
        $("#applications-div-container").removeClass("display-none");
        //hide the buttons on open application window
        $(".btn-decline-application").addClass("display-none");
        $(".btn-convert-application").addClass("display-none");
        $(".btn-accept-application").addClass("display-none");
        getApplications();
    });

    $("#finishButton").click(function () {
        if(sessionStorage.getItem("application_reference") !== null){
            $('#supporting-docs-div').addClass("display-none");
            openApplicationDetails(sessionStorage.getItem("application-guid"))
        }else{
            showToast(" Please upload all supporting documents");
        }
    });
});

let logACall = () => {
    const summary = $("#call-summary").val().trim();
    let url = "/api/tenant/maintenance/new";
    const data = {
        summary: summary,
    };

    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (response) {
            showToast(response.result_message);
            if (response.result_code === 0) {
                getMaintenanceCalls();
                $("#maintenanceModal").modal("toggle");
            }
        },
    });
};

let getMaintenanceCalls = () => {
    let url = "/api/tenant/maintenance/get";
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
                const Difference_In_Days = parseInt(
                    Difference_In_Time / (1000 * 3600 * 24)
                );

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
                html +=
                    '<div class="maintenance-card d-flex w-100 mt-1 ' +
                    cardClass +
                    '">\n' +
                    '                <div class="col-1">\n' +
                    '                  <i class="fa-solid fa-wrench green-text expense-icon m-0"></i>\n' +
                    "                </div>\n" +
                    '                <div class="col-7">\n' +
                    '                  <p class="m-0 fw-normal">' +
                    response.summary +
                    "</p>\n" +
                    "                </div>\n" +
                    '                <div class="col-3">\n' +
                    '                  <p class="m-0 fw-normal">' +
                    response.status.toLocaleString() +
                    "</p>\n" +
                    '                  <p class="m-0 ' +
                    dateClass +
                    '">' +
                    numberOfDays +
                    "</p>\n" +
                    "                </div>\n";
                if (response.status.localeCompare("new") === 0) {
                    html +=
                        '                <div class="col-1" style="text-align: right;">\n' +
                        '                  <i class="fa-solid fa-xmark m-0 delete-maintenance-icon red-text"  role="button" maintenance-guid="' +
                        response.uid +
                        '"></i>\n' +
                        "                </div>";
                } else {
                    html +=
                        '                <div class="col-1" style="text-align: right;">\n' +
                        '                  <i class="fa-solid fa-check green-text"></i>\n' +
                        "                </div>";
                }

                html += "            </div> ";
            });

            if (html.length > 0) {
                $("#maintenance-div").html(html);
                $("#maintenance-div").removeClass("display-none");
            }

            $(".delete-maintenance-icon").click(function (event) {
                sessionStorage.setItem(
                    "maintenance-guid",
                    event.target.getAttribute("maintenance-guid")
                );
                $("#confirmMaintenanceModal").modal("toggle");
            });
        },
        error: function (xhr) {
        },
    });
};

let getTenantInfo = () => {
    let url = "/api/tenant/get";
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if (data.result_code === 1) {
                return;
            } else {
                sessionStorage.setItem("lease-guid", data.guid);
                const parking = data.unit.parking === true ? "1" : "0";
                const children = data.unit.children_allowed === true ? "1" : "0";

                if (data.start !== undefined) {
                    $("#lease-dates").html(
                        data.start.substring(0, data.start.indexOf("T")) +
                        " to " +
                        data.end.substring(0, data.end.indexOf("T"))
                    );
                } else {
                    $("#lease-dates-p").addClass("display-none");
                }
                $("#inspection_link").attr("href", "/view/inspection/?guid=" + data.guid);
                $("#unit-name").html(data.unit.name);
                $("#unit-address").html(data.property.address);
                $("#unit-rent").html( data.unit.rent.toFixed(2));
                $("#unit-beds").html(data.unit.bedrooms);
                $("#unit-bathrooms").html(data.unit.bathrooms);
                $("#unit-max-occupants").html(data.unit.max_occupants);
                $("#unit-parking").html(parking);
                $("#unit-children").html(children);
                $(".unit-details").removeClass("display-none");
                $("#active-tenant-div").removeClass("display-none");
                $(".tenant-div-toggle").removeClass("display-none");
            }
        },
        error: function (xhr) {
        },
    });
};

let getPropertyLeaseToSign = () => {
    let url =
        "/api/tenant/lease_to_sign/" + sessionStorage.getItem("application-guid");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if (data.name === undefined) {
                showToast("Property lease not found. Please contact agent.");
                $("#download-lease-agreement-link").removeAttr("href");
            } else {
                $("#download-lease-agreement-link").attr("href", "/api/lease_document/" +data.name);
            }
        },
        error: function (xhr) {
        },
    });
};

function uploadLeaseDocuments(documentType, file_data) {
    let url = "/api/tenant/upload/lease";
    const uid = sessionStorage.getItem("application-guid");
    const form_data = new FormData();
    form_data.append("file", file_data);
    form_data.append("application_guid", uid);
    form_data.append("document_type", documentType);

    if (file_data === undefined) {
        showToast(" Please upload file");
        return;
    }

    const fileSize = file_data.size;
    const fileMb = fileSize / 1024 ** 2;
    if (fileMb >= 5) {
        showToast(" Please upload files less than 5mb");
        return;
    }

    $.ajax({
        url: url,
        type: "POST",
        data: form_data,
        dataType: "script",
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
        },
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
            showToast(response.result_message);
            if (response.result_code === 0) {
                getMaintenanceCalls();
                $("#confirmMaintenanceModal").modal("toggle");
            }
        },
    });
};

let openApplicationDetails = (applicaitonGuid) => {
    sessionStorage.setItem("application-guid", applicaitonGuid);
    let url = "/api/application/get/" + applicaitonGuid;
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

            $(".application-div-toggle").addClass("display-none");
            $("." + data.application.status.replace(" ", "-")).removeClass("display-none");

            if (data.application.status.localeCompare("accepted") === 0) {
                getPropertyLeaseToSign();
            }

            $("#application_reference").html(
                "AL-APP-" + data.application.id + " (" + data.application.status + ")"
            );

            $("#applicant_name").html(data.application.tenant.name);
            $("#applicant_email").html(data.application.tenant.email);
            $("#applicant_phone").html(data.application.tenant.phone);
            $("#applicant_salary").html(
                 data.application.tenant.salary.toLocaleString()
            );
            $("#applicant_adults").html(data.application.tenant.adults);
            $("#applicant_children").html(data.application.tenant.children);
            $("#applicant_id_number").html(data.application.tenant.id_number);
            $("#applicant_occupation").html(data.application.tenant.occupation);
            $("#parking_bays").html(data.application.parking_bays);


            data.documents.forEach(function (document) {
                html +=
                    '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/api/document/' +
                    document.name +
                    '"><i class="fa-solid fa-file-pdf red-icon me-3"></i><small>' +
                    document.document_type.name +
                    "</small></a></p>\n";
            });

            $("#application-documents").html(html);

            $(".application-card").addClass("display-none");
            $(".application-card-details").removeClass("display-none");

            if (data.application.status.localeCompare("accepted") === 0) {
                //accepted applications cant be converted to lease as the KYC docs are not uploaded
                $(".btn-decline-application").removeClass("display-none");
            }

            if (data.application.status.localeCompare("lease uploaded") === 0) {
                $(".btn-decline-application").removeClass("display-none");
                $(".btn-convert-application").removeClass("display-none");
            }

            if (data.application.status.localeCompare("financials uploaded") === 0) {
                $(".btn-decline-application").removeClass("display-none");
                $(".btn-accept-application").removeClass("display-none");
            }
        },
        error: function (xhr) {
        },
    });
};

let getApplications = () => {
    let url = "/api/applications/tenant/get";
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let html = "";
            if (data.result_code !== undefined) {
                if (data.result_code === 1) {
                    $('.no-applications-message').removeClass("display-none");
                    return;
                }
            }
            data.forEach(function (application) {

                let declinedClass = "orange-text";
                if (application.status.localeCompare("declined") === 0) {
                    declinedClass = "red-text";
                } else if (application.status.localeCompare("tenant") === 0) {
                    declinedClass = "green-text";
                }

                html +=
                    '<div class="application-card w-100">\n' +
                    '<div class="row align-items-center mt-1">\n' +
                    '  <div class="col-5 border-right">\n' +
                    '    <div class="row">\n' +
                    '      <div class="col-3 d-flex align-items-cente application-guid="' +
                    application.uid +
                    '">\n' +
                    '        <i class="fa-solid fa-hand-pointer application-details-button me-5 green-text" style="z-index: 99;" role="button" application-guid="' +
                    application.uid +
                    '"></i>\n' +
                    "      </div>\n" +
                    '      <div class="col-9">\n' +
                    '        <p class="m-0">' +
                    application.tenant.name +
                    "</p>\n" +
                    '        <p class="m-0">' +
                    application.unit.name +
                    "</p>\n" +
                    "      </div>\n" +
                    "    </div>\n" +
                    "  </div>\n" +
                    '  <div class="col-3 border-right align-items-center">\n' +
                    '    <p class="' +
                    declinedClass +
                    '">' +
                    application.status +
                    "</p>\n" +
                    "  </div>\n" +
                    '  <div class="col-4">\n' +
                    '    <div class="row align-items-center">\n' +
                    '     <div class="col-9">\n' +
                    '        <p class="m-0">' +
                    application.tenant.salary.toLocaleString() +
                    "</p>\n" +
                    '        <p class="m-0 font-10">' +
                    application.date.substring(0, application.date.indexOf("T")) +
                    "</p>\n" +
                    "      </div>\n" +
                    "  </div>\n" +
                    "</div>\n" +
                    "</div>\n" +
                    "</div> ";
            });

            $("#applications-div").html(html);

            $(".application-details-button").click(function (event) {
                openApplicationDetails(event.target.getAttribute("application-guid"));
            });

            //reset the buttons on open application window
            $(".btn-decline-application").addClass("display-none");
            $(".btn-convert-application").addClass("display-none");
            $("#decline-application-button").addClass("display-none");

            //close the application details window
            $(".closable-div").addClass("display-none");
            $(".application-card").removeClass("display-none");
            $("#applications-div-container").removeClass("display-none");

        },
        error: function (xhr) {
        },
    });
};


function uploadIncomeDocuments(documentType, file_data) {
    let url = "/api/application/upload/";
    const form_data = new FormData();
    form_data.append("file", file_data);
    form_data.append("application_id", sessionStorage.getItem("application-guid"));
    form_data.append("document_type", documentType);

    if (file_data === undefined) {
        showToast(" Please upload file")
        return;
    }

    const fileSize =file_data.size;
    const fileMb = fileSize / 1024 ** 2;
    if (fileMb >= 5) {
        showToast(" Please upload files less than 5mb");
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
                sessionStorage.setItem("application_reference", jsonObj.application_id);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showToast(errorThrown);
        }
    });
}