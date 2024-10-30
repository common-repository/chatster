(function ($) {
"use strict";

// Instantiates color-picker
  $('.my-color-field').wpColorPicker();

/**
 * Automatically opens the Current Saved tab to show the options menu as it reloads
 */
  function go_to_option() {
   let open_option = window.location.hash.substr(1);
   $("#"+open_option).delay(200).slideDown(300);
   $("#"+open_option).parent().find('.ch-option-title').animate({width:"100%"},200);
   history.pushState("", document.title, window.location.pathname + window.location.search);
  }
  go_to_option();

/**
 * Animates Option windows slide up and down
 */
  $('.ch-option-title').on('click', function(e) {
    e.preventDefault();
    let window_width = parseInt($('#ch-options-main-container').css('width') , 10);
    let current_tab_width = parseInt($('.ch-option-title').css('width') , 10);

    if ( $(this).parent().find('.ch-option-container').css('display') == 'none' ) {
       $(this).animate({width:"100%"},200);
    }
    else if ( window_width > current_tab_width &&
                $(this).parent().find('.ch-option-container').css('display') != 'none' ) {
       $(this).animate({width:"500px"},200);
    }
    if ( e.target.className == 'ch-option-title' ||  e.target.className == 'mobile-removable' ) {

      $(this).parent().find('.ch-option-container').slideToggle(300,"linear", function() {


      });
    }

  });

/**
 * Asks for confirmation before resetting options.
 */
  $('.submit-reset').on('click', function(e) {
    e.preventDefault();
    if ( confirm(chatsterDataAdmin.translation.reset) ) {
      let $option_form = $(this).parent().parent().parent();
      let option_page = $option_form.find('input[name ="option_page"]').val();
      $('<input>').attr({ type: 'hidden',
                          id: option_page +'_default_settings',
                          name: option_page +'[default_settings]',
                          value: 'reset' })
                  .appendTo($option_form);

      $option_form.submit();
    }
  });

/**
 * Enables/Disables Email Alert / Forward
 */
  $('#chatster_request_options_ch_request_alert').on('change', function() {
    if ( this.checked ) {
      $('#chatster_request_options_ch_request_alert_email').prop('disabled', false).prop('required',true);
    } else {
      $('#chatster_request_options_ch_request_alert_email').removeAttr('required').prop('disabled', true);
    }
  }).trigger('change');
  $('#chatster_request_options_ch_response_forward').on('change', function() {
    if ( this.checked ) {
      $('#chatster_request_options_ch_response_forward_email').prop('disabled', false).prop('required',true);
    } else {
      $('#chatster_request_options_ch_response_forward_email').removeAttr('required').prop('disabled', true);
    }
  }).trigger('change');

/**
* Preview Header Image
*/
  var header_img_input = $('#chatster_request_options_ch_response_header_url');
  var header_container = $('<div>', { id: 'header_img_input_container' });
  $(header_container).insertAfter(header_img_input);
  $(header_img_input).appendTo(header_container);
  function update_header_img( source ) {
      $('.header_img').remove();
      let $img_display = $('<img>', { class: 'header_img' });
      $img_display.hide();
      $img_display.on('load', function() {
                                            $('.header_img').remove();
                                            $(this).appendTo(header_container);
                                            $img_display.show(400);
                                          })
                  .on('error', function() {
                                            $(this).attr('src', chatsterDataAdmin.no_image_link );
                                            $(this).addClass('no-image-found');
                                            $img_display.show();
                                          })
                  .attr("src", source);
  }
  $('#chatster_request_options_ch_response_header_url').on('paste keyup', function() {
    let new_source = $(this).val();
    if ( new_source ) {
       update_header_img( new_source );
    } else {
       update_header_img( chatsterDataAdmin.default_header_img_url );
    }
  }).trigger('paste');

/**
 * Sends a test email
 */
  $('#chatster-test-email-form').on('submit', function(e) {
  e.preventDefault();
  let $submit = $('#ch-test-email');
  let recipient = $('#chatster_request_options_ch_request_test_email').val();
  let payload = { test_email: recipient };
  $(this).find('.ch-smaller-loader').show();
  $submit.attr('disabled', true);

  $.ajax( {
     url: chatsterDataAdmin.api_base_url + '/request/admin/email/test',
     method: 'POST',
     beforeSend: function ( xhr ) {
         xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
     },
     data: payload,
     success: function(data) {
       console.log(data.payload);
       $('#chatster-test-email-form').find('.ch-smaller-loader').hide();
       $('#chatster-test-email-form').find('.ch-success').show(100).delay(3000).hide(200);
       $submit.attr('disabled', false);
     },
     error: function(error) {
       $('#chatster-test-email-form').find('.ch-smaller-loader').hide();
       $('#chatster-test-email-form').find('.ch-fail').show(100).delay(3000).hide(200);
       $submit.attr('disabled', false);
     },

   } ).done( function ( response ) {

   });
 });

/**
 * Front chat option sound on mouseup/touchend
 */
  function ch_chat_sound(volume_val){
     var mp3Source = '<source src="' + chatsterDataAdmin.chat_sound_file_path + '.mp3" type="audio/mpeg">';
     var oggSource = '<source src="' + chatsterDataAdmin.chat_sound_file_path + '.ogg" type="audio/ogg">';
     var embedSource = '<embed hidden="true" autostart="true" loop="false" src="' + chatsterDataAdmin.chat_sound_file_path +'.mp3">';
     document.getElementById("sound").innerHTML='<audio id="ch-audio" autoplay="autoplay">' + mp3Source + oggSource + embedSource +'</audio>';
     var chatSound = document.getElementById("ch-audio");
     chatSound.volume = volume_val;
   }
  $('#chatster_chat_options_ch_chat_volume').on('mouseup touchend', function(e) {
     let volume_val = ( 1 / 50 ) * parseInt($(this).val());
     ch_chat_sound(volume_val);
  });
  $('#chatster_chat_options_ch_chat_volume_admin').on('mouseup touchend', function(e) {
     let volume_val = ( 1 / 50 ) * parseInt($(this).val());
     ch_chat_sound(volume_val);
  });

/**
 * Q and A Requests
 */
  function build_qa_single( answer, questions ) {

    let $question_container = $("<div>", { "class": "question-container" });
    $.each( questions, function( key, question ) {
       let $question = $("<div>", {id: "ch-question-single-"+ question.id, "class": "single-question", "data-question_id": question.id }).text(question.question);
       $question_container.append($question);
    });

    let $qa_container = $("<div>", {id: "ch-qa-single-"+answer.id, "class": "single-qa", "data-answer_id": answer.id });
    let $answer_container = $("<div>", { "class": "ch-answer" }).text(answer.answer);
    let $edit = $("<span>", { "class": "ch-edit-answer" }).text(chatsterDataAdmin.translation.edit);
    let $delete = $("<span>", { "class": "ch-delete-answer", "data-answer_id": answer.id  }).text(chatsterDataAdmin.translation.delete);
    let $loader = $('<div>',{"class": "ch-smaller-loader hidden" , "style":"margin-left:20px;"});
    let $edit_block = $("<edit>", { "class": "ch-edit-qa", "data-answer_id": answer.id  }).append($delete).append($edit).append($loader);
    $qa_container.append($question_container);
    $qa_container.append($answer_container);
    $qa_container.append($edit_block);

    return $qa_container;
  }
  function build_qa_list( payload ) {
    if ( payload ) {

       $.each( payload, function( key, qa_data ) {
           let answer = qa_data.answer_data;
           let questions = qa_data.questions;
           let $qa_container = build_qa_single( answer, questions );
           $('#q-and-a-block').append($qa_container);
       });

    }
  }
  function update_qa( $qa_single, answer_id) {
    $qa_single.hide();
    let $old_element = $("#ch-qa-single-"+answer_id);
    $old_element.attr('id','removed-'+answer_id);
    $old_element.after($qa_single);
    $old_element.slideUp(300, function() {
      $old_element.remove();
    });
    $qa_single.slideDown(300);

  }
  function save_q_and_a( questions, answer, answer_id ) {

    var payload = { questions: questions, answer: answer, answer_id: answer_id };

    $.ajax( {
       url: chatsterDataAdmin.api_base_url + '/bot/admin/update',
       method: 'POST',
       beforeSend: function ( xhr ) {
           xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
       },
       data: payload,
       success: function(data) {
         let $qa_single = build_qa_single(data.payload.answer_data, data.payload.questions);
         if ( !data.is_update ) {
           $qa_single.hide();
           $('#q-and-a-block').prepend($qa_single);
           $qa_single.slideDown(200);
         } else {
           update_qa( $qa_single, answer_id);
         }
         $('#ch-no-q-and-a').hide(100);
         $('#chatster_bot_qa_options_ch_bot_answer').val('');
         $('#chatster_bot_qa_options_ch_bot_question').val('');
       },
       error: function(error) {
         $('#save-bot-q-and-a').attr('disabled', false);
         $('#cancel-bot-q-and-a').attr('disabled', false);
         $('#chatster-bot-and-a-form').find('.ch-smaller-loader').hide(100);
       },

     } ).done( function ( response ) {
         $('#q-and-a-input').attr('data-qa_edit_id', 0);
         $('#save-bot-q-and-a').attr('disabled', false);
         $('#cancel-bot-q-and-a').attr('disabled', false).hide(100);
         $('#chatster-bot-and-a-form').find('.ch-smaller-loader').hide(100);
     });
  }
  $('#chatster-bot-and-a-form').on('submit', function(e) {
    e.preventDefault();
    let questions_text = $('#chatster_bot_qa_options_ch_bot_question').val();
    let questions = questions_text.match(/\S[^?]*(?:\?+|$)/g);
    let answer = $('#chatster_bot_qa_options_ch_bot_answer').val();
    let answer_id = $('#q-and-a-input').attr('data-qa_edit_id');
    $('#save-bot-q-and-a').attr('disabled', true);
    $('#cancel-bot-q-and-a').attr('disabled', true);
    $(this).find('.ch-smaller-loader').show(100);

    save_q_and_a( questions, answer, answer_id );
  });

  function pagination_page_refresh(current_page) {
    let $pages = $('#ch-qa-pagination .page-numbers li');
    $pages.removeClass('current');
    $.each( $pages, function( key, page_li ) {
      if ( $(page_li).find('a, span').length ) {
        let page_number =  $(page_li).find('a, span').text();
        if ( page_number == current_page ) {
          $(this).addClass('current');
        }
      }
    });

  }
  function load_q_and_a_list(page) {

    $('#q-and-a-list').find('.ch-small-loader').stop().show(200);
    $.ajax( {
       url: chatsterDataAdmin.api_base_url + '/bot/admin/' + page + '/get-page',
       method: 'POST',
       beforeSend: function ( xhr ) {
           xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
       },
       data: {},
       success: function(data) {

         if( data.payload.length ) {
           $('#q-and-a-block').empty();
           build_qa_list(data.payload);
           $('#q-and-a-block').show(200);
           $('#ch-no-q-and-a').hide(100);
         }
         else if( page > 1 ) {
           $($('#ch-qa-pagination .page-numbers li')[page - 1]).remove();
           current_page_qa = 1;
           load_q_and_a_list(current_page_qa);
         } else {
           $('#ch-no-q-and-a').show();
         }
         current_page_qa = page;
         pagination_page_refresh( page );

       },
       error: function(error) {
         $('#ch-qa-pagination').removeClass('disabled-link');
       },

     } ).done( function ( response ) {
         $('#ch-qa-pagination').removeClass('disabled-link');
         $('#q-and-a-list').find('.ch-small-loader').hide(200);
     });
  }
  var current_page_qa = 1;
  $('#ch-qa-pagination a,#ch-qa-pagination span').on('click', function(e) {
      e.preventDefault(); e.stopPropagation();
      $('#ch-qa-pagination').addClass('disabled-link');
      $('#q-and-a-block').hide(100);
      var page = 1;
      if ( $(this).hasClass('next') ) {

         let page_max = parseInt( $('#ch-qa-pagination').attr('data-page_max') );
         if ( current_page_qa < page_max ) {
           page = current_page_qa + 1;
         } else {
           page = current_page_qa;
         }

      } else {
         page = parseInt( $(this).text() );
      }
      load_q_and_a_list(page);
  });
  load_q_and_a_list(current_page_qa);

  $( document ).on( "click", '.ch-delete-answer', function() {
    let answer_id = $(this).attr('data-answer_id');
    $("#ch-qa-single-"+answer_id).find('.ch-smaller-loader').show(200);
    $.ajax( {
       url: chatsterDataAdmin.api_base_url + '/bot/admin/'+ answer_id +'/delete',
       method: 'POST',
       beforeSend: function ( xhr ) {
           xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
       },
       data: {},
       success: function(data) {
          $("#ch-qa-single-"+answer_id).slideUp(200, function() {
            $(this).remove();
            if ( $('.single-qa').length == 0 ) {
              current_page_qa = 1;
              load_q_and_a_list(current_page_qa);
            }
          });
       },
       error: function(error) {
          $("#ch-qa-single-"+answer_id).find('.ch-smaller-loader').hide(200);
       },

     } ).done( function ( response ) {

     });
  });
  $( document ).on( "click", '.ch-edit-answer', function() {
    $('#cancel-bot-q-and-a').show(100);
    $('.single-qa').removeClass('ch-qa-edited');
    let answer_id = $(this).parent().attr('data-answer_id');
    let $qa_block = $('#ch-qa-single-'+answer_id);
    let $question_block = $qa_block.find('.single-question');
    $('#q-and-a-input').attr('data-qa_edit_id', answer_id);
    $qa_block.addClass('ch-qa-edited');
    let answer = $qa_block.find('.ch-answer').text();
    let question_join = '';
    $question_block.each(function(index, obj) {
      question_join += $(this).text()+' ';
    });

    $('#chatster_bot_qa_options_ch_bot_question').val(question_join);
    $('#chatster_bot_qa_options_ch_bot_answer').val(answer);
  });
  $('#cancel-bot-q-and-a').on('click', function(e) {
      e.preventDefault();
      $('#q-and-a-input').attr('data-qa_edit_id', 0);
      $('.single-qa').removeClass('ch-qa-edited');
      $('#chatster_bot_qa_options_ch_bot_question').val('');
      $('#chatster_bot_qa_options_ch_bot_answer').val('');
      $(this).hide(100);

  })

  // Reset bot Q and A
  function reset_qa_display() {
    $('.single-qa').remove();
    $('#setting-error-bot_qa_message').show(100).delay(5000).hide(100);
    $('#ch-no-q-and-a').show(200);
  }
  function reset_qa() {

    $.ajax( {
       url: chatsterDataAdmin.api_base_url + '/bot/admin/reset',
       method: 'POST',
       beforeSend: function ( xhr ) {
           xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
       },
       data: {},
       success: function(data) {

           $('#q-and-a-block div').slideUp(100);
           reset_qa_display();


       },
       error: function(error) {

       },

     } ).done( function ( response ) {

     });
  }
  $('#reset-bot-q-and-a').on('click', function(e) {
    e.preventDefault();
    if ( confirm( chatsterDataAdmin.translation.reset ) ) {
      reset_qa();
    }
  });

  /**
   * Resets All Options
   */
   $('#reset-all').on('click', function(e) {
     e.preventDefault();
     if ( confirm( chatsterDataAdmin.translation.reset_all ) ) {
       $('#chatster-reset-all-settings').submit();
     }
   });

  /**
   * Display the backend chat after loading
   */
   $(document).ready(function(){
       $('#ch-options-main-container').show(500);
   });

})(jQuery);
