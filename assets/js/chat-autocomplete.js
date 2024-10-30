(function ($) {

  /**
  * Adds product and page links with search and autocomplete
  */
  var autocompleteURL = chatsterDataAdmin.wp_api_base_url + 'wp/v2/search';
  $('.ch-chat-autocomplete').autocomplete({ hint: true },
    [
      {
         source: function(query, cb) {
             $.ajax( {
                 url: autocompleteURL + '?search=' + query + '&_embed' ,
                 method: 'GET',
                 beforeSend: function ( xhr ) {
                     xhr.setRequestHeader( 'X-WP-Nonce', chatsterDataAdmin.nonce );
                 }
               } ).then( function ( data ) {
                     cb(data);
               })
         },
         debounce: 500,
         templates: {
            suggestion: function(suggestion, answer) {
              if ( suggestion.subtype == 'product') {
                  return ch_product_suggestion(suggestion);
              }
              return ch_post_suggestion(suggestion);
            }
         }
       }
     ]
   ).on('autocomplete:selected', function(event, suggestion, dataset, context) {
      // Do nothing on click, as the browser will already do it
      if (context.selectionMethod === 'click') {
        if ( suggestion.subtype == 'product') {
              let selected = ch_product_selected(suggestion);
              $('#ch-attachments').append(selected);
          } else {
              let selected = ch_post_selected(suggestion);
              $('#ch-attachments').append(selected);
          }
        }
        return;
    });

  /**
  * Suggestion - Template functions
  */
  function ch_post_suggestion( post ) {
    let excerpt = post._embedded.self[0].excerpt.rendered ? post._embedded.self[0].excerpt.rendered : '';
    let thumbnail = post._embedded.self[0].ch_thumbnail ? post._embedded.self[0].ch_thumbnail : chatsterDataAdmin.no_image_link;
    let template;

    template  = '<div class="ch-product-auto" id="slink-id-'+ post.id +'">';
    template += ' <div class="ch-auto-img">';
    template +=    '<img src="' + thumbnail + '" alt="product or page" height="62" width="62">';
    template += ' </div>';
    template += ' <div class="ch-auto-descr">';
    template +=     '<div class="ch-auto-title">' + post.title + '</div>';
    template +=     '<div class="ch-auto-excerpt">' + excerpt + '</div>';
    template += ' </div>';
    template += ' <div class="ch-auto-exlink hidden"><a href="' + post.url + '"  target="_blank">Open</a></div>';
    template += '</div>';
    return template;
  }
  function ch_product_suggestion( post ) {
    let excerpt = post._embedded.self[0].excerpt.rendered ? post._embedded.self[0].excerpt.rendered : '';
    let is_stock_class = post._embedded.self[0].ch_status ? 'in-stock' : 'out-of-stock';
    let in_stock_word = is_stock_class == 'in-stock' ? 'In Stock' : 'Out of Stock';
    let prod_type = post._embedded.self[0].ch_variation;
    let thumbnail = post._embedded.self[0].ch_thumbnail ? post._embedded.self[0].ch_thumbnail : chatsterDataAdmin.no_image_link;
    let template;

    template  = '<div class="ch-product-auto" id="slink-id-'+ post.id +'">';
    template += ' <div class="ch-auto-img">';
    template +=    '<img src="' + thumbnail + '" alt="product or page" height="62" width="62">';
    template += ' </div>';
    template += ' <div class="ch-auto-descr">';
    template +=     '<div class="ch-auto-title">' + post.title + '</div>';
    template +=     '<div class="ch-auto-status '+is_stock_class+'">' + in_stock_word + '</div>';
    template +=     '<div class="ch-auto-variation">Product type: ' + prod_type.charAt(0).toUpperCase() + prod_type.slice(1) + '</div>';
    template +=     '<div class="ch-auto-excerpt">' + excerpt + '</div>';
    template += ' </div>';
    template += ' <div class="ch-auto-exlink hidden"><a href="' + post.url + '"  target="_blank">Open</a></div>';
    template += '</div>';
    return template;
  }

  /**
  * Selected - Template functions
  */
  function ch_post_selected( post ) {
    let excerpt = post._embedded.self[0].excerpt.rendered ? post._embedded.self[0].excerpt.rendered : '';
    let thumbnail = post._embedded.self[0].ch_thumbnail ? post._embedded.self[0].ch_thumbnail : chatsterDataAdmin.no_image_link;
    let template;

    template  = '<div class="ch-product-auto" id="link-id-'+ post.id +'" data-link_id="' + post.id + '">';
    template += ' <div class="ch-auto-img">';
    template +=    '<img src="' + thumbnail + '" alt="product or page" height="62" width="62">';
    template += ' </div>';
    template += ' <div class="ch-auto-descr">';
    template +=     '<div class="ch-auto-title"><a href="' + post.url + '"  target="_blank">' + post.title + '</a></div>';
    template +=     '<div class="ch-auto-excerpt">' + excerpt + '</div>';
    template += ' </div>';
    template += ' <div class="ch-auto-delete"><a href="' + post.url + '"  target="_blank">X</a></div>';
    template += '</div>';
    return template;
  }
  function ch_product_selected( post ) {
    let excerpt = post._embedded.self[0].excerpt.rendered ? post._embedded.self[0].excerpt.rendered : '';
    let is_stock_class = post._embedded.self[0].ch_status ? 'in-stock' : 'out-of-stock';
    let in_stock_word = is_stock_class == 'in-stock' ? 'In Stock' : 'Out of Stock';
    let prod_type = post._embedded.self[0].ch_variation;
    let thumbnail = post._embedded.self[0].ch_thumbnail ? post._embedded.self[0].ch_thumbnail : chatsterDataAdmin.no_image_link;
    let template;

    template  = '<div class="ch-product-auto" id="link-id-'+ post.id +'" data-link_id="' + post.id + '">';
    template += ' <div class="ch-auto-img">';
    template +=    '<img src="' + thumbnail + '" alt="product or page" height="62" width="62">';
    template += ' </div>';
    template += ' <div class="ch-auto-descr">';
    template +=     '<div class="ch-auto-title"><a href="' + post.url + '"  target="_blank">' + post.title + '</a></div>';
    template +=     '<div class="ch-auto-status '+is_stock_class+'">' + in_stock_word + '</div>';
    template +=     '<div class="ch-auto-variation">Product type: ' + prod_type.charAt(0).toUpperCase() + prod_type.slice(1) + '</div>';
    template +=     '<div class="ch-auto-excerpt">' + excerpt + '</div>';
    template += ' </div>';
    template += ' <div class="ch-auto-delete"><a href="' + post.url + '"  target="_blank">X</a></div>';
    template += '</div>';
    return template;
  }
  // Removes attachment from pending message
  $( document ).on( "click", '.ch-auto-delete', function(e) {
      e.preventDefault();
      $(this).parent().remove();
  });

})(jQuery);
