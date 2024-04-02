$ = jQuery.noConflict();

let check = false;
const reviewApiKeyInput = $("#review_api_key");
const FirmNameInput = $("#firm_name");
let btnProcess = $("#api_key_setting_form .btn-process");
let btnProcess_get_set = $("#google_review_upload_form .btn-process");
let btnProcess_check = $("#google_review_upload_form .check_start");


let correctSign_business = $(
  "#google_review_upload_form .correct-sign"
);
// correctSign_business.removeClass("visible");
// correctSign_business.addClass("visible");

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

function getClientIP(callback) {
  $.get("https://api.ipify.org?format=json", function (response) {
    callback(response.ip);
  });
}


// btnProcess_get_set.removeClass("spinning");
// btnProcess_get_set.prop("disabled", true);
// btnProcess_get_set.addClass('disabled');
// btnProcess_check.addClass('visible');
// btnProcess_check.removeClass('disabled');

jQuery(document).ready(function(){

  let element = jQuery("#review_api_key");
  jQuery(element).bind("focus blur", function (event) {
    event.stopPropagation();
    if (event.type == "focus") {
      btnProcess.removeClass('disabledapi');
      btnProcess.prop("disabled", false);
  
      btnProcess_check.addClass('visible');
  
      console.log("focus in !");
    }
  
    else if (event.type == "blur") {
      console.log("focus out !");
    }
  });
  
  
  let element_business = jQuery("#firm_name");
  jQuery(element_business).bind("focus blur", function (event) {
    event.stopPropagation();
    if (event.type == "focus") {
      btnProcess_get_set.prop("disabled", true);
      btnProcess_get_set.addClass('disabled');
      btnProcess_check.addClass('visible');
      btnProcess_check.removeClass('disabled');  
      console.log("focus in !");
    }
  
    else if (event.type == "blur") {
      handleInputKeyUp();
      console.log("focus out !");
    }
  });
  
  
  function handleInputKeyUp() {
    $(element_business).on('keyup', function () {
      if (this.value.length > 1) {
        btnProcess_get_set.prop("disabled", false);
        btnProcess_get_set.removeClass('disabled');
        btnProcess_check.removeClass('visible');
        btnProcess_check.addClass('disabled');
      }
    });
  }
});


function initial_check() {
  const nonce = $("#review_api_key_nonce").val();

  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    data: {
      action: "initial_check_api",
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

      if (response.success_api == 1) {
        correctSign.addClass("visible");
        // correctSign_business.addClass("visible");
        cont.removeClass("hidden");
        $("#firm_name").focus().focusAtEnd();
        btnProcess.addClass("disabledapi");
        btnProcess.prop("disabled", true);
      }
      else {
        wrongSign.addClass("visible");
        cont.addClass("hidden");
        $("#firm_name").focus().focusAtEnd();
      }

      if (response.success_business == 1) {
        console.log('bbbbb');
        // correctSign.addClass("visible");
        correctSign_business.addClass("visible");
        cont.removeClass("hidden");
        $("#firm_name").focus().focusAtEnd();
        btnProcess.addClass("disabledapi");
        btnProcess.prop("disabled", true);
      }
      else {
        wrongSign.addClass("visible");
        cont.addClass("hidden");
        $("#firm_name").focus().focusAtEnd();
      }



    },
    error: function () {
      toastr.error("", "Something went wrong!");
    },
    complete: function () { },
  });
}

function ApiKeySave(check) {
  const reviewApiKey = reviewApiKeyInput.val().replace(/\s/g, "");
  reviewApiKeyInput.val(reviewApiKey);
  const nonce = $("#review_api_key_nonce").val();

  // btnProcess.html("Loading").addClass("spinning");
  // btnProcess.prop("disabled", true);
  // btnProcess_get_set.prop("disabled", true);

  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#loader').removeClass('hidden')
    },
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

      setTimeout(function () {
        if (response.success === 1) {

          correctSign.addClass("visible");
          cont.removeClass("hidden");
          if (check) {
            // toastr.success("", response.msg); 
            $("#firm_name").focus();


            Swal.fire({
              position: "top-end",
              icon: "success",
              title: response.msg,
              // text: response.msg,
              showConfirmButton: false,
              timer: 1500
            });
            btnProcess.addClass("disabledapi");
            btnProcess.prop("disabled", true);
            // btnProcess.removeClass("spinning").html("Save");
            // btnProcess.prop("disabled", false).val("Save");
            // btnProcess_get_set.prop("disabled", false);

          }

        } else {

          wrongSign.addClass("visible");
          cont.addClass("hidden");
          if (check) {
            Swal.fire({
              position: "top-end",
              icon: "error",
              title: response.msg,
              // text: response.msg,
              showConfirmButton: false,
              timer: 1500
            });
            // btnProcess.removeClass("spinning").html("Save");
            // btnProcess.prop("disabled", false).val("Save");
            // btnProcess_get_set.prop("disabled", false);
          }

        }

      }, 100);


    },
    error: function (xhr, status, error) {
      Swal.fire({
        position: "top-end",
        icon: "error",
        // title: response.msg,
        // text: response.msg,
        showConfirmButton: false,
        timer: 1500
      });
    },
    complete: function () {
      setTimeout(function () {
        $('#loader').addClass('hidden');
      }, 100);
    },
  });
}

$("#google_review_upload_form").submit(function (event) {
  event.preventDefault(); // Prevent the default form submission behavior
});

// Assuming ".get" is the class of the button you want to trigger the form submission
$("#google_review_upload_form button.job_start").click(function (event) {
  check = true;
  Swal.fire({
    title: "Confirmation: Initiate Job?",
    text: "Are you certain about initiating this job? Once completed, you'll be able to upload reviews.",
    // icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Start Job",
    popup: 'swal2-show',
    backdrop: 'swal2-backdrop-show',
    icon: 'question',
    color: "#716add",
  }).then((result) => {
    /* Read more about isConfirmed, isDenied below */
    if (result.isConfirmed) {
      job_start(check);
      // Swal.fire("Saved!", "", "success");
    } else if (result.isDenied) {
      Swal.fire("Changes are not saved", "", "info");
    }
  });
  $("#google_review_upload_form").submit(); // Manually submit the form
});

function confirm_msg(msg, jobID) {
  // Calculate the timer duration based on the length of the message
  const timerDuration = 5000; // Minimum 1.5 seconds, 100 milliseconds per character

  // Setting up SweetAlert2 configuration
  let timerInterval;
  Swal.fire({
    title: jobID, // Displaying jobID as the title
    html: msg, // Displaying message as HTML content
    timer: timerDuration, // Setting a dynamic timer based on the length of the message
    timerProgressBar: true, // Displaying progress bar for timer
    showCloseButton: false,
    allowOutsideClick: false,
    grow: false,
    position: 'bottom-end',

    didOpen: () => {
      Swal.showLoading(); // Showing loading animation when dialog opens
      // jQuery(btnProcess_get_set).text('Loading.....');
      btnProcess_get_set.addClass("spinning");
      btnProcess_get_set.prop("disabled", true);
    },
    willClose: () => {
      clearInterval(timerInterval); // Clearing interval when dialog is about to close
    }
  }).then((result) => {
    // Handling the result after the dialog is closed
    if (result.dismiss === Swal.DismissReason.timer) {
      // If the dialog was closed due to timer expiration
      Swal.fire({
        // position: "top-end",
        icon: "success",
        title: "Your job has been completed", // Success message
        showConfirmButton: false,
        timer: 3500,
        allowOutsideClick: false,
        grow: false,
        position: 'bottom-end',
      });
      btnProcess_get_set.removeClass("spinning");
      btnProcess_get_set.prop("disabled", true);
      btnProcess_get_set.addClass('disabled');
      btnProcess_check.addClass('visible');
      btnProcess_check.removeClass('disabled');
    }
  });
}



function job_start(check) {
  const firm_name = FirmNameInput.val();
  const nonce = $("#get_set_trigger_nonce").val();

  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#loader').removeClass('hidden')
    },
    data: {
      action: "job_start_ajax_action",
      firm_name: firm_name,
      review_api_key: ajax_object.review_api_key,
      nonce: nonce,
    },
    success: function (response, status, error) {
      const correctSign = $("#api_key_setting_form .correct-sign");
      const wrongSign = $("#api_key_setting_form .wrong-sign");
      const cont = $(".cont");
      wrongSign.removeClass("visible");
      correctSign.removeClass("visible");

      setTimeout(function () {
        if (response.success === 1) {
          correctSign.addClass("visible");
          cont.removeClass("hidden");
          if (check) {
            $("#firm_name").focus();
            confirm_msg(response.msg, response.data.jobID);
          }

        } else {
          wrongSign.addClass("visible");
          cont.addClass("hidden");
          if (check) {
            Swal.fire({
              // position: "top-end",
              icon: "error",
              title: response.msg,
              showConfirmButton: false,
              timer: 2000,
              allowOutsideClick: false,
              grow: false,
              position: 'bottom-end',
            });
          }

        }
      }, 1000);
    },
    error: function (xhr, status, error) {
      Swal.fire({
        position: "top-end",
        icon: "error",
        title: response.msg,
        showConfirmButton: false,
        timer: 2000
      });
    },
    complete: function () {
      setTimeout(function () {
        $('#loader').addClass('hidden');
      }, 1000);
    },
  });
}


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
    beforeSend: function () { },
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
    complete: function () { },
  });
}
