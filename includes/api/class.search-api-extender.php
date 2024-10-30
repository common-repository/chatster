<?php
namespace Chatster\Api;

if ( ! defined( 'ABSPATH' ) ) exit;

class SearchApiExtender {

  public function __construct() {
    add_filter( 'rest_api_init', array( $this,'ch_add_search_api_fields') );
  }

  public function ch_add_search_api_fields () {

    // Embeds thumbnail in Search
    register_rest_field(    'product',
                            'ch_thumbnail',
                             array(
                                   'get_callback' => array( $this,'get_thumbnail')
                                  )
    );
    register_rest_field(    'post',
                            'ch_thumbnail',
                             array(
                                   'get_callback' => array( $this,'get_thumbnail')
                                  )
    );
    register_rest_field(    'page',
                            'ch_thumbnail',
                             array(
                                   'get_callback' => array( $this,'get_thumbnail')
                                  )
    );
    // Adds in stock attribute for products
    register_rest_field(    'product',
                            'ch_status',
                             array(
                                   'get_callback' => array( $this,'get_product_status')
                                  )
    );
    // Adds variation flag for products
    register_rest_field(    'product',
                            'ch_variation',
                             array(
                                   'get_callback' => array( $this,'get_product_type')
                                  )
    );
  }

  public function get_thumbnail($object) {
      return  esc_url( get_the_post_thumbnail_url($object['id'], 'thumbnail') );
  }

  public function get_product_status($object) {
      $is_in_stock = false;
      if ( $product = wc_get_product($object['id']) ) {
        $is_in_stock = esc_attr( $product->is_in_stock() );
      }
      return  $is_in_stock;
  }

  public function get_product_type($object) {
      $product_type = false;
      if ( $product = wc_get_product($object['id']) ) {
        $product_type = esc_html($product->get_type());
      }
      return  $product_type;
  }

}

new SearchApiExtender();
