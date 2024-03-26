$ = jQuery.noConflict();

let check = false;
const reviewApiKeyInput = $("#review_api_key");
const FirmNameInput = $("#firm_name");
let btnProcess = $("#api_key_setting_form .btn-process");
let btnProcess_get_set = $("#google_review_upload_form .btn-process");

jQuery(document).ready(function ($) {
  initial_check();
});

$("#api_key_setting_form").submit(function (event) {
  event.preventDefault();
  check = true;
  ApiKeySave(check);
});

$.fn.focusAtEnd = function () {
  return this.each(function () {
    var input = $(this)[0];
    var textLength = input.value.length;
    input.setSelectionRange(textLength, textLength);
  });
};

function initial_check() {
  const nonce = $("#review_api_key_nonce").val();

  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    data: {
      action: "initial_check",
      nonce: nonce,
    },
    success: function (response) {
      const correctSign = $("#api_key_setting_form .correct-sign");
      const correctSign_business = $(
        "#google_review_upload_form .correct-sign"
      );
      const wrongSign = $("#api_key_setting_form .wrong-sign");
      wrongSign.removeClass("visible");
      correctSign.removeClass("visible");
      correctSign_business.removeClass("visible");
      const cont = $(".cont");

      if (response.success == 1) {
        if (response.api_sign == 1) {
          if (response.api_sign == 1) {
            correctSign.addClass("visible");
          }
          if (response.business_sign == 1) {
            correctSign_business.addClass("visible");
          }
          if (response.api_sign !== 0) {
            cont.removeClass("hidden");
          }
          $("#firm_name").focus().focusAtEnd();
        } else {
          wrongSign.addClass("visible");
          cont.addClass("hidden");
          $("#firm_name").focus().focusAtEnd();
        }
      }
    },
    error: function () {
      toastr.error("", "Something went wrong!");
    },
    complete: function () {},
  });
}

function ApiKeySave(check) {
  const reviewApiKey = reviewApiKeyInput.val().replace(/\s/g, "");
  reviewApiKeyInput.val(reviewApiKey);
  const nonce = $("#review_api_key_nonce").val();

  btnProcess.html("Loading").addClass("spinning");
  btnProcess.prop("disabled", true);
  btnProcess_get_set.prop("disabled", true);

  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {},
    data: {
      action: "review_api_key_ajax_action",
      review_api_key: reviewApiKey,
      nonce: nonce,
    },
    success: function (response, status, error) {
      const correctSign = $("#api_key_setting_form .correct-sign");
      const wrongSign = $("#api_key_setting_form .wrong-sign");
      const cont = $(".cont");
      wrongSign.removeClass("visible");
      correctSign.removeClass("visible");

      if (response.success === 1) {
        setTimeout(function () {
          correctSign.addClass("visible");
          cont.removeClass("hidden");
          if (check) {
            // toastr.success("", response.msg); 
            Swal.fire({
              title: "Success",
              text: response.msg,
              icon: "success"
            });           
            btnProcess.removeClass("spinning").html("Save");
            btnProcess.prop("disabled", false).val("Save");
            btnProcess_get_set.prop("disabled", false);
            $("#firm_name").focus();
          }
        }, 1500);
      } else {
        setTimeout(function () {
          wrongSign.addClass("visible");
          cont.addClass("hidden");
          if (check) {
            Swal.fire({
              title: "Failed !",
              text: response.msg,
              icon: "error"
            });           
            btnProcess.removeClass("spinning").html("Save");
            btnProcess.prop("disabled", false).val("Save");
            btnProcess_get_set.prop("disabled", false);
          }
        }, 1500);
      }
    },
    error: function (xhr, status, error) {
      Swal.fire({
        title: "Failed !",
        text: "Something went wrong!",
        icon: "error"
      });      
    },
    complete: function () {},
  });
}

$("#google_review_upload_form").submit(function (event) {
  event.preventDefault();  
});

// Assuming ".get" is the class of the button you want to trigger the form submission
$("#google_review_upload_form button.get").click(function() {
  GetAndSet(); // Call your function here
  $("#google_review_upload_form").submit(); // Manually submit the form
});

function GetAndSet(check) {
  // const firm_name = FirmNameInput.val().replace(/\s/g, "");
  const firm_name = FirmNameInput.val();
  const nonce = $("#get_set_trigger_nonce").val();
  btnProcess_get_set.html("Loading").addClass("spinning");
  btnProcess_get_set.prop("disabled", true);
  btnProcess.prop("disabled", true);

  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {},
    data: {
      action: "review_get_set_ajax_action",
      firm_name: firm_name,
      review_api_key: ajax_object.review_api_key,
      nonce: nonce,
    },
    success: function (response, status, error) {
      const correctSign = $("#google_review_upload_form .correct-sign");
      const wrongSign = $("#google_review_upload_form .wrong-sign");
      wrongSign.removeClass("visible");
      correctSign.removeClass("visible");
      if (response.success === 1) {
        setTimeout(function () {
          correctSign.addClass("visible");
          toastr.success("", response.message);
          btnProcess_get_set.removeClass("spinning").html("GET & SET");
          btnProcess_get_set.prop("disabled", false).val("GET & SET");
          btnProcess.prop("disabled", false);
        }, 1500);
      } else {
        setTimeout(function () {
          wrongSign.addClass("visible");
          toastr.error("", response.message);
          btnProcess_get_set.removeClass("spinning").html("GET & SET");
          btnProcess_get_set.prop("disabled", false).val("GET & SET");
          btnProcess.prop("disabled", false);
          $("#firm_name").focus().select();
        }, 1500);
      }
    },
    error: function (xhr, status, error) {
      var errorMessage = xhr.responseText;
      if (errorMessage.startsWith("Error")) {
        errorMessage = errorMessage
          .substring(errorMessage.indexOf("Error") + 6)
          .trim();
      }
      toastr.error(errorMessage || "An error occurred", "Error");
    },
    complete: function () {},
  });
}
