$(document).ready(function () {
    let propertyId = getURLParameter("id");
    sessionStorage.setItem("property-id",propertyId);
    generateAllGraphs();
});

function generateAllGraphs(){
    generateIncomeVSExpenses();
    generateExpensesPie();
    generateProfitTable();
    generateExpensesByMonthGraph();
    generateInvoicesDue();
}


function generateIncomeVSExpenses(){
    let url = "/api/expense_income/total?property_id="+sessionStorage.getItem("property-id")+"&number_od_days=30"
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (response) {

            let chartStatus = Chart.getChart("pieChartProfit"); // <canvas> id
            if (chartStatus !== undefined) {
                chartStatus.destroy();
            }

            if(response.income.total > 1){
                $('#pieChartProfit').removeClass("display-none");
            }

            var data = {
                labels: ['Income', 'Expenses'],
                datasets: [{
                    data: [response.income.total, response.expense.total], // Percentage values for Paid and Owing (can be customized)
                    borderWidth: 0 // Border width of each slice (can be customized)
                }]
            };

            // Options for the pie chart
            var options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Income VS Expenses', // Title for the chart (can be customized)
                        font: {
                            size: 16 // Font size of the title (can be customized)
                        }
                    }
                }
            };

            // Create the pie chart
            var ctx = document.getElementById('pieChartProfit').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: data,
                options: options
            });
        },
        error: function (xhr) {

        }
    });

}

function generateProfitTable(){
    let url = "/api/expense_income/total?property_id="+sessionStorage.getItem("property-id")+"&number_od_days=365"
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (response) {
            let profit = response.income.total - response.expense.total;
            $('#td-income').html("R" + response.income.total.toLocaleString());
            $('#td-expenses').html("R" + response.expense.total.toLocaleString());
            $('#td-profit').html("R" + profit.toLocaleString());

            if(response.income.total > 1){
                $('#profit-loss-table').removeClass("display-none");
            }

        },
        error: function (xhr) {

        }
    });
}

function generateExpensesPie(){
    let url = "/api/expense_by_account/total?property_id="+sessionStorage.getItem("property-id")+"&number_od_days=30"
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (expenses) {
            let chartStatus = Chart.getChart("pieChartExpenses"); // <canvas> id
            if (chartStatus !== undefined) {
                chartStatus.destroy();
            }

            if(expenses.result_code === undefined){
                $('#pieChartExpenses').removeClass("display-none");
            }

            const amounts = [];
            const names = [];

            expenses.forEach(function (expense) {
                names.push(expense.expense);
                amounts.push(expense.amount);
            });

            var data = {
                labels: names,
                datasets: [{
                    data: amounts, // Percentage values for Paid and Owing (can be customized)
                    borderWidth: 0 // Border width of each slice (can be customized)
                }]
            };

            // Options for the pie chart
            var options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Expenses Past 30 days', // Title for the chart (can be customized)
                        font: {
                            size: 16 // Font size of the title (can be customized)
                        }
                    }
                }
            };

            // Create the pie chart
            var ctx = document.getElementById('pieChartExpenses').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: data,
                options: options
            });
        },
        error: function (xhr) {

        }
    });

}

function generateExpensesByMonthGraph(){
    let url = "/api/expenses_income/monthly?property_id="+sessionStorage.getItem("property-id")+"&number_od_days=365"
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (expenses) {

            let chartStatus = Chart.getChart("lineChartExpensesByMonth"); // <canvas> id
            if (chartStatus !== undefined) {
                chartStatus.destroy();
            }

            const months = [];
            const expensesArray = [];
            const incomeArray = [];

            expenses.forEach(function (expense) {
                months.push(expense.month);
                expensesArray.push(expense.expense);
                incomeArray.push(expense.income);
                if(!expense.date.includes("0000")){
                    $('#lineChartExpensesByMonth').removeClass("display-none");
                }
            });

            var ctxL = document.getElementById("lineChartExpensesByMonth").getContext('2d');
            var myLineChart = new Chart(ctxL, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: "Expense By Month",
                        data: incomeArray,
                        borderWidth: 2
                    },
                        {
                            label: "Expenses",
                            data: expensesArray,
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true
                }
            });
        },
        error: function (xhr) {

        }
    });

}

let generateInvoicesDue = () => {
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    const id = urlParams.get('id');

    let url = "/api/property/balance/" + id.replace("#", "");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            const totalDue = data.total_due > 0 ? 0 : data.total_due;

            $("#invoice-due-stat").html("R" + totalDue.toLocaleString());
        },
        error: function (xhr) {

        }
    });
}
