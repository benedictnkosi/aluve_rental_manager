$(document).ready(function () {
  $("#login-form").validate({
    // Specify validation rules
    rules: {},
    submitHandler: function () {
      authenticateUser();
    },
  });

  $("#login-form").submit(function (event) {
    event.preventDefault();
  });

  $("#register-form").validate({
    // Specify validation rules
    rules: {},
    submitHandler: function () {
      registerUser();
    },
  });

  $("#register-form").submit(function (event) {
    event.preventDefault();
  });


  $(".user-type").click(function (event) {
    $("#drop-user-type-selected").html(event.target.innerText);
  });

});

let authenticateUser = () => {
  let data = {
    _username: $("#email").val(),
    _password: $("#password").val(),
  };

  let url = "/login";

  $.ajax({
    url: url,
    type: "POST",
    data: data,
    dataType: "script",
    success: function (response) {
      if (response.includes("_username")) {
        showToast("Error. Login Failed");
      } else {
        //check if tenant or landlord logged in
        let url = "/no_auth/me";

        $.get(url, function (data) {
          console.log(data.authenticated);
          if(data.roles.includes("ROLE_ADMIN")){
            location.href = "/properties";
          }else if(data.roles.includes("ROLE_TENANT")){
            location.href = "/tenant";
          }else{
            window.location.href = "/logout";
          }
        });
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      showToast(errorThrown);
    },
  });
};

let registerUser = () => {

  const selectedType = $("#drop-user-type-selected").html();
  if(selectedType.includes("User Type")){
    showToast("Error: Please select user type");
    return;
  }

  let data = {
    _username: $("#email").val(),
    _password: $("#password").val(),
    _confirm_password: $("#_confirm_password").val(),
    _user_type: selectedType,
  };

  let url = "/register";

  $.ajax({
    url: url,
    type: "POST",
    data: data,
    success: function (response) {
      $(".spinner-border").hide();
      $(".overlay").hide();
      showToast(response.result_message);
    },
    error: function (jqXHR, textStatus, errorThrown) {
      $(".spinner-border").hide();
      $(".overlay").hide();
      showToast(errorThrown);
    },
  });
};

let showToast = (message) => {
  const liveToast = document.getElementById("liveToast");
  const toastBootstrap = bootstrap.Toast.getOrCreateInstance(liveToast);
  if (message.toLowerCase().includes("success")) {
    $("#toast-message").html(
      '<div class="alert alert-success" role="alert">' + message + "</div>"
    );
  } else if (
    message.toLowerCase().includes("fail") ||
    message.toLowerCase().includes("error")
  ) {
    $("#toast-message").html(
      '<div class="alert alert-danger" role="alert">' + message + "</div>"
    );
  } else {
    $("#toast-message").html(
      '<div class="alert alert-dark" role="alert">' + message + "</div>"
    );
  }
  toastBootstrap.show();
};


