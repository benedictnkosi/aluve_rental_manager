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
                html += '<tr>\n' +
                    '                        <td>' + response.id + '</td>\n' +
                    '                        <td>' + response.summary + '</td>\n' +
                    '                        <td>' + response.unit + '</td>\n' +
                    '                        <td>' + response.date + '</td>\n' +
                    '                        <td>' + response.status + '</td>\n' +
                    '                        <td>\n' +
                    '                            <div class="btn-group">\n' +
                    '                                <button class="btn btn-secondary " type="button">\n' +
                    '                                    Actions\n' +
                    '                                </button>\n' +
                    '                                <button type="button" class="btn btn-dark dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">\n' +
                    '                                    <span class="visually-hidden">Toggle Dropdown</span>\n' +
                    '                                </button>\n' +
                    '                                \n';

                if (response.status.localeCompare("new") === 0) {
                    html += '        <ul class="dropdown-menu dropdown-menu-dark"><li><a class="dropdown-item btn-mark-as-done" maintenance-id="' + response.id + '" href="#" data-bs-toggle="modal" data-bs-target="#confirmMaintenanceModal">Mark As Done</a></li></ul>\n';
                }

                html += '                            </div>\n' +
                    '                        </td>\n' +
                    '                    </tr>';
            });

            $("#tbody-maintenance").html(html);

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