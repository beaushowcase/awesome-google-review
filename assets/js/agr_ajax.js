$ = jQuery.noConflict();

let check = false;
let reviewApiKeyInput = $("#review_api_key");
let FirmNameInput = $("#firm_name");

// let btnProcess_get_set = $("#google_review_upload_form .btn-process");
let btnProcess_check = $("#google_review_upload_form .check_start");


// let correctSign_business = $("#google_review_upload_form .correct-sign");
// correctSign_business.removeClass("visible");
// correctSign_business.addClass("visible");

jQuery(document).ready(function ($) {
  initial_check();
});

// jQuery(document).ready(function() {
//   var h1 = $('.output');
//   if (h1.hasClass('output')) {
//       // Load the text file
//       let file_path = ajax_object.plugin_url + '/logs.txt';      
//       $.get(file_path, function(data) {         
//           // Split data by newline characters and create <p> elements
//           let lines = data.split('\n');
//           let paragraphs = lines.map(function(line) {
//               return '<p>' + line + '</p>';
//           });
//           // Join the paragraphs and set as HTML content of h1
//           h1.html(paragraphs.join(''));
//           // h1.addClass('display');
//       })
//       .fail(function(error) {
//           console.error('Error:', error);
//       });
//   }
// });

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



// btnProcess_get_set.removeClass("spinning");
// btnProcess_get_set.prop("disabled", true);
// btnProcess_get_set.addClass('disabled');
// btnProcess_check.addClass('visible');
// btnProcess_check.removeClass('disabled');




// jQuery(document).ready(function(){



//   let element = jQuery("#review_api_key");


//   jQuery(element).on('input', function () {   
//     if (jQuery(this).val() == '') {
//       console.log('blank');
//       // Check to see if there is any text entered
//       // If there is no text within the input then disable the button
//       // jQuery(".submit_btn.save.btn-process").prop('disabled', true);
//       btnProcess.removeClass('disabledapi');
//       btnProcess.prop("disabled", false);  
//       btnProcess_check.addClass('visible');  
//     } else {
//       console.log('not blank');
//       // If there is text in the input, then enable the button
//       jQuery(".submit_btn.save.btn-process").prop('disabled', false);
//     }
//   });

// });

// jQuery(element).bind("focus blur", function (event) {
//   event.stopPropagation();
//   if (event.type == "focus") {
//     btnProcess.removeClass('disabledapi');
//     btnProcess.prop("disabled", false);  
//     btnProcess_check.addClass('visible');  
//     console.log("focus in !");
//   }

//   else if (event.type == "blur") {
//     console.log("focus out !");
//   }
// });


// let element_business = jQuery("#firm_name");
// jQuery(element_business).bind("focus blur", function (event) {
//   event.stopPropagation();
//   if (event.type == "focus") {
//     btnProcess_get_set.prop("disabled", true);
//     btnProcess_get_set.addClass('disabled');
//     btnProcess_check.addClass('visible');
//     btnProcess_check.removeClass('disabled');  
//     console.log("focus in !");
//   }

//   else if (event.type == "blur") {
//     handleInputKeyUp();
//     console.log("focus out !");
//   }
// });


// function handleInputKeyUp() {
//   $(element_business).on('keyup', function () {
//     if (this.value.length > 1) {
//       btnProcess_get_set.prop("disabled", false);
//       btnProcess_get_set.removeClass('disabled');
//       btnProcess_check.removeClass('visible');
//       btnProcess_check.addClass('disabled');
//     }
//   });
// }



// const correctSign_BUSINESS = $("#google_review_upload_form .correct-sign");
// const wrongSign_API = $("#api_key_setting_form .wrong-sign");

// wrongSign_API.removeClass("visible");

// correctSign_BUSINESS.removeClass("visible");

// FOR API BOX
let sign_TRUE = false;
let sign_FALSE = false;
let btnProcess_API = $("#api_key_setting_form .btn-process");
let correctSign_API = $("#api_key_setting_form .correct-sign");
let wrongSign_API = $("#api_key_setting_form .wrong-sign");

if (correctSign_API.is(":visible")) {
  sign_TRUE = true;
}
if (wrongSign_API.is(":visible")) {
  sign_FALSE = true;
}

let BUSINESS_BOX = $(".cont");
// FOR BUSINESS BOX
let sign_BUSINESS_TRUE = false;
let sign_BUSINESS_FALSE = false;
let btnProcess_BUSINESS_START = $("#google_review_upload_form .btn-process.job_start");
let btnProcess_BUSINESS_CHECK = $("#google_review_upload_form .btn-process.check_start");
let correctSign_BUSINESS_BOX = $("#google_review_upload_form .correct-sign");
let wrongSign_BUSINESS_BOX = $("#google_review_upload_form .wrong-sign");

let btnProcess_BUSINESS_UPLOAD = $("#google_review_upload_form .btn-process.upload_start");

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
    beforeSend: function () {
      $('#loader').removeClass('hidden');
      btnProcess_API.prop("disabled", true);
    },
    success: function (response) {
      if (response.success_api == 1) {
        if (sign_TRUE) {
          correctSign_API.addClass("visible");
          $('.api_key_setting_form').addClass("showdisable");
        }
        if (sign_FALSE) {
          wrongSign_API.removeClass("visible");
        }
        BUSINESS_BOX.removeClass("hidden");
        btnProcess_API.prop("disabled", true);
      }
      else {
        if (sign_TRUE) {
          correctSign_API.removeClass("visible");
          $('.api_key_setting_form').removeClass("showdisable");
        }
        if (sign_FALSE) {
          wrongSign_API.addClass("visible");
        }
        BUSINESS_BOX.addClass("hidden");
        btnProcess_API.prop("disabled", false);
      }

      //business CHECK


      // START SHOW ONLY
      if (response.success_api && !response.success_business) {
        if (response.success_api && !response.success_business && !response.success_check) {
          btnProcess_BUSINESS_CHECK.hide();
          btnProcess_BUSINESS_UPLOAD.hide();

          //start show only
          btnProcess_BUSINESS_START.show();
        }
      }


      else if (response.success_api && response.success_business) {
        //CHECK SHOW
        if (response.success_api && response.success_business && !response.success_check) {
          btnProcess_BUSINESS_START.hide();
          btnProcess_BUSINESS_UPLOAD.hide();

          //check show
          btnProcess_BUSINESS_CHECK.show();
        }

        //UPLOAD SHOW
        else if (response.success_api && response.success_business && response.success_check) {
          btnProcess_BUSINESS_START.hide();
          btnProcess_BUSINESS_CHECK.hide();

          //upload show
          btnProcess_BUSINESS_UPLOAD.show();
        }
      }


      // btnProcess_BUSINESS_START.show();
      // btnProcess_BUSINESS_CHECK.hide();

      // else if (response.success_api && response.success_business && response.success_check == false) {

      // }

      // //business UPLOAD
      // if (response.success_api && response.success_business && response.success_check == true) {
      //   btnProcess_BUSINESS_START.hide();                
      //   btnProcess_BUSINESS_CHECK.hide();        
      //   btnProcess_BUSINESS_UPLOAD.show();
      // }     


      //business CHECK
      // if (response.success_api == 1 && response.success_business == 1 ) {
      //   btnProcess_BUSINESS_START.hide();
      //   btnProcess_BUSINESS_CHECK.hide();
      //   btnProcess_BUSINESS_UPLOAD.show();
      // }
      // else {
      //   btnProcess_BUSINESS_START.show();
      //   btnProcess_BUSINESS_CHECK.show();
      //   btnProcess_BUSINESS_UPLOAD.hide();
      // }


      // //business UPLOAD
      // if (response.success_check == 1) {     
      //   btnProcess_BUSINESS_START.hide();
      //   btnProcess_BUSINESS_CHECK.hide();
      //   btnProcess_BUSINESS_UPLOAD.show();
      // }
      // else {        
      //   btnProcess_BUSINESS_START.show();
      //   btnProcess_BUSINESS_CHECK.show();
      //   btnProcess_BUSINESS_UPLOAD.hide();
      // }



      // if (response.success_api == 1 && response.success_business == 1) {
      //   // btnProcess_BUSINESS_START.prop("disabled", true);
      //   btnProcess_BUSINESS_CHECK.addClass("visible");
      // }
      // else {
      //   // btnProcess_BUSINESS_START.prop("disabled", false);
      //   btnProcess_BUSINESS_CHECK.removeClass("visible");
      // }
    },
    error: function () {
      response_fail('Something went wrong !');
    },
    complete: function () {
      setTimeout(function () {
        $('#loader').addClass('hidden');
      }, 100);

    },
  });
}


// $(document).ready(function(){
//   $('#review_api_key').focusin(function(){
//       console.log('Input field focused');

//   });

//   $('#review_api_key').focusout(function(){
//       console.log('Input field focus lost');

//   });
// });

// key up check API KEY
let element = jQuery("#review_api_key");
$(document).ready(function () {
  var initialValue = $(element).val();
  $(element).on('input', function () {
    var currentValue = $(this).val();
    if (currentValue !== initialValue) {
      button_effects_enable();
    }
    else {
      button_effects_disable();
    }
  });
  function button_effects_enable() {
    console.log("button_effects_enable!");
    if (sign_TRUE) {
      correctSign_API.removeClass("visible");
      wrongSign_API.removeClass("visible");
      $('.api_key_setting_form').removeClass("showdisable");
    }

    if (jQuery('.google_review_upload_form.cont').length > 0) {
      correctSign_API.removeClass("visible");
      wrongSign_API.addClass("visible");
      $('.api_key_setting_form').removeClass("showdisable");
    }

    BUSINESS_BOX.addClass("hidden");
    btnProcess_API.prop("disabled", false);
    return true;
  }
  function button_effects_disable() {
    console.log("button_effects_disable!");
    if (jQuery('.google_review_upload_form.cont').length > 0) {
      correctSign_API.addClass("visible");
      wrongSign_API.removeClass("visible");
      $('.api_key_setting_form').addClass("showdisable");
    }

    BUSINESS_BOX.removeClass("hidden");
    btnProcess_API.prop("disabled", true);
    return true;
  }
});


// key up check BUSINESS TERM

// $(document).ready(function () {
//   var initialValue = $(business_term).val(); 
//   $(business_term).on('input', function () {    
//     var currentValue = $(this).val();
//     if (currentValue !== initialValue) {
//       button_effects_business_enable();
//     }
//     else {
//       button_effects_business_disable();
//     }
//   });  
// });


// function button_effects_business_enable() { 
//   btnProcess_BUSINESS_START.prop("disabled", false);
//   btnProcess_BUSINESS_CHECK.hide(500);
//   return true;
// }
// function button_effects_business_disable() {  
//   btnProcess_BUSINESS_START.prop("disabled", true);
//   btnProcess_BUSINESS_CHECK.show(500);
//   return true;
// }



//new function
// key up check BUSINESS TERM
// let business_term = jQuery("#firm_name");
// $(document).ready(function(){
//   btnProcess_BUSINESS_CHECK.show();
//   $(business_term).on('input', function () {
//     button_effects_business_enable();
//   });

//   $(business_term).on('blur', function () {
//     button_effects_business_disable();
//   });

//   function button_effects_business_enable() {
//     console.log("button_effects_business_enable!");
//     // if (sign_TRUE) {
//     //   correctSign_BUSINESS_BOX.removeClass("visible");
//     //   wrongSign_BUSINESS_BOX.removeClass("visible");
//     // }

//     // if (jQuery('.google_review_upload_form.cont').length > 0) {
//     //   correctSign_BUSINESS_BOX.removeClass("visible");
//     //   wrongSign_BUSINESS_BOX.addClass("visible");
//     // }

//     // BUSINESS_BOX.addClass("hidden");
//     btnProcess_BUSINESS_START.prop("disabled", false);
//     btnProcess_BUSINESS_CHECK.hide();
//     return true;
//   }
//   function button_effects_business_disable() {
//     // console.log("button_effects_business_disable!");
//     // if (jQuery('.google_review_upload_form.cont').length > 0) {
//     //   correctSign_BUSINESS_BOX.addClass("visible");
//     //   wrongSign_BUSINESS_BOX.removeClass("visible");
//     // }

//     // BUSINESS_BOX.removeClass("hidden");
//     btnProcess_BUSINESS_START.prop("disabled", false);
//     btnProcess_BUSINESS_CHECK.show();
//     return true;
//   }
// });


// let business_term = jQuery("#firm_name");
// $(document).ready(function () {
//   var initialValue = $(business_term).val();
//   $(business_term).on('input focus', function () {
//     var currentValue = $(this).val();
//     if (currentValue !== initialValue) {
//       button_effects_business_enable();
//     }
//     else {
//       button_effects_business_disable();
//     }
//   });
//   function button_effects_business_enable() {
//     console.log("button_effects_business_enable!");
//     // if (sign_TRUE) {
//     //   correctSign_BUSINESS_BOX.removeClass("visible");
//     //   wrongSign_BUSINESS_BOX.removeClass("visible");
//     // }

//     // if (jQuery('.google_review_upload_form.cont').length > 0) {
//     //   correctSign_BUSINESS_BOX.removeClass("visible");
//     //   wrongSign_BUSINESS_BOX.addClass("visible");
//     // }

//     // BUSINESS_BOX.addClass("hidden");
//     btnProcess_BUSINESS_START.prop("disabled", false);
//     return true;
//   }
//   function button_effects_business_disable() {
//     // console.log("button_effects_business_disable!");
//     // if (jQuery('.google_review_upload_form.cont').length > 0) {
//     //   correctSign_BUSINESS_BOX.addClass("visible");
//     //   wrongSign_BUSINESS_BOX.removeClass("visible");
//     // }

//     // BUSINESS_BOX.removeClass("hidden");
//     btnProcess_BUSINESS_START.prop("disabled", true);
//     return true;
//   }
// });

//api save call
function response_success(response) {
  Swal.fire({
    icon: "success",
    position: 'bottom-end',
    title: response,
    showConfirmButton: false,
    allowOutsideClick: false,
    grow: false,
    timer: 3500,
  });
  $('#loader').addClass('hidden');
  btnProcess_API.removeClass("spinning");
  btnProcess_API.prop("disabled", false);
  return true;
}

function response_fail(response) {
  $('#loader').addClass('hidden');
  btnProcess_BUSINESS_START.removeClass("spinning");
  Swal.fire({
    icon: "error",
    position: 'bottom-end',
    title: 'Error !',
    text: response,
    showConfirmButton: false,
    allowOutsideClick: false,
    grow: false,
    timer: 3500,
  });
  $('#loader').addClass('hidden');
  btnProcess_API.removeClass("spinning");
  btnProcess_API.prop("disabled", false);
  return true;
}





function ApiKeySave(check) {
  const reviewApiKey = reviewApiKeyInput.val().replace(/\s/g, "");
  reviewApiKeyInput.val(reviewApiKey);
  const nonce = $("#review_api_key_nonce").val();
  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#loader').removeClass('hidden');
      btnProcess_API.addClass("spinning");
      btnProcess_API.prop("disabled", true);
    },
    data: {
      action: "review_api_key_ajax_action",
      review_api_key: reviewApiKey,
      nonce: nonce,
    },
    success: function (response, status, error) {
      setTimeout(function () {
        if (response.success === 1) {
          if (check) {
            response_success(response.msg);
            correctSign_API.addClass("visible");
            wrongSign_API.removeClass("visible");
            BUSINESS_BOX.removeClass("hidden");
          }
        } else {
          if (check) {
            response_fail(response.msg);
            correctSign_API.removeClass("visible");
            wrongSign_API.addClass("visible");
            BUSINESS_BOX.addClass("hidden");
          }
        }
      }, 1500);
    },
    error: function (xhr, status, error) {
      response_fail('Something went wrong !');
    },
    complete: function () {
      setTimeout(function () {
        $('#loader').addClass('hidden');
        btnProcess_API.removeClass("spinning");
        btnProcess_API.prop("disabled", false);
        location.reload();
      }, 5000);
    },
  });

}

$("#google_review_upload_form").submit(function (event) {
  event.preventDefault(); // Prevent the default form submission behavior
});

// JOB START CLICKED 
$("#google_review_upload_form button.job_start").click(function (event) {
  check = true;
  Swal.fire({
    title: "Confirmation: Initiate Job?",
    text: "Are you certain about initiating this job? Once completed, you'll be able to upload reviews.",
    showCancelButton: false,
    showCloseButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Start Job",
    allowOutsideClick: false,
    backdrop: 'swal2-backdrop-show',
    icon: 'question',
    color: "#716add",
  }).then((result) => {
    if (result.isConfirmed) {
      job_start(check);
    } else if (result.isDenied) {
      Swal.fire("Changes are not saved", "", "info");
    }
  });
  $("#google_review_upload_form").submit(); // Manually submit the form
});


// JOB UPLOAD CLICKED 
$("#google_review_upload_form button.upload_start ").click(function (event) {
  let firm_name_display = $(FirmNameInput).val();
  check = true;
  Swal.fire({
    title: `Confirmation: Upload ?`,
    html: `Upload your reviews now and let your voice be heard about <strong>${firm_name_display}</strong>`,
    showCancelButton: false,
    confirmButtonColor: "#405640",
    cancelButtonColor: "#d33",
    confirmButtonText: "Upload",
    allowOutsideClick: false,
    backdrop: 'swal2-backdrop-show',
    showCloseButton: true,
    icon: 'question',
    color: "#716add",
  }).then((result) => {
    if (result.isConfirmed) {
      job_start(check);
    } else if (result.isDenied) {
      Swal.fire("Changes are not saved", "", "info");
    }
  });
  $("#google_review_upload_form").submit(); // Manually submit the form
});

// Assuming ".get" is the class of the button you want to trigger the form submission
$("#google_review_upload_form button.check_start").click(function (event) {

  check = true;
  Swal.fire({
    title: "Check",
    html: "Start to proceed the reviews of " + `<b>${$(FirmNameInput).val()}</b>` + " !",
    showCloseButton: true,
    allowOutsideClick: false,
    confirmButtonColor: "#405640",
    confirmButtonText: "Check",
    backdrop: 'swal2-backdrop-show',
    icon: "question",
  }).then((result) => {
    if (result.isConfirmed) {
      check_start(check);
    }
  });
  $("#google_review_upload_form").submit();
});


//GMB call
function response_business_success(response) {
  let timerInterval;
  Swal.fire({
    title: "Google Reviews !",
    html: "Checking in <b></b> milliseconds.",
    timer: 3500,
    timerProgressBar: true,
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
      const timer = Swal.getPopup().querySelector("b");
      timerInterval = setInterval(() => {
        timer.textContent = `${Swal.getTimerLeft()}`;
      }, 100);
    },
    willClose: () => {
      clearInterval(timerInterval);
    }
  }).then((result) => {
    /* Read more about handling dismissals below */
    if (result.dismiss === Swal.DismissReason.timer) {
      // let display_msg = "" + `<b>${$(FirmNameInput).val()} = 250 Reviews</b>` + " !";
      let display_msg = response;
      console.log("I was closed by the timer");
      Swal.fire({
        icon: "success",
        title: "Completed",
        html: display_msg,
        showConfirmButton: false,
        timer: 3500,
        allowOutsideClick: false,
        grow: false,
        position: 'bottom-end',
      }).then(function () {
        setTimeout(function () {
          location.reload();
        }, 100);
      });

      // btnProcess_BUSINESS_START.hide();
      // btnProcess_BUSINESS_CHECK.hide();
      // btnProcess_BUSINESS_UPLOAD.addClass('visible');
      // btnProcess_BUSINESS_START.prop("disabled", true);
      // btnProcess_BUSINESS_CHECK.prop("disabled", true);      
    }
  });
  return true;
}

function response_business_fail(response) {
  let display_msg = response;
  console.log("I was closed by the timer");
  Swal.fire({
    icon: "error",
    title: "Failed !",
    html: display_msg,
    showConfirmButton: false,
    timer: 3500,
    allowOutsideClick: false,
    grow: false,
    position: 'bottom-end',
  });
  btnProcess_BUSINESS_START.show();
  btnProcess_BUSINESS_CHECK.show();
  btnProcess_BUSINESS_UPLOAD.removeClass('visible');
  btnProcess_BUSINESS_START.prop("disabled", false);
  $('.right-box .output').html(display_msg);
  $('.right-box .output').addClass('display');
  return true;
}

function check_start(check) {
  console.log('check = ' + check);

  let current_job_id = FirmNameInput.attr('data-jobid');
  // const firm_name = FirmNameInput.val();
  const nonce = $("#get_set_trigger_nonce").val();

  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#loader').removeClass('hidden');
      btnProcess_BUSINESS_CHECK.addClass("spinning");
    },
    data: {
      action: "job_check_ajax_action",
      current_job_id: current_job_id,
      review_api_key: ajax_object.review_api_key,
      nonce: nonce,
    },
    success: function (response, status, error) {
      setTimeout(function () {
        if (response.success === 1) {
          if (check) {
            response_business_success(response.msg);
          }
        } else {
          if (check) {
            response_business_fail(response.msg);
          }
        }
      }, 3500);
    },
    error: function (xhr, status, error) {
      Swal.fire({
        position: "top-end",
        icon: "error",
        title: response.msg,
        showConfirmButton: false,
        timer: 3500
      });
    },
    complete: function () {
      setTimeout(function () {
        $('#loader').addClass('hidden');
        btnProcess_BUSINESS_CHECK.removeClass("spinning");
      }, 3500);
    },
  });

}

function confirm_msg(msg, jobID) {
  const timerDuration = 5000;
  let timerInterval;
  Swal.fire({
    title: jobID,
    html: msg,
    timer: timerDuration,
    timerProgressBar: true,
    showCloseButton: false,
    allowOutsideClick: false,
    grow: false,
    position: 'bottom-end',
    didOpen: () => {
      Swal.showLoading();
    },
    willClose: () => {
      clearInterval(timerInterval);
    }
  }).then((result) => {
    if (result.dismiss === Swal.DismissReason.timer) {
      $('#loader').addClass('hidden');
      btnProcess_BUSINESS_START.removeClass("spinning");

      Swal.fire({
        icon: "success",
        title: "Your job has been completed",
        showConfirmButton: false,
        timer: 3500,
        allowOutsideClick: false,
        grow: false,
        position: 'bottom-end',
      }).then(function () {
        setTimeout(function () {
          location.reload();
        }, 100);
      })
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
      $('#loader').removeClass('hidden');
      btnProcess_BUSINESS_START.addClass("spinning");
    },
    data: {
      action: "job_start_ajax_action",
      firm_name: firm_name,
      review_api_key: ajax_object.review_api_key,
      nonce: nonce,
    },
    success: function (response, status, error) {
      setTimeout(function () {
        if (response.success === 1) {
          if (check) {
            confirm_msg(response.msg, response.data.jobID);
            // btnProcess_BUSINESS_START.prop("disabled", true);
            btnProcess_BUSINESS_CHECK.addClass("visible");
          }
        } else {
          if (check) {
            response_fail(response.msg);
            btnProcess_BUSINESS_START.prop("disabled", false);
            btnProcess_BUSINESS_CHECK.removeClass("visible");
          }

        }
      }, 3500);
    },
    error: function (xhr, status, error) {
      Swal.fire({
        position: "top-end",
        icon: "error",
        title: response.msg,
        showConfirmButton: false,
        timer: 3500
      });
    },
    complete: function () {
      // setTimeout(function () {
      //   $('#loader').addClass('hidden');
      //   btnProcess_BUSINESS_START.removeClass("spinning");
      // }, 3500);
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


// RESET PROCESS


function reset_success() {
  let timerInterval;
  Swal.fire({
    title: "Reset Reviews Data !",
    html: "Checking in <b></b> milliseconds.",
    timer: 3500,
    timerProgressBar: true,
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
      const timer = Swal.getPopup().querySelector("b");
      timerInterval = setInterval(() => {
        timer.textContent = `${Swal.getTimerLeft()}`;
      }, 100);
    },
    willClose: () => {
      clearInterval(timerInterval);
    }
  }).then((result) => {
    if (result.dismiss === Swal.DismissReason.timer) {
      console.log("I was closed by the timer");
      Swal.fire({
        icon: "success",
        title: "Reset Completed",
        html: 'Reset reviews data !',
        showConfirmButton: false,
        timer: 3500,
        allowOutsideClick: false,
        grow: false,
      }).then(function () {
        setTimeout(function () {
          location.reload();
        }, 100);
      });
    }
  });
  return true;
}

function delete_start() {
  let current_job_id = FirmNameInput.attr('data-jobid');
  let firm_name = FirmNameInput.val();
  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#loader').removeClass('hidden');
      btnProcess_BUSINESS_CHECK.addClass("spinning");
    },
    data: {
      action: "job_reset_ajax_action",
      current_job_id: current_job_id,
      review_api_key: ajax_object.review_api_key,
      firm_name: firm_name
    },
    success: function (response, status, error) {
      if (response.success === 1) {
        if (check) {
          reset_success();
        }
      } else {
        if (check) {
          response_business_fail(response.msg);
        }
      }
    },
    error: function (xhr, status, error) {
      Swal.fire({
        position: "top-end",
        icon: "error",
        title: response.msg,
        showConfirmButton: false,
        timer: 3500
      });
    },
    complete: function () {
      setTimeout(function () {
        $('#loader').addClass('hidden');
        btnProcess_BUSINESS_CHECK.removeClass("spinning");
      }, 3500);
    },
  });
}


$(function () {
  $(document).on('click', 'p.reset', function (e) {
    e.preventDefault();
    check = true;
    Swal.fire({
      title: "Reset",
      html: "Resetting the reviews !",
      showCloseButton: true,
      allowOutsideClick: false,
      confirmButtonColor: "rgb(230 62 50)",
      confirmButtonText: "Reset",
      backdrop: 'swal2-backdrop-show',
      icon: "question",
    }).then((result) => {
      if (result.isConfirmed) {
        delete_start(check);
      }
    });
  });



});


var typingtext = $('.output.typing').find('p').html();
var speed = 35;

function typeWriter(typingtext, i, fnCallback) {
  if (i < typingtext.length) {
    $('.typing p').html(typingtext.substring(0, i + 1));
    setTimeout(function () {
      typeWriter(typingtext, i + 1, fnCallback)
    }, speed);
  } else if (typeof fnCallback == 'function') {
    setTimeout(fnCallback, 700);
  }
}

// start typing animation
$(document).ready(function () {
  typeWriter(typingtext, 0, function () {
    console.log('Typing complete');
  });
});