<?php

namespace Chatster\Core;
require_once( CHATSTER_PATH . '/includes/functions.global.php' );

class ChatFormSerializer  {

  private static $customer_name = false;
  private static $customer_email = false;
  private static $chat_subject = false;

  private static $form_fields = ['customer_name', 'customer_email', 'chat_subject'];

  public static function serialized_form_data( $encoding = true ) {
      $field_container = array();
      foreach ( self::$form_fields as $field ) {
        if ( !empty(self::$$field)) {
          $field_container[$field] = sanitize_text_field( self::$$field );
        }
      }
      if ( $encoding ) {
        return !empty($field_container) ? base64url_encode(serialize($field_container)) : false;
      } else {
        return !empty($field_container) ? serialize($field_container) : false;
      }

  }

  public static function deserialized_form_data() {

      if ( !isset( $_COOKIE['ch_form_data']) ||
             empty( $_COOKIE['ch_form_data'])) return false;

      $form_container = unserialize( base64url_decode( $_COOKIE['ch_form_data'] ));

      foreach ( self::$form_fields as $field ) {
        if ( !empty($form_container[$field])) {
           self::$$field = esc_html($form_container[$field]);
        }
      }

      return !empty($form_container) ? $form_container : true;
  }

  public static function set_form_data( $form ) {
    $is_not_empty = false;
    foreach ( self::$form_fields as $field ) {
      if ( !empty($form[$field])) {
         self::$$field = sanitize_text_field( $form[$field] );
      } else {
        return false;
      }
    }
    return true;
  }

  public static function set_cookie_form_data() {
    return setrawcookie('ch_form_data', self::serialized_form_data() , (time() + 8419200), "/");
  }

}
