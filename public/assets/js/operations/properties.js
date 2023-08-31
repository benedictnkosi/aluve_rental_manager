$(document).ready(function () {
    sessionStorage.setItem("property-id","0");
    sessionStorage.setItem("current_page", "dashboard-content-div")
    sessionStorage.setItem("current_page_header", "Dashboard")
    
    getAllProperties();

    $("#form-create-property").submit(function (event) {
        event.preventDefault();
    });

    $("#form-create-property").validate({
        // Specify validation rules
        rules: {

        }, submitHandler: function () {
            createProperty();
        }
    });

    $("#btn-delete-property").click(function (event) {
        event.preventDefault();
        deleteProperty();
    });

 

});

let createProperty = () =>{
    const name = $("#property-name").val().trim();
    const parking_bays = $("#property-parking").val().trim();
    const units = $("#property-units").val().trim();
    const address = $("#property-address").val().trim();
    let url = "/api/properties/create";
    const data = {
        name: name,
        parking_bays: parking_bays,
        units: units,
        address: address,
        id: sessionStorage.getItem("property-id")
    };

    $.ajax({
        url : url,
        type: "POST",
        data : data,
        success: function(response)
        {
            showToast(response.result_message);
            if (response.result_code === 0) {
                $('#newPropertyModal').modal('toggle');
                getAllProperties();
            }
        }
    });
}

let deleteProperty = () =>{
    let url = "/api/property/update";
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

let getAllProperties = () => {
    let url = "/api/properties"
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let propertiesHTML = "";
            if(data.result_code !== undefined){
                if(data.result_code === 1){
                    $('.no-results-image').removeClass("display-none");
                    return;
                }
            }
            data.forEach(function (property) {
                $('.no-results-image').addClass("display-none");
                if (property.property.status.localeCompare("active") === 0) {

                    propertiesHTML += '<a href="/dashboard/?id='+property.property.guid+'"> <div class="property-card">\n' +
                        '        <img src="/assets/images/property2.jpg" alt="landing-image" border="0">\n' +
                        '        <div class="property-details">\n' +
                        '            <p>' + property.property.name + '</p>\n' +
                        '            <p>' + property.property.address + '</p></a>\n' +
                        '            <div><i class="fa-solid fa-house"></i> ' + property.property.units + '\n' +
                        '                <i class="fa-solid fa-car"></i> ' + property.property.parking_bays + '\n' +
                        '                <button type="button" class="btn btn-info transparent-white-button w-100 mt-2 d-block" data-bs-toggle="modal" data-bs-target="#newPropertyModal" data-bs-action="update" property-id="' + property.property.guid + '" property-name="' + property.property.name + '" property-address="' + property.property.address + '">Update\n' +
                        '        </button>\n' +
                        '            </div>\n' +
                        '        </div>\n' +
                        '    </div>';
                }
            });

            $(".properties-container").html(propertiesHTML);
            const newPropertyModal = document.getElementById('newPropertyModal')
            if (newPropertyModal) {
                newPropertyModal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget
                    // Extract info from data-bs-* attributes
                    const action = button.getAttribute('data-bs-action')
                    const modalPropertyName = newPropertyModal.querySelector('#property-name')
                    const modalPropertyAddress = newPropertyModal.querySelector('#property-address')
                    modalPropertyName.value = ""
                    modalPropertyAddress.value = ""
                    sessionStorage.setItem("property-id", "0")
                    if(action.localeCompare("update") === 0){
                        // Update the modal's content.
                        const modalTitle = newPropertyModal.querySelector('.modal-title')
                        const propertyName = button.getAttribute('property-name')
                        const propertyAddress = button.getAttribute('property-address')
                        const propertyId = button.getAttribute('property-id')

                        sessionStorage.setItem("property-id", propertyId)
                        modalTitle.textContent = `Update Property`
                        modalPropertyName.value = propertyName
                        modalPropertyAddress.value = propertyAddress

                        $("#btn-delete-property").removeClass("display-none");
                    }else{
                        $("#btn-delete-property").addClass("display-none");
                    }
                })
            }
        },
        error: function (xhr) {

        }
    });
}