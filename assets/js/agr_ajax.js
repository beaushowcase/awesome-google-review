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
  if (current_page != 'delete-review') {
    initial_check();
  }

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

// Get the value of 'page' parameter from the URL query string
// function getPageParameter() {
//   var queryString = window.location.search;
//   var urlParams = new URLSearchParams(queryString);
//   return urlParams.get('page');
// }
// var pageValue = getPageParameter();


var current_page = ajax_object.get_url_page;
var admin_plugin_main_url = ajax_object.admin_plugin_main_url;


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
  let current_job_id = FirmNameInput.attr('data-jobid');
  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    data: {
      action: "initial_check_api",
      current_job_id: current_job_id,
      nonce: nonce,
    },
    beforeSend: function () {
      $('#loader').removeClass('hidden');
      btnProcess_API.prop("disabled", true);
    },
    success: function (response) {

      // console.log('start = '+response.data.btn_start);
      // console.log('check = '+response.data.btn_check);
      // console.log('upload = '+response.data.btn_upload);

      if (response.success && response.api) {
        if (sign_TRUE) {
          correctSign_API.addClass("visible");
          $('.api_key_setting_form').addClass("showdisable");
          BUSINESS_BOX.removeClass("hidden");
          btnProcess_API.prop("disabled", true);
        }
        if (sign_FALSE) {
          wrongSign_API.removeClass("visible");
        }
      }
      else {
        if (sign_TRUE) {
          correctSign_API.removeClass("visible");
          $('.api_key_setting_form').removeClass("showdisable");
          BUSINESS_BOX.addClass("hidden");
          btnProcess_API.prop("disabled", false);
        }
        if (sign_FALSE) {
          wrongSign_API.addClass("visible");
        }
      }

      //business CHECK

      // start 1
      if (response.data.btn_start && !response.data.btn_check && !response.data.btn_upload) {
        btnProcess_BUSINESS_START.hide();        
        btnProcess_BUSINESS_UPLOAD.hide();
        $("#google_review_upload_form .btn-process.check_start_status").show();
        btnProcess_BUSINESS_CHECK.show();        
      }
      else if (response.data.btn_start && response.data.btn_check && !response.data.btn_upload) {
        btnProcess_BUSINESS_START.hide();
        btnProcess_BUSINESS_UPLOAD.show();
        btnProcess_BUSINESS_CHECK.hide();
      }

      else if (response.data.btn_start && response.data.btn_check && response.data.btn_upload) {
        // btnProcess_BUSINESS_UPLOAD.show();
        btnProcess_BUSINESS_UPLOAD.show().find('span').text('FINISHED');
        btnProcess_BUSINESS_UPLOAD.prop("disabled", true);
        btnProcess_BUSINESS_START.show();
        btnProcess_BUSINESS_CHECK.hide();
      }




      // check 1
      // if(response.data.btn_check){
      //   console.log('check 1');
      // }
      // else{

      // }

      // // upload 1
      // if(response.data.btn_upload){
      //   console.log('upload 1');
      // }
      // else{

      // }


      // // START SHOW ONLY
      // if (response.data.btn_start && !response.data.btn_check) {
      //   if (response.data.btn_start && !response.data.btn_check && !response.data.btn_upload) {
      //     btnProcess_BUSINESS_CHECK.hide();
      //     btnProcess_BUSINESS_UPLOAD.hide();

      //     //start show only
      //     btnProcess_BUSINESS_START.show();
      //   }
      // }


      // else if (response.data.btn_start && response.data.btn_check) {
      //   //CHECK SHOW
      //   if (response.data.btn_start && response.data.btn_check && !response.data.btn_upload) {
      //     btnProcess_BUSINESS_START.hide();
      //     btnProcess_BUSINESS_UPLOAD.hide();

      //     //check show
      //     btnProcess_BUSINESS_CHECK.show();
      //   }

      //   //UPLOAD SHOW
      //   else if (response.data.btn_start && response.data.btn_check && response.data.btn_upload) {
      //     btnProcess_BUSINESS_START.hide();
      //     btnProcess_BUSINESS_CHECK.hide();

      //     //upload show          
      //     btnProcess_BUSINESS_UPLOAD.show();
      //   }
      // }


      // btnProcess_BUSINESS_START.show();
      // btnProcess_BUSINESS_CHECK.hide();

      // else if (response.success_api && response.success_business && response.btn_upload == false) {

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
  var api_status = $(element).data('apivalid');

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
      // correctSign_API.removeClass("visible");
      // wrongSign_API.removeClass("visible");
      $('.api_key_setting_form').removeClass("showdisable");
    }

    if (jQuery('.google_review_upload_form.cont').length > 0) {
      // correctSign_API.removeClass("visible");
      // wrongSign_API.addClass("visible");
      $('.api_key_setting_form').removeClass("showdisable");
    }

    // BUSINESS_BOX.addClass("hidden");
    btnProcess_API.prop("disabled", false);
    return true;
  }

  function button_effects_disable() {
    console.log("button_effects_disable!");
    if (jQuery('.google_review_upload_form.cont').length > 0) {
      // correctSign_API.addClass("visible");
      // wrongSign_API.removeClass("visible");
      $('.api_key_setting_form').addClass("showdisable");
    }

    // BUSINESS_BOX.removeClass("hidden");
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
  let flagKey = 'api';
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
      setRandomFlag(flagKey, 0);
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
            setRandomFlag(flagKey, 1);
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
// $("#google_review_upload_form button.upload_start ").click(function (event) {  
//   let firm_name_display = $(FirmNameInput).val();
//   check = true;
//   Swal.fire({
//     title: `Confirmation: Upload ?`,
//     html: `Upload your reviews now and let your voice be heard about <strong>${firm_name_display}</strong>`,
//     showCancelButton: false,
//     confirmButtonColor: "#405640",
//     cancelButtonColor: "#d33",
//     confirmButtonText: "Upload",
//     allowOutsideClick: false,
//     backdrop: 'swal2-backdrop-show',
//     showCloseButton: true,
//     icon: 'question',
//     color: "#716add",
//   }).then((result) => {
//     if (result.isConfirmed) {
//       job_start(check);
//     } else if (result.isDenied) {
//       Swal.fire("Changes are not saved", "", "info");
//     }
//   });
//   // $("#google_review_upload_form").submit(); // Manually submit the form
// });

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
          localStorage.setItem("checkval", 2);
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
  }).then(function () {
    setTimeout(function () {
      btnProcess_BUSINESS_START.show();
      btnProcess_BUSINESS_CHECK.show();
      btnProcess_BUSINESS_UPLOAD.removeClass('visible');
      btnProcess_BUSINESS_START.prop("disabled", false);
      $('.right-box .output').html(display_msg);
      $('.right-box .output').addClass('display');
      location.reload();
    }, 100);
  });
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
          localStorage.setItem("checkval", 1);
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
      // btnProcess_BUSINESS_START.addClass("spinning");
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
            // btnProcess_BUSINESS_CHECK.addClass("visible");            
          }
        } else {
          if (check) {
            response_fail(response.msg);
            // btnProcess_BUSINESS_START.prop("disabled", false);
            // btnProcess_BUSINESS_CHECK.removeClass("visible");
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

// RESET REVIEWS PROCESS
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


// RESET LOGS PROCESS
function reset_logs_success() {
  let timerInterval;
  Swal.fire({
    title: "Reset Logs !",
    html: "Checking in <b></b> milliseconds.",
    timer: 2500,
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
        title: "Reset Logs Completed",
        html: 'Reset logs data !',
        showConfirmButton: false,
        timer: 2500,
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

//delete review
function delete_start() {
  let current_job_id = FirmNameInput.attr('data-jobid');
  let firm_name = FirmNameInput.val();
  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#loader').removeClass('hidden');
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
      }, 3500);
    },
  });
}


//delete logs
function delete_logs_start() {
  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#loader').removeClass('hidden');
    },
    data: {
      action: "job_reset_logs_ajax_action",
      review_api_key: ajax_object.review_api_key,
    },
    success: function (response, status, error) {
      if (response.success === 1) {
        if (check) {
          reset_logs_success();
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
      }, 3500);
    },
  });
}


$(function () {
  // reset review
  $(document).on('click', 'p.reset', function (e) {
    e.preventDefault();
    check = true;
    Swal.fire({
      title: "Reset Review",
      html: "Resetting the review !",
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


  //reset logs
  $(document).on('click', 'p.reset.status', function (e) {
    e.preventDefault();
    check = true;
    Swal.fire({
      title: "Reset Logs",
      html: "Resetting the Logs !",
      showCloseButton: true,
      allowOutsideClick: false,
      confirmButtonColor: "rgb(230 62 50)",
      confirmButtonText: "Reset",
      backdrop: 'swal2-backdrop-show',
      icon: "question",
    }).then((result) => {
      if (result.isConfirmed) {
        delete_logs_start(check);
      }
    });


  });



});



// var typingtext = $('.output.typing').find('p').html();
// var speed = 35;

// function typeWriter(typingtext, i, fnCallback) {
//   if (i < typingtext.length) {
//     $('.typing p').html(typingtext.substring(0, i + 1));
//     setTimeout(function () {
//       typeWriter(typingtext, i + 1, fnCallback)
//     }, speed);
//   } else if (typeof fnCallback == 'function') {
//     setTimeout(fnCallback, 700);
//   }
// }

// // start typing animation
// $(document).ready(function () {
//   typeWriter(typingtext, 0, function () {
//     console.log('Typing complete');
//   });
// });







// function GetAndSet(check) {
//   // const firm_name = FirmNameInput.val().replace(/\s/g, "");
//   const firm_name = FirmNameInput.val();
//   const nonce = $("#get_set_trigger_nonce").val();
//   btnProcess_get_set.html("Loading").addClass("spinning");
//   btnProcess_get_set.prop("disabled", true);
//   btnProcess.prop("disabled", true);
//   $.ajax({
//     type: "POST",
//     url: ajax_object.ajax_url,
//     dataType: "json",
//     beforeSend: function () { },
//     data: {
//       action: "review_get_set_ajax_action2222222222",
//       firm_name: firm_name,
//       review_api_key: ajax_object.review_api_key,
//       nonce: nonce,
//     },
//     success: function (response, status, error) {
//       const correctSign = $("#google_review_upload_form .correct-sign");
//       const wrongSign = $("#google_review_upload_form .wrong-sign");
//       wrongSign.removeClass("visible");
//       correctSign.removeClass("visible");
//       if (response.success === 1) {
//         setTimeout(function () {
//           correctSign.addClass("visible");
//           toastr.success("", response.message);
//           btnProcess_get_set.removeClass("spinning").html("GET & SET");
//           btnProcess_get_set.prop("disabled", false).val("GET & SET");
//           btnProcess.prop("disabled", false);
//         }, 1500);
//       } else {
//         setTimeout(function () {
//           wrongSign.addClass("visible");
//           toastr.error("", response.message);
//           btnProcess_get_set.removeClass("spinning").html("GET & SET");
//           btnProcess_get_set.prop("disabled", false).val("GET & SET");
//           btnProcess.prop("disabled", false);
//         }, 1500);
//       }
//     },
//     error: function (xhr, status, error) {
//       var errorMessage = xhr.responseText;
//       if (errorMessage.startsWith("Error")) {
//         errorMessage = errorMessage
//           .substring(errorMessage.indexOf("Error") + 6)
//           .trim();
//       }
//       toastr.error(errorMessage || "An error occurred", "Error");
//     },
//     complete: function () { },
//   });
// }


//UPLOAD REVIEWS
// Assuming ".get" is the class of the button you want to trigger the form submission
$("#google_review_upload_form button.upload_start").click(function (event) {
  check = true;
  Swal.fire({
    title: "Confirmation: Upload Job?",
    html: "Start to upload the reviews of " + `<b>${$(FirmNameInput).val()}</b>` + " !",
    showCloseButton: true,
    allowOutsideClick: false,
    confirmButtonColor: "#405640",
    confirmButtonText: "Check",
    backdrop: 'swal2-backdrop-show',
    icon: "question",
  }).then((result) => {
    if (result.isConfirmed) {
      upload_process_box(check);
    }
  });
  $("#google_review_upload_form").submit();
});




function upload_process_box(check) {
  console.log('check = ' + check);
  let flagKey = 'upload';

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
      setRandomFlag(flagKey, 0);
    },
    data: {
      action: "review_get_set_ajax_action",
      current_job_id: current_job_id,
      review_api_key: ajax_object.review_api_key,
      nonce: nonce,
    },
    success: function (response, status, error) {

      // console.log('heeeeeeeeeeeeee'+response);
      // return false;

      setTimeout(function () {
        if (response.success === 1) {
          if (check) {
            response_upload_success(response.msg);
            setRandomFlag(flagKey, 1);
          }
        } else {
          if (check) {
            response_business_fail(response.msg);
          }
        }
      }, 3500);
    },
    error: function (xhr, status, error) {
      // Swal.fire({
      //   position: "top-end",
      //   icon: "error",
      //   title: response.msg,
      //   showConfirmButton: false,
      //   timer: 3500
      // });
    },
    complete: function () {
      setTimeout(function () {
        $('#loader').addClass('hidden');
        btnProcess_BUSINESS_CHECK.removeClass("spinning");
      }, 3500);
    },
  });

}


//GMB call
function response_upload_success(response) {
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
        backdrop: 'swal2-backdrop-show',
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



// celebration effect

(function () {
  // globals
  var canvas;
  var ctx;
  var W;
  var H;
  var mp = 150; //max particles
  var particles = [];
  var angle = 0;
  var tiltAngle = 0;
  var confettiActive = true;
  var animationComplete = true;
  var deactivationTimerHandler;
  var reactivationTimerHandler;
  var animationHandler;

  // colors
  var calypso = "#00a4bd";
  var sorbet = "#ff8f59";
  var lorax = "#ff7a59";
  var marigold = "#f5c26b";
  var candy_apple = "#f2545b";
  var norman = "#f2547d";
  var thunderdome = "#6a78d1";
  var oz = "00bda5";


  // objects


  var particleColors = {
    colorOptions: [
      calypso,
      sorbet,
      lorax,
      marigold,
      candy_apple,
      norman,
      thunderdome,
      oz
    ],
    colorIndex: 0,
    colorIncrementer: 0,
    colorThreshold: 10,
    getColor: function () {
      if (this.colorIncrementer >= 10) {
        this.colorIncrementer = 0;
        this.colorIndex++;
        if (this.colorIndex >= this.colorOptions.length) {
          this.colorIndex = 0;
        }
      }
      this.colorIncrementer++;
      return this.colorOptions[this.colorIndex];
    }
  }

  function confettiParticle(color) {
    this.x = Math.random() * W; // x-coordinate
    this.y = (Math.random() * H) - H; //y-coordinate
    this.r = RandomFromTo(10, 30); //radius;
    this.d = (Math.random() * mp) + 10; //density;
    this.color = color;
    this.tilt = Math.floor(Math.random() * 10) - 10;
    this.tiltAngleIncremental = (Math.random() * 0.07) + .05;
    this.tiltAngle = 0;

    this.draw = function () {
      ctx.beginPath();
      ctx.lineWidth = this.r / 2;
      ctx.strokeStyle = this.color;
      ctx.moveTo(this.x + this.tilt + (this.r / 4), this.y);
      ctx.lineTo(this.x + this.tilt, this.y + this.tilt + (this.r / 4));
      return ctx.stroke();
    }
  }

  $(document).ready(function () {
    SetGlobals();
    InitializeButton();
    //InitializeConfetti();

    $(window).resize(function () {
      W = window.innerWidth;
      H = window.innerHeight;
      canvas.width = W;
      canvas.height = H;
    });

  });

  function InitializeButton() {
    function handler1() {
      RestartConfetti();
      $(this).text('Cops are here!');
      $(this).one("click", handler2);
    }

    function handler2() {
      DeactivateConfetti();
      $(this).text('Celebrate!');
      $(this).one("click", handler1);
    }
    $("button.control").one("click", handler1);
  };

  function SetGlobals() {
    canvas = document.getElementById("canvas");
    ctx = canvas.getContext("2d");
    W = window.innerWidth;
    H = window.innerHeight;
    canvas.width = W;
    canvas.height = H;
  }

  function InitializeConfetti() {
    particles = [];
    animationComplete = false;
    for (var i = 0; i < mp; i++) {
      var particleColor = particleColors.getColor();
      particles.push(new confettiParticle(particleColor));
    }
    StartConfetti();
  }

  function Draw() {
    ctx.clearRect(0, 0, W, H);
    var results = [];
    for (var i = 0; i < mp; i++) {
      (function (j) {
        results.push(particles[j].draw());
      })(i);
    }
    Update();

    return results;
  }

  function RandomFromTo(from, to) {
    return Math.floor(Math.random() * (to - from + 1) + from);
  }


  function Update() {
    var remainingFlakes = 0;
    var particle;
    angle += 0.01;
    tiltAngle += 0.1;

    for (var i = 0; i < mp; i++) {
      particle = particles[i];
      if (animationComplete) return;

      if (!confettiActive && particle.y < -15) {
        particle.y = H + 100;
        continue;
      }

      stepParticle(particle, i);

      if (particle.y <= H) {
        remainingFlakes++;
      }
      CheckForReposition(particle, i);
    }

    if (remainingFlakes === 0) {
      StopConfetti();
    }
  }

  function CheckForReposition(particle, index) {
    if ((particle.x > W + 20 || particle.x < -20 || particle.y > H) && confettiActive) {
      if (index % 5 > 0 || index % 2 == 0) //66.67% of the flakes
      {
        repositionParticle(particle, Math.random() * W, -10, Math.floor(Math.random() * 10) - 10);
      } else {
        if (Math.sin(angle) > 0) {
          //Enter from the left
          repositionParticle(particle, -5, Math.random() * H, Math.floor(Math.random() * 10) - 10);
        } else {
          //Enter from the right
          repositionParticle(particle, W + 5, Math.random() * H, Math.floor(Math.random() * 10) - 10);
        }
      }
    }
  }
  function stepParticle(particle, particleIndex) {
    particle.tiltAngle += particle.tiltAngleIncremental;
    particle.y += (Math.cos(angle + particle.d) + 3 + particle.r / 2) / 2;
    particle.x += Math.sin(angle);
    particle.tilt = (Math.sin(particle.tiltAngle - (particleIndex / 3))) * 15;
  }

  function repositionParticle(particle, xCoordinate, yCoordinate, tilt) {
    particle.x = xCoordinate;
    particle.y = yCoordinate;
    particle.tilt = tilt;
  }

  function StartConfetti() {
    W = window.innerWidth;
    H = window.innerHeight;
    canvas.width = W;
    canvas.height = H;
    (function animloop() {
      if (animationComplete) return null;
      animationHandler = requestAnimFrame(animloop);
      return Draw();
    })();
  }

  function ClearTimers() {
    clearTimeout(reactivationTimerHandler);
    clearTimeout(animationHandler);
  }

  function DeactivateConfetti() {
    confettiActive = false;
    ClearTimers();
  }

  function StopConfetti() {
    animationComplete = true;
    if (ctx == undefined) return;
    ctx.clearRect(0, 0, W, H);
  }

  function RestartConfetti() {
    ClearTimers();
    StopConfetti();
    reactivationTimerHandler = setTimeout(function () {
      confettiActive = true;
      animationComplete = false;
      InitializeConfetti();
    }, 100);

  }

  window.requestAnimFrame = (function () {
    return window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame || function (callback) {
      return window.setTimeout(callback, 1000 / 60);
    };
  })();
})();

function success_celebration() {
  $("button.control").click();
  setTimeout(function () {
    $("button.control").click();
  }, 3500);
}



// generate token

function setRandomFlag(rec, flag) {
  if (flag === 0 || flag === 1) {
    sessionStorage.setItem(`${rec}-flag`, flag);
    console.log(`${rec}-flag set: `, flag);
  } else {
    console.error('Invalid manage flag. Please provide 0 or 1.');
  }
}
function getRandomFlag(rec) {
  var flag = sessionStorage.getItem(`${rec}-flag`);
  console.log(`${rec}-flag retrieved : `, flag);
  return flag;
}

$(document).ready(function () {
  $(window).one('load', function () {
    let get_api_token = sessionStorage.getItem(`api-flag`);
    let get_job_token = sessionStorage.getItem(`upload-flag`);
    if (get_api_token == 1 || get_job_token == 1) {
      console.log('fired !');
      success_celebration();
      setRandomFlag('api', 0);
      setRandomFlag('upload', 0);
    }
  });
});


function error_notify() {
  Swal.fire({
    position: "top-end",
    icon: "error",
    title: "please select !",
    showConfirmButton: false,
    timer: 1500
  });
}


//DELETE REVIEWS
function review_delete_process(check,$this) {
  var selected_value = $this.find(":selected").val();
  var selected_value_name  = $this.find(":selected").text();
  console.log(selected_value_name);
  if (selected_value == 0) {
    error_notify(); 
  }
  else {    
    check = true;
    Swal.fire({
      title: "Delete Review?",
      html: "Start to delete the reviews data of " + `<b>${selected_value_name}</b>` + " !",
      showCloseButton: true,
      allowOutsideClick: false,
      confirmButtonColor: "#e63e32",
      confirmButtonText: "Delete",
      backdrop: 'swal2-backdrop-show',
      icon: "question",
    }).then((result) => {
      if (result.isConfirmed) {
        delete_review_start(selected_value);
      }
    });
  }
};


$("#review_delete_form").submit(function (event) {  
  var $this = jQuery(this);
  event.preventDefault();
  check = true;
  review_delete_process(check,$this);
});

//delete review
function delete_review_start(id) {
  let current_term_id = id;  
  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#loader').removeClass('hidden');
    },
    data: {
      action: "job_review_delete_ajax_action",
      current_term_id: current_term_id,
      review_api_key: ajax_object.review_api_key,      
    },
    success: function (response, status, error) {
      if (response.success === 1) {
        if (check) {
          delete_reviews_success();
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
      }, 3500);
    },
  });
}



// RESET REVIEWS PROCESS
function delete_reviews_success() {
  let timerInterval;
  Swal.fire({
    title: "Delete Reviews Data !",
    html: "Deleting in <b></b> milliseconds.",
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
        title: "Delete Completed",
        // html: 'Delete reviews data !',
        showConfirmButton: false,
        timer: 3500,
        allowOutsideClick: false,
        grow: false,
      }).then(function () {
        setTimeout(function () {
          window.location.href = admin_plugin_main_url;
          // location.reload();
        }, 100);
      });
    }
  });
  return true;
}




$(document).ready(function(){
  $('input[type="radio"][name="radio3"]').change(function(){
      var selectedValue = $(this).val();
      // alert("Selected value: " + selectedValue);
  });
});




//countdown
// $(document).ready(function() {   
//   const button = $('.check_start');
//   var checkval = localStorage.getItem("checkval");  
//   if(checkval == 0){
//     button.prop('disabled', false);
//   }
//   if(checkval == 1){    
//     countdown();    
//   }
//   if(checkval == 2){    
//     localStorage.removeItem("checkval");
//   }
  
//   function countdown() {
//       let seconds = 30;      
//       const interval = setInterval(() => {
//           button.find('.label').text(`CHECK (${seconds})`);
//           if (seconds <= 0) {
//               clearInterval(interval);
//               button.prop('disabled', false);
//               button.find('.label').text('CHECK');       
//               localStorage.setItem("checkval", 0);       
//           }
//           seconds--;          
//       }, 800);
//   }
// });


// status update
$(document).on('click', 'button.check_start_status', function (e) {
  e.preventDefault();
  check = true;
  Swal.fire({
    title: "Check Status",
    html: "Checking the status !",
    showCloseButton: true,
    allowOutsideClick: false,
    confirmButtonColor: "#008000",
    confirmButtonText: "Check Status",
    backdrop: 'swal2-backdrop-show',
    icon: "info",
  }).then((result) => {
    if (result.isConfirmed) {
      check_status_update(check);
    }
  });
});


//delete review
function check_status_update() {
  let current_job_id = FirmNameInput.attr('data-jobid');  
  let firm_name = FirmNameInput.val();
  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#loader').removeClass('hidden');
    },
    data: {
      action: "job_check_status_update_ajax_action",
      current_job_id: current_job_id,
      review_api_key: ajax_object.review_api_key,
      firm_name: firm_name
    },
    success: function (response, status, error) {
      if (response.success === 1) {
        if (check) {
          check_success();
        }
      } else {
        if (check) {
          check_failed(response.msg);
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
      }, 3500);
    },
  });
}


function check_success() {
  let timerInterval;
  Swal.fire({
    title: "Status Checking...",
    html: "<br>Checking in <b></b> milliseconds.",
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
        title: "Check Completed",
        html: 'Checked status !',
        showConfirmButton: false,
        timer: 3500,
        allowOutsideClick: false,
        grow: false,
      }).then(function () {
        setTimeout(function () {
          localStorage.setItem("checkval", 1);
          location.reload();
        }, 100);
      });
    }
  });
  return true;
}


function check_failed(response) {
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
  }).then(function () {
    setTimeout(function () { 
      localStorage.setItem("checkval",0);  
      location.reload();
    }, 100);
  });
}