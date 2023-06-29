$(document).ready(function () {
    getAllUnites();

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
    let url = "/api/units/create";
    const data = {
        name: name,
        id: sessionStorage.getItem("unit-id")
    };

    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                $('#unitModal').modal('toggle');
                getAllUnites();
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
                getAllUnites();
            }
        }
    });
}

let getAllUnites = () => {

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
                if(unit.tenant_name  === undefined){
                    html += '<div class="col">\n' +
                        '                            <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg property-image"\n' +
                        '                                 style="background-image: url(\'/assets/images/house.jpg\');">\n' +
                        '                                <div class="flex-column h-100 p-5 pb-3 text-white text-shadow-1">\n' +
                        '                                    <h3 class="pt-1 mt-1 mb-4 display-6 lh-1 fw-bold">' + unit.unit_name + '</h3>\n' +
                                                            '  <h5>Vacant Unit</h5>' +
                        '                                            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#unitModal" data-bs-action="update" unit-id="' + unit.unit_id + '" unit-name="' + unit.unit_name + '">Update\n' +

                        '                                </div>\n' +
                        '                            </div>\n' +
                        '                    </div>';
                }else{
                    html += '<div class="col">\n' +
                        '                            <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg property-image"\n' +
                        '                                 style="background-image: url(\'/assets/images/house.jpg\');">\n' +
                        '                                <div class="flex-column h-100 p-5 pb-3 text-white text-shadow-1">\n' +
                        '                                    <h3 class="pt-1 mt-1 mb-4 display-6 lh-1 fw-bold">' + unit.unit_name + '</h3>\n' +
                        '                                    <ul class=" list-unstyled mt-auto">\n' +
                        '                                        <li class="d-flex align-items-center me-3">\n' +
                        '                                            <i class="bi-telephone-forward bootstrap-icon-text"></i>\n' +
                        '                                            <small>' + unit.phone_number + '</small>\n' +
                        '                                        </li>\n' +
                        '                                        <li class=" align-items-center me-3">\n' +
                        '                                            <i class="bi-envelope bootstrap-icon-text"></i>\n' +
                        '                                            <small>' + unit.email + '</small>\n' +
                        '                                        </li>\n' +
                        '                                        <li class="d-flex align-items-center me-3">\n' +
                        '                                            <i class="bi-person-circle bootstrap-icon-text"></i>\n' +
                        '                                            <small>' + unit.tenant_name + '</small>\n' +
                        '                                        </li>\n' +
                        '                                       <li class=" align-items-center me-3">\n' +
                        '                                            <i class="bi-cash bootstrap-icon-text"></i>\n' +
                        '                                            <small>Deposit: R' + unit.deposit +'</small>\n' +
                        '                                        </li>' +
                        '                                        <li class=" align-items-center me-3">\n' +
                        '                                            <i class="bi-calendar bootstrap-icon-text"></i>\n' +
                        '                                            <small>' + unit.lease_start + " - " + unit.lease_end + '</small>\n' +
                        '                                        </li>\n' +
                        '                                        <li class=" align-items-center me-3">\n' +
                        '                                            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#unitModal" data-bs-action="update" unit-id="' + unit.unit_id + '" unit-name="' + unit.unit_name + '">Update\n' +
                        '                                       </button>' +
                        '                                        </li>\n' +
                        '                                    </ul>\n' +
                        '                                </div>\n' +
                        '                            </div>\n' +
                        '                    </div>';
                }


            });

            $("#div-units").html(html);

            const newUnitModal = document.getElementById('unitModal')
            if (newUnitModal) {
                newUnitModal.addEventListener('show.bs.modal', event => {
                    hideMessage();
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

                        sessionStorage.setItem("unit-id", unitId)
                        modalTitle.textContent = `Update Unit`
                        modalUnitName.value = unitName
                    }
                })
            }
        },
        error: function (xhr) {

        }
    });
}