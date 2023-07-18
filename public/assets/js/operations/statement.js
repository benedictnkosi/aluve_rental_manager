$(document).ready(function () {
    getTransactions();
    getStatementDetails();


    $('#btn-confirm-delete-transaction').click(function () {
        deleteTransaction();
    });
});


let getTransactions = () => {

    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    const guid = urlParams.get('guid');
    let url = "/no_auth/lease/transactions/" + guid
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let html = "";
            if(data.result_code !== undefined){
                if(data.result_code === 1){
                    return;
                }
            }
            data.forEach(function (transaction) {
                const delete_button = transaction.logged_in === true ? '<td><i class="bi bi-trash-fill" transaction-id="'+transaction.id+'"></i></td>' : "";
                html += '<tr>\n' +
                    '                    <td>'+transaction.description+'</td>\n' +
                    '                    <td>'+transaction.date+'</td>\n' +
                    '                    <td>'+transaction.amount+'</td>\n' +
                    '                    <td>'+transaction.balance+'</td>'+ delete_button +
                    '                </tr>';
            });

            $("#tbody-transactions").html(html);
            $('.bi-trash-fill').click(function (event) {
                sessionStorage.setItem("transaction-id", event.target.getAttribute("transaction-id"));
                $('#confirmModal').modal('toggle');
            });
        },
        error: function (xhr) {

        }
    });
}

let deleteTransaction = () => {
    let url = "/api/delete/transaction/?id=" + sessionStorage.getItem("transaction-id");
    $.ajax({
        url: url,
        type: "delete",
        success: function (response) {
            showToast(response.result_message);
            if (response.result_code === 0) {
                getTransactions();
            }
            $('#confirmModal').modal('toggle');
        }
    });
}


let getStatementDetails = () => {
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    const guid = urlParams.get('guid');
    let url = "/no_auth/lease/" + guid
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            $("#property-name").html(data.property);
            $("#unit-name").html(data.unit_name);
            $("#tenant-name").html(data.tenant_name);
            $("#tenant-phone").html(data.phone_number);
            $("#tenant-email").html(data.email);
            $("#statement-date").html("Statement Date: " + data.statement_date);
            $("#lease-start-date").html("Lease Start Date: " + data.lease_start);
            $("#lease-end-date").html("Lease End Date: " + data.lease_end);
            $("#amount-due").html("Amount Due: R" + data.due);
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

let showToast = (message) =>{
    const liveToast = document.getElementById('liveToast')
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(liveToast)
    if(message.toLowerCase().includes("success")){
        $('#toast-message').html('<div class="alert alert-success" role="alert">'+message+'</div>');
    }else if(message.toLowerCase().includes("fail") || message.toLowerCase().includes("error")){
        $('#toast-message').html('<div class="alert alert-danger" role="alert">'+message+'</div>');
    }else{
        $('#toast-message').html('<div class="alert alert-dark" role="alert">'+message+'</div>');
    }
    toastBootstrap.show();
}