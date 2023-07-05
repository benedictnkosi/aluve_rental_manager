$(document).ready(function () {
    getAllUnits();

    $("#form-create-unit").submit(function (event) {
        event.preventDefault();
    });

    $("#form-create-unit").validate({
        // Specify validation rules
        rules: {}, submitHandler: function () {
            createUnit();
        }
    });

    $("#btn-delete-unit").click(function (event) {
        event.preventDefault();
        deleteUnit();
    });
});

let createUnit = () => {
    const name = $("#unit-name").val().trim();
    const listed = $("#checkListed").is(':checked');
    const minGrossSalary = $("#min-gross-salary").val().trim();
    const maxOccupants = $("#max-occupants").val().trim();
    const childrenAllowed = $("#checkChildrenAllowed").is(':checked');
    const parkingProvided = $("#checkParking").is(':checked');
    const rent = $("#unit-rent").val().trim();
    const bedrooms = $("#unit-bedrooms").val().trim();
    const bathrooms = $("#unit-bathrooms").val().trim();

    let url = "/api/units/create";
    const data = {
        name: name,
        listed: listed,
        minGrossSalary: minGrossSalary,
        maxOccupants: maxOccupants,
        childrenAllowed: childrenAllowed,
        parkingProvided: parkingProvided,
        id: sessionStorage.getItem("unit-id"),
        rent:rent,
        bedrooms:bedrooms,
        bathrooms:bathrooms
    };

    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                $('#unitModal').modal('toggle');
                getAllUnits();
            }
        }
    });
}

let deleteUnit = () => {
    let url = "/api/units/update";
    const data = {
        field: "status",
        value: "deleted",
        id: sessionStorage.getItem("unit-id")
    };

    $.ajax({
        url: url,
        type: "put",
        data: data,
        success: function (response) {
            showToast(response.result_message);
            if (response.result_code === 0) {
                $('#unitModal').modal('toggle');
                sessionStorage.setItem("unit-id", "0");
                getAllUnits();
            }
        }
    });
}

let getAllUnits = () => {

    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    const id = urlParams.get('id');
    let url = "/api/units/get/" + id
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let html = "";
            data.forEach(function (unit) {
                let listed = "NOT LISTED";
                let eyeIcon = "bi-eye-slash";
                if(unit.listed === true){
                    listed = "LISTED";
                    eyeIcon = "bi-eye-fill";
                }
                let children = "Allowed";
                if(unit.children === false){
                    children = "Not Allowed"
                }

                let parking = "Provided";
                if(unit.parking === 0){
                    parking = "Not Provided"
                }


                    html += '<div class="col">\n' +
                        '                            <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg property-image"\n' +
                        '                                 style="background-image: url(\'/assets/images/house.jpg\');">\n' +
                        '                                <div class="flex-column h-100 p-5 pb-3 text-white text-shadow-1">\n' +
                        '                                    <h3 class="pt-1 mt-1 mb-4 display-6 lh-1 fw-bold">' + unit.unit_name + '</h3>\n' +
                        '                                    <ul class=" list-unstyled mt-auto">\n' +
                        '                                        <li class=" align-items-center me-3 mt-2">\n' +
                        '                                            <i class="bi-currency-dollar bootstrap-icon-text"></i>\n' +
                        '                                            <medium>Rent: ' + unit.rent + '</medium>\n' +
                        '                                        </li>\n' +
                        '                                        <li class=" align-items-center me-3 mt-2">\n' +
                        '                                            <i class="bi-moon-stars-fill bootstrap-icon-text"></i>\n' +
                        '                                            <medium>Bedrooms: ' + unit.bedrooms + '</medium>\n' +
                        '                                        </li>\n' +
                        '                                        <li class=" align-items-center me-3 mt-2">\n' +
                        '                                            <i class="bi-gender-female bootstrap-icon-text" bootstrap-icon-text"></i>\n' +
                        '                                            <medium>Bathrooms: ' + unit.bathrooms + '</medium>\n' +
                        '                                        </li>\n' +
                        '                                        <li class=" align-items-center me-3 mt-2">\n' +
                        '                                            <i class="bi-currency-dollar bootstrap-icon-text"></i>\n' +
                        '                                            <medium>Minimum Gross Salary: ' + unit.min_gross_salary + '</medium>\n' +
                        '                                        </li>\n' +
                        '                                        <li class=" align-items-center me-3 mt-2">\n' +
                        '                                            <i class="bi-person-plus-fill bootstrap-icon-text"></i>\n' +
                        '                                            <medium>Max Occupants: ' + unit.max_occupants + '</medium>\n' +
                        '                                        </li>\n' +
                        '                                        <li class=" align-items-center me-3 mt-2">\n' +
                        '                                            <i class="bi-car-front bootstrap-icon-text"></i>\n' +
                        '                                            <medium>Parking: ' + parking + '</medium>\n' +
                        '                                        </li>\n' +
                        '                                        <li class=" align-items-center me-3 mt-2">\n' +
                        '                                            <i class="bi-person-slash bootstrap-icon-text"></i>\n' +
                        '                                            <medium>Children: ' + children + '</medium>\n' +
                        '                                        </li>\n' +
                        '                                        <li class=" align-items-center me-3 mt-2">\n' +
                        '                                            <i class="'+eyeIcon+' bootstrap-icon-text"></i>\n' +
                        '                                            <medium>' + listed + '</medium>\n' +
                        '                                        </li>\n' +
                        '                                        <li class=" align-items-center me-3 mt-2">\n' +
                        '                                            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#unitModal" data-bs-action="update" unit-id="' + unit.unit_id + '" unit-bedrooms="' + unit.bedrooms + '" unit-bathrooms="' + unit.bathrooms + '"unit-rent="' + unit.rent + '" parking="' + unit.parking + '" children="' + unit.children + '" min-salary="' + unit.min_gross_salary + '" max-occupants="' + unit.max_occupants + '"listed="' + unit.listed + '" unit-name="' + unit.unit_name + '">Update\n' +
                        '                                       </button><button type="button" class="btn btn-link btn-copy-listing-link" unit-id="' + unit.unit_id + '">Copy Listing Link\n' +
                    '                                       </button>' +
                        '                                        </li>\n' +
                        '                                    </ul>\n' +
                        '                                </div>\n' +
                        '                            </div>\n' +
                        '                    </div>';


            });

            $("#div-units").html(html);

            $(".btn-copy-listing-link").click(function (event) {
                event.preventDefault();
                navigator.clipboard.writeText(location.protocol + '/' + location.host + '/applications/?id=' + event.target.getAttribute("unit-id"));
                showToast("Listing link copied to clipboard")
            });

            const newUnitModal = document.getElementById('unitModal')
            if (newUnitModal) {
                newUnitModal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget
                    // Extract info from data-bs-* attributes
                    const action = button.getAttribute('data-bs-action')
                    const modalUnitName = newUnitModal.querySelector('#unit-name')

                    modalUnitName.value = ""
                    sessionStorage.setItem("unit-id", "0")
                    const modalTitle = newUnitModal.querySelector('.modal-title')
                    modalTitle.textContent = `Create Unit`
                    if (action.localeCompare("update") === 0) {
                        // Update the modal's content.
                        const unitName = button.getAttribute('unit-name')
                        const unitId = button.getAttribute('unit-id')
                        const listed = button.getAttribute('listed')
                        const parking = button.getAttribute('parking')
                        const children = button.getAttribute('children')
                        const minSalary = parseInt(button.getAttribute('min-salary').replace("R",""))
                        const maxOccupants = button.getAttribute('max-occupants')
                        const rent = parseInt(button.getAttribute('unit-rent').replace("R",""))
                        const bedrooms = button.getAttribute('unit-bedrooms')
                        const bathrooms = button.getAttribute('unit-bathrooms')

                        sessionStorage.setItem("unit-id", unitId)
                        modalTitle.textContent = `Update Unit`
                        modalUnitName.value = unitName
                        $('#unit-rent').val(rent);
                        $('#max-occupants').val(maxOccupants);
                        $('#min-gross-salary').val(minSalary);
                        $('#unit-bedrooms').val(bedrooms);
                        $('#unit-bathrooms').val(bathrooms);

                        if(children.localeCompare("true") === 0){
                            $('#checkChildrenAllowed').prop('checked', true);
                        }else{
                            $('#checkChildrenAllowed').prop('checked', false);
                        }

                        if(parking.localeCompare("true") === 0){
                            $('#checkParking').prop('checked', true);
                        }else{
                            $('#checkParking').prop('checked', false);
                        }

                        if(listed.localeCompare("true") === 0){
                            $('#checkListed').prop('checked', true);
                        }else{
                            $('#checkListed').prop('checked', false);
                        }

                    }
                })
            }
        },
        error: function (xhr) {

        }
    });
}