$(document).ready(function () {
    getTransactions();
    getStatementDetails();
});


let getTransactions = () => {

    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    const guid = urlParams.get('guid');
    let url = "/public/lease/transactions/" + guid
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let html = "";
            data.forEach(function (transaction) {
                html += '<tr>\n' +
                    '                    <td>'+transaction.description+'</td>\n' +
                    '                    <td>'+transaction.date+'</td>\n' +
                    '                    <td>'+transaction.amount+'</td>\n' +
                    '                    <td>'+transaction.balance+'</td>\n' +
                    '                </tr>';
            });

            $("#tbody-transactions").html(html);
        },
        error: function (xhr) {

        }
    });
}


let getStatementDetails = () => {
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    const guid = urlParams.get('guid');
    let url = "/public/lease/" + guid
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