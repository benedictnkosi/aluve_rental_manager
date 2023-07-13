$(document).ready(function () {
    sessionStorage.removeItem("expense-account-id");
    getExpenseAccounts();
    getExpenses();

    $("#form-add-expense").submit(function (event) {
        event.preventDefault();
    });

    $("#form-add-expense").validate({
        // Specify validation rules
        rules: {}, submitHandler: function () {
            addExpense();
        }
    });

    $('#btn-confirm-delete-expense').click(function () {
        deleteExpense();
    });

});


let getExpenses = () => {
    let id = getURLParameter("id");
    let url = "/api/expenses/get/" + id
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
            data.forEach(function (expense) {
                const expenseDescription = expense.description === undefined ? "" : expense.description;

                html += '<div class="col-xl-3 col-md-6 mb-4">\n' +
                    '                        <div class="card border-left-success shadow h-100 py-2"  style="background-image: url(\'/assets/images/house.jpg\');">\n' +
                    '                            <div class="card-body">\n' +
                    '                                <div class="row no-gutters align-items-center">\n' +
                    '                                    <div class="col mr-2">\n' +
                    '                                        <div class="text-xs font-weight-bold text-uppercase mb-1">\n' +
                    '                                            '+expense.expense.name+'</div>\n' +
                    '                                        <div class="text-xs text-gray-800 mb-1">\n' +
                    '                                            '+expenseDescription+'</div>\n' +
                    '                                        <div class="h5 mb-0 text-gray-800">R'+expense.amount.toLocaleString()+'</div>\n' +
                    '                                        <div class="text-xs text-gray-800 mb-1">\n' +
                    '                                            '+expense.date.substring(0, expense.date.indexOf("T")) +'</div>\n' +
                    '                                    </div>\n' +
                    '                                    <div class="col-auto">\n' +
                    '                                        <i class="text-danger bi bi-trash-fill" expense-id="'+expense.guid+'"></i>\n' +
                    '                                    </div>\n' +
                    '                                </div>\n' +
                    '                            </div>\n' +
                    '                        </div>\n' +
                    '                    </div>';
            });

            $("#expenses-div").html(html);

            $('.bi-trash-fill').click(function (event) {
                sessionStorage.setItem("expense-id", event.target.getAttribute("expense-id"));
                $('#confirmDeleteExpenseModal').modal('toggle');
            });
        },
        error: function (xhr) {

        }
    });
}

let getExpenseAccounts = () => {
    let url = "/api/expenses/accounts/get"
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            let expenseAccountsDropDownHtml = "";
            data.forEach(function (expenseAccount) {
                expenseAccountsDropDownHtml += '<li><a class="dropdown-item expense-account-dropdown" expense-account-id="'+expenseAccount.id+'"\n' +
                    '                                           href="javascript:void(0)">'+expenseAccount.name+'</a></li>';

            });

            $("#ul-expense_accounts").html(expenseAccountsDropDownHtml);

            $(".expense-account-dropdown").click(function (event) {
                sessionStorage.setItem("expense-account-id", event.target.getAttribute("expense-account-id"));
                $('#drop-expense-name-selected').html(event.target.innerText);
            });
        },
        error: function (xhr) {

        }
    });
}

let addExpense = () => {
    const amount = $("#expense-amount").val().trim();
    const summary = $("#expense-summary").val().trim();
    const date = $("#expense-date").val().trim();
    const expenseAccountId = sessionStorage.getItem("expense-account-id");

    let url = "/api/expenses/new";
    const data = {
        amount: amount,
        date: date,
        expense_id: expenseAccountId,
        description: summary,
        property_id: sessionStorage.getItem("property-id")
    };

    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                getExpenses();
                $('#propertyExpenseModal').modal('toggle');
            }
        }
    });
}

let deleteExpense = () => {
    let url = "/api/expenses/delete?guid=" + sessionStorage.getItem("expense-id");
    $.ajax({
        url: url,
        type: "delete",
        success: function (response) {
            showToast(response.result_message);
            if (response.result_code === 0) {
                getExpenses();
            }
            $('#confirmDeleteExpenseModal').modal('toggle');
        }
    });
}

