$ = jQuery.noConflict();

let check = false;
let reviewApiKeyInput = $("#review_api_key");
let FirmNameInput = $("#firm_name");

let btnProcess_check = $("#google_review_upload_form .check_start");

var current_page = ajax_object.get_url_page;
var admin_plugin_main_url = ajax_object.admin_plugin_main_url;

jQuery(document).ready(function ($) {
  // $('#processbar').hide();  
  if (current_page != 'delete-review' && current_page != 'review-cron-job') {
    console.log(current_page);
    initial_check();
    upload_done_process();
  }
});

function upload_done_process() {
  var urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('uploaded') && urlParams.get('uploaded') === 'true' && urlParams.has('slug') && urlParams.get('slug') !== '' && urlParams.has('page') && urlParams.get('page') === 'awesome-google-review') {
    setTimeout(function () {
      var newUrl = ajax_object.main_site_url + "/wp-admin/edit.php?business=" + urlParams.get('slug') + "&post_type=agr_google_review";
      window.location.href = newUrl;
    }, 5000);

  }
}

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
      // btnProcess_API.prop("disabled", true);
    },
    success: function (response) {
      if (response.data.btn_check == 0 && response.data.btn_check_status == 1 && response.data.btn_start == 1 && response.data.btn_upload == 0) {
        $('.check_start_status').prop('disabled', true);
      }

      if (response.success && response.api) {
        if (sign_TRUE) {
          correctSign_API.addClass("visible");
          BUSINESS_BOX.removeClass("hidden");
        }
        if (sign_FALSE) {
          wrongSign_API.removeClass("visible");
        }
      }
      else {
        if (sign_TRUE) {
          correctSign_API.removeClass("visible");
          BUSINESS_BOX.addClass("hidden");
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
// key up check API KEY
let element = jQuery("#review_api_key");

//api save call
function response_success(response) {
  Swal.fire({
    icon: "success",
    position: 'bottom-end',
    title: response,
    showConfirmButton: false,
    allowOutsideClick: false,
    grow: false,
    timer: 1500,
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
    timer: 1500,
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
      $('#processbar').show();
      $('#loader').removeClass('hidden');
      // btnProcess_API.addClass("spinning");
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
            $('#processbar').hide();
            // setRandomFlag(flagKey, 1);
          }
        } else {
          if (check) {
            response_fail(response.msg);
            correctSign_API.removeClass("visible");
            wrongSign_API.addClass("visible");
            BUSINESS_BOX.addClass("hidden");
            $('#processbar').hide();
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
        // btnProcess_API.removeClass("spinning");
        btnProcess_API.prop("disabled", false);
        $('#processbar').hide();
        location.reload();
      }, 3000);
    },
  });

}

$("#google_review_upload_form").submit(function (event) {
  event.preventDefault(); // Prevent the default form submission behavior  
});

var spinnerSVG = `<svg class="svg-loader" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" xml:space="preserve"><path fill="#fff" d="M10 40v-3.2c0-.3.1-.6.1-.9.1-.6.1-1.4.2-2.1.2-.8.3-1.6.5-2.5.2-.9.6-1.8.8-2.8.3-1 .8-1.9 1.2-3 .5-1 1.1-2 1.7-3.1.7-1 1.4-2.1 2.2-3.1 1.6-2.1 3.7-3.9 6-5.6 2.3-1.7 5-3 7.9-4.1.7-.2 1.5-.4 2.2-.7.7-.3 1.5-.3 2.3-.5.8-.2 1.5-.3 2.3-.4l1.2-.1.6-.1h.6c1.5 0 2.9-.1 4.5.2.8.1 1.6.1 2.4.3.8.2 1.5.3 2.3.5 3 .8 5.9 2 8.5 3.6 2.6 1.6 4.9 3.4 6.8 5.4 1 1 1.8 2.1 2.7 3.1.8 1.1 1.5 2.1 2.1 3.2.6 1.1 1.2 2.1 1.6 3.1.4 1 .9 2 1.2 3 .3 1 .6 1.9.8 2.7.2.9.3 1.6.5 2.4.1.4.1.7.2 1 0 .3.1.6.1.9.1.6.1 1 .1 1.4.4 1 .4 1.4.4 1.4.2 2.2-1.5 4.1-3.7 4.3s-4.1-1.5-4.3-3.7V37.2c0-.2-.1-.5-.1-.8-.1-.6-.1-1.2-.2-1.9s-.3-1.4-.4-2.2c-.2-.8-.5-1.6-.7-2.4-.3-.8-.7-1.7-1.1-2.6-.5-.9-.9-1.8-1.5-2.7-.6-.9-1.2-1.8-1.9-2.7-1.4-1.8-3.2-3.4-5.2-4.9-2-1.5-4.4-2.7-6.9-3.6-.6-.2-1.3-.4-1.9-.6-.7-.2-1.3-.3-1.9-.4-1.2-.3-2.8-.4-4.2-.5h-2c-.7 0-1.4.1-2.1.1-.7.1-1.4.1-2 .3-.7.1-1.3.3-2 .4-2.6.7-5.2 1.7-7.5 3.1-2.2 1.4-4.3 2.9-6 4.7-.9.8-1.6 1.8-2.4 2.7-.7.9-1.3 1.9-1.9 2.8-.5 1-1 1.9-1.4 2.8-.4.9-.8 1.8-1 2.6-.3.9-.5 1.6-.7 2.4-.2.7-.3 1.4-.4 2.1-.1.3-.1.6-.2.9 0 .3-.1.6-.1.8 0 .5-.1.9-.1 1.3-.2.7-.2 1.1-.2 1.1z"><animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 40 40" to="360 40 40" dur="0.8s" repeatCount="indefinite"/></path><path fill="#fff" d="M62 40.1s0 .2-.1.7c0 .2 0 .5-.1.8v.5c0 .2-.1.4-.1.7-.1.5-.2 1-.3 1.6-.2.5-.3 1.1-.5 1.8-.2.6-.5 1.3-.7 1.9-.3.7-.7 1.3-1 2.1-.4.7-.9 1.4-1.4 2.1-.5.7-1.1 1.4-1.7 2-1.2 1.3-2.7 2.5-4.4 3.6-1.7 1-3.6 1.8-5.5 2.4-2 .5-4 .7-6.2.7-1.9-.1-4.1-.4-6-1.1-1.9-.7-3.7-1.5-5.2-2.6s-2.9-2.3-4-3.7c-.6-.6-1-1.4-1.5-2-.4-.7-.8-1.4-1.2-2-.3-.7-.6-1.3-.8-2l-.6-1.8c-.1-.6-.3-1.1-.4-1.6-.1-.5-.1-1-.2-1.4-.1-.9-.1-1.5-.1-2v-.7s0 .2.1.7c.1.5 0 1.1.2 2 .1.4.2.9.3 1.4.1.5.3 1 .5 1.6.2.6.4 1.1.7 1.8.3.6.6 1.2.9 1.9.4.6.8 1.3 1.2 1.9.5.6 1 1.3 1.6 1.8 1.1 1.2 2.5 2.3 4 3.2 1.5.9 3.2 1.6 5 2.1 1.8.5 3.6.6 5.6.6 1.8-.1 3.7-.4 5.4-1 1.7-.6 3.3-1.4 4.7-2.4 1.4-1 2.6-2.1 3.6-3.3.5-.6.9-1.2 1.3-1.8.4-.6.7-1.2 1-1.8.3-.6.6-1.2.8-1.8.2-.6.4-1.1.5-1.7l.3-1.5c.1-.4.1-.8.1-1.2 0-.2 0-.4.1-.5v-2c0-1.1.9-2 2-2s2 .9 2 2c.1-.1.1 0 .1 0z"><animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 40 40" to="-360 40 40" dur="0.6s" repeatCount="indefinite"/></path></svg>`;

$("#google_review_upload_form button.search_btn").click(function (event) {
  $('.submit_btn_setget button.job_start').prop("disabled", true);
  var firm_name = $(FirmNameInput).val();
  $('.submit_btn.job_start.btn-process').removeClass('next_highlight');
  if (firm_name.trim() != '') {
    $.ajax({
      url: 'https://api.spiderdunia.com:3000/search/',
      // url: 'http://localhost:3000/search/',
      type: 'GET',
      data: {
        api_key: ajax_object.review_api_key,
        businessName: firm_name
      },
      beforeSend: function () {
        var search_box = `<div class="search--txt">${spinnerSVG}</div>`;
        $('.search-result').empty().append(search_box);
        $('#processbar').show();
        $('.search_btn').prop("disabled", true);
      },
      success: function (response) {
        console.log('API Call Successful:', response);
        if (response.success === 1) {
          $('.search-result').empty().show();
          var result_html = `          
          <div class="search--txt" onclick="selectbusiness()">
            <div class="close-button" onclick="closePopup()">x</div>
                                      <p><strong>Title</strong>: ${response.data.businessTitle}</p>
                                      <p><strong>Address</strong>: ${response.data.businessAddress}</p>
                                      <p><strong>Total</strong>: ${response.data.totalReviews}</p>
                                  </div>`;
          $('.search-result').empty().append(result_html);
          $('.submit_btn_setget button.job_start').prop("disabled", false);
          $('.google_review_upload_form span.wrong-sign').addClass('firm_area_sign').hide();
          $('.google_review_upload_form span.correct-sign').addClass('firm_area_sign').show();
          notify_success('Ready. Please proceed.');
          $('.submit_btn.job_start.btn-process').addClass('next_highlight');
        } else {
          $('.search-result').empty().show();
          $('.search-result').empty().append(`<div class="search--txt">No results found <div class="close-button" onclick="closePopup()">x</div></div>`);
          $('.google_review_upload_form span.correct-sign').addClass('firm_area_sign').hide();
          $('.google_review_upload_form span.wrong-sign').addClass('firm_area_sign').show();
          $('.submit_btn.job_start.btn-process').removeClass('next_highlight');
        }
      },
      error: function (xhr, status, error) {
        console.error('API Call Failed:', status, error);
        if (error === 'Too Many Requests') {
          error = 'Too Many Requests , please try after 60 seconds.';
        }
        $('.search-result').empty().append(`<div class="search--txt">API Call Failed: ${error}</div>`);
      },
      complete: function () {
        $('#processbar').hide();
        $('.search_btn').prop("disabled", false);
      }
    });

  }

});

function closePopup() {
  $('.search-result').hide(); // Assuming you want to hide the entire search-result container
}

function notify_success(msg) {
  const Toast = Swal.mixin({
    toast: true,
    position: "bottom-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.onmouseenter = Swal.stopTimer;
      toast.onmouseleave = Swal.resumeTimer;
    }
  });
  Toast.fire({
    icon: "success",
    title: msg
  });
}


function notify_failed(msg) {
  const Toast = Swal.mixin({
    toast: true,
    position: "bottom-end",
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.onmouseenter = Swal.stopTimer;
      toast.onmouseleave = Swal.resumeTimer;
    }
  });
  Toast.fire({
    icon: "error",
    title: msg
  });
}

function notify_success_cron(msg) {
  const Toast = Swal.mixin({
    toast: true,
    position: "bottom-end",
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.onmouseenter = Swal.stopTimer;
      toast.onmouseleave = Swal.resumeTimer;
    }
  });
  Toast.fire({
    icon: "success",
    title: msg
  });
}



function selectbusiness() {
  $('.search-result').empty().hide();
  $('.submit_btn_setget button.job_start').prop("disabled", false);
}
// JOB START CLICKED 
$("#google_review_upload_form button.job_start").click(function (event) {
  var firm_name = $(FirmNameInput).val();
  if (firm_name.trim() != '') {
    check = true;
    Swal.fire({
      title: "Confirmation: Initiate Job?",
      text: "Are you certain about initiating this job? Once completed, you'll be able to upload reviews.",
      showCancelButton: false,
      showCloseButton: true,
      confirmButtonColor: "#405640",
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
    $("#google_review_upload_form").submit();
  }
});

// Assuming ".get" is the class of the button you want to trigger the form submission
$("#google_review_upload_form button.check_start").click(function (event) {
  check = true;
  Swal.fire({
    title: "GET",
    html: "Let's begin gathering reviews for " + `<b>${$(FirmNameInput).val()}</b>` + " !",
    showCloseButton: true,
    allowOutsideClick: false,
    confirmButtonColor: "#405640",
    confirmButtonText: "GET",
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
    html: "Getting in <b></b> milliseconds.",
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
  const nonce = $("#get_set_trigger_nonce").val();

  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#loader').removeClass('hidden');
      $('#processbar').show();
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
        $('#processbar').hide();
      }, 3500);
    },
    error: function (xhr, status, error) {
      Swal.fire({
        position: 'bottom-end',
        icon: "error",
        title: response.msg,
        showConfirmButton: false,
        timer: 3500
      });
      $('#processbar').hide();
    },
    complete: function () {
      setTimeout(function () {
        $('#loader').addClass('hidden');
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
  const firm_name = encodeURIComponent(FirmNameInput.val());
  console.log('firm_name ===' + firm_name);
  const nonce = $("#get_set_trigger_nonce").val();

  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#loader').removeClass('hidden');
      $('#processbar').show();
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
            $('#processbar').hide();
          }
        } else {
          if (check) {
            response_fail(response.msg);
            $('#processbar').hide();
          }

        }
      }, 3500);
    },
    error: function (xhr, status, error) {
      Swal.fire({
        position: 'bottom-end',
        icon: "error",
        title: response.msg,
        showConfirmButton: false,
        timer: 3500
      });
    },
    complete: function () {
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
          localStorage.removeItem("checkval");
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
        position: 'bottom-end',
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
        position: 'bottom-end',
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
      $('#processbar').show();
    },
    data: {
      action: "job_reset_logs_ajax_action",
      review_api_key: ajax_object.review_api_key,
    },
    success: function (response, status, error) {
      if (response.success === 1) {
        if (check) {
          reset_logs_success();
          $('#processbar').hide();
        }
      } else {
        if (check) {
          response_business_fail(response.msg);
          $('#processbar').hide();
        }
      }
    },
    error: function (xhr, status, error) {
      Swal.fire({
        position: 'bottom-end',
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

//UPLOAD REVIEWS
$("#google_review_upload_form button.upload_start").click(function (event) {
  check = true;
  Swal.fire({
    title: "Upload Reviews?",
    html: "Initiate the uploading process for " + `<b>${$(FirmNameInput).val()}</b>` + " !",
    showCloseButton: true,
    allowOutsideClick: false,
    confirmButtonColor: "#405640",
    confirmButtonText: "Upload",
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
  const nonce = $("#get_set_trigger_nonce").val();
  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#loader').removeClass('hidden');
      btnProcess_BUSINESS_CHECK.addClass("spinning");
      setRandomFlag(flagKey, 0);
      $('#processbar').show();
    },
    data: {
      action: "review_get_set_ajax_action",
      current_job_id: current_job_id,
      review_api_key: ajax_object.review_api_key,
      nonce: nonce,
    },
    success: function (response, status, error) {
      setTimeout(function () {
        if (response.success === 1) {
          if (check) {
            setRandomFlag(flagKey, 1);
            setTimeout(function () {
              response_upload_success(response.message, response.term_slug);
            }, 1500);
          }
        } else {
          if (check) {
            response_business_fail(response.message);
          }
        }
        $('#processbar').hide();
      }, 2500);
    },
    error: function (xhr, status, error) {
      $('#processbar').hide();
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
function response_upload_success(response, termslug) {
  let timerInterval;
  Swal.fire({
    title: "Google Reviews !",
    html: "Uploading in <b></b> milliseconds.",
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
      let display_msg = response;
      console.log("I was closed by the timer");
      Swal.fire({
        icon: "success",
        title: "Completed",
        html: display_msg,
        showConfirmButton: false,
        timer: 2500,
        allowOutsideClick: false,
        grow: false,
        backdrop: 'swal2-backdrop-show',
      }).then(function () {
        setTimeout(function () {
          var currentUrl = window.location.href;
          // var newUrl = currentUrl + "?business=" + termslug + "&post_type=agr_google_review&uploaded=true";
          var newUrl = currentUrl + "&slug=" + termslug + "&uploaded=true";
          window.location.href = newUrl;
        }, 100);
      });
    }
  });
  return true;
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

function error_notify() {
  Swal.fire({
    position: 'bottom-end',
    icon: "error",
    title: "please select !",
    showConfirmButton: false,
    timer: 1500
  });
}


//DELETE REVIEWS
function review_delete_process(check, $this) {
  var selected_value = $this.find(":selected").val();
  var selected_value_name = $this.find(":selected").text();
  console.log(selected_value_name);
  if (selected_value == 0) {
    error_notify();
  }
  else {
    check = true;
    Swal.fire({
      title: "Delete Review?",
      html: "Initiate deletion of the review data for " + `<b>${selected_value_name}</b>` + " !",
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
  review_delete_process(check, $this);
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
      $('#processbar').show();
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
      $('#processbar').hide();
    },
    error: function (xhr, status, error) {
      Swal.fire({
        position: 'bottom-end',
        icon: "error",
        title: response.msg,
        showConfirmButton: false,
        timer: 3500
      });
      $('#processbar').hide();
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
        position: 'bottom-end',
      }).then(function () {
        setTimeout(function () {
          // window.location.href = admin_plugin_main_url;
          location.reload();
        }, 100);
      });
    }
  });
  return true;
}

// status update
$(document).on('click', 'button.check_start_status', function (e) {
  e.preventDefault();
  check = true;
  Swal.fire({
    title: "Check Status",
    html: "Checking the status !",
    showCloseButton: true,
    allowOutsideClick: false,
    confirmButtonColor: "#405640",
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
  // const firm_name = FirmNameInput.val();
  const nonce = $("#get_set_trigger_nonce").val();

  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#loader').removeClass('hidden');
      $('#processbar').show();
    },
    data: {
      action: "job_check_status_update_ajax_action",
      current_job_id: current_job_id,
      review_api_key: ajax_object.review_api_key,
      nonce: nonce,
    },
    success: function (response, status, error) {
      setTimeout(function () {
        if (response.success === 1) {
          if (check) {
            check_success(response.msg);
          }
        } else {
          if (check) {
            check_failed(response.msg);
          }
        }
        $('#processbar').hide();
      }, 3500);
    },
    error: function (xhr, status, error) {
      Swal.fire({
        position: 'bottom-end',
        icon: "error",
        title: response.msg,
        showConfirmButton: false,
        timer: 3500
      });
      $('#processbar').hide();
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
        position: 'bottom-end',
      }).then(function () {
        setTimeout(function () {
          localStorage.setItem("checkval", 0);
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
      localStorage.setItem("checkval", 0);
      location.reload();
    }, 100);
  });
}



// upload celebration = START
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
    if (!canvas) {
      return;
    }
    ctx = canvas.getContext("2d");
    if (!ctx) {
      return;
    }
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
// upload celebration = END


// check start status
$(document).ready(function () {
  const button = $('.check_start_status');
  let checkval = localStorage.getItem("checkval");

  if (!checkval || checkval == '0') {
    button.prop('disabled', false);
  } else if (checkval == '1') {
    countdown();
    button.prop('disabled', true);
  } else if (checkval == '2') {
    localStorage.removeItem("checkval");
  }
});

function countdown() {
  const button = $('.check_start_status');
  let targetTime = localStorage.getItem("targetTime");

  if (!targetTime) {
    let now = new Date().getTime();
    targetTime = now + 30000;
    localStorage.setItem("targetTime", targetTime);
  } else {
    targetTime = parseInt(targetTime, 10);
  }

  const interval = setInterval(() => {
    let now = new Date().getTime();
    let seconds = Math.round((targetTime - now) / 1000);

    if (seconds <= 0) {
      clearInterval(interval);
      button.prop('disabled', false);
      button.find('.label').text('CHECK STATUS');
      localStorage.setItem("checkval", 0);
      localStorage.removeItem("targetTime");
    } else {
      button.find('.label').text(`CHECK STATUS (${seconds})`);
      button.prop('disabled', true);
      localStorage.setItem("checkval", 1);
    }
  }, 1000);
}

// CRON START CLICKED 
let recurrence = 0;
let timeSlot = 0;
let cron_switch = $("#cron_switch");

$(cron_switch).change(function () {
  let is_checked = $(this).is(':checked');
  if (is_checked) {
    box_for_schedule(is_checked, recurrence);
  } else {
    fire_cron(recurrence, timeSlot, is_checked);
  }
});

function box_for_schedule(is_checked, selectedRecurrence) {
  Swal.fire({
    title: "Recurrence",
    iconHtml: '<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet"><i class="fa fa-cog fa-spin" style="color: rgb(140, 194, 19); font-size: 40px; text-shadow: rgb(0, 0, 0) 0px 0px 4px; box-sizing: content-box; line-height: 72px; text-align: center; width: 72px; height: 72px; display: inline-block; overflow: hidden; border-radius: 50%; border-color: rgb(255, 255, 255); border-style: solid; border-width: 1px;"></i>',
    input: "select",
    inputOptions: {
      norepeat: "-- Select Recurrence --",
      hourly: "Once Hourly (hourly)",
      twicedaily: "Twice Daily (twicedaily)",
      daily: "Once Daily (daily)",
      weekly: "Once Weekly (weekly)",
    },
    inputValue: selectedRecurrence || "norepeat",
    customClass: {
      confirmButton: 'cron_confirm_btn',
    },
    showCancelButton: false,
    showCloseButton: true,
    confirmButtonColor: "#405640",
    cancelButtonColor: "#d33",
    confirmButtonText: "Next",
    allowOutsideClick: false,
    backdrop: 'swal2-backdrop-show',
    icon: 'question',
    color: "#716add",
    animation: true,
    didClose: () => {
      // location.reload();
    },
    inputValidator: (value) => {
      return new Promise((resolve) => {
        if (value != "norepeat") {
          resolve();
        } else {
          resolve("You need to select Recurrence !");
        }
      });
    }
  }).then((result) => {
    if (result.isConfirmed) {
      recurrence = result.value;
      show_time_slot_selection(is_checked, recurrence);
    }
    else {
      if (result.isDismissed) {
        location.reload();
      }
    }
  });
}

function show_time_slot_selection(is_checked, selectedRecurrence) {
  const timeMapping = {    
    "1:00 AM": "01:00:00",    
    "1:30 AM": "01:30:00",
    "2:00 AM": "02:00:00",
    "2:30 AM": "02:30:00",
    "3:00 AM": "03:00:00",
    "3:30 AM": "03:30:00",
    "4:00 AM": "04:00:00",
    "4:30 AM": "04:30:00",
    "5:00 AM": "05:00:00",
    "5:30 AM": "05:30:00",
    "6:00 AM": "06:00:00",
    "6:30 AM": "06:30:00",
    "7:00 AM": "07:00:00",
    "7:30 AM": "07:30:00",
    "8:00 AM": "08:00:00",
    "8:30 AM": "08:30:00",
    "9:00 AM": "09:00:00",
    "9:30 AM": "09:30:00",
    "10:00 AM": "10:00:00",
    "10:30 AM": "10:30:00",
    "11:00 AM": "11:00:00",
    "11:30 AM": "11:30:00",   
    "12:00 PM": "12:00:00",
    "12:30 PM": "12:30:00",
    "1:00 PM": "13:00:00",
    "1:30 PM": "13:30:00",
    "2:00 PM": "14:00:00",
    "2:30 PM": "14:30:00",
    "3:00 PM": "15:00:00",
    "3:30 PM": "15:30:00",
    "4:00 PM": "16:00:00",
    "4:30 PM": "16:30:00",
    "5:00 PM": "17:00:00",
    "5:30 PM": "17:30:00",
    "6:00 PM": "18:00:00",
    "6:30 PM": "18:30:00",
    "7:00 PM": "19:00:00",
    "7:30 PM": "19:30:00",
    "8:00 PM": "20:00:00",
    "8:30 PM": "20:30:00",
    "9:00 PM": "21:00:00",
    "9:30 PM": "21:30:00",
    "10:00 PM": "22:00:00",
    "10:30 PM": "22:30:00",
    "11:00 PM": "23:00:00",
    "11:30 PM": "23:30:00",
  };

  Swal.fire({
    title: "Select Time Slot",
    input: "select",
    inputOptions: {
      "": "-- Select time slot --",
      ...Object.fromEntries(Object.entries(timeMapping).map(([key, value]) => [key, key]))
    },
    inputValue: timeSlot,
    customClass: {
      confirmButton: 'cron_confirm_btn',
    },
    showCancelButton: true,
    showCloseButton: true,
    confirmButtonColor: "#405640",
    cancelButtonColor: "#d33",
    confirmButtonText: "Save Setting",
    cancelButtonText: "Back",
    allowOutsideClick: false,
    didClose: () => {
      // location.reload();
    },
    inputValidator: (value) => {
      return new Promise((resolve) => {
        if (value) {
          resolve();
        } else {
          resolve("You need to select a time slot!");
        }
      });
    }
  }).then((result) => {
    if (result.isConfirmed) {
      const selectedTime = timeMapping[result.value];
      timeSlot = selectedTime;
      fire_cron(selectedRecurrence, timeSlot, is_checked);
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      box_for_schedule(is_checked, selectedRecurrence);
    } else if (result.isDismissed) {
      location.reload();
    }
  });
}


function fire_cron(recurrence, timeSlot, is_checked) {
  $.ajax({
    type: "POST",
    url: ajax_object.ajax_url,
    dataType: "json",
    beforeSend: function () {
      $('#processbar').show();
      $('.toggle-sec').addClass('process');
      if (is_checked != true) {
        $('.toggle-sec#show_cron').hide();
      }
    },
    data: {
      action: "cron_is_checked_ajax_action",
      is_checked: is_checked,
      recurrence: recurrence,
      timeSlot: timeSlot,
      review_api_key: ajax_object.review_api_key,
    },
    success: function (response, status, error) {
      setTimeout(function () {
        if (response.success === 1) {
          $('.toggle-sec').removeClass('process');
          $('#processbar').show();
          $('.toggle-sec#show_cron').hide();
          setTimeout(function () {
            location.reload();
          }, 1500);
          notify_success_cron(response.msg);
        } else {
          $('#processbar').hide();
          setTimeout(function () {
            location.reload();
          }, 1500);
          notify_failed(response.msg);
        }
      }, 600);
    },
    error: function (xhr, status, error) {
      $('#processbar').hide();
    },
    complete: function () {
    },
  });
}
