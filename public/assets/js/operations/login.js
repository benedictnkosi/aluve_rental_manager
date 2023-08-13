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

  $("#reset-form").validate({
    // Specify validation rules
    rules: {},
    submitHandler: function () {
      resetPassword();
    },
  });

  $("#reset-form").submit(function (event) {
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
        showToast("Login Failed");
      } else {
        //check if tenant or landlord logged in
        let url = "/no_auth/me";

        $.get(url, function (data) {
          console.log(data.authenticated);
          if(data.roles.includes("ROLE_LANDLORD")){
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
    showToast(" Please select user type");
    return;
  }

  let data = {
    _name: $("#name").val(),
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
      if(response.result_code === 0){
        $('#success-reg-div').removeClass('display-none');
        $('#register-form').addClass('display-none');
      }else{
        showToast(response.result_message);
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      showToast(errorThrown);
    },
  });
};




let resetPassword = () => {
  let data = {
    _username: $("#email").val(),
  };

  let url = "/reset/password";

  $.ajax({
    url: url,
    type: "POST",
    data: data,
    success: function (response) {
      $('#success-reg-div').removeClass('display-none');
      $('#register-form').addClass('display-none');
      showToast(response.result_message);
    },
    error: function (jqXHR, textStatus, errorThrown) {
      showToast(errorThrown);
    },
  });
};
