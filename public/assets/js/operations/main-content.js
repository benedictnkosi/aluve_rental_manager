$(document).ready(function () {
  $("div.spanner").addClass("show");
  $("div.overlay").addClass("show");

  if (sessionStorage.getItem("current_page") === null) {
    updateView("dashboard-content-div", "Dashboard");
  } else {
    if (
      sessionStorage
        .getItem("current_page")
        .localeCompare("new-unit-content-div") === 0
    ) {
      updateView("units-content-div", "Units");
    } else if (
      sessionStorage
        .getItem("current_page")
        .localeCompare("new-lease-content-div") === 0
    ) {
      updateView("leases-content-div", "Leases");
    } else {
      updateView(
        sessionStorage.getItem("current_page"),
        sessionStorage.getItem("current_page_header")
      );
    }
  }

  $(".nav-link").click(function (event) {
    if (!$(event.target).hasClass("settings-nav-link")) {
      updateView(
        event.target.getAttribute("main-content"),
        event.target.getAttribute("header")
      );
    }
  });

  $(".settings-nav-link").click(function (event) {
    $(".settings-nav-link").removeClass("active");
    updateSettingsView(event.target.getAttribute("form-to-show"));
    $(event.target).addClass("active");
    if (
      event.target
        .getAttribute("form-to-show")
        .localeCompare("settings-form-banking") === 0
    ) {
      getBankingDetails();
    }
  });

  $(".nav-links").unbind("click");
  $(".nav-links").click(function (event) {
    event.stopImmediatePropagation();
    $(".headcol").css("position", "absolute");
  });

  $(".mobile-menu-link").click(function (event) {
    $("#offcanvasNavbar").removeClass("show");
  });

  isUserLoggedIn();
});

function updateSettingsView(selectedForm) {
  $(".settings-form").addClass("display-none");
  $("#" + selectedForm).removeClass("display-none");
}

function updateView(selectedDiv, header) {
  $(".main-content").addClass("display-none");
  $("#" + selectedDiv).removeClass("display-none");
  //hide mobile menu
  $("#navbarSupportedContent").removeClass("show");

  $("#main-content-header").html(header);
  $("#page-header").html(header);
  $(".no-results-image").addClass("display-none");

  sessionStorage.setItem("current_page", selectedDiv);
  sessionStorage.setItem("current_page_header", header);
  $(".no-results-image").addClass("display-none");
  switch (selectedDiv) {
    case "dashboard-content-div":
      generateAllGraphs();
      break;
    case "units-content-div":
      getAllUnits();
      break;
    case "leases-content-div":
      getAllLeases();
      break;
    case "applications-content-div":
      getApplications();
      break;
    case "expenses-content-div":
      getExpenseAccounts();
      getExpenses();
      break;
    case "maintenance-content-div":
      getMaintenanceCalls();
      break;
    case "settings-content-div":
      getProperty();
      break;
    default:
  }
}

function myFunction() {
  var x = document.getElementById("myLinks");
  if (x.style.display === "block") {
    x.style.display = "none";
  } else {
    x.style.display = "block";
  }
}
