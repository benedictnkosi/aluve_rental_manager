$(document).ready(function () {
  //getAllUnits();
  sessionStorage.setItem("unit-id", "0");

  $("#form-create-unit").submit(function (event) {
    event.preventDefault();
  });

  $("#form-create-unit").validate({
    // Specify validation rules
    rules: {},
    submitHandler: function () {
      createUnit();
    },
  });

  $("#checkBulkCreateUnits").change(function () {
    if (this.checked) {
      $("#number-of-units-div").removeClass("display-none");
      $("#unit-name-label").text("Unit name prefix:");
    } else {
      $("#number-of-units-div").addClass("display-none");
      $("#unit-name-label").text("Unit Name:");
    }
  });

  $("#btn-create-new-unit").click(function (event) {
    sessionStorage.setItem("unit-id", "0");
    $("#checkBulkCreateUnits").prop("checked", false);
    updateView("new-unit-content-div", "Unit");
    $(".new-unit-fields").removeClass("display-none");

    $("#unit-name-header").html("");
    $("#unit-name").val("");
    $("#unit-rent").val("");
    $("#max-occupants").val("");
    $("#min-gross-salary").val("");
    $("#unit-bedrooms").val("");
    $("#unit-bathrooms").val("");
    $("#unit-meter").val("");
    $("#checkChildrenAllowed").prop("checked", false);
    $("#checkParking").prop("checked", false);
    $("#checkListed").prop("checked", false);
  });

  $("#btn-close-new-unit").click(function (event) {
    updateView("units-content-div", "Units");
  });

  $(".btn-confirm-delete-unit").unbind("click");
  $("#btn-confirm-delete-unit").click(function (event) {
    event.preventDefault();
    deleteUnit(sessionStorage.getItem("unit-id"));
  });

  $(".unit-details-close").click(function () {
    $("#new-unit-content-div").addClass("display-none");
    $("#units-content-div").removeClass("display-none");
  });

  $(".dropdown-water-charge").click(function (event) {
    $("#ul-unit-water-selected").html(event.target.innerText);
  });

  $(".dropdown-electricity-charge").click(function (event) {
    $("#ul-unit-electricity-selected").html(event.target.innerText);
  });
});

let createUnit = () => {
  const name = $("#unit-name").val().trim();
  const listed = $("#checkListed").is(":checked");
  const minGrossSalary = $("#min-gross-salary").val().trim();
  const maxOccupants = $("#max-occupants").val().trim();
  const childrenAllowed = $("#checkChildrenAllowed").is(":checked");
  const parkingProvided = $("#checkParking").is(":checked");
  const rent = $("#unit-rent").val().trim();
  const bedrooms = $("#unit-bedrooms").val().trim();
  const bathrooms = $("#unit-bathrooms").val().trim();
  const numberOfUnits = $("#number-of-units").val().trim();
  const meter = $("#unit-meter").val().trim();
  const checkBulkCreateUnits = $("#checkBulkCreateUnits").is(":checked");
  const waterCharge = $("#ul-unit-water-selected").html();
  const electricityCharge = $("#ul-unit-electricity-selected").html();

  let url = "/api/units/create";
  const data = {
    name: name,
    listed: listed,
    minGrossSalary: minGrossSalary,
    maxOccupants: maxOccupants,
    childrenAllowed: childrenAllowed,
    parkingProvided: parkingProvided,
    id: sessionStorage.getItem("unit-id"),
    rent: rent,
    bedrooms: bedrooms,
    bathrooms: bathrooms,
    bulkCreate: checkBulkCreateUnits,
    numberOfUnits: numberOfUnits,
    property_id: sessionStorage.getItem("property-id"),
    meter: meter,
    water: waterCharge,
    electricity: electricityCharge,
  };

  $.ajax({
    url: url,
    type: "post",
    data: data,
    success: function (response) {
      showToast(response.result_message);
      if (response.result_code === 0) {
        getAllUnits();
      }
    },
  });
};

let deleteUnit = (guid) => {
  let url = "/api/units/update";
  const data = {
    field: "status",
    value: "deleted",
    guid: guid,
  };

  $.ajax({
    url: url,
    type: "put",
    data: data,
    success: function (response) {
      showToast(response.result_message);
      if (response.result_code === 0) {
        sessionStorage.setItem("unit-id", "0");
        getAllUnits();
      }
      $("#confirmDeleteUnitModal").modal("toggle");
    },
  });
};

let getAllUnits = () => {
  const queryString = window.location.search;
  console.log(queryString);
  const urlParams = new URLSearchParams(queryString);
  const id = urlParams.get("id");
  let url = "/api/units/get/" + id;
  $.ajax({
    type: "GET",
    url: url,
    contentType: "application/json; charset=UTF-8",
    success: function (data) {
      let html = "";
      let unitsDropDownHtml = "";
      if (data.result_code !== undefined) {
        if (data.result_code === 1) {
          return;
        }
      }
      data.forEach(function (unit) {
        let listed = "NOT LISTED";
        let eyeIcon = "bi-eye-slash";
        if (unit.listed === true) {
          listed = "LISTED";
          eyeIcon = "bi-eye-fill";
        }
        let children = "Allowed";
        if (unit.children === false) {
          children = "Not Allowed";
        }

        let parking = "Provided";
        if (unit.parking === false) {
          parking = "Not Provided";
        }

        unitsDropDownHtml +=
          '<li><a class="dropdown-item lease-unit-dropdown" lease-unit-id="' +
          unit.guid +
          '"\n' +
          '                                           href="javascript:void(0)">' +
          unit.unit_name +
          "</a></li>";

        html +=
          '<div class="property-card">\n' +
          '<img src="/assets/images/unit2.jpg" alt="landing-image" border="0" class="btn-update-unit"  unit-id="' +
          unit.guid +
          '" unit-bedrooms="' +
          unit.bedrooms +
          '" unit-bathrooms="' +
          unit.bathrooms +
          '"unit-meter="' +
          unit.meter +
          '"unit-rent="' +
          unit.rent +
          '" parking="' +
          unit.parking +
          '" children="' +
          unit.children +
          '" min-salary="' +
          unit.min_gross_salary +
          '" max-occupants="' +
          unit.max_occupants +
          '" listed="' +
          unit.listed +
          '" unit-name="' +
          unit.unit_name +
          '" unit-water="' +
          unit.water +
          '" unit-electricity="' +
          unit.electricity +
          '">\n' +
          '<div class="property-details">\n' +
          '<p><a class="btn-update-unit"  unit-id="' +
          unit.guid +
          '" unit-bedrooms="' +
          unit.bedrooms +
          '" unit-bathrooms="' +
          unit.bathrooms +
          '"unit-meter="' +
          unit.meter +
          '"unit-rent="' +
          unit.rent +
          '" parking="' +
          unit.parking +
          '" children="' +
          unit.children +
          '" min-salary="' +
          unit.min_gross_salary +
          '" max-occupants="' +
          unit.max_occupants +
          '" listed="' +
          unit.listed +
          '" unit-name="' +
          unit.unit_name +
          '" unit-water="' +
          unit.water +
          '" unit-electricity="' +
          unit.electricity +
          '">' +
          unit.unit_name +
          "</a></p>\n";

        if (unit.tenant_name) {
          html += "<p>" + unit.tenant_name + "</p>\n";
        } else {
          html += "<p>Vacant</p>\n";
        }

        html +=
          ' </a><div><i class="fa-solid fa-bed ml-1"></i> ' +
          unit.bedrooms +
          "\n" +
          '   <i class="fa-solid fa-shower ml-1"></i>  ' +
          unit.bathrooms;
          
        if (unit.listed) {
          html +=
            ' <i class="fa-solid fa-link ml-1 btn-copy-listing-link" role="button" unit-id="' +
            unit.guid +
            '"></i>\n';
        }

        html += " </div>\n" + "</div>\n" + "</div> ";
      });

      $("#div-units").html(html);

      $(".btn-copy-listing-link").click(function (event) {
        event.preventDefault();
        navigator.clipboard.writeText(
          location.protocol +
            "/" +
            location.host +
            "/applications/?id=" +
            event.target.getAttribute("unit-id")
        );
        showToast("Listing link copied to clipboard");
      });

      $(".btn-update-unit").click(function (event) {
        // Update the modal's content.
        const unitName = event.target.getAttribute("unit-name");
        const unitId = event.target.getAttribute("unit-id");
        const listed = event.target.getAttribute("listed");
        const parking = event.target.getAttribute("parking");
        const children = event.target.getAttribute("children");
        const waterCharge = event.target.getAttribute("unit-water");
        const electricityCharge = event.target.getAttribute("unit-electricity");
        const minSalary = parseInt(
          event.target.getAttribute("min-salary")
        );
        const maxOccupants = event.target.getAttribute("max-occupants");
        const rent = parseInt(
          event.target.getAttribute("unit-rent")
        );
        const bedrooms = event.target.getAttribute("unit-bedrooms");
        const bathrooms = event.target.getAttribute("unit-bathrooms");
        const meter = event.target.getAttribute("unit-meter");
        $(".btn-delete-unit").removeClass("display-none");
        sessionStorage.setItem("unit-id", unitId);

        $("#unit-name-header").html(unitName);
        $("#unit-name").val(unitName);
        $("#unit-rent").val(rent);
        $("#max-occupants").val(maxOccupants);
        $("#min-gross-salary").val(minSalary);
        $("#unit-bedrooms").val(bedrooms);
        $("#unit-bathrooms").val(bathrooms);
        $("#unit-meter").val(meter);
        if (waterCharge.localeCompare("undefined") === 0) {
          $("#ul-unit-water-selected").html("Free");
        } else {
          $("#ul-unit-water-selected").html(waterCharge);
        }

        if (electricityCharge.localeCompare("undefined") === 0) {
          $("#ul-unit-electricity-selected").html("Free");
        } else {
          $("#ul-unit-electricity-selected").html(electricityCharge);
        }

        if (children.localeCompare("true") === 0) {
          $("#checkChildrenAllowed").prop("checked", true);
        } else {
          $("#checkChildrenAllowed").prop("checked", false);
        }

        if (parking.localeCompare("true") === 0) {
          $("#checkParking").prop("checked", true);
        } else {
          $("#checkParking").prop("checked", false);
        }

        if (listed.localeCompare("true") === 0) {
          $("#checkListed").prop("checked", true);
        } else {
          $("#checkListed").prop("checked", false);
        }

        updateView("new-unit-content-div", unitName);
        $(".new-unit-fields").addClass("display-none");
        $("#unit-name-label").text("Unit Name:");
      });

      $(".btn-delete-unit").click(function (event) {
        event.preventDefault();
        $("#confirmDeleteUnitModal").modal("toggle");
      });

      $(".lease-unit-dropdown").click(function (event) {
        sessionStorage.setItem(
          "lease-unit-id",
          event.target.getAttribute("lease-unit-id")
        );
        $("#unit-dropdown-selected").html(event.target.innerText);
        $("#maintenance-unit-dropdown-selected").html(event.target.innerText);
      });

      $("#btn-create-new-unit").removeClass("display-none");
    },
    error: function (xhr) {},
  });
};

let populateUnitsDropdown = (elementId) => {
  const queryString = window.location.search;
  console.log(queryString);
  const urlParams = new URLSearchParams(queryString);
  const id = urlParams.get("id");
  let url = "/api/unitsnames/get/" + id;
  $.ajax({
    type: "GET",
    url: url,
    contentType: "application/json; charset=UTF-8",
    success: function (data) {
      if (data.result_code !== undefined) {
        if (data.result_code === 1) {
          return;
        }
      }

      let unitsDropDownHtml = "";
      data.forEach(function (unit) {
        unitsDropDownHtml +=
          '<li><a class="dropdown-item unit-dropdown" unit-guid="' +
          unit.guid +
          '"\n' +
          '                                           href="javascript:void(0)">' +
          unit.unit_name +
          "</a></li>";
      });

      $("#" + elementId).html(unitsDropDownHtml);

      $(".unit-dropdown").click(function (event) {
        sessionStorage.setItem(
          "unit-guid",
          event.target.getAttribute("unit-guid")
        );
        $("#" + elementId + "-selected").html(event.target.innerText);
      });
    },
    error: function (xhr) {},
  });
};
