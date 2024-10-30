(function ($) {
"use strict";
  /**
   * It sends a presence ping to the database
   */
  function presence_admin() {

    let current_status = $('#chatster-chat-switch').is(':checked');

    $.ajax( {
        url: chatsterDataAdmin.api_base_url + '/chat/presence/admin',
        method: 'POST',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
        },
        data: {},
        success: function(data) {
          if ( ! chat_switch_debounce ) {
            let new_status = data.payload.status == true ? true : false;
            if ( new_status != current_status ) {
                $('#chatster-chat-switch').prop( "checked", new_status );
                if ( new_status ) {
                  $('.active-convs-link').removeClass('hidden');
                } else {
                    $('.active-convs-link').addClass('hidden');
                }
            }
          }
        },
        error: function(error) {

        },

      } ).done( function ( response ) {

      });
  }
  setInterval(presence_admin, 10000);
  presence_admin();

  /**
   * User Utility fn
   */
  function ch_chat_sound(){
    var mp3Source = '<source src="' + chatsterDataAdmin.chat_sound_file_path + '.mp3" type="audio/mpeg">';
    var oggSource = '<source src="' + chatsterDataAdmin.chat_sound_file_path + '.ogg" type="audio/ogg">';
    var embedSource = '<embed hidden="true" autostart="true" loop="false" src="' + chatsterDataAdmin.chat_sound_file_path +'.mp3">';
    document.getElementById("chat-sound").innerHTML='<audio id="ch-chat-audio" autoplay="autoplay">' + mp3Source + oggSource + embedSource +'</audio>';
    var chatSound = document.getElementById("ch-chat-audio");
    chatSound.volume = chatsterDataAdmin.chat_sound_vol;
  }
  function ch_conv_sound(){
    var mp3Source = '<source src="' + chatsterDataAdmin.conv_sound_file_path + '.mp3" type="audio/mpeg">';
    var oggSource = '<source src="' + chatsterDataAdmin.conv_sound_file_path + '.ogg" type="audio/ogg">';
    var embedSource = '<embed hidden="true" autostart="true" loop="false" src="' + chatsterDataAdmin.conv_sound_file_path +'.mp3">';
    document.getElementById("conv-sound").innerHTML='<audio id="ch-conv-audio" autoplay="autoplay">' + mp3Source + oggSource + embedSource +'</audio>';
    var chatSound = document.getElementById("ch-conv-audio");
    chatSound.volume = chatsterDataAdmin.chat_sound_vol;
  }

  /**
   * Disconnects the chats
   */
  function disable_chat_style() {
    $('#ch-reply-block, #ch-message-board, #ch-conversation-container').css('background-color','#FAFAFA');
    $('#ch-reply').attr('disabled', 'true').addClass('disabled');
  }
  function remove_disconnected( conv_id ) {
    let $conversation = $('#conv-' + conv_id);
    if ( parseInt($('#ch-message-board').attr('data-conv_id')) == conv_id ) {
      $('#ch-message-board').find('.single-message').remove();
      $('#ch-no-message-overlay').show(100);
    }
    $conversation.slideUp(200, function() {
      $(this).remove();
    });
    if ( parseInt($('.single-conversation').length) - 1 == 0 ) {
      $('#ch-load-conv-container').show(200);

    }

  }
  function disable_disconnected( conv_id, $disconnect_link ) {
    let $conversation = $('#conv-' + conv_id);
    if ( parseInt($('#ch-message-board').attr('data-conv_id')) == conv_id ) {
      disable_chat_style();
    }
    $conversation.addClass('disconnected');
    $($disconnect_link).text(chatsterDataAdmin.translation.delete);
  }
  $( document ).on( "click", '.ch-disconnect', function(e) {
    e.stopPropagation();
    e.preventDefault();

    let $disconnect_link = $(this);
    let $conversation = $(this).parent();
    let conv_id = parseInt( $conversation.attr('data-single_conv_id') );
      console.log(conv_id);
    if ($conversation.hasClass('disconnected')) {
      remove_disconnected( conv_id, $conversation );
      return;
    }

    $.ajax( {
        url: chatsterDataAdmin.api_base_url + '/admin/chat/'+ conv_id +'/disconnect',
        method: 'POST',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
        },
        data: {},
        success: function(data) {

          if ( chatsterDataAdmin.remove_offline_conv == 1 ) {
            remove_disconnected( conv_id );
          } else {
            disable_disconnected( conv_id, $disconnect_link );
          }

        },
        error: function(error) {

        },

      } ).done( function ( response ) {

      });
  });

  /**
   * Inserts current messages into the conversation
   */
  function insert_messages( new_message, temp_id ) {

    let customer_id = $("#ch-message-board").attr("data-curr_customer_id");
    let conv_id = $("#ch-message-board").attr("data-conv_id");

    var payload = { new_message: new_message, msg_link: get_msg_links(), conv_id: conv_id, customer_id: customer_id, temp_id: temp_id };

    $.ajax( {
        url: chatsterDataAdmin.api_base_url + '/chat/insert/admin',
        method: 'POST',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
        },
        data: payload,
        success: function(data) {

        },
        error: function(error) {

        },

      } ).done( function ( response ) {

      });
  }
  function get_attachment_objs() {

    let attachments = $(".ch-product-auto");
    let attachment_cont = [];
    if ( attachments ) {

      $.each( attachments, function( key, attachment ) {
        let found_attachment = {};
        found_attachment['id'] = $(attachment).attr('data-link_id');
        found_attachment['thumbnail'] = $(attachment).find('img').attr('src');
        found_attachment['title'] = $(attachment).find('.ch-auto-title').text();
        found_attachment['excerpt'] = $(attachment).find('.ch-auto-excerpt').text();
        found_attachment['link'] = $(attachment).find('.ch-auto-exlink a').attr('href');
        attachment_cont.push(found_attachment);
      });
      return attachment_cont;
    }
    return false;
  }
  function get_msg_links() {
    let attachments = $(".ch-product-auto");
    let links = [];
    if ( attachments ) {
      $.each( attachments, function( key, attachment ) {
        let link_id = $(attachment).attr('data-link_id');
        links.push(link_id);
      });
    }
    $("#ch-attachments div").slideUp( 300, function() {$("#ch-attachments div").remove();});
    return links;
  }
  $('#ch-reply').on('keypress', function(e) {
    if ( e.keyCode == 13 && ! e.shiftKey ) {
      e.preventDefault();
      var message = $(this).val().trim();
      if (message && ( message.length <= 799 ) ) {
        $(this).attr('rows', 1 ).val('');
        let temp_id = (new Date()).getTime().toString();
        temp_id = parseInt(temp_id.slice(4, temp_id.length));
        // Create elements
        let $message_cont = $("<div>", {id: "message-"+temp_id, "class": "single-message self", "data-author_id": "self" });
        let $message_text = $("<div>", {"class": "ch-msg-text"});
        let $message_links = $("<div>", {"class": "ch-link-cont"});
        // Populate elements
        $message_text.text(message);
        $message_links.html(msg_link_template(get_attachment_objs()));
        // Append elements
        $message_cont.append($message_text);
        $message_cont.append($message_links);
        // Append Message Block
        $("#ch-message-board").append($message_cont);
        // Scroll up
        scrollTopAdminChat();
        // Insert Message - Ajax Call
        insert_messages(message, temp_id);
      }
    }
  });
  // Textarea rows controller
  $('#ch-reply').on('keydown', function(e) {
    if ( e.keyCode == 13 && e.shiftKey ) {
      let rows = $(this).attr('rows');
      $(this).attr('rows', parseInt(rows) + 1 );
    }
    else if ( e.keyCode == 8 ) {
      let lines = $(this).val().split(/\r*\n/).length;
      let rows = parseInt(lines) - 1;
      rows = rows >= 1 ? rows : 1;
      $(this).attr('rows', rows);
    }
  });

  /**
   * Accessory functions to build the conversation list and current convs
   */
  function time_ago(date) {
      var t = date.split(/[- :]/);
      var date = new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]));

      var periods = {
        month: 30 * 24 * 60 * 60 * 1000,
        week: 7 * 24 * 60 * 60 * 1000,
        day: 24 * 60 * 60 * 1000,
        hour: 60 * 60 * 1000,
        minute: 60 * 1000
      };

      var diff = Date.now() - date;

      if (diff > periods.day) {
          return chatsterDataAdmin.translation.created + ' ' +chatsterDataAdmin.translation.hours_plus;
      } else if (diff > periods.hour) {
        if (Math.floor(diff > periods.hour) > 1 ) {
          return chatsterDataAdmin.translation.created + ' ' + Math.floor(diff > periods.hour) + ' ' + chatsterDataAdmin.translation.hours;
        } else {
          return chatsterDataAdmin.translation.created + ' ' + Math.floor(diff > periods.hour) + ' ' + chatsterDataAdmin.translation.hour;
        }
      } else if (diff > periods.minute) {
        if (Math.floor(diff / periods.minute) > 1 ) {
          return chatsterDataAdmin.translation.created + ' ' + Math.floor(diff / periods.minute) + ' ' + chatsterDataAdmin.translation.minutes;
        } else {
          return chatsterDataAdmin.translation.created + ' ' + Math.floor(diff / periods.minute) + ' ' + chatsterDataAdmin.translation.minute;
        }
      }
      return chatsterDataAdmin.translation.created + ' ' + chatsterDataAdmin.translation.now;

  }
  function esc_json(str) {
     return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  function build_convs(convs) {

    if ( convs ) {
       $('#ch-load-conv-container').hide();
       let last_conv_id = convs[Object.keys(convs)[convs.length - 1]].id;
       $("#conversations-block").attr('data-last_conv_id', last_conv_id);

       $.each( convs, function( key, conversation ) {
         let $conversation = $("<div>", {id: "conv-"+conversation.id, "class": "single-conversation", "data-customer_id": conversation.customer_id, "data-single_conv_id": conversation.id, "data-is_connected": true });
         let $subject = $("<div>", { "class": "ch-subject" }).text(conversation.form_data.chat_subject);
         let $email = $("<div>", { "class": "ch-email" }).text(conversation.form_data.customer_email);
         let $customer_name = $("<div>", { "class": "ch-name" }).text(conversation.form_data.customer_name);
         let $info = $("<div>", { "class": "ch-created-at", "data-created_at":conversation.created_at }).text(time_ago(conversation.created_at));
         let $unread = $("<div>", { "class": "unread"}).hide();
         let $disconnect = $("<div>", { "class": "ch-disconnect"}).text(chatsterDataAdmin.translation.disconnect);
         if ( conversation.not_read > 0 ) {
           $unread.text( conversation.not_read ).show();
         }
         $conversation.append($customer_name).append($subject).append($email).append($info).append($unread).append($disconnect);
         $conversation.hide();
         $("#conversations-block").append($conversation);
         $conversation.slideDown(200);
       });
       ch_conv_sound();
    }

  }
  function build_current_conv(current_conv, conv_id) {

    let current_conv_id = $('#ch-message-board').attr('data-conv_id');
    /* If it's not the selected conversation ignore it */
    if ( current_conv && ( current_conv_id == conv_id ) ) {
      let prev_last_msg_id = $("#ch-message-board").attr('data-last_msg_id');
      let last_msg_id = current_conv[Object.keys(current_conv)[current_conv.length - 1]].id;
      if ( prev_last_msg_id < last_msg_id ) {

        $("#ch-message-board").attr('data-last_msg_id', esc_json(last_msg_id) );
        $.each( current_conv, function( key, message ) {

          if ( $("#message-" + message.temp_id).length ) {
            $( "#message-" + message.temp_id ).attr("id", "message-" + message.id );
          } else {
            let is_self = message.is_author == "1" ? "single-message self" : "single-message";
            let $message_cont = $("<div>", {id: "message-" + message.id, "class": is_self });
            let $message_text = $("<div>", {"class": "ch-msg-text"});
            let $message_links = $("<div>", {"class": "ch-link-cont"});
            $message_text.html(message.message);
            $message_links.html(msg_link_template(message.product_ids));
            $message_cont.append($message_text);
            $message_cont.append($message_links);
            $("#ch-message-board").append($message_cont);
            if ( message.is_author != "1" ) ch_chat_sound();

          }
        });

        // Scroll up
        scrollTopAdminChat();
      }

    }

  }
  function msg_link_template(links) {

    if ( links ) {
      var template = '';
      $.each( links, function( key, attachment ) {
        let thumbnail = attachment.thumbnail ? attachment.thumbnail : chatsterDataAdmin.no_image_link;
        template += '<div class="ch-link-chat" data-link_id="' + attachment.id + '">';
        template += ' <div class="ch-link-img">';
        template +=    '<img src="' + thumbnail + '" alt="product or page" height="32" width="32">';
        template += ' </div>';
        template += ' <div class="ch-link-descr">';
        template +=     '<div class="ch-link-title"><a href="' + attachment.link + '"  target="_blank">'+ attachment.title + '</a></div>';
        template +=     '<div class="ch-link-excerpt">' + attachment.excerpt + '</div>';
        template += ' </div>';
        template += '</div>';
      });
      return template;
    }
    return '';
  }
  $( document ).on( "click", '.single-conversation', function() {
    $('#ch-reply-block, #ch-message-board, #ch-conversation-container').css('background-color','#FFF');
    $('#ch-reply').attr('disabled', false).removeClass('disabled');
    $('#ch-no-message-overlay').hide();
    $('#ch-loading-conversation').css('display', 'flex');
    $('.single-conversation').removeClass('selected');
    $(this).addClass('selected');
    $(this).find('.unread').hide(100);
    $('#ch-message-board').empty();
    let current_conv_id = $(this).attr('data-single_conv_id');
    let current_customer_id = $(this).attr('data-customer_id');
    $('#ch-message-board').attr('data-conv_id', current_conv_id);
    $('#ch-message-board').attr('data-curr_customer_id', current_customer_id);
    $("#ch-message-board").attr('data-last_msg_id', 0);

    get_messages();

    if ( $(this).hasClass('disconnected') ) {
      disable_chat_style();
    }

  });
  // Updates Timestamps on conversations
  setInterval(function(){

    $('.ch-created-at').each(function() {
        let timestamp = $( this ).attr('data-created_at');
        $( this ).text(time_ago(timestamp));
      });

  }, 30000);
  /**
   * Live Update status fns
   */
  function update_disconnected( disconnected ) {
   if ( disconnected ) {
       $.each( disconnected, function( key, conversation ) {

         if ( chatsterDataAdmin.remove_offline_conv == 1 &&
                parseInt($('#ch-message-board').attr('data-conv_id')) !== parseInt(conversation.id) ) {
                  remove_disconnected( conversation.id );
         } else {
                 $('#conv-'+conversation.id).attr( 'data-is_connected', false );
                 $('#conv-'+conversation.id).addClass('disconnected');
                 $('#conv-'+conversation.id).find('.ch-disconnect').text(chatsterDataAdmin.translation.delete);
                 if ( parseInt($('#ch-message-board').attr('data-conv_id')) === parseInt(conversation.id) ) {
                    let disconnect_link = $('#conv-'+conversation.id).find('.ch-disconnect');
                    disable_disconnected( conversation.id, disconnect_link );
                 }
         }

       });
   }
  }
  function update_unread_messages( new_messages_count ) {
   if ( new_messages_count ) {
     $.each( new_messages_count, function( key, conversation ) {
       if ($('#ch-message-board').attr('data-conv_id') != conversation.id ) {
         $('#conv-'+conversation.id).find('.unread').text( conversation.not_read ).show(300);
         console.log(conversation.not_read);
       }
     });
   }
  }
  function update_queue( queue_number ) {

   if ( queue_number && queue_number == 1 ) {
     $("#ch-queue-counter").find('.ch-plural').slideUp(200);
     let $queue = $("#ch-queue-counter").find('.ch-singular');
     $queue.find('span').text(queue_number);
     $queue.slideDown(200);
   }
   else if ( queue_number > 1 ){
     $("#ch-queue-counter").find('.ch-singular').slideUp(200);
     let $queue = $("#ch-queue-counter").find('.ch-plural');
     $queue.find('span').text(queue_number);
     $queue.slideDown(200);
   } else {
     $("#ch-queue-counter div").hide(100);
   }

  }

  /**
   * Retrieves messages for the current conversation
   */
  function scrollTopAdminChat() {
   $("#ch-reply-block").animate({ scrollTop: $('#ch-reply-block').prop("scrollHeight")}, 400);
  }
  function get_messages() {

    let ch_current_conv = $('#ch-message-board').attr('data-conv_id');
    ch_current_conv = ch_current_conv ? ch_current_conv : 0;
    let ch_last_msg = $("#ch-message-board").attr('data-last_msg_id');
    ch_last_msg = ch_last_msg ? ch_last_msg : 0;

    var payload = { current_conv: ch_current_conv, last_message: ch_last_msg };

    $.ajax( {
        url: chatsterDataAdmin.api_base_url + '/chat/messages/admin',
        method: 'POST',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
        },
        data: payload,
        success: function(data) {
          //  $('#ch-roller-container').addClass('hidden');

            if ( $('#chatster-chat-switch').prop("checked") ) {
              build_current_conv(data.payload.current_conv, ch_current_conv);
            }
            $('#ch-loading-conversation').css('display', 'none');
            scrollTopAdminChat();
        },
        error: function(error) {
          //  $('#ch-roller-container').addClass('hidden');
        },

      } ).done( function ( response ) {

      });
  }

  /**
   * Long poll with self relaunching technique
   */
  var LongPollRun = false;
  var lp_ajax = false;
  function long_poll( first_load ) {
    if ( LongPollRun === false ) {
      first_load = typeof(first_load) !== 'undefined' ? first_load : false;
      LongPollRun = true;
      let ch_last_conv = $('#conversations-block').attr('data-last_conv_id');
      ch_last_conv = ch_last_conv ? ch_last_conv : 0;
      let ch_current_conv = $('#ch-message-board').attr('data-conv_id');
      ch_current_conv = ch_current_conv ? ch_current_conv : 0;
      let ch_last_msg = $("#ch-message-board").attr('data-last_msg_id');
      ch_last_msg = ch_last_msg ? ch_last_msg : 0;
      var ch_conv_list = $(".single-conversation").map(function(){
        if ( $(this).attr("data-is_connected") == 'true' ) { return $(this).attr("data-single_conv_id"); }
          return;
        }).get();
      ch_conv_list = ch_conv_list ? ch_conv_list : 0;
      var payload = { last_conv: ch_last_conv, current_conv: ch_current_conv, last_message: ch_last_msg, conv_ids: ch_conv_list, first_load: first_load };

      lp_ajax = $.ajax( {
          url: chatsterDataAdmin.api_base_url + '/chat/polling/admin',
          method: 'POST',
          beforeSend: function ( xhr ) {
              xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
          },
          data: payload,
          success: function(data) {
              $('#ch-roller-container').addClass('hidden');
              if ( $('#chatster-chat-switch').prop("checked") ) {

                build_convs(data.payload.convs);
                build_current_conv(data.payload.current_conv, ch_current_conv);
                update_disconnected(data.payload.disconnected);
                update_unread_messages(data.payload.new_messages);
                update_queue(data.payload.queue_number);

                setTimeout( long_poll, 500 );
              }
              LongPollRun = false;

          },
          error: function(error) {
              $('#ch-roller-container').addClass('hidden');
              LongPollRun = false;
              setTimeout( long_poll, 4000 );
          },

        } ).done( function ( response ) {

        });
      }


  }

  /**
   * Changes "Live chat" status and calls a long_poll
   */
  var chat_switch_debounce = false;
  function resets_chat() {
    if (lp_ajax) {
        lp_ajax.abort();
        lp_ajax = false;
    }
    $('#conversations-block').attr('data-last_conv_id', 0);
    $('.single-conversation').slideUp(100, function() {
        $('#conversations-block').find('.single-conversation').remove();
        $('#ch-load-conv-container').show(200);
    });
    $('#ch-message-board').find('.single-message').remove();
    $('#ch-no-message-overlay').show(100);
    disable_chat_style();
  }
  function change_admin_status( admin_status ) {
    $.ajax( {

        url: chatsterDataAdmin.api_base_url + '/chat/is_active/admin',
        method: 'POST',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
        },
        data: { is_active: admin_status },
        success: function(data) {
          chat_switch_debounce = false;
          $('#switch-loader').addClass('hidden');
          if ( data.payload.is_active == true ) {
              long_poll( true );
          } else {
              resets_chat();
          }
        },
        error: function(error) {
              $('#ch-roller-container').addClass('hidden');
              $('#switch-loader').addClass('hidden');
              $('#chatster-chat-switch').prop("checked", ! admin_status);
              chat_switch_debounce = false;
        },

      } ).done( function ( response ) {

      });
  }
  $('#chatster-chat-switch').change(function(e) {

      if ( chat_switch_debounce ) {
         $('#chatster-chat-switch').prop("checked", ! this.checked );
         return;
      }

      chat_switch_debounce = true;
      $('#switch-loader').removeClass('hidden');
      if( this.checked ) {
        $('#ch-roller-container').removeClass('hidden');
        $('.active-convs-link').removeClass('hidden');
        change_admin_status(true);
      } else {
        $('.active-convs-link').addClass('hidden');
        change_admin_status(false);
      }
   });
  if ( $('#chatster-chat-switch').prop("checked") ) {
        long_poll( true );
  }

 /**
  * Display the backend chat after loading
  */
  $(document).ready(function(){
      $('.wrap').show(500);
  });

})(jQuery);
