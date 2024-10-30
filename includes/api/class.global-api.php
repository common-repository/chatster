<?php

namespace Chatster\Api;

if ( ! defined( 'ABSPATH' ) ) exit;

class GlobalApi  {

  private static $attributes = [
                                  'chat_subject_length' => 100,
                                  'chat_name_length' => 100
                                ];

  public static function get_attribute( $key = '' ) {
    if ( array_key_exists($key, self::$attributes) ) {
      return self::$attributes[$key];
    }
    return false;
  }

  protected function set_customer_id_cookie() {
     if ( ! $this->customer_id ) {
       $this->customer_id = substr(md5(uniqid(rand(), true)), 0, 19);
     }
     setrawcookie('ch_ctmr_id', base64_encode($this->customer_id) , (time() + 8419200), "/");
     return $this->customer_id;
  }

  public function get_customer_id() {
    $this->customer_id = false;
    $current_user = wp_get_current_user();
    if ( $current_user && get_current_user_id() ) {
      return $this->customer_id = sanitize_email( $current_user->user_email );
    }

    if ( isset($_COOKIE['ch_ctmr_id'])) {
      $this->customer_id = $this->validate_customer_id( base64_decode($_COOKIE['ch_ctmr_id']) );
    }

    return $this->set_customer_id_cookie();
  }

  protected function get_admin_email() {
    if ( current_user_can( 'manage_options' ) ) {
      $current_user = wp_get_current_user();
      if ( $current_user && get_current_user_id() ) {
        return $this->admin_email = $current_user->user_email;
      }
    }
    return false;
  }

  public function validate_admin( $request ) {
    if ( $this->get_admin_email() ) {
      $request['chatster_admin_email'] = $this->admin_email;
      return true;
    }
    return false;
  }

  protected function format_timezone($dateTime = false) {
    if ( $dateTime ) {
      $dt = new \DateTime("now", chatter_get_timezone() );
      $dt->setTimestamp(strtotime($dateTime));
      return esc_attr( $dt->format('F d, Y h:i A') );
    }
    return $dateTime;
  }

  public function validate_customer_id( $customer_id = '' ) {
    $customer_id = sanitize_text_field( $customer_id );
    if ( !empty($customer_id) &&
            strlen( $customer_id ) == 19 &&
              ! is_email( $customer_id ) ) {

      return $customer_id;
    }

    return false;
  }


  public function validate_name( $customer_name = '' ) {
    if ( !empty($customer_name) && strlen( $customer_name ) <= 100 ) {
      return htmlentities( $customer_name, ENT_QUOTES, 'UTF-8');
    }
    return false;
  }

  public function validate_subject( $chat_subject = '') {
    if ( !empty($chat_subject) && strlen( $chat_subject ) <= 200 ) {
      return htmlentities( $chat_subject, ENT_QUOTES, 'UTF-8');
    }
    return false;
  }

  public function validate_email( $email = '' ) {
    if ( !empty($email) && is_email($email) ) {
      return htmlentities( $email, ENT_QUOTES, 'UTF-8');
    }
    return false;
  }

  public function validate_request_msg( $message = '', $length = 1500 ) {
    if ( !empty($message) && strlen( $message ) <= $length ) {
      return nl2br( htmlentities( $message, ENT_QUOTES, 'UTF-8'));
    }
    return false;
  }

  public function validate_text_length( $message, $length) {
    if ( !empty($message) ) {
      $message = trim($message);
      if (strlen($message) > 0 && strlen($message) <= $length ) {
        return $message;
      }
    }
    return false;
  }

  public function validate_int_id( $id ) {
    if ( !empty($id) ) {
        return intval($id) > 0 ? intval($id) : false;
    }
    return false;
  }

  public function validate_customer( $request ) {

    $request['chatster_customer_id'] = $this->get_customer_id();
    return true;

  }

}
