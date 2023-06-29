$(document).ready(function () {
    getProperty();
});

let updateProperty = () =>{
    let url = "/api/properties/update";
    const data = {
        field: "status",
        value: "deleted",
        id: sessionStorage.getItem("property-id")
    };

    $.ajax({
        url : url,
        type: "put",
        data : data,
        success: function(response)
        {

            showToast(response.result_message);
            if (response.result_code === 0) {
                $('#newPropertyModal').modal('toggle');
                sessionStorage.setItem("property-id", "0");
                getAllProperties();
            }
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
        },
        error: function (xhr) {

        }
    });
}

