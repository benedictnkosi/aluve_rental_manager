$(document).ready(function () {
    sessionStorage.removeItem("application_reference");
    $("#applicationForm").validate({
        rules: {
            application_name: {
                maxlength: 100
            },
            application_id_number: {
                maxlength: 20
            }
            ,
            application_phone: {
                maxlength: 10
            }
            ,
            application_email: {
                maxlength: 100
            }
            ,
            application_salary: {
                maxlength: 11
            }
            ,
            application_occupation: {
                maxlength: 100
            }
        }, submitHandler: function () {
            submitApplication();
        }
    });

    $("#regForm").submit(function (event) {
        event.preventDefault();
    });

    $('#bank_statement').change(function () {
        uploadSupportingDocuments("statement", $("#bank_statement").prop("files")[0]);
    });

    $('#application_payslip').change(function () {
        uploadSupportingDocuments("payslip", $("#application_payslip").prop("files")[0]);
    });

    $('#co_bank_statement').change(function () {
        uploadSupportingDocuments("co_statement", $("#co_bank_statement").prop("files")[0]);
    });

    $('#co_application_payslip').change(function () {
        uploadSupportingDocuments("co_payslip", $("#co_application_payslip").prop("files")[0]);
    });


    $('#onboarding-pop').change(function () {
        uploadSupportingDocuments("proof_of_payment", $("#onboarding-pop").prop("files")[0]);
    });

    $(".id-doc-type").click(function (event) {
        sessionStorage.setItem("document-type", event.target.getAttribute("document-type"));
        $('#drop-id-doc-type-selected').html(event.target.innerText);
    });

    $("#finishButton").click(function () {
        if(sessionStorage.getItem("application_reference") !== null){
            $('#applicationreferences').html("Your application reference is <large><b>" + sessionStorage.getItem("application_reference") + "</b></large>");
            $('#text-message').removeClass("display-none");
            $('#text-message').removeClass("display-none");
            $('#supporting-docs-div').addClass("display-none");
        }else{
            showToast("Error: Please upload all supporting documents");
        }
    });

    getUnit();
});

let getURLParameter= (name) =>{
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    return urlParams.get(name);
}

let getUnit = () => {
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    const id = urlParams.get('id');

    let url = "/api/unit/get/" + id.replace("#", "");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {

            if(data.listed === false){
                $("#text-message-no-listed").removeClass("display-none");
                $(".thanks-message").addClass("display-none");
                $("#applicant-details-div").addClass("display-none");
                $("#supporting-docs-div").addClass("display-none");
            }else{
                $("#applicant-details-div").removeClass("display-none");
                $("#supporting-docs-div").addClass("display-none");
                $("#text-message-no-listed").addClass("display-none");
                $(".thanks-message").addClass("display-none");
            }
            const parking = data.parking === true ? "1" : "0";
            const children = data.children_allowed === true ? "1" : "0";

            $("#unit-name").html(data.name);
            $("#unit-address").html(data.property.address);
            $("#unit-rent").html( data.rent.toFixed(2) );
            $("#unit-beds").html(data.bedrooms );
            $("#unit-bathrooms").html(data.bathrooms );
            $("#unit-max-occupants").html(data.max_occupants );
            $("#unit-parking").html(parking);
            $("#unit-children").html(children);
            $("#unit-water").html(data.water);
            $("#unit-electricity").html(data.electricity);
            $(".unit-details-card").removeClass("display-none");

            //deposit
            const depositPercent = parseInt(data.property.deposit_pecent) / 100;
            const deposit = parseInt(data.rent) * depositPercent;
            $("#unit-deposit").html(deposit.toFixed(2));
            $("#unit-application-fee").html(data.property.application_fee.toFixed(2));
        },
        error: function (xhr) {

        }
    });
}

let submitApplication = () => {
    const tenantName = $("#application_name").val().trim();
    const phone = $("#application_phone").val().trim();
    const email = $("#application_email").val().trim();
    const idNumber = $("#application_id_number").val().trim();
    const salary = $("#application_salary").val().trim();
    const adultCount = $("#adult_count").val().trim();
    const childCount = $("#child_count").val().trim();
    const occupancy = $("#application_occupation").val().trim();

    const unitId = getURLParameter("id");

    let url = "/api/application/new";
    const data = {
        unit_id: unitId,
        application_name: tenantName,
        application_phone: phone,
        application_email: email,
        application_id_number: idNumber,
        application_salary: salary,
        application_occupation: occupancy,
        adult_count: adultCount,
        child_count: childCount,
        id_document_type: sessionStorage.getItem("document-type")
    };

    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (response) {
            showToast(response.result_message)
            if (response.result_code === 0) {
                sessionStorage.setItem("application_guid", response.id);
                $("#applicant-details-div").addClass("display-none");
                $("#applicant-details-div").removeClass("row");
                $("#supporting-docs-div").removeClass("display-none");

            }
        }
    });
}

function uploadSupportingDocuments(documentType, file_data) {
    let url = "/api/application/upload/";
    const form_data = new FormData();
    form_data.append("file", file_data);
    form_data.append("application_id", sessionStorage.getItem("application_guid"));
    form_data.append("document_type", documentType);

    if (file_data === undefined) {
        showToast("Error: Please upload file")
        return;
    }

    const fileSize =file_data.size;
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
            if(jsonObj.alldocs_uploaded === true){
                sessionStorage.setItem("application_reference", jsonObj.application_id);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showToast(errorThrown);
        }
    });
}

function showTab(n) {
    var x = document.getElementsByClassName("tab");
    x[n].style.display = "block";
    if (n === (x.length - 1)) {
        document.getElementById("nextBtn").innerHTML = "Submit";
    } else {
        document.getElementById("nextBtn").innerHTML = "Next";
    }
    fixStepIndicator(n)
}

function nextPrev(n) {
    var x = document.getElementsByClassName("tab");
    x[currentTab].style.display = "none";
    currentTab = currentTab + n;
    if (currentTab >= x.length) {
        document.getElementById("nextprevious").style.display = "none";
        document.getElementById("all-steps").style.display = "none";
        document.getElementById("register").style.display = "none";
        document.getElementById("text-message").style.display = "block";
    }
    showTab(currentTab);

}


function fixStepIndicator(n) {
    var i, x = document.getElementsByClassName("step");
    for (i = 0; i < x.length; i++) {
        x[i].className = x[i].className.replace(" active", "");
    }
    x[n].className += " active";
}