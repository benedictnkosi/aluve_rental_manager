$(document).ready(function () {
    //getApplications();

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

    $(".btn-accept-application").click(function () {
        $('#acceptApplicationModal').modal('toggle');
    });

    $(".btn-decline-application").click(function () {
        $('#confirmDeclineApplicationModal').modal('toggle');
    });

    $(".btn-convert-application").click(function () {
        $('#convertApplicationModal').modal('toggle');
    });

    $(".application-details-close").click(function () {
        $('.closable-div').addClass('d-none');
        $('#applications-div').removeClass('d-none');
        //hide the buttons on open application window
        $('.btn-decline-application').addClass('d-none');
        $('.btn-convert-application').addClass('d-none');
        $('.btn-accept-application').addClass('d-none');
    });
    
});

let openApplicationDetails = (applicaitonGuid) => {
    sessionStorage.setItem("application-guid", applicaitonGuid);
    let url = "/api/application/get/" + applicaitonGuid
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

            $('#application_reference').html("AL-APP-" + data.application.id);
            $('#applicant_name').html(data.application.tenant.name);
            $('#applicant_email').html(data.application.tenant.email);
            $('#applicant_phone').html(data.application.tenant.phone);
            $('#applicant_salary').html("R" + data.application.tenant.salary.toLocaleString());
            $('#applicant_adults').html(data.application.tenant.adults);
            $('#applicant_children').html(data.application.tenant.children);
            $('#applicant_id_number').html(data.application.tenant.id_number);
            $('#applicant_occupation').html(data.application.tenant.occupation);

            data.documents.forEach(function (document) {
                html += '<p><a class="dropdown-item" target="_blank"  style="color: #000 !important;" href="/api/document/' + document.name + '"><i class="fa-solid fa-file-pdf red-icon me-3"></i><small>'+document.document_type.name+'</small></a></p>\n';
            });

            $("#application-documents").html(html);

            $('#applications-div').addClass('d-none');
            $('.application-card-details').removeClass('d-none');
            
            if (data.application.status.localeCompare("accepted") === 0) {
                //accepted applications cant be converted to lease as the KYC docs are not uploaded
                $('.btn-decline-application').removeClass('d-none');
            }

            if (data.application.status.localeCompare("lease uploaded") === 0) {
                $('.btn-decline-application').removeClass('d-none');
                $('.btn-convert-application').removeClass('d-none');
            }

            if (data.application.status.localeCompare("financials uploaded") === 0) {
                $('.btn-decline-application').removeClass('d-none');
                $('.btn-accept-application').removeClass('d-none');
            }

        },
        error: function (xhr) {

        }
    });


}


let declineApplication = () => {
    let url = "/api/application/decline";
    const data = {
        id: sessionStorage.getItem("application-guid")
    };

    $.ajax({
        url: url,
        type: "put",
        data: data,
        success: function (response) {
            showToast(response.result_message);
            if (response.result_code === 0) {
                $('#confirmDeclineApplicationModal').modal('toggle');
                sessionStorage.setItem("application-guid", "0");
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
            data.forEach(function (application) {
                let declinedClass = "orange-text"; 
                if(application.status.localeCompare("declined") === 0){
                    declinedClass = "red-text";
                }else if(application.status.localeCompare("tenant") === 0){
                    declinedClass = "green-text";
                }

                html += '<div class="application-card w-100">\n' +
                '<div class="row align-items-center mt-1">\n' +
                '  <div class="col-5 border-right">\n' +
                '    <div class="row">\n' +
                '      <div class="col-3 d-flex align-items-cente application-guid="' + application.uid + '">\n' +
                '        <i class="fa-solid fa-hand-pointer application-details-button me-5 green-text" style="z-index: 999;" role="button" application-guid="' + application.uid + '"></i>\n' +
                '      </div>\n' +
                '      <div class="col-9">\n' +
                '        <p class="m-0">' + application.tenant.name + '</p>\n' +
                '        <p class="m-0">' + application.unit.name + '</p>\n' +
                '      </div>\n' +
                '    </div>\n' +
                    
                '  </div>\n' +
                '  <div class="col-3 border-right align-items-center">\n' +
                '    <p class="'+declinedClass+'">'+application.status+'</p>\n' +
                '  </div>\n' +
                '  <div class="col-4">\n' +
                '    <div class="row align-items-center">\n' +
                '     <div class="col-9">\n' +
                '        <p class="m-0">R' + application.tenant.salary.toLocaleString() + '</p>\n' +
                '        <p class="m-0 font-10">' + application.date.substring(0, application.date.indexOf("T")) + '</p>\n' +
                '      </div>\n' +
                '  </div>\n' +
                '</div>\n' +
                '</div>\n' +
                '</div> ';
            });

            $("#applications-div").html(html);

            $(".application-details-button").click(function (event) {
                openApplicationDetails(event.target.getAttribute("application-guid"));
            });

            //reset the buttons on open application window
            $('.btn-decline-application').addClass('d-none');
            $('.btn-convert-application').addClass('d-none');
            $('#decline-application-button').addClass('d-none');

            //close the application details window
            $('.closable-div').addClass('d-none');
            $('#applications-div').removeClass('d-none');

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
        id: sessionStorage.getItem("application-guid"),
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
        id: sessionStorage.getItem("application-guid"),
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