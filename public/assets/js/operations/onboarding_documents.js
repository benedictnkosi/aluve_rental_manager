$(document).ready(function () {
     $('#onboarding_lease').change(function () {
        uploadSupportingDocuments("lease", $("#onboarding_lease").prop("files")[0]);
    });

    $('#onboarding_iddoc').change(function () {
        uploadSupportingDocuments("id", $("#onboarding_iddoc").prop("files")[0]);
    });

    $('#onboarding_pop').change(function () {
        uploadSupportingDocuments("pop", $("#onboarding_pop").prop("files")[0]);
    });

    getLease();
});

let getLease = () => {
    const uid = getURLParameter("guid");

    let url = "/public/lease/" + uid.replace("#", "");
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            $("#property_details").html(data.property + ", " + data.unit_name  );
            $("#lease_date").html(data.lease_start + " - "  + data.lease_end);
            $("#tenant_name").html(data.tenant_name );
            if(data.alldocs_uploaded === true){
                $('#text-message').show();
                $('#regForm').hide();
            }else{
                $('#regForm').show();
            }

        },
        error: function (xhr) {

        }
    });
}

function uploadSupportingDocuments(documentType, file_data) {
    let url = "/public/lease/upload/";
    const uid = getURLParameter("guid");
    const form_data = new FormData();
    form_data.append("file", file_data);
    form_data.append("guid", uid.replace("#", ""));
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
                $('#text-message').show();
                $('#regForm').hide();
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showToast(errorThrown);
        }
    });
}