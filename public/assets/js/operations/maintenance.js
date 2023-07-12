$(document).ready(function () {
    getMaintenanceCalls();

    $("#btn-log-call").click(function (event) {
        event.preventDefault();
        logACall();
    });
    $("#btn-confirm-maintenance").click(function (event) {
        event.preventDefault();
        closeACall();
    });
});

let getMaintenanceCalls = () => {
    let id = getURLParameter("id");
    let url = "/api/maintenance/get/" + id
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let html = "";
            data.forEach(function (response) {

                html += '<div class="col-xl-3 col-md-6 mb-4">\n' +
                    '                        <div class="card border-left-success shadow h-100 py-2">\n' +
                    '                            <div class="card-body">\n' +
                    '                                <div class="row no-gutters align-items-center">\n' +
                    '                                    <div class="col mr-2">\n' +
                    '                                        <div class="text-xs text-gray-800 mb-1 text-uppercase mb-1">\n' +
                    '                                            '+response.unit+'</div>\n'+
                '                                        <div class="text-xs text-gray-800 mb-1">\n' +
                '                                            '+response.date+'</div>\n';
                    if (response.status.localeCompare("new") === 0) {
                        html +=   '                                        <div class="text-xs text-gray-800 mb-1">\n' +
                            '                                            '+response.summary+'</div>\n' +
                            '                                     <div class="text-xs text-gray-800 mb-1">\n' +
                        '                                        <a data-bs-toggle="modal" data-bs-target="#confirmMaintenanceModal" role="button" maintenance-id="'+response.id+'" href="javascript:void(0)" class="btn-mark-as-done">Close Call</a>\n' +
                        '                                    </div>\n';
                    }else if(response.status.localeCompare("closed") === 0) {
                        html +=   '                                        <div class="text-xs text-gray-800 mb-1">\n' +
                            '                                            <s>'+response.summary+'</s></div>\n' +
                            '<div class="col-auto">\n' +
                            '                                        <i class="text-success bi bi-check2-circle"></i>\n' +
                            '                                    </div>\n';
                    }

                html +=
                    '                                </div></div>\n' +
                    '                            </div>\n' +
                    '                        </div>\n' +
                    '                    </div>';
            });

            $("#maintenance-div").html(html);

            $(".btn-mark-as-done").click(function (event) {
                sessionStorage.setItem("maintenance-id", event.target.getAttribute("maintenance-id"));
            });

        },
        error: function (xhr) {

        }
    });
}

let logACall = () => {
    const summary = $("#call-summary").val().trim();
    let url = "/api/maintenance/new";
    const data = {
        unit_guid: sessionStorage.getItem("lease-unit-id"),
        property_guid: sessionStorage.getItem("property-id"),
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

let closeACall = () => {
    let url = "/api/maintenance/close";
    const data = {
        unit_id: sessionStorage.getItem("maintenance-id"),
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