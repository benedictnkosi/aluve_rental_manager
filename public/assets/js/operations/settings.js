$(document).ready(function () {
    getProperty();

    $('.property-value').blur(function (event) {
        if(sessionStorage.getItem("current_page").localeCompare("settings-content-div")===0){
            updatePropertyField(event.target.getAttribute("field-name"), event.target.value)
        }
    })
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


let getProperty = () => {
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    const id = urlParams.get('id');

    let url = "/api/properties/" + id.replace("#", "");
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
        },
        error: function (xhr) {

        }
    });
}

