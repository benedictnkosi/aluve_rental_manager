$(document).ready(function () {
    getAllLeases();
    sessionStorage.setItem("lease-id", "0")
    sessionStorage.setItem("lease-unit-id", "0")

    $("#form-create-lease").submit(function (event) {
        event.preventDefault();
    });

    $("#form-create-lease").validate({
        // Specify validation rules
        rules: {}, submitHandler: function () {
            createLease();
        }
    });

    $("#form-create-lease").submit(function (event) {
        event.preventDefault();
    });

    $("#form-lease-add-payment").validate({
        // Specify validation rules
        rules: {}, submitHandler: function () {
            addPayment();
        }
    });


    $("#btn-new-lease").click(function (event) {
        event.preventDefault();
        sessionStorage.setItem("lease-id", "0");
        $('#leaseModal').modal('toggle');
        $('#unit-dropdown-selected').html("Select Unit");
        $("#lease-tenant-name").val("");
        $("#lease-tenant-phone").val("");
        $("#lease-tenant-email").val("");
        $("#lease-deposit").val("");
    });

    $("#btn-delete-lease").click(function (event) {
        event.preventDefault();
        deleteLease();
    });

    $(".lease-unit-dropdown").click(function (event) {
        sessionStorage.setItem("lease-unit-id", event.target.getAttribute("lease-unit-id"));
        $('#unit-dropdown-selected').html(event.target.innerText);
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
});

let createLease = () => {
    const tenantName = $("#lease-tenant-name").val().trim();
    const phone = $("#lease-tenant-phone").val().trim();
    const email = $("#lease-tenant-email").val().trim();
    const startDate = $("#lease-start-date").val().trim();
    const endDate = $("#lease-end-date").val().trim();

    const deposit = $("#lease-deposit").val().trim();
    let url = "/api/lease/create";
    const data = {
        unitId: sessionStorage.getItem("lease-unit-id"),
        tenantName: tenantName,
        phone: phone,
        email: email,
        start_date: startDate,
        end_date: endDate,
        deposit: deposit,
        lease_id: sessionStorage.getItem("lease-id")
    };

    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                $('#leaseModal').modal('toggle');
                getAllLeases();
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

    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    const id = urlParams.get('id');
    let url = "/api/leases/get/" + id
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let html = "";
            data.forEach(function (lease) {
                html += '<tr>\n' +
                    '                        <td>\n' +
                    '                            '+lease.tenant_name+'\n' +
                    '                        </td>\n' +
                    '                        <td>'+lease.unit_name+'</td>\n' +
                    '                        <td>'+lease.lease_start+'</td>\n' +
                    '                        <td>'+lease.lease_end+'</td>\n' +
                    '                        <td>'+lease.deposit+'</td>\n' +
                    '                        <td>'+lease.due+'</td>\n' +

                    '                        <td>\n' +
                    '                            <div class="btn-group">\n' +
                    '                                <button class="btn btn-secondary add-payment-button" lease-id="'+lease.lease_id+'" type="button" data-bs-toggle="modal" data-bs-target="#leasePaymentModal">\n' +
                    '                                    Add Payment\n' +
                    '                                </button>\n' +
                    '                                <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">\n' +
                    '                                    <span class="visually-hidden">Toggle Dropdown</span>\n' +
                    '                                </button>\n' +
                    '                                <ul class="dropdown-menu dropdown-menu-dark">\n' +
                    '                                    <li><a class="dropdown-item btn-cancel-lease" lease-id="'+lease.lease_id+'" href="#">Cancel Lease</a></li>\n' +
                    '                                    <li><a class="dropdown-item" target="_blank" href="/statement/?guid='+lease.guid+'">View Statement</a></li>\n' +
                    '                                    <li><a class="dropdown-item update-lease-dpr-button" lease-id="'+lease.lease_id+'" tenant_name="'+lease.tenant_name+'" phone="'+lease.phone_number+'" email="'+lease.email+'" unit_name="'+lease.unit_name+'" unit_id="'+lease.unit_id+'" lease_start="'+lease.lease_start+'" lease_end="'+lease.lease_end+'" deposit="'+lease.deposit+'" href="#">Update Lease</a></li>\n' +
                    '                                </ul>\n' +
                    '                            </div>\n' +
                    '                        </td>\n' +
                    '                    </tr>';
            });

            $("#tbody-leases").html(html);

            $('.update-lease-dpr-button').click(function (event) {
                sessionStorage.setItem("lease-unit-id", event.target.getAttribute("unit_id"));
                sessionStorage.setItem("lease-id", event.target.getAttribute("lease-id"));

                $('#unit-dropdown-selected').html(event.target.getAttribute("unit_name"));
                $("#lease-tenant-name").val(event.target.getAttribute("tenant_name"));
                $("#lease-tenant-phone").val(event.target.getAttribute("phone"));
                $("#lease-tenant-email").val(event.target.getAttribute("email"));
                $("#lease-start-date").val(event.target.getAttribute("lease_start"));
                $("#lease-end-date").val(event.target.getAttribute("lease_end"));
                $("#lease-deposit").val(event.target.getAttribute("deposit"));
                $('#leaseModal').modal('toggle');
            });

            $(".add-payment-button").click(function (event) {
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