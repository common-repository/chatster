(function ($) {
"use strict";

  /**
   * Builds the reply messages and shows the reply section
   */
  function esc_json(str) {
     return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  function reply_template( message_obj ) {
    let $reply = $("<div>", {id: "message-" + esc_json(message_obj.id), "class": "ch-single-reply"});
    let $reply_info = $("<div>", { "class": "ch-reply-info" });
    let $reply_text = $("<div>", {"class": "ch-reply-text"});

    $reply_info =  '<span class="replier-admin-intro">'+esc_json(chatsterDataAdmin.translation.admin)+': '+
    '</span><span>'+esc_json(message_obj.admin_name ? message_obj.admin_name : '') +'</span>';
    $reply_info += '<span>('+ esc_json(message_obj.admin_email)+')</span>&vert; ';
    $reply_info += '<span>'+ esc_json(message_obj.replied_at)+'</span>';

    $reply_text.html(message_obj.message);
    $reply.append($reply_info).append($reply_text);
    return $reply;
  }
  function build_reply_conv(replies, request_id) {

    if ( replies ) {
      var $reply_container = $('#reply-section-' + request_id).find('.reply-all-container').empty();
      $.each( replies, function( key, message ) {
        let $reply = reply_template(message);
        $reply_container.append($reply);
      });

      $reply_container.slideDown(300);

    }

  }
  function get_replies(request_id) {

    var payload = { request_id: request_id };

    $.ajax( {
        url: chatsterDataAdmin.api_base_url + '/request/admin/retrieve',
        method: 'POST',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
        },
        data: payload,
        success: function(data) {
          build_reply_conv(data.payload, request_id);
        },
        error: function(error) {

        },

      } ).done( function ( response ) {
          $('#reply-section-' + request_id).find('.ch-small-loader').stop().slideUp(10, function(){ $(this).addClass('hidden');});
      });
  }
  $('.reply').on('click', function(e) {
      e.preventDefault(); e.stopPropagation();
      let request_id = $(this).parent().attr('data-request_id');
      $('.reply-container').slideUp(100);
      $('#reply-section-' + request_id).find('.ch-small-loader').stop().removeClass('hidden').slideDown(100);
      $('#reply-section-' + request_id).find('.reply-container').slideDown(300);
      get_replies(request_id);

  });

  /**
   * Changes the pinned status for each request
   */
  function change_flagged(request_id, flag_status) {
    let $flag = $('#request-' + request_id).find('.pinned-flag');
    // Reverses to actual status if DB not updated.
    if (flag_status) {
      $flag.removeClass('unflagged');
    } else {
      $flag.addClass('unflagged');
    }
    // Removes the request id from the debounce array so it can be called again.
    var index = debounce_id.indexOf(request_id);
    if (index > -1) {
       debounce_id.splice(index, 1);
    }

  }
  function pin_request(request_id, pinned_status) {

    var payload = { request_id: request_id, pinned_value: pinned_status };

    $.ajax( {
        url: chatsterDataAdmin.api_base_url + '/request/admin/pin',
        method: 'POST',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
        },
        data: payload,
        success: function(data) {
          change_flagged(request_id, data.payload);
        },
        error: function(error) {
          change_flagged(request_id, !pinned_status);
        },

      } ).done( function ( response ) {

      });
  }
  var debounce_id = [];
  $('.pinned-flag').on('click', function() {
    var request_id = parseInt( $(this).parent().attr('data-request_id') );
    if ( debounce_id.length == 0 || debounce_id.indexOf(request_id) == -1 ) {
        debounce_id.push(request_id);
        let pinned_status = $(this).hasClass('unflagged') == 1 ? 1 : 0;
        let $flag = $('#request-' + request_id).find('.pinned-flag');
        $flag.toggleClass('unflagged');
        pin_request(request_id, pinned_status);
    }
  });

  /**
   * Sends the email and saves the reply to the database
   */
  function send_reply_email(request_id, reply_text) {

     var payload = { request_id: request_id, reply_text: reply_text };

     $.ajax( {
         url: chatsterDataAdmin.api_base_url + '/request/admin/reply',
         method: 'POST',
         beforeSend: function ( xhr ) {
             xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
         },
         data: payload,
         success: function(data) {
          if (data.payload) {
            let $reply_section = $('#reply-section-' + request_id);
            $reply_section.find('textarea').val('');
            $reply_section.find('.ch-smaller-loader').hide();
            $reply_section.find(".ch-btn-reply input").prop("disabled",false).removeClass('disabled');
            let $reply_container = $('#reply-section-' + request_id).find('.reply-all-container');
            let $reply = reply_template(data.payload);
            $reply.hide();
            $reply_container.append($reply);
            $reply.slideDown(300);
          }
         },
         error: function(error) {

         },

       } ).done( function ( response ) {
       });
   }
  $(".ch-reply-form").submit(function(e){
      e.preventDefault();
      let reply_text = $(this).find('textarea').val();
      let request_id =  $(this).attr('data-request_id');
      $(this).find('.ch-smaller-loader').show(100);
      $(this).find(".ch-btn-reply input").prop("disabled",true).addClass('disabled');
      send_reply_email(request_id, reply_text);
  });

  /**
   * Deletes the request and related messages
   */
  function no_request_action() {

    // IF no more results nor paginated -> Show No results Block
    if ( $('.request-row').length == 0 &&
           $('.ch-pagination').length == 0 ) {
        $('#ch-no-results').show();
        $('#ch-request-list').hide();
        $('#show-replied').hide();
        window.location.reload();
    }
    // IF no more results BUT has pagination -> Reload
    else if ( $('.request-row').length == 0 &&
               $('.ch-pagination').length > 0 ) {
        window.location.reload();
    }
  }
  function delete_request( request_id ) {

    var payload = { request_id: request_id };

    $.ajax( {
        url: chatsterDataAdmin.api_base_url + '/request/admin/delete',
        method: 'POST',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
        },
        data: payload,
        success: function(data) {
         if (data.payload) {
           $('#reply-section-' + request_id + ', #striped-separator-'+ request_id).hide(200, function () {
              $(this).remove();
             });
           $('#request-' + request_id).hide(200, function () {
              $(this).remove();
              no_request_action();
            });
         }
        },
        error: function(error) {

        },

      } ).done( function ( response ) {
         $('#reply-section-'+request_id).find(".ch-smaller-loader").hide();
         no_request_action();
      });
  }
  $('.delete').on('click', function(e) {
    e.preventDefault(); e.stopPropagation();
    let request_id = $(this).parent().attr('data-request_id');
    $('#reply-section-'+request_id).find(".ch-smaller-loader").show(100);
    delete_request(request_id);
  });

 /**
  * Slide Select Show Replied/Unreplied
  */
  $('#show-replied').on('click', function(e) {
    e.stopPropagation();
    setTimeout( function() {
      if ( $('.switch').find('input').attr('checked') == 'checked' ) {
         window.location.href = chatsterDataAdmin.go_to_show_unreplied;
      } else {
         window.location.href = chatsterDataAdmin.go_to_hide_unreplied;
      }
    }, 410);
  });

  /**
   * Display the backend chat after loading
   */
   $(document).ready(function(){
       $('.wrap').show(500);
   });

})(jQuery);
