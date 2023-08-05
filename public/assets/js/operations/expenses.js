$(document).ready(function () {
    sessionStorage.removeItem("expense-account-id");
    getExpenseAccounts();
    //getExpenses();

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
    let guid = sessionStorage.getItem("property-guid");
    let url = "/api/expenses/get/" + guid
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
                let colorClass = "";
                if(expense.expense.name.localeCompare("Repairs & Maintenance") === 0){
                    colorClass = "green-text";
                }else if(expense.expense.name.localeCompare("Utilities") === 0){
                    colorClass = "blue-text"
                }else if(expense.expense.name.localeCompare("Insurance") === 0){
                    colorClass = "red-text"
                }else if(expense.expense.name.localeCompare("Mortgage Interest") === 0){
                    colorClass = "orange-text"
                }else if(expense.expense.name.localeCompare("Wages & Salaries") === 0){
                    colorClass = "yellow-text"
                }else if(expense.expense.name.localeCompare("Professional Services") === 0){
                    colorClass = "purple-text"
                }else if(expense.expense.name.localeCompare("Office Supplies") === 0){
                    colorClass = "grey-text"
                }

                const expenseDescription = expense.description === undefined ? "" : expense.description;
                html += '<div class="expenses-card d-flex w-100">\n' +
                    '                <div class="col-1">\n' +
                    '                  <i class="fa-solid fa-house '+colorClass+' expense-icon m-0"></i>\n' +
                    '                </div>\n' +
                    '                <div class="col-7">\n' +
                    '                  <p class="m-0 fw-normal">'+expenseDescription+'</p>\n' +
                    '                  <p class="m-0 '+colorClass+'">'+expense.expense.name+'</p>\n' +
                    '                </div>\n' +
                    '                <div class="col-3">\n' +
                    '                  <p class="m-0 fw-normal">-R'+expense.amount.toLocaleString()+'</p>\n' +
                    '                  <p class="m-0">'+expense.date.substring(0, expense.date.indexOf("T")) +'</p>\n' +
                    '                </div>\n' +
                    '                <div class="col-1" style="text-align: right;">\n' +
                    '                  <i class="fa-solid fa-trash-can m-0 delete-expense-icon red-text" role="button" expense-guid="'+expense.guid+'"></i>\n' +
                    '                </div>\n' +
                    '            </div> ';

            });

            $("#expenses-div").html(html);

            $('.delete-expense-icon').click(function (event) {
                sessionStorage.setItem("expense-guid", event.target.getAttribute("expense-guid"));
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
        property_guid: sessionStorage.getItem("property-id")
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
    let url = "/api/expenses/delete?guid=" + sessionStorage.getItem("expense-guid");
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

