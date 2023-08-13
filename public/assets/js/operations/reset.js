$(document).ready(function () {

  let guid = getURLParameter("guid");
  if(guid === null){
    $('.reset-password').addClass("display-none");
    $('.reset-email').removeClass("display-none");
  }else{
    $('.reset-password').removeClass("display-none");
    $('.reset-email').addClass("display-none");
  }

  $("#reset-form").validate({
    // Specify validation rules
    rules: {},
    submitHandler: function () {
      let guid = getURLParameter("guid");
      if(guid === null){
        sendResetEmail();
      }else{
        resetPassword(guid);
      }
    },
  });

  $("#reset-form").submit(function (event) {
    event.preventDefault();
  });
});


let resetPassword = (guid) => {
  let data = {
    _guid: guid,
    _password: $("#password").val(),
    _confirm_password: $("#_confirm_password").val(),
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

let sendResetEmail = () => {
  let data = {
    _username: $("#email").val(),
  };

  let url = "/reset/email";

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
