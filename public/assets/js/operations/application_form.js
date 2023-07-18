$(document).ready(function () {
    showTab(currentTab);

    $("#regForm").validate({
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
        }, submitHandler: function (event) {
            if (currentTab === 0) {
                submitApplication();
            } else {
                if($("#nextBtn").text().localeCompare("Finish")===0){
                    $(".thanks-message").show();
                    $("#text-message-no-listed").hide();
                    $(".tab").hide();
                    $("#nextBtn").hide();
                    document.getElementsByClassName("step")[currentTab].className = "step finish";
                }
            }
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

    let url = "/no_auth/unit/get/" + id.replace("#", "");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {

            if(data.listed === false){
                $(".thanks-message").hide();
                $("#text-message-no-listed").show();
                $(".tab").hide();
                $("#nextBtn").hide();
                document.getElementsByClassName("step")[currentTab].className = "step finish";
            }
            const parking = data.parking === true ? "Provided" : "Not Provided";
            const children = data.children_allowed === true ? "Allowed" : "Not Allowed";

            $("#property_details").html(data.property.name + ", " +  data.property.address + ", " + data.name  );
            $("#unit_max_occupation").html(data.max_occupants );
            $("#unit_children").html(children );
            $("#unit_parking").html(parking);
            $("#unit_min_salary").html("R" + data.min_gross_salary.toFixed(2));
            $("#unit_rent").html("R" + data.rent.toFixed(2));

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

    let url = "/no_auth/application/new";
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
                document.getElementsByClassName("step")[currentTab].className += " finish";
                nextPrev(1);
                sessionStorage.setItem("application_id", response.id);
                $('#nextBtn').html("Finish");
                $('#nextBtn').attr("disabled", true);
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

function uploadSupportingDocuments(documentType, file_data) {
    let url = "/no_auth/application/upload/";
    const form_data = new FormData();
    form_data.append("file", file_data);
    form_data.append("application_id", sessionStorage.getItem("application_id"));
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
                $('#nextBtn').attr("disabled", false);
                $('#applicationreferences').text("Your application reference is " + jsonObj.application_id);
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