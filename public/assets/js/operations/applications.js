$(document).ready(function () {
    getApplications();

    $("#form-accept-application").validate({
        // Specify validation rules
        rules: {}, submitHandler: function () {
            acceptApplication();
        }
    });

    $("#form-accept-application").submit(function (event) {
        event.preventDefault();
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

let getApplications = () => {
    let id = getURLParameter("id");
    let url = "/api/applications/get/" + id
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let html = "";
            data.forEach(function (response) {
                html += '<tr>\n' +
                    '                        <td>'+response.application.id+'</td>\n' +
                    '                        <td>\n' +
                    '                            '+response.application.unit.name+'\n' +
                    '                        </td>\n' +
                    '                        <td>'+response.application.tenant.name+'</td>\n' +
                    '                        <td>'+response.application.tenant.phone+'</td>\n' +
                    '                        <td>'+response.application.tenant.email+'</td>\n' +
                    '                        <td>'+response.application.tenant.id_number+'</td>\n' +
                    '                        <td>'+response.application.tenant.adults+'</td>\n' +
                    '                        <td>'+response.application.tenant.children+'</td>\n' +

                    '                        <td>'+response.application.tenant.salary+'</td>\n' +
                    '                        <td>'+response.application.tenant.occupation+'</td>\n' +
                    '                        <td>'+response.application.status+'</td>\n' +
                    '                        <td>'+response.application.date+'</td>\n' +
                    '                        <td>\n' +
                    '                            <div class="btn-group">\n' +
                    '                                <button class="btn btn-secondary " type="button">\n' +
                    '                                    Actions\n' +
                    '                                </button>\n' +
                    '                                <button type="button" class="btn btn-dark dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">\n' +
                    '                                    <span class="visually-hidden">Toggle Dropdown</span>\n' +
                    '                                </button>\n'+
                    '                                <ul class="dropdown-menu dropdown-menu-dark">\n';

                    if(response.application.status.localeCompare("accepted") !== 0){
                        html += '        <li><a class="dropdown-item btn-decline-application" application-id="'+response.application.id+'" href="#">Decline Application</a></li>\n' ;
                    }

                if(response.application.status.localeCompare("docs_uploaded") === 0){
                    html +=  '                                   <li><a class="dropdown-item btn-accept-application" application-id="'+response.application.id+'" href="#">Accept Application</a></li>\n' +
                            '                                    <li><a class="dropdown-item"  target="_blank" href="/api/document/'+response.applicant_bank_statement+'">Bank Statement</a></li>\n' +
                            '                                    <li><a class="dropdown-item" target="_blank"  href="/api/document/'+response.applicant_payslip+'">Payslip</a></li>\n';

                        if (response.co_applicant_bank_statement.length !== 0 && typeof response.co_applicant_payslip.length !== 0) {
                            html += '                                      <li><a class="dropdown-item" target="_blank" href="/api/document/'+response.co_applicant_bank_statement+'">Co-Bank Statement</a></li>\n' +
                            '                                    <li><a class="dropdown-item"  target="_blank" href="/api/document/'+response.co_applicant_payslip+'">Co-Payslip</a></li>\n';
                        }

                        html += '</ul>\n';
                    }

                html += '                            </div>\n' +
                    '                        </td>\n' +
                    '                    </tr>';
            });

            $("#tbody-applications").html(html);

            $(".btn-accept-application").click(function (event) {
                sessionStorage.setItem("application-id", event.target.getAttribute("application-id"));
                $('#acceptApplicationModal').modal('toggle');
            });

            $(".btn-decline-application").click(function (event) {
                sessionStorage.setItem("application-id", event.target.getAttribute("application-id"));
                $('#confirmDeclineApplicationModal').modal('toggle');
            });
        },
        error: function (xhr) {

        }
    });
}


let acceptApplication = () => {
    const amount = $("#deposit-amount-payed").val().trim();
    const startDate = $("#accept-lease-start-date").val().trim();
    const endDate = $("#accept-lease-end-date").val().trim();

    let url = "/api/application/accept";
    const data = {
        id: sessionStorage.getItem("application-id"),
        deposit: amount,
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
                getAllLeases();
                getApplications();
            }
        }
    });
}