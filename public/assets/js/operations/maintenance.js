$(document).ready(function () {
    //getMaintenanceCalls();

    $("#btn-log-call").click(function (event) {
        event.preventDefault();
        logACall();
    });

    $("#btn-confirm-maintenance").click(function (event) {
        event.preventDefault();
        closeACall();
    });

    populateUnitsDropdown("maintenance-units");

    $("#form-log-maintenance").submit(function (event) {
        event.preventDefault();
    });

    $("#form-log-maintenance").validate({
        // Specify validation rules
        rules: {}, submitHandler: function () {
            logACall();
        }
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
            if(data.result_code !== undefined){
                if(data.result_code === 1){
                    return;
                }
            }
            data.forEach(function (response) {
                const callDate =  new Date(response.date);
                const today = new Date();
                // To calculate the time difference of two dates
                const Difference_In_Time = today.getTime() - callDate.getTime() ;
                // To calculate the no. of days between two dates
                const Difference_In_Days = parseInt(Difference_In_Time / (1000 * 3600 * 24));

                let dateClass = "green-text";
                let cardClass = "";
                if(Difference_In_Days > 7){
                    dateClass = "red-text";
                    cardClass =  "border-left-red";
                }

                let numberOfDays = Difference_In_Days + " days ago";
                if(Difference_In_Days === 0){
                    numberOfDays = "Today";
                }else if(Difference_In_Days === 1){
                    numberOfDays = "Yesterday";
                }
                html += '<div class="maintenance-card d-flex w-100 mt-1 '+cardClass+'">\n' +
                    '                <div class="col-1">\n' +
                    '                  <i class="fa-solid fa-wrench green-text expense-icon m-0"></i>\n' +
                    '                </div>\n' +
                    '                <div class="col-7">\n' +
                    '                  <p class="m-0 fw-normal">'+response.summary+'</p>\n' +
                    '                  <p class="m-0 green-text">'+response.unit+'</p>\n' +
                    '                </div>\n' +
                    '                <div class="col-3">\n' +
                    '                  <p class="m-0 fw-normal">'+response.status.toLocaleString()+'</p>\n' +
                    '                  <p class="m-0 '+dateClass+'">'+numberOfDays +'</p>\n' +
                    '                </div>\n';
                    if (response.status.localeCompare("new") === 0) {
                        html += '                <div class="col-1" style="text-align: right;">\n' +
                        '                  <i class="fa-solid fa-xmark m-0 delete-maintenance-icon red-text"  role="button" maintenance-guid="'+response.guid+'"></i>\n' +
                        '                </div>';
                    }else{
                        html += '                <div class="col-1" style="text-align: right;">\n' +
                            '                  <i class="fa-solid fa-check green-text"></i>\n' +
                            '                </div>';
                    }

                html +=  '            </div> ';

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

let logACall = () => {
    const summary = $("#call-summary").val().trim();
    let url = "/api/maintenance/new";
    const data = {
        unit_guid: sessionStorage.getItem("unit-guid"),
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
                $('#maintenanceModal').modal('toggle');
                sessionStorage.setItem("unit-guid", "0");
                $("#maintenance-units-selected").html("Select Unit");
            }
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