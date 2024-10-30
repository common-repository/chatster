(function ($) {
"use strict";
  /**
   * It sends a presence ping to the database
   */
  var presence_set = false;
  function presence() {
    $.ajax( {

        url: chatsterDataPublic.api_base_url + '/chat/presence/customer',
        method: 'POST',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataPublic.nonce );
        },
        data: {},
        success: function(data) {
          console.log(data);
          presence_set = true;
        },
        error: function(error) {

        },

      } ).done( function ( response ) {

      });
  }
  setInterval(presence, 10000);
  presence();

  /**
   * User Utility fn
   */
  var open_chat = false;
  function ch_chat_sound(){
    var mp3Source = '<source src="' + chatsterDataPublic.sound_file_path + '.mp3" type="audio/mpeg">';
    var oggSource = '<source src="' + chatsterDataPublic.sound_file_path + '.ogg" type="audio/ogg">';
    var embedSource = '<embed hidden="true" autostart="true" loop="false" src="' + chatsterDataPublic.sound_file_path +'.mp3">';
    document.getElementById("sound").innerHTML='<audio id="ch-audio" autoplay="autoplay">' + mp3Source + oggSource + embedSource +'</audio>';
    var chatSound = document.getElementById("ch-audio");
    chatSound.volume = chatsterDataPublic.chat_sound_vol;
  }
  function scrollTopChat() {
    $("#ch-msg-container").animate({ scrollTop: $('#ch-msg-container').prop("scrollHeight")}, 400);
  }
  function instantiate_chat_pos() {
    if ( chatsterDataPublic.chat_position == 'left' ) {
      $('#chatster-container').css({left:'1%'});
      $('#chatster-opener').css({left: '-300px'});
      let button_width = parseInt( $('#ch-open-button').css('width') , 10) + 45;
      $('#chatster-opener').css({width:button_width+'px'});
      $('#chatster-opener').animate({
        left: '2%'
      });
    } else {
      $('#chatster-container').css({right:'1%'});
      $('#chatster-opener').css({right: '-300px'});
      $('#chatster-opener').animate({
        right: '2%'
      });
    }


  }
  function ch_open_chat() {
    setCookie('ch_window_open', true, 15);
    open_chat = true;
    $('#ch-red-dot-alert').hide();
    if ( chatsterDataPublic.chat_position == 'left' ) {
      $('#chatster-opener').animate({
        left: '-350px'
      });
    } else {
      $('#chatster-opener').animate({
        right: '-350px'
      });
    }
    $('#chatster-container').animate({
      bottom: '15px'
    });

  }
  function ch_close_chat() {
    deleteCookie('ch_window_open');
    open_chat = false;
    if ( chatsterDataPublic.chat_position == 'left' ) {
      $('#chatster-opener').animate({
        left: '2%'
      });
    } else {
      $('#chatster-opener').animate({
        right: '2%'
      });
    }

    $('#chatster-container').animate({
      bottom: '-650px'
    });
  }
  $('#chatster-opener').on('click', ch_open_chat );
  $('.ch-arrow').on('click', ch_close_chat);
  instantiate_chat_pos();

  /**
   * Simplified Cookie fn
   */
  function setCookie(name, value, mins) {
      if (mins) {
          var date = new Date();
          date.setTime(date.getTime() + (mins * 60 * 1000));
          var expires = "; expires=" + date.toGMTString();
      }
      else var expires = "";

      document.cookie = name + "=" + value + expires + "; path=/";
  }
  function getCookie(name) {
      var nameEQ = name + "=";
      var ca = document.cookie.split(';');
      for (var i = 0; i < ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0) == ' ') c = c.substring(1, c.length);
          if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
      }
      return null;
  }
  function deleteCookie(name) {
      setCookie(name, "", -1);
  }

  /**
  * Sends request form
  */
  function send_request_form() {

   let r_name = $('#ch-customer-name').val();
   let r_email =  $('#ch-customer-email').val();
   let r_subject =  $('#ch-customer-subject').val();
   let r_message =  $('#ch-customer-message').val();
   var payload = { customer_name: r_name, customer_email: r_email, customer_subject: r_subject, customer_message: r_message };

   $('#ch-send-request-form .ch-inline-selector').find('.ch-smaller-loader').show(100);
   $('#ch-send-request').attr('disabled',true);

    $.ajax( {

        url: chatsterDataPublic.api_base_url + '/request/public/insert',
        method: 'POST',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataPublic.nonce );
        },
        data: payload,
        success: function(data) {
            $('#ch-send-request-form .ch-inline-selector').find('.ch-smaller-loader').hide(200, function() {

              $('#ch-send-request').attr('disabled',false);
              $('#ch-send-request-form .ch-confirm-sent').show(200).delay(4000).hide(100);
              $('#ch-customer-name').val('');
              $('#ch-customer-email').val('');
              $('#ch-customer-subject').val('');
              $('#ch-customer-message').val('');

            });

        },
        error: function(error) {
            $('#ch-send-request-form .ch-inline-selector').find('.ch-smaller-loader').hide(200, function() {

              $('#ch-send-request').attr('disabled',false);
              $('#ch-send-request-form .ch-error-sent').show(200).delay(2000).hide(100);

            });
        },

      } ).done( function ( response ) {

      });
  }

  /**
   * Inserts current messages into the conversation
   */
  function insert_messages( new_message, temp_id ) {

    let customer_id = $("#ch-message-board").attr("data-curr_customer_id");
    let conv_id = $("#ch-msg-container").attr('data-conv_id');
    var payload = { new_message: new_message, temp_id: temp_id, conv_id: conv_id };

    $.ajax( {
        url: chatsterDataPublic.api_base_url + '/chat/insert',
        method: 'POST',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataPublic.nonce );
        },
        data: payload,
        success: function(data) {

        },
        error: function(error) {

        },

      } ).done( function ( response ) {

      });
  }
  $('#ch-reply-public').on('keypress', function(e) {

    if ( e.keyCode == 13 && ! e.shiftKey) {
      e.preventDefault();
      var message = $(this).val().trim();
      if (message && ( message.length <= 799 ) ) {
        scrollTopChat();
        $(this).val('');
        let temp_id = (new Date()).getTime().toString();
        temp_id = parseInt(temp_id.slice(4, temp_id.length));
        let $message = $("<div>", {id: "ch-msg-"+temp_id, "class": "ch-single-message ch-right", "data-author_id": "self" });
        $message.text(message);
        $("#ch-msg-container").append($message);
        prev_author_admin = false;
        insert_messages(message, temp_id);
      }
    }
  });
  $('#ch-chat-msg, #ch-reply-chat-button').on('click', function() {
    var press = jQuery.Event("keypress");
    press.ctrlKey = false;
    press.which = 13;
    press.keyCode = 13;
    $('#ch-reply-public').trigger(press);
  });

  /**
   * Ends current conversation
   */
  function disconnect_style() {
    $('.ch-queue-info div').hide();
    $('#ch-chat-disconnected').slideDown(300);
    // $('#ch-indent-header').css('background-color','#FAFAFA');
    // $('#ch-main-conv-container').css('background-color','#FAFAFA');
    $("#ch-end-chat, #ch-chat-msg").hide(200);
    $(".ch-cancel-btn").show(200);
    $('#chatster-container').css('background-color','#FAFAFA');
    $("#ch-msg-container").find('.ch-small-loader').hide();
    $('#ch-reply-public').attr('disabled',true);
    $('#ch-reply-public').css('background-color','#FAFAFA');
    $('#ch-reply-chat-button').css('background-color','#FAFAFA');
  }
  function disconnect_chat() {

   var payload = {};

   $.ajax( {
       url: chatsterDataPublic.api_base_url + '/chat/disconnect',
       method: 'POST',
       beforeSend: function ( xhr ) {
           xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataPublic.nonce );
       },
       data: payload,
       success: function(data) {
         disconnect_style();
       },
       error: function(error) {

       },

     } ).done( function ( response ) {

     });
  }
  $('#ch-end-chat').on('click', function() {
    disconnect_chat();
  });

  /**
   * Accessory functions to build the conversation list and current convs
   */
  var prev_author_admin = false;
  function esc_json(str) {
     return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  function msg_link_template(links) {

    if ( links ) {
      var template = '';
      $.each( links, function( key, attachment ) {
        let thumbnail = attachment.thumbnail ? attachment.thumbnail : chatsterDataPublic.no_image_link;
        template += '<div class="ch-link-chat" data-link_id="' + attachment.id + '">';
        template += ' <div class="ch-link-img">';
        template +=    '<img src="' + thumbnail + '" alt="product or page" height="32" width="32">';
        template += ' </div>';
        template += ' <div class="ch-link-descr">';
        template +=     '<div class="ch-link-title"><a href="' + attachment.link + '"  target="_blank">' + attachment.title + '</a></div>';
        template +=     '<div class="ch-link-excerpt">' + attachment.excerpt + '</div>';
        template += ' </div>';
        template += ' <div class="ch-link-exlink"><a href="' + attachment.link + '"  target="_blank">'+chatsterDataPublic.translation.open+'</a></div>';
        template += '</div>';
      });
      return template;
    }
    return '';
  }
  function build_current_conv(current_conv) {

    if ( current_conv ) {
        let current_msg_id = $("#ch-msg-container").attr('data-last_msg_id');
        let last_msg_id = current_conv[Object.keys(current_conv)[current_conv.length - 1]].id;
        $("#ch-msg-container").attr('data-last_msg_id', esc_json(last_msg_id) );
        $.each( current_conv, function( key, message ) {

          if ( $("#ch-msg-" + message.temp_id).length ) {
            $( "#ch-msg-" + message.temp_id ).attr("id", "ch-msg-" + message.id );
          } else {

            let is_self = message.is_author == "1" ? "ch-single-message ch-right" : "ch-single-message ch-left";
            let $message_cont = $("<div>", {id: "ch-msg-" + message.id, "class": is_self });
            let $message_text = $("<div>", {"class": "ch-msg-text"});
            let $message_links = $("<div>", {"class": "ch-link-cont"});

            $message_text.html(message.message);
            $message_links.html(msg_link_template(message.product_ids));
            $message_cont.append($message_text);
            $message_cont.append($message_links);

            if ( message.is_author != "1" && !prev_author_admin ) {
              let admin_thumb_url = $("#ch-msg-container").attr('data-admin_thumb_url');
              $message_cont.prepend($("<img>", {"class": "ch-admin-thumb", "src": admin_thumb_url}));
            }

            prev_author_admin = message.is_author == "1" ? false : true;

            $("#ch-msg-container").append($message_cont);

            if ( current_msg_id ) {
              ch_chat_sound();
            }
            if ( ! open_chat ) {
              $('#ch-red-dot-alert').show();
            }
          }
        });
     }
  }

  /**
   * Retrieves messages for the open conversation
   */
  var lp_ajax = false;
  function long_poll_msg() {
    let conv_id = $("#ch-msg-container").attr('data-conv_id');
    if ( conv_id ) {
      $("#ch-msg-container").find('.ch-small-loader').hide();
      let ch_last_msg = $("#ch-msg-container").attr('data-last_msg_id');
      ch_last_msg = ch_last_msg ? ch_last_msg : 0;
      var payload = { last_msg_id: ch_last_msg, conv_id: conv_id };

      if ( presence_set ) {
        lp_ajax = $.ajax( {
            url: chatsterDataPublic.api_base_url + '/chat/poll',
            method: 'POST',
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataPublic.nonce );
            },
            data: payload,
            success: function(data) {
              if ( data.status == 'false' || data.status == 0 ) {
                disconnect_style();
                return;
              }
              if ( data.payload ) {
                build_current_conv(data.payload);
                scrollTopChat();
              }
              setTimeout( long_poll_msg, 500 );

            },
            error: function(error) {
               if ( $("#ch-msg-container").attr('data-conv_id') != 'false' ) {
                 setTimeout( long_poll_msg, 500 );
               }
            },

          } ).done( function ( response ) {
            $("#ch-msg-container").find('.ch-small-loader').hide();
          });
        } else {
          setTimeout( long_poll_msg, 500 );
        }
    }

  }
  long_poll_msg();

  /**
   * Adds Live chat initial form / Initiates chat
   */
  function set_chat_form() {

    let c_name = $('#ch-chat-name').val();
    let c_email =  $('#ch-chat-email').val();
    let c_subject =  $('#ch-chat-subject').val();
    var payload = { customer_name: c_name, customer_email: c_email, chat_subject: c_subject };
    $('#ch-start-chat-form .ch-inline-selector').find('.ch-smaller-loader').show(100);
    $('#ch-start-chatting').attr('disabled',true);
     $.ajax( {

         url: chatsterDataPublic.api_base_url + '/chat/chat-form',
         method: 'POST',
         beforeSend: function ( xhr ) {
             xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataPublic.nonce );
         },
         data: payload,
         success: function(data) {
           $('#ch-start-chat-form .ch-inline-selector').find('.ch-smaller-loader').hide();
           $('#ch-start-chatting').attr('disabled',false);
           $("#ch-end-chat, #ch-chat-msg").show(0);
           $(".ch-cancel-btn").hide(0);
           $('#ch-chat-form').slideUp(300);
           $('#ch-chat-section').slideDown(300);
           long_poll_ticketing();
         },
         error: function(error) {
           $('#ch-start-chatting').attr('disabled',false);
           $('#ch-start-chat-form .ch-inline-selector').find('.ch-smaller-loader').hide();
         },

       } ).done( function ( response ) {

       });
 }
  function long_poll_ticketing() {

    setCookie('ch_ticketing_started', true, 5);

    $.ajax( {

        url: chatsterDataPublic.api_base_url + '/chat/ticketing',
        method: 'POST',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataPublic.nonce );
        },
        success: function(data) {
          if ( data.payload.conv_id !== undefined ) {
              deleteCookie('ch_ticketing_started');
              $("#ch-msg-container").attr('data-conv_id', parseInt(data.payload.conv_id));
              $("#ch-msg-container").attr('data-admin_thumb_url', data.payload.admin_thumb_url);
              $("#ch-reply-public").attr('disabled', false);
              $("#ch-msg-container").find('.ch-small-loader').hide();
              $(".ch-queue-info div").hide();
              $('#ch-assigned-admin').find('span').text(data.payload.admin_name);
              $('#ch-assigned-admin').slideDown(300);

              long_poll_msg();
              return;
          }
          if ( data.payload.queue_status !== undefined ) {
            switch(true) {
              case ( data.payload.queue_status > 0 ):
                $("#ch-inqueue").slideDown(100);
                $("#ch-inqueue").find('span').text(data.payload.queue_status);
                break;
              default:
                $("#ch-inqueue").slideUp(100);
                $("#ch-inqueue-end").slideDown(300);
            }
            long_poll_ticketing();
            return;
          }

        },
        error: function(error) {
            setTimeout( long_poll_ticketing, 2000 );
        },

      } ).done( function ( response ) {

      });
  }
  function check_ticketing() {
     let conv_id = $("#ch-msg-container").attr('data-conv_id');
     let has_ticket = getCookie('ch_ticketing_started');
     let is_window_open = getCookie('ch_window_open');
     if ( !conv_id && has_ticket ) {
       $('#ch-chat-form').hide();
       $('#ch-chat-section').show();
       $("#ch-chat-select").hide();
       long_poll_ticketing();
     }
     if ( conv_id && is_window_open ) {
       setTimeout(function() {$('#chatster-opener').trigger("click");}, 2000);
     }
  }
  check_ticketing();

  /**
   * Chat Select Options
   */
  $('#ch-btn-chat').on('click', function(e) {
    if ( $(this).hasClass('ch-unavailable') ) {
        return;
    }

    $("#ch-chat-select").slideUp(300);
    $('#ch-chat-form').slideDown(300);


  });
  $('#ch-btn-request').on('click', function(e) {
    $("#ch-chat-select").slideUp(300);
    $('#ch-request-form').slideDown(300);

  });
  $("#ch-start-chat-form").submit(function(e){
      e.preventDefault();
      set_chat_form();
  });
  $('#ch-send-request-form').submit(function(e){
      e.preventDefault();
      send_request_form();
  });
  $(".ch-cancel-btn").on('click', function() {
    $("#ch-msg-container").attr('data-conv_id', false);
    $("#ch-msg-container").attr('data-last_msg_id', false);
    $('#chatster-container').css('background-color','#FFF');
    $('#ch-reply-public').css('background-color','#FFF');
    $('#ch-reply-chat-button').css('background-color','#FFF');
    $('#ch-msg-container').find('.ch-single-message').remove();
    $("#ch-msg-container").find('.ch-small-loader').show();
    $("#ch-chat-select").slideDown(200);
    $("#ch-chat-section").slideUp(200);
    $('#ch-request-form').slideUp(200);
    $('#ch-chat-form').slideUp(200);
    if (lp_ajax) {
        lp_ajax.abort();
    }
  });

  /**
   * Bot Activation
   */
  var answer_ids = [];
  var prev_author_bot = false;
  function scrollTopBotChat() {
    $("#ch-bot-msg-container").animate({ scrollTop: $('#ch-bot-msg-container').prop("scrollHeight")}, 400);
  }
  function build_bot_response(response) {

    if ( response ) {

        $.each( response, function( key, message ) {

            let $message = $("<div>", {id: "ch-msg-" + message.id, "class": "ch-single-message ch-left" });
            if( answer_ids.indexOf(message.id) === -1) {
                answer_ids.push(message.id);
            }
            $message.html(message.answer);
            if ( !prev_author_bot ) {
                $message.prepend($("<img>", {"class": "ch-admin-thumb", "src": chatsterDataPublic.bot_img_path }));
            }
            $message.hide();
            $("#ch-bot-msg-container").append($message);
            $message.show(100);
            prev_author_bot = true;
        });
     }
  }
  function build_bot_static_response(response) {

    if ( response ) {

        $.each( response, function( key, message ) {
            let $message = $("<div>", {"class": "ch-single-message ch-left static-response" });
            $message.html(message);
            if ( !prev_author_bot ) {
                $message.prepend($("<img>", {"class": "ch-admin-thumb", "src": chatsterDataPublic.bot_img_path }));
            }
            $message.hide();
            $("#ch-bot-msg-container").append($message);
            $message.show(100);
            if ( $('#ch-chat-select').is(':visible')) {
              ch_chat_sound();
            }
            prev_author_bot = true;
        });


     }
  }
  function submit_question( question ) {

    var payload = { user_question: question, answer_ids: answer_ids };
    $('.loading-dots').removeClass('invisible');

    $.ajax( {
       url: chatsterDataPublic.api_base_url + '/bot/public/answer',
       method: 'POST',
       beforeSend: function ( xhr ) {
           xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataPublic.nonce );
       },
       data: payload,
       success: function(data) {
         let response = [];
         if ( data.payload ) {
           build_bot_response(data.payload);
           response.push(chatsterDataPublic.chat_static_string.bot_followup);
           build_bot_static_response(response);
           scrollTopBotChat();
         } else {
           response.push(chatsterDataPublic.chat_static_string.bot_nomatch);
           build_bot_static_response(response);
           scrollTopBotChat();
         }
       },
       error: function(error) {

       },

     } ).done( function ( response ) {
         $('.loading-dots').addClass('invisible');
     });
  }
  function submit_action() {

    let $input_bar = $("#ch-reply-bot");
    var message = $input_bar.val().trim();

    if (message && ( message.length <= 799 ) ) {
      scrollTopBotChat();
      $input_bar.val('');
      let $message = $("<div>", { "class": "ch-single-message ch-right", "data-author_id": "self" });
      $message.text(message);
      $("#ch-bot-msg-container").append($message);
      prev_author_bot = false;
      submit_question(message);
    }
  }
  $('#ch-reply-bot-button').on('click', function() {
    submit_action();
  });
  $("#ch-reply-bot").on('keypress', function(e) {

    if ( e.keyCode == 13 && ! e.shiftKey) {
      e.preventDefault();
      submit_action();
    }
  });
  $('#chatster-opener').one('click', function(e) {
    $('.loading-dots').removeClass('invisible');
    let x = setTimeout( function() {
                          $('.loading-dots').addClass('invisible');
                          let response = [];
                          response.push(chatsterDataPublic.chat_static_string.bot_intro);
                          build_bot_static_response(response);
                        }, 800);
  });

})(jQuery);
