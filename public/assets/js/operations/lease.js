$(document).ready(function () {
  //getAllLeases();
  sessionStorage.setItem("lease-id", "0");
  sessionStorage.setItem("lease-unit-id", "0");

  $("#form-create-lease").validate({
    // Specify validation rules
    rules: {},
    submitHandler: function () {
      createLease();
    },
  });

  $("#onboarding_lease").change(function () {
    uploadSupportingDocuments(
      "Signed Lease",
      $("#onboarding_lease").prop("files")[0]
    );
  });

  $("#onboarding_iddoc").change(function () {
    uploadSupportingDocuments(
      "ID Document",
      $("#onboarding_iddoc").prop("files")[0]
    );
  });

  $("#onboarding_pop").change(function () {
    uploadSupportingDocuments(
      "Proof OF Payment",
      $("#onboarding_pop").prop("files")[0]
    );
  });

  $("#form-create-lease").submit(function (event) {
    event.preventDefault();
  });

  $("#btn-close-new-lease").click(function (event) {
    updateView("leases-content-div", "Lease");
  });

  $(".statement-close").click(function (event) {
    $("#div-leases").removeClass("display-none");
    $('#btn-new-lease').removeClass('display-none');
    $(".statement-card-details").addClass("display-none");
    updateView("leases-content-div", "Lease");
  });

  
  $("#form-lease-add-payment").validate({
    // Specify validation rules
    rules: {},
    submitHandler: function () {
      addPayment();
    },
  });

  $("#form-lease-add-payment").submit(function (event) {
    event.preventDefault();
  });

  $("#form-lease-add-bill").validate({
    // Specify validation rules
    rules: {},
    submitHandler: function () {
      billTheTenant();
    },
  });

  $("#form-lease-add-bill").submit(function (event) {
    event.preventDefault();
  });

  $("#btn-new-lease").click(function (event) {
    event.preventDefault();
    sessionStorage.setItem("lease-id", "0");
    sessionStorage.setItem("unit-id", "0");
    sessionStorage.setItem("unit_guid", "0");
    sessionStorage.setItem("lease-guid", "0");
    $("#unit-dropdown-selected").html("Select Unit");
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

  $(".datepicker").datepicker({
    format: "yyyy-mm-dd", // Date format (can be customized)
    autoclose: true, // Close the datepicker on selection
    clearBtn: true, // Show a "Clear" button to clear the selection
    todayHighlight: true, // Highlight today's date
  });

  $(".datepicker").datepicker("update", new Date());

  $(".id-doc-type").click(function (event) {
    sessionStorage.setItem(
      "document-type",
      event.target.getAttribute("document-type")
    );
    $("#drop-id-doc-type-selected").html(event.target.innerText);
  });

  $(".leases-details-close").click(function () {
    $(".closable-div").addClass("display-none");
    $("#leases-content-div").removeClass("display-none");
  });

  $('#btn-confirm-delete-transaction').click(function () {
    deleteTransaction();
});
});

function uploadSupportingDocuments(documentType, file_data) {
  let url = "/api/tenant/upload/lease";
  const uid = sessionStorage.getItem("tenant_guid");
  const form_data = new FormData();
  form_data.append("file", file_data);
  form_data.append("tenant_guid", uid);
  form_data.append("document_type", documentType);

  if (file_data === undefined) {
    showToast("Error: Please upload file");
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
    dataType: "script",
    cache: false,
    contentType: false,
    processData: false,
    success: function (response) {
      const jsonObj = JSON.parse(response);
      showToast(jsonObj.result_message);
      if (jsonObj.alldocs_uploaded === true) {
        // $(".tenant-div-toggle").addClass("display-none");
        // $(".lease uploaded").removeClass("display-none");
      }

      if (documentType.localeCompare("Signed Lease") === 0) {
        $("#signed-lease-pdf-icon").attr(
          "href",
          "/api/document/" + jsonObj.document_name
        );
        $("#signed-lease-pdf-icon").removeClass("display-none");
      }

      if (documentType.localeCompare("ID Document") === 0) {
        $("#id-ducument-pdf-icon").attr(
          "href",
          "/api/document/" + jsonObj.document_guid
        );
        $("#id-ducument-pdf-icon").removeClass("display-none");
      }

      if (documentType.localeCompare("Proof OF Payment") === 0) {
        $("#pop-pdf-icon").attr(
          "href",
          "/api/document/" + jsonObj.document_name
        );
        $("#pop-pdf-icon").removeClass("display-none");
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      showToast(errorThrown);
    },
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
    unitId: sessionStorage.getItem("unit-guid"),
    tenantName: tenantName,
    phone: phone,
    email: email,
    start_date: startDate,
    end_date: endDate,
    lease_id: sessionStorage.getItem("lease-guid"),
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
      showToast(response.result_message);
      if (response.result_code === 0) {
        getAllLeases();
        updateView("leases-content-div", "Leases");
      }
    },
  });
};

let deleteLease = () => {
  let url = "/api/lease/update";
  const data = {
    field: "status",
    value: "deleted",
    id: sessionStorage.getItem("lease-guid"),
  };

  $.ajax({
    url: url,
    type: "put",
    data: data,
    success: function (response) {
      showToast(response.result_message);
      if (response.result_code === 0) {
        $("#confirmModal").modal("toggle");
        sessionStorage.setItem("lease-id", "0");
        getAllLeases();
      }
    },
  });
};

let getAllLeases = () => {
  populateUnitsDropdown("ul-lease-units");
  let id = getURLParameter("id");
  let url = "/api/leasessummary/get/" + id;
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
        const dueClass =
          lease.due.localeCompare("R0.00") !== 0 ? "border-left-red" : "";

        html +=
          '<div class="lease-card">\n' +
          '<img src="/assets/images/default-person.jpg" alt="landing-image" border="0" role="button" lease-guid="' +
          lease.guid +
          '" class="update-lease-dpr-button">\n' +
          '<div class="lease-details">\n' +
          '  <p  lease-guid="' +
          lease.guid +
          '" class="green-text update-lease-dpr-button pointer" role="button">' +
          lease.lease_start +
          " - " +
          lease.lease_end +
          "</p>\n" +
          '  <p  lease-guid="' +
          lease.guid +
          '" class="green-text update-lease-dpr-button" role="button">' +
          lease.tenant_name +
          "</p>\n" +
          '  <p   lease-guid="' +
          lease.guid +
          '" class="green-text update-lease-dpr-button" role="button">' +
          lease.unit_name +
          "</p>\n" +
          '  <div class="lease-price-cards">\n' +
          '    <div class="lease-rent-div">\n' +
          "      <p>Rent</p>\n" +
          "      <p>R" +
          lease.rent +
          ".00</p>\n" +
          "    </div>\n" +
          '    <a class="dropdown-item view-statement-button" lease-guid="' + lease.guid +
          '"  href="javascript:void(0)"><div lease-guid="' + lease.guid +'" class="lease-due-div ' +
          dueClass +
          '">\n' +
          '      <p lease-guid="' + lease.guid +'">Due</p>\n' +
          '      <p lease-guid="' + lease.guid +'">' +
          lease.due +
          "</p>\n" +
          "    </div></a>\n" +
          "  </div>\n" +
          '<div class="col-12 mt-2 w-100">\n' +
          '<button type="submit" class="btn transparent-green-button w-100 add-payment-button" lease-guid="' +
          lease.guid +
          '" type="button" data-bs-toggle="modal" data-bs-target="#leasePaymentModal">\n' +
          "    Add Payment\n" +
          "</button>\n" +
          "</div>\n" +
          '<div class="col-12 mt-2 w-100">\n' +
          '<button type="submit" class="btn transparent-green-button w-100 bill-tenant-button" lease-guid="' +
          lease.guid +
          '" href="#"  data-bs-toggle="modal" data-bs-target="#addExpenseToLeaseModal">\n' +
          "    Bill The Tenant\n" +
          "</button>\n" +
          "</div>\n" +
          "</div>\n" +
          "</div> ";
      });

      $("#div-leases").html(html);

      $(".view-statement-button").click(function (event) {
        populateStatement(event.target.getAttribute("lease-guid"));
        getTransactions(event.target.getAttribute("lease-guid"), "landlord");
        $("#div-leases").addClass("display-none");
        $('#btn-new-lease').addClass('display-none');
      });

      $(".update-lease-dpr-button").click(function (event) {
        populateLeaseDetails(event.target.getAttribute("lease-guid"));
        updateView("new-lease-content-div", "Lease");
        $("#regForm").removeClass("display-none");
        $('#new-inspection-link').removeClass("display-none");
      });

      $(".add-payment-button").click(function (event) {
        sessionStorage.setItem(
          "lease-guid",
          event.target.getAttribute("lease-guid")
        );
      });

      $(".bill-tenant-button").click(function (event) {
        sessionStorage.setItem(
          "lease-guid",
          event.target.getAttribute("lease-guid")
        );
      });

      $(".btn-cancel-lease").click(function (event) {
        sessionStorage.setItem(
          "lease-guid",
          event.target.getAttribute("lease-guid")
        );
        $("#confirmModal").modal("toggle");
      });

      $("#btn-new-lease").removeClass("display-none");

      $("#div-leases").removeClass("display-none");
      $('#btn-new-lease').removeClass('display-none');
    },
    error: function (xhr) {},
  });
};

let populateLeaseDetails = (leaseGuid) => {
  let id = getURLParameter("id");
  let url = "/api/lease/" + leaseGuid;
  $.ajax({
    type: "GET",
    url: url,
    contentType: "application/json; charset=UTF-8",
    success: function (lease) {
      if (lease.result_code !== undefined) {
        if (lease.result_code === 1) {
          return;
        }
      }

      sessionStorage.setItem("lease-guid", lease.lease_guid);
      sessionStorage.setItem("tenant_guid", lease.tenant_guid);
      sessionStorage.setItem("unit_guid", lease.unit_guid);

      $("#ul-lease-units-selected").html(lease.unit_name);
      $("#lease-tenant-name").val(lease.tenant_name);
      $("#lease-tenant-phone").val(lease.phone_number);
      $("#lease-tenant-email").val(lease.email);
      $("#lease-start-date").val(lease.lease_start);
      $("#lease-end-date").val(lease.lease_end);
      $("#payment-rules").val(lease.payment_rules);
      $("#lease-occupation").val(lease.occupation);
      $("#lease-salary").val(lease.salary);
      $("#application_id_number").val(lease.id_number);
      $("#drop-id-doc-type-selected").html(lease.id_document_type);

      if (lease.signed_lease.length !== 0) {
        $("#signed-lease-pdf-icon").attr(
          "href",
          "/api/document/" + lease.signed_lease
        );
        $("#signed-lease-pdf-icon").removeClass("display-none");
      }

      if (lease.id_document.length !== 0) {
        $("#id-ducument-pdf-icon").attr(
          "href",
          "/api/document/" + lease.id_document
        );
        $("#id-ducument-pdf-icon").removeClass("display-none");
      }

      if (lease.proof_of_payment.length !== 0) {
        $("#pop-pdf-icon").attr(
          "href",
          "/api/document/" + lease.proof_of_payment
        );
        $("#pop-pdf-icon").removeClass("display-none");
      }

      if (lease.inspection_exist === true) {
        $("#existing_lease_link").attr(
          "href",
          "/view/inspection/?guid=" + lease.lease_guid
        );

        $("#existing_lease_link").removeClass("display-none");
        $("#existing_lease_link").text("View Existing Inspection");
      }

      $("#new-inspection-link").attr(
        "href",
        "/inspection/?guid=" + lease.lease_guid
      );

      sessionStorage.setItem("document-type", lease.id_document_type);
      sessionStorage.setItem("tenant_guid", lease.tenant_guid);
    },
    error: function (xhr) {},
  });
};

let addPayment = () => {
  const amount = $("#payment-amount").val().trim();
  const paymentDate = $("#payment-date").val().trim();

  let url = "/api/transaction/payment";
  const data = {
    lease_id: sessionStorage.getItem("lease-guid"),
    amount: amount,
    payment_date: paymentDate,
  };

  $.ajax({
    url: url,
    type: "post",
    data: data,
    success: function (response) {
      showToast(response.result_message);
      if (response.result_code === 0) {
        $("#leasePaymentModal").modal("toggle");
        getAllLeases();
      }
    },
  });
};

let billTheTenant = () => {
  const amount = $("#bill-amount").val().trim();
  const summary = $("#bill-summary").val().trim();
  const billDate = $("#bill-date").val().trim();

  let url = "/api/transaction/bill_tenant";
  const data = {
    lease_id: sessionStorage.getItem("lease-guid"),
    amount: amount,
    summary: summary,
    bill_date: billDate,
  };

  $.ajax({
    url: url,
    type: "post",
    data: data,
    success: function (response) {
      showToast(response.result_message);
      if (response.result_code === 0) {
        $("#addExpenseToLeaseModal").modal("toggle");
        getAllLeases();
      }
    },
  });
};

let deleteTransaction = () => {
  let url = "/api/delete/transaction/?id=" + sessionStorage.getItem("transaction-id");
  $.ajax({
      url: url,
      type: "delete",
      success: function (response) {
          showToast(response.result_message);
          if (response.result_code === 0) {1
              getTransactions(sessionStorage.getItem("lease-guid"), "landlord");
          }
          $('#confirmDeleteTransactionModal').modal('toggle');
      }
  });
}
