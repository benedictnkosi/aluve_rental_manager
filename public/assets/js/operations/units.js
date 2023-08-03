$(document).ready(function () {
    //getAllUnits();
    sessionStorage.setItem("unit-id", "0");

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
            $('#number-of-units-div').removeClass("display-none");
            $('#unit-name-label').text("Unit name prefix:");
        } else {
            $('#number-of-units-div').addClass("display-none");
            $('#unit-name-label').text("Unit Name:");
        }
    });

    $("#btn-create-new-unit").click(function (event) {
        sessionStorage.setItem("unit-id", "0");
        $('#checkBulkCreateUnits').prop("checked", false);
        updateView("new-unit-content-div", "Unit");
        $('.new-unit-fields').show();
        $('#unit-name-label').text("Unit Name:");
    });

    $("#btn-close-new-unit").click(function (event) {
        updateView("units-content-div", "Lease");
    });

    $('.btn-confirm-delete-unit').unbind('click')
    $("#btn-confirm-delete-unit").click(function (event) {
        event.preventDefault();
        deleteUnit(sessionStorage.getItem("unit-id"));
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
    const meter = $("#unit-meter").val().trim();
    const checkBulkCreateUnits = $("#checkBulkCreateUnits").is(':checked');

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
        numberOfUnits: numberOfUnits,
        property_id: sessionStorage.getItem("property-id"),
        meter: meter
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

let deleteUnit = (guid) => {
    let url = "/api/units/update";
    const data = {
        field: "status",
        value: "deleted",
        guid: guid
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
            let unitsDropDownHtml = "";
            if(data.result_code !== undefined){
                if(data.result_code === 1){
                    return;
                }
            }
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
                if (unit.parking === false) {
                    parking = "Not Provided"
                }

                unitsDropDownHtml += '<li><a class="dropdown-item lease-unit-dropdown" lease-unit-id="'+unit.guid+'"\n' +
                    '                                           href="javascript:void(0)">'+unit.unit_name+'</a></li>';

    
                html += '<div class="property-card">\n' +
                '<img src="/assets/images/unit2.jpg" alt="landing-image" border="0">\n' +
                '<div class="property-details">\n' +
                '<p><a class="btn-update-unit"  unit-id="' + unit.guid + '" unit-bedrooms="' + unit.bedrooms + '" unit-bathrooms="' + unit.bathrooms + '"unit-meter="' + unit.meter + '"unit-rent="' + unit.rent + '" parking="' + unit.parking + '" children="' + unit.children + '" min-salary="' + unit.min_gross_salary + '" max-occupants="' + unit.max_occupants + '" listed="' + unit.listed + '" unit-name="' + unit.unit_name + '">' + unit.unit_name + '</a></p>\n';

                if(unit.tenant_name){
                    html += '<p>' + unit.tenant_name + '</p>\n';
                }else{
                    html += '<p>Vacant</p>\n';
                }
                
                html += ' </a><div><i class="fa-solid fa-bed"></i> ' + unit.bedrooms + '\n' +
                '   <i class="fa-solid fa-shower"></i> ' + unit.bathrooms + '\n' +
                ' </div>\n' +
                '</div>\n' +
                '</div> ';


                

            });

            $("#div-units").html(html);

            $("#ul-units").html(unitsDropDownHtml);

            $("#maintenance-ul-units").html(unitsDropDownHtml);

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
                const meter = event.target.getAttribute('unit-meter')

                sessionStorage.setItem("unit-id", unitId)

    
                $('#unit-name').val(unitName);
                $('#unit-rent').val(rent);
                $('#max-occupants').val(maxOccupants);
                $('#min-gross-salary').val(minSalary);
                $('#unit-bedrooms').val(bedrooms);
                $('#unit-bathrooms').val(bathrooms);
                $('#unit-meter').val(meter);

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

                updateView("new-unit-content-div", unitName);
                $('.new-unit-fields').hide();
                $('#unit-name-label').text("Unit Name:");
            });

            $(".btn-delete-unit").click(function (event) {
                event.preventDefault();
                sessionStorage.setItem("unit-id", event.target.getAttribute("unit-id"));
                $('#confirmDeleteUnitModal').modal('toggle');
            });



            $(".lease-unit-dropdown").click(function (event) {
                sessionStorage.setItem("lease-unit-id", event.target.getAttribute("lease-unit-id"));
                $('#unit-dropdown-selected').html(event.target.innerText);
                $('#maintenance-unit-dropdown-selected').html(event.target.innerText);
            });
        },
        error: function (xhr) {

        }
    });
}