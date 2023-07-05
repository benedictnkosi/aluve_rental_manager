$(document).ready(function () {
    getAllUnits();
    sessionStorage.removeItem("unit-id")

    $("#form-create-unit").submit(function (event) {
        event.preventDefault();
    });

    $("#form-create-unit").validate({
        // Specify validation rules
        rules: {}, submitHandler: function () {
            createUnit();
        }
    });


    $('#checkBulkCreateUnits').change(function () {
        if (this.checked) {
            $('#number-of-units-div').show();
            $('#unit-name-label').text("Unit name prefix:");
        } else {
            $('#number-of-units-div').hide();
            $('#unit-name-label').text("Unit Name:");
        }
    });

    $("#btn-create-new-unit").click(function (event) {
        sessionStorage.removeItem("unit-id")
        $('#checkBulkCreateUnits').prop("checked", false);
        updateView("new-unit-content-div", "Unit");
        $('.new-unit-fields').show();
        $('#unit-name-label').text("Unit Name:");
    });

    $("#btn-close-new-unit").click(function (event) {
        updateView("units-content-div", "Lease");
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
    const numberOfUnits = $("#number-of-units").val().trim();
    const checkBulkCreateUnits = $("#checkParking").is(':checked');

    let url = "/api/units/create";
    const data = {
        name: name,
        listed: listed,
        minGrossSalary: minGrossSalary,
        maxOccupants: maxOccupants,
        childrenAllowed: childrenAllowed,
        parkingProvided: parkingProvided,
        id: sessionStorage.getItem("unit-id"),
        rent: rent,
        bedrooms: bedrooms,
        bathrooms: bathrooms,
        bulkCreate: checkBulkCreateUnits,
        numberOfUnits: numberOfUnits
    };

    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                getAllUnits();
            }
        }
    });
}

let deleteUnit = (unitId) => {
    let url = "/api/units/update";
    const data = {
        field: "status",
        value: "deleted",
        id: unitId
    };

    $.ajax({
        url: url,
        type: "put",
        data: data,
        success: function (response) {
            showToast(response.result_message);
            if (response.result_code === 0) {
                sessionStorage.setItem("unit-id", "0");
                getAllUnits();
            }
            $('#confirmDeleteUnitModal').modal('toggle');
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
                if (unit.listed === true) {
                    listed = "LISTED";
                    eyeIcon = "bi-eye-fill";
                }
                let children = "Allowed";
                if (unit.children === false) {
                    children = "Not Allowed"
                }

                let parking = "Provided";
                if (unit.parking === 0) {
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
                    '                                            <i class="' + eyeIcon + ' bootstrap-icon-text"></i>\n' +
                    '                                            <medium>' + listed + '</medium>\n' +
                    '                                        </li>\n' +
                    '                                        <li class=" align-items-center me-3 mt-2">\n' +
                    '                            <div class="btn-group">\n' +
                    '                                <button class="btn btn-secondary"  unit-id="' + unit.unit_id + '" type="button">\n' +
                    '                                    Actions\n' +
                    '                                </button>\n' +
                    '                                <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">\n' +
                    '                                    <span class="visually-hidden">Toggle Dropdown</span>\n' +
                    '                                </button>\n' +
                    '                                <ul class="dropdown-menu dropdown-menu-dark">\n' +
                    '                                    <li><a class="dropdown-item btn-update-unit" href="javascript:void(0)" unit-id="' + unit.unit_id + '" unit-bedrooms="' + unit.bedrooms + '" unit-bathrooms="' + unit.bathrooms + '"unit-rent="' + unit.rent + '" parking="' + unit.parking + '" children="' + unit.children + '" min-salary="' + unit.min_gross_salary + '" max-occupants="' + unit.max_occupants + '"listed="' + unit.listed + '" unit-name="' + unit.unit_name + '">Update Unit</a></li>\n' +
                    '                                    <li><a class="dropdown-item btn-delete-unit" href="javascript:void(0)" unit-id="' + unit.unit_id + '" >Delete Unit</a></li>\n' +
                    '                                    <li><a class="dropdown-item btn-copy-listing-link" unit-id="' + unit.unit_id + '" href="javascript:void(0)">Copy Application Link</a></li>\n' +
                    '</ul>\n' +
                    '                            </div>\n' +
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

            $('.btn-update-unit').click(function (event) {
                // Update the modal's content.
                const unitName = event.target.getAttribute('unit-name')
                const unitId = event.target.getAttribute('unit-id')
                const listed = event.target.getAttribute('listed')
                const parking = event.target.getAttribute('parking')
                const children = event.target.getAttribute('children')
                const minSalary = parseInt(event.target.getAttribute('min-salary').replace("R", ""))
                const maxOccupants = event.target.getAttribute('max-occupants')
                const rent = parseInt(event.target.getAttribute('unit-rent').replace("R", ""))
                const bedrooms = event.target.getAttribute('unit-bedrooms')
                const bathrooms = event.target.getAttribute('unit-bathrooms')

                sessionStorage.setItem("unit-id", unitId)

                $('#unit-name').val(unitName);
                $('#unit-rent').val(rent);
                $('#max-occupants').val(maxOccupants);
                $('#min-gross-salary').val(minSalary);
                $('#unit-bedrooms').val(bedrooms);
                $('#unit-bathrooms').val(bathrooms);

                if (children.localeCompare("true") === 0) {
                    $('#checkChildrenAllowed').prop('checked', true);
                } else {
                    $('#checkChildrenAllowed').prop('checked', false);
                }

                if (parking.localeCompare("true") === 0) {
                    $('#checkParking').prop('checked', true);
                } else {
                    $('#checkParking').prop('checked', false);
                }

                if (listed.localeCompare("true") === 0) {
                    $('#checkListed').prop('checked', true);
                } else {
                    $('#checkListed').prop('checked', false);
                }

                updateView("new-unit-content-div", "Unit");
                $('.new-unit-fields').hide();
                $('#unit-name-label').text("Unit Name:");
            });

            $(".btn-delete-unit").click(function (event) {
                event.preventDefault();
                sessionStorage.setItem("unit-id", event.target.getAttribute("unit-id"));
                $('#confirmDeleteUnitModal').modal('toggle');
            });

            $("#btn-confirm-delete-unit").click(function (event) {
                event.preventDefault();
                deleteUnit(sessionStorage.getItem("unit-id"));
            });
        },
        error: function (xhr) {

        }
    });
}