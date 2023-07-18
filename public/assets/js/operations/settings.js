$(document).ready(function () {
    sessionStorage.setItem("update_property_settings", "false");
    getProperty();



    $('.property-value').focus(function () {
        sessionStorage.setItem("update_property_settings", "true");
    });

});

let updatePropertyField = (field, value) =>{
    let url = "/api/property/update";
    const data = {
        field: field,
        value: value,
        id: sessionStorage.getItem("property-id")
    };

    $.ajax({
        url : url,
        type: "PUT",
        data : data,
        success: function(response)
        {
            showToast(response.result_message);
        }
    });


}

function uploadPropertyLease(file_data) {
    let url = "/no_auth/property/upload_lease";
    const form_data = new FormData();
    form_data.append("file", file_data);
    form_data.append("property_id", sessionStorage.getItem("property-id"));

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
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showToast(errorThrown);
        }
    });
}


let getProperty = () => {
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    const id = urlParams.get('id');

    let url = "/api/properties/get/" + id.replace("#", "");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            $("#late-fee-day").val(data.rent_late_days);
            $("#late-fee").val(data.late_fee);
            $("#rent-due-day").val(data.rent_due);
            $("#accountNumber").val(data.account_number);
            $("#depositPercent").val(data.deposit_pecent);
            $("#applicationFee").val(data.application_fee);
            $("#property_name").html(data.name);

            if(data.lease_file_name !== undefined){
                $("#view_uploaded_property_lease").removeClass("display-none");
                $("#view_uploaded_property_lease").attr("href", "/api/lease_document/" + data.lease_file_name);
            }

            $('#property_lease').change(function () {
                uploadPropertyLease($("#property_lease" ).prop("files")[0]);
            });

            $('.property-value').change(function (event) {
                if(sessionStorage.getItem("current_page").localeCompare("settings-content-div")===0 && sessionStorage.getItem("update_property_settings").localeCompare("true")===0){
                    updatePropertyField(event.target.getAttribute("field-name"), event.target.value)
                }
            })
        },
        error: function (xhr) {

        }
    });
}

