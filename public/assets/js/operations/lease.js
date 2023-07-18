$(document).ready(function () {
    getAllLeases();
    sessionStorage.setItem("lease-id", "0")
    sessionStorage.setItem("lease-unit-id", "0")

    $("#form-create-lease").validate({
        // Specify validation rules
        rules: {}, submitHandler: function () {
            createLease();
        }
    });

    $('#onboarding_lease').change(function () {
        uploadSupportingDocuments("Signed Lease", $("#onboarding_lease").prop("files")[0]);
    });

    $('#onboarding_iddoc').change(function () {
        uploadSupportingDocuments("ID Document", $("#onboarding_iddoc").prop("files")[0]);
    });

    $('#onboarding_pop').change(function () {
        uploadSupportingDocuments("Proof OF Payment", $("#onboarding_pop").prop("files")[0]);
    });

    $("#form-create-lease").submit(function (event) {
        event.preventDefault();
    });

    $("#btn-close-new-lease").click(function (event) {
        updateView("leases-content-div", "Lease");
    });


    $("#form-lease-add-payment").validate({
        // Specify validation rules
        rules: {}, submitHandler: function () {
            addPayment();
        }
    });

    $("#form-lease-add-payment").submit(function (event) {
        event.preventDefault();
    });


    $("#form-lease-add-bill").validate({
        // Specify validation rules
        rules: {}, submitHandler: function () {
            billTheTenant();
        }
    });

    $("#form-lease-add-bill").submit(function (event) {
        event.preventDefault();
    });


    $("#btn-new-lease").click(function (event) {
        event.preventDefault();
        sessionStorage.setItem("lease-id", "0");
        $('#unit-dropdown-selected').html("Select Unit");
        $("#lease-tenant-name").val("");
        $("#lease-tenant-phone").val("");
        $("#lease-tenant-email").val("");
        $("#payment-rules").val("");
        $("#application_id_number").val("");
        $("#lease-salary").val("");
        $("#lease-occupation").val("");
        updateView("new-lease-content-div", "Lease");
    });

    $("#btn-delete-lease").click(function (event) {
        event.preventDefault();
        deleteLease();
    });

    $("#btn-confirm-delete-lease").click(function () {
        deleteLease(sessionStorage.getItem("lease-id"));
    });

    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd', // Date format (can be customized)
        autoclose: true, // Close the datepicker on selection
        clearBtn: true, // Show a "Clear" button to clear the selection
        todayHighlight: true // Highlight today's date
    });

    $('.datepicker').datepicker('update', new Date());

    $(".id-doc-type").click(function (event) {
        sessionStorage.setItem("document-type", event.target.getAttribute("document-type"));
        $('#drop-id-doc-type-selected').html(event.target.innerText);
    });

});

function uploadSupportingDocuments(documentType, file_data) {
    let url = "/no_auth/tenant/upload/lease";
    const uid = sessionStorage.getItem("tenant_guid");
    const form_data = new FormData();
    form_data.append("file", file_data);
    form_data.append("tenant_guid", uid);
    form_data.append("document_type", documentType);

    if (file_data === undefined) {
        showToast("Error: Please upload file")
        return;
    }

    const fileSize = file_data.size;
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
            if (jsonObj.alldocs_uploaded === true) {
                $(".tenant-div-toggle").addClass("display-none");
                $(".lease_uploaded").removeClass("display-none");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showToast(errorThrown);
        }
    });
}

let createLease = () => {
    const tenantName = $("#lease-tenant-name").val().trim();
    const phone = $("#lease-tenant-phone").val().trim();
    const email = $("#lease-tenant-email").val().trim();
    const startDate = $("#lease-start-date").val().trim();
    const endDate = $("#lease-end-date").val().trim();
    const paymentRules = $("#payment-rules").val().trim();
    const idNumber = $("#application_id_number").val().trim();
    const salary = $("#lease-salary").val().trim();
    const occupancy = $("#lease-occupation").val().trim();
    const adultCount = $("#adult_count").val().trim();
    const childCount = $("#child_count").val().trim();

    let url = "/api/lease/create";
    const data = {
        unitId: sessionStorage.getItem("lease-unit-id"),
        tenantName: tenantName,
        phone: phone,
        email: email,
        start_date: startDate,
        end_date: endDate,
        lease_id: sessionStorage.getItem("lease-id"),
        payment_rules: paymentRules,
        id_document_type: sessionStorage.getItem("document-type"),
        application_id_number: idNumber,
        salary: salary,
        occupation: occupancy,
        adult_count: adultCount,
        child_count: childCount,
    };

    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                getAllLeases();
                updateView("leases-content-div", "Leases");
            }
        }
    });
}

let deleteLease = () => {
    let url = "/api/lease/update";
    const data = {
        field: "status",
        value: "deleted",
        id: sessionStorage.getItem("lease-id")
    };

    $.ajax({
        url: url,
        type: "put",
        data: data,
        success: function (response) {
            showToast(response.result_message);
            if (response.result_code === 0) {
                $('#confirmModal').modal('toggle');
                sessionStorage.setItem("lease-id", "0");
                getAllLeases();
            }
        }
    });
}

let getAllLeases = () => {
    let id = getURLParameter("id");
    let url = "/api/leases/get/" + id
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let html = "";
            if (data.result_code !== undefined) {
                if (data.result_code === 1) {
                    return;
                }
            }

            data.forEach(function (lease) {

                html += '<div class="col">\n' +
                    '                            <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg property-image"\n' +
                    '                                 style="background-image: url(\'/assets/images/house.jpg\');">\n' +
                    '                                <div class="flex-column h-100 p-4 pb-3 text-white text-shadow-1">\n' +
                    '                                    <h3 class="pt-1 mt-1 mb-4 lh-1 fw-bold">' + lease.tenant_name + '</h3>\n' +
                    '                                    <ul class=" list-unstyled mt-auto">\n' +
                    '                                        <li class=" align-items-center me-3 mt-2">\n' +
                    '                                            <i class="bi-house bootstrap-icon-text"></i>\n' +
                    '                                            <medium>' + lease.unit_name + '</medium>\n' +
                    '                                        </li>\n' +
                    '                                        <li class=" align-items-center me-3 mt-2">\n' +
                    '                                            <i class="bi-telephone bootstrap-icon-text"></i>\n' +
                    '                                            <medium>' + lease.phone_number + '</medium>\n' +
                    '                                        </li>\n' +

                    '                                        <li class=" align-items-center me-3 mt-2">\n' +
                    '                                            <i class="bi-envelope bootstrap-icon-text"></i>\n' +
                    '                                            <medium>' + lease.email + '</medium>\n' +
                    '                                        </li>\n' +
                    '                                        <li class=" align-items-center me-3 mt-2">\n' +
                    '                                            <i class="bi-person-hearts bootstrap-icon-text"></i>\n' +
                    '                                            <medium>Adults: ' + lease.adults + '</medium>\n' +
                    '                                        </li>\n' +
                    '                                        <li class=" align-items-center me-3 mt-2">\n' +
                    '                                            <i class="bi-person-hearts bootstrap-icon-text"></i>\n' +
                    '                                            <medium>Children: ' + lease.children + '</medium>\n' +
                    '                                        </li>\n' +
                    '                                        <li class=" align-items-center me-3 mt-2">\n' +
                    '                                            <i class="bi-person-badge bootstrap-icon-text"></i>\n' +
                    '                                            <medium>' + lease.id_number + '</medium>\n' +
                    '                                        </li>\n' +
                    '                                        <li class=" align-items-center me-3 mt-2">\n' +
                    '                                            <i class="bi-person-badge bootstrap-icon-text"></i>\n' +
                    '                                            <medium>Salary: ' + lease.occupation + '</medium>\n' +
                    '                                        </li>\n' +
                    '                                        <li class=" align-items-center me-3 mt-2">\n' +
                    '                                            <i class="bi-person-badge bootstrap-icon-text"></i>\n' +
                    '                                            <medium>Occupation: ' + lease.salary + '</medium>\n' +
                    '                                        </li>\n' +
                    '                                        <li class=" align-items-center me-3 mt-2">\n' +
                    '                                            <i class="bi-calendar-check-fill bootstrap-icon-text"></i>\n' +
                    '                                            <medium>' + lease.lease_start + ' - ' + lease.lease_end + '</medium>\n' +
                    '                                        </li>\n';

                if (lease.due.localeCompare("R0.00") !== 0) {
                    html += '                                        <li class=" align-items-center me-3 mt-2">\n' +
                        '                                            <i class="bi-currency-dollar bootstrap-icon-text"></i>\n' +
                        '                                            <medium style="color: #dc3545;">Due: ' + lease.due + '</medium>\n' +
                        '                                        </li>\n';
                } else {
                    html += '                                        <li class=" align-items-center me-3 mt-2">\n' +
                        '                                            <i class="bi-currency-dollar bootstrap-icon-text"></i>\n' +
                        '                                            <medium>Due: ' + lease.due + '</medium>\n' +
                        '                                        </li>\n';
                }


                html += '                                       <li class=" align-items-center me-3 mt-2">\n' +
                    '                                            <i class="bi-check2-square bootstrap-icon-text"></i>\n' +
                    '                                            <medium>Status: ' + lease.status + '</medium>\n' +
                    '                                        </li>\n' +
                    '                                        <li class=" align-items-center me-3 mt-2">\n' +
                    '                            <div class="btn-group">\n' +
                    '                                <button class="btn btn-secondary add-payment-button" lease-id="' + lease.lease_id + '" type="button" data-bs-toggle="modal" data-bs-target="#leasePaymentModal">\n' +
                    '                                    Add Payment\n' +
                    '                                </button>\n' +
                    '                                <button type="button" class="btn btn-dark dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">\n' +
                    '                                    <span class="visually-hidden">Toggle Dropdown</span>\n' +
                    '                                </button>\n' +
                    '                                <ul class="dropdown-menu dropdown-menu-dark">\n' +
                    '                                    <li><a class="dropdown-item bill-tenant-button" lease-id="' + lease.lease_id + '" href="#"  data-bs-toggle="modal" data-bs-target="#addExpenseToLeaseModal">Bill The Tenant</a></li>\n' +
                    '                                    <li><a class="dropdown-item btn-cancel-lease" lease-id="' + lease.lease_id + '" href="#">Cancel Lease</a></li>\n';

                if (lease.signed_lease.length !== 0) {
                    html += '                                      <li><a class="dropdown-item" target="_blank" href="/api/document/' + lease.signed_lease + '">Signed Lease</a></li>\n';
                }

                if (lease.id_document.length !== 0) {
                    html += '                                      <li><a class="dropdown-item" target="_blank" href="/api/document/' + lease.id_document + '">ID Document</a></li>\n';
                }

                if (lease.proof_of_payment.length !== 0) {
                    html += '                                      <li><a class="dropdown-item" target="_blank" href="/api/document/' + lease.proof_of_payment + '">Proof Of Deposit</a></li>\n';
                }

                html += '                                    <li><a class="dropdown-item" target="_blank" href="/statement/?guid=' + lease.guid + '">View Statement</a></li>\n' +
                    '                                    <li><a class="dropdown-item" target="_blank" href="/inspection/?guid=' + lease.guid + '">New Inspection</a></li>\n';
                if (lease.inspection_exist === true) {
                    html += '                                    <li><a class="dropdown-item" target="_blank" href="/view/inspection/?guid=' + lease.guid + '">View Latest Inspection</a></li>\n';
                }

                html += '                                    <li><a class="dropdown-item update-lease-dpr-button" lease-id="' + lease.lease_id + '" tenant_guid="' + lease.tenant_guid + '" tenant_name="' + lease.tenant_name + '" phone="' + lease.phone_number + '" email="' + lease.email + '" id_number="' + lease.id_number + '" id_type="' + lease.id_document_type + '" salary="' + lease.salary + '" occupation="' + lease.occupation + '" unit_name="' + lease.unit_name + '" unit_id="' + lease.unit_id + '" lease_start="' + lease.lease_start + '" lease_end="' + lease.lease_end + '" payment_rules="' + lease.payment_rules + '" href="#">Update Lease</a></li>\n' +
                    '                                ' +
                    '</ul>\n' +
                    '                            </div>\n' +
                    '                                        </li>\n' +
                    '                                    </ul>\n' +
                    '                                </div>\n' +
                    '                            </div>\n' +
                    '                    </div>';
            });

            $("#div-leases").html(html);

            $('.update-lease-dpr-button').click(function (event) {
                sessionStorage.setItem("lease-unit-id", event.target.getAttribute("unit_id"));
                sessionStorage.setItem("lease-id", event.target.getAttribute("lease-id"));

                $('#unit-dropdown-selected').html(event.target.getAttribute("unit_name"));
                $("#lease-tenant-name").val(event.target.getAttribute("tenant_name"));
                $("#lease-tenant-phone").val(event.target.getAttribute("phone"));
                $("#lease-tenant-email").val(event.target.getAttribute("email"));
                $("#lease-start-date").val(event.target.getAttribute("lease_start"));
                $("#lease-end-date").val(event.target.getAttribute("lease_end"));
                $("#payment-rules").val(event.target.getAttribute("payment_rules"));
                $("#lease-occupation").val(event.target.getAttribute("occupation"));
                $("#lease-salary").val(event.target.getAttribute("salary"));
                $("#application_id_number").val(event.target.getAttribute("id_number"));
                sessionStorage.setItem("document-type", event.target.getAttribute("id_type"));
                sessionStorage.setItem("tenant_guid", event.target.getAttribute("tenant_guid"));

                $('#drop-id-doc-type-selected').html(event.target.getAttribute("id_type"));
                updateView("new-lease-content-div", "Lease");
            });

            $(".add-payment-button").click(function (event) {
                sessionStorage.setItem("lease-id", event.target.getAttribute("lease-id"));
            });

            $(".bill-tenant-button").click(function (event) {
                sessionStorage.setItem("lease-id", event.target.getAttribute("lease-id"));
            });

            $(".btn-cancel-lease").click(function (event) {
                sessionStorage.setItem("lease-id", event.target.getAttribute("lease-id"));
                $('#confirmModal').modal('toggle');
            });
        },
        error: function (xhr) {

        }
    });
}

let addPayment = () => {
    const amount = $("#payment-amount").val().trim();
    const paymentDate = $("#payment-date").val().trim();

    let url = "/api/transaction/payment";
    const data = {
        lease_id: sessionStorage.getItem("lease-id"),
        amount: amount,
        payment_date: paymentDate
    };

    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                $('#leasePaymentModal').modal('toggle');
                getAllLeases();
            }
        }
    });
}


let billTheTenant = () => {
    const amount = $("#bill-amount").val().trim();
    const summary = $("#bill-summary").val().trim();
    const billDate = $("#bill-date").val().trim();

    let url = "/api/transaction/bill_tenant";
    const data = {
        lease_id: sessionStorage.getItem("lease-id"),
        amount: amount,
        summary: summary,
        bill_date: billDate
    };

    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                $('#addExpenseToLeaseModal').modal('toggle');
                getAllLeases();
            }
        }
    });
}