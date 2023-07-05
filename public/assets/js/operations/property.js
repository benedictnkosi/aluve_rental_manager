$(document).ready(function () {
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
    const address = $("#property-address").val().trim();
    let url = "/api/properties/create";
    const data = {
        name: name,
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

let getAllProperties = () => {
    let url = "/api/properties"
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let propertiesHTML = "";
            data.forEach(function (property) {
                if (property.property.status.localeCompare("active") === 0) {

                    propertiesHTML += '<div class="col">\n' +
                        '               \n' +
                        '                    <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg property-image"\n' +
                        '                         style="background-image: url(\'/assets/images/house.jpg\');">\n' +
                        '                        <div class="d-flex flex-column h-100 p-5 pb-3 text-shadow-1">\n' +
                        '                             <a href="/dashboard/?id='+property.property.idproperties+'">' +
                        '<h4 class="pt-3 mt-3 mb-4 display-6 lh-1 fw-bold">' + property.property.name + '</h4></a>\n' +
                        '                            <ul class="d-flex list-unstyled mt-auto">\n' +
                        '                                <li class="d-flex align-items-center me-3">\n' +
                        '                                    <svg class="bi me-2" width="1em" height="1em">\n' +
                        '                                        <use xlink:href="#geo-fill"/>\n' +
                        '                                    </svg>\n' +
                        '                                    <small>' + property.property.address + '</small>\n' +
                        '                                </li>' +
                        '<li class="d-flex align-items-center me-3">\n' +
                        '                                    <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#newPropertyModal" data-bs-action="update" property-id="' + property.property.idproperties + '" property-name="' + property.property.name + '" property-address="' + property.property.address + '">Update\n' +
                        '        </button>\n' +
                        '                                </li>\n' +
                        '                            </ul>\n' +
                        '                        </div>\n' +
                        '                    </div>\n' +
                        '                \n' +
                        '            </div>';
                }
            });

            $("#div-properties").html(propertiesHTML);
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
                    }
                })
            }
        },
        error: function (xhr) {

        }
    });
}

