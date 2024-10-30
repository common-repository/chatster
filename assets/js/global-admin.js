(function ($) {
"use strict";

  /**
   * Checks chat status from anywhere else in the WP Admin menu
   */
   function check_online_status() {

     $.ajax( {
         url: chatsterDataAdmin.api_base_url + '/chat/status/admin',
         method: 'GET',
         beforeSend: function ( xhr ) {
             xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
         },
         data: {},
         success: function(data) {

           let status = data.payload.is_active == true ? true : false;
           if ( status ) {
              $('.active-convs-link').removeClass('hidden');
           } else {
              $('.active-convs-link').addClass('hidden');
           }

         },
         error: function(error) {

         },

       } ).done( function ( response ) {

       });

   }
   setInterval(check_online_status, 6000);

})(jQuery);
