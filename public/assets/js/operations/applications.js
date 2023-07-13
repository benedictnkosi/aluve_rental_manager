$(document).ready(function () {
    getApplications();

    $("#submitAcceptApplication").click(function (event) {
        event.preventDefault();
        acceptApplication();
    });

    $("#submitConvertApplication").click(function (event) {
        event.preventDefault();
        convertApplication();
    });

    $("#btn-confirm-decline-application").click(function () {
        declineApplication();
    });
});

let declineApplication = () => {
    let url = "/api/application/decline";
    const data = {
        id: sessionStorage.getItem("application-id")
    };

    $.ajax({
        url: url,
        type: "put",
        data: data,
        success: function (response) {
            showToast(response.result_message);
            if (response.result_code === 0) {
                $('#confirmDeclineApplicationModal').modal('toggle');
                sessionStorage.setItem("application-id", "0");
                getApplications();
            }
        }
    });
}

let getURLParameter = (name) => {
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    return urlParams.get(name);
}

let getApplications = () => {
    let id = getURLParameter("id");
    let url = "/api/applications/get/" + id
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

                html += '<div class="col-xl-3 col-md-6 mb-4">\n' +
                    '                        <div class="card border-left-success shadow h-100 py-2" style="background-image: url(\'/assets/images/house.jpg\');">\n' +
                    '                            <div class="card-body">\n' +
                    '                                <div class="row no-gutters align-items-center">\n' +
                    '                                    <div class="col mr-2">\n' +
                    '                                        <div class="h5 mb-0 font-weight-bold text-gray-800">\n' +
                    '                                            ' + response.application.tenant.name + '</div>\n' +
                    '                                        <div class="text-xs text-gray-800 mb-1"><i class="bi-house bootstrap-icon-text"></i>\n' +
                    '                                            ' + response.application.unit.name + '</div>\n' +
                    '                                        <div class="text-xs font-weight-bold text-uppercase mb-1"><i class="bi-telephone bootstrap-icon-text"></i>\n' +
                    '                                            ' + response.application.tenant.phone + '</div>\n' +
                    '                                        <div class="text-xs font-weight-boldtext-uppercase mb-1"><i class="bi-person-badge bootstrap-icon-text"></i>\n' +
                    '                                            ' + response.application.tenant.id_number + '</div>\n' +
                    '                                        <div class="text-xs font-weight-boldtext-uppercase mb-1">\n' +
                    '                                            <i class="bi-person-hearts bootstrap-icon-text"></i>\n' +
                    '                                            Adults: ' + response.application.tenant.adults + ', Children: ' + response.application.tenant.children + '\n' +
                    '                                        </div>\n' +
                    '                                        <div class="text-xs font-weight-boldtext-uppercase mb-1"><i class="bi-person-badge bootstrap-icon-text"></i>' + response.application.tenant.occupation + '</div>\n' +
                    '                                        <div class="text-xs font-weight-boldtext-uppercase mb-1"><i class="bi-currency-dollar bootstrap-icon-text"></i>R' + response.application.tenant.salary.toLocaleString() + '</div>\n' +
                    '                                        <div class="text-xs font-weight-boldtext-uppercase mb-1"><i class="bi-clipboard-check bootstrap-icon-text"></i>' + response.application.status + '</div>\n' +
                    '                                        <div class="text-xs font-weight-boldtext-uppercase mb-1"><i class="bi-calendar-check-fill bootstrap-icon-text"></i>R' + response.application.date.substring(0, response.application.date.indexOf("T")) + '</div>\n' +
                    '                            <div class="btn-group">\n' +
                    '                                <button class="btn btn-dark " type="button">\n' +
                    '                                    Actions\n' +
                    '                                </button>\n' +
                    '                                <button type="button" class="btn btn-dark dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">\n' +
                    '                                    <span class="visually-hidden">Toggle Dropdown</span>\n' +
                    '                                </button>\n' +
                    '                                <ul class="dropdown-menu dropdown-menu-dark">\n';
                if (response.application.status.localeCompare("financials_uploaded") === 0) {
                    html += '        <li><a class="dropdown-item btn-decline-application" application-id="' + response.application.id + '" href="#">Decline Application</a></li>\n';
                    html += '        <li><a class="dropdown-item btn-accept-application" application-id="' + response.application.id + '" href="#">Accept Application</a></li>\n';
                }

                if (response.application.status.localeCompare("declined") === 0) {}

                if (response.application.status.localeCompare("accepted") === 0) {
                    html += '        <li><a class="dropdown-item btn-decline-application" application-id="' + response.application.id + '" href="#">Decline Application</a></li>\n';
                }

                if (response.application.status.localeCompare("lease_uploaded") === 0) {
                    html += '        <li><a class="dropdown-item btn-decline-application" application-id="' + response.application.id + '" href="#">Decline Application</a></li>\n';
                    html += '        <li><a class="dropdown-item btn-convert-application" application-id="' + response.application.id + '" href="#">Convert To Lease</a></li>\n';
                }

                if (response.applicant_bank_statement.length !== 0) {
                    html += '                                      <li><a class="dropdown-item" target="_blank" href="/api/document/' + response.applicant_bank_statement + '">Bank Statement</a></li>\n';
                }

                if (response.applicant_payslip.length !== 0) {
                    html += '                                      <li><a class="dropdown-item" target="_blank" href="/api/document/'+response.applicant_payslip+'">Payslip</a></li>\n';
                }

                if (response.co_applicant_bank_statement.length !== 0) {
                    html += '                                      <li><a class="dropdown-item" target="_blank" href="/api/document/'+response.co_applicant_bank_statement+'">Co-Bank Statement</a></li>\n';
                }

                if (response.co_applicant_payslip.length !== 0) {
                    html += '                                      <li><a class="dropdown-item" target="_blank" href="/api/document/'+response.co_applicant_payslip+'">Co-Payslip</a></li>\n';
                }

                if (response.signed_lease.length !== 0) {
                    html += '                                      <li><a class="dropdown-item" target="_blank" href="/api/document/'+response.signed_lease+'">Lease</a></li>\n';
                }

                if (response.id_document.length !== 0) {
                    html += '                                      <li><a class="dropdown-item" target="_blank" href="/api/document/'+response.id_document+'">ID Document</a></li>\n';
                }

                if (response.proof_of_payment.length !== 0) {
                    html += '                                      <li><a class="dropdown-item" target="_blank" href="/api/document/'+response.proof_of_payment+'">Proof Of Deposit</a></li>\n';
                }

                html += '                                    </div></div>\n' +
                    '                                </div>\n' +
                    '                            </div>\n' +
                    '                        </div>\n' +
                    '                    </div>';


            });

            $("#applications-div").html(html);

            $(".btn-accept-application").click(function (event) {
                sessionStorage.setItem("application-id", event.target.getAttribute("application-id"));
                $('#acceptApplicationModal').modal('toggle');
            });

            $(".btn-decline-application").click(function (event) {
                sessionStorage.setItem("application-id", event.target.getAttribute("application-id"));
                $('#confirmDeclineApplicationModal').modal('toggle');
            });

            $(".btn-convert-application").click(function (event) {
                sessionStorage.setItem("application-id", event.target.getAttribute("application-id"));
                $('#convertApplicationModal').modal('toggle');
            });
        },
        error: function (xhr) {

        }
    });
}

let acceptApplication = () => {
    const startDate = $("#accept-lease-start-date").val().trim();
    const endDate = $("#accept-lease-end-date").val().trim();

    let url = "/api/application/accept";
    const data = {
        id: sessionStorage.getItem("application-id"),
        start_date: startDate,
        end_date: endDate
    };

    $.ajax({
        url: url,
        type: "put",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                $('#acceptApplicationModal').modal('toggle');
                getApplications();
            }
        }
    });
}

let convertApplication = () => {
    const startDate = $("#accept-lease-start-date").val().trim();
    const endDate = $("#accept-lease-end-date").val().trim();

    let url = "/api/application/convert_to_lease";
    const data = {
        id: sessionStorage.getItem("application-id"),
        start_date: startDate,
        end_date: endDate
    };

    $.ajax({
        url: url,
        type: "put",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                $('#convertApplicationModal').modal('toggle');
                getAllLeases();
                getApplications();
            }
        }
    });
}