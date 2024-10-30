<?php

namespace Chatster\Api;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/includes/core/trait.request.php' );
require_once( CHATSTER_PATH . '/includes/api/class.global-api.php' );
require_once( CHATSTER_PATH . '/includes/core/class.emailer.php' );

use Chatster\Core\RequestCollection;
use Chatster\Core\Emailer;

class RequestApiAdmin extends GlobalApi  {
  use RequestCollection;

  private $admin_email;

    public function __construct() {

      $this->reply_request_message_route();
      $this->delete_request_message_route();
      $this->get_request_message_route();
      $this->insert_request_message_route();
      $this->send_test_email_route();
      $this->pin_request_message_route();

    }

  /**
   * Routes
   */
    public function reply_request_message_route() {
      add_action('rest_api_init', function () {
       register_rest_route( 'chatster/v1', '/request/admin/reply', array(
                     'methods'  => 'POST',
                     'callback' => array( $this, 'reply_received_request' ),
                     'permission_callback' => array( $this, 'validate_reply_request' )
           ));
      });
    }

    public function delete_request_message_route() {
      add_action('rest_api_init', function () {
       register_rest_route( 'chatster/v1', '/request/admin/delete', array(
                     'methods'  => 'POST',
                     'callback' => array( $this, 'delete_received_request' ),
                     'permission_callback' => array( $this, 'validate_admin' )
           ));
      });
    }

    public function get_request_message_route() {
      add_action('rest_api_init', function () {
       register_rest_route( 'chatster/v1', '/request/admin/retrieve', array(
                     'methods'  => 'POST',
                     'callback' => array( $this, 'get_admin_replies' ),
                     'permission_callback' => array( $this, 'validate_admin' )
           ));
      });
    }

    public function pin_request_message_route() {
      add_action('rest_api_init', function () {
       register_rest_route( 'chatster/v1', '/request/admin/pin', array(
                     'methods'  => 'POST',
                     'callback' => array( $this, 'set_request_pin' ),
                     'permission_callback' => array( $this, 'validate_admin' )
           ));
      });
    }

    public function send_test_email_route() {
      add_action('rest_api_init', function () {
       register_rest_route( 'chatster/v1', '/request/admin/email/test', array(
                     'methods'  => 'POST',
                     'callback' => array( $this, 'send_test_email' ),
                     'permission_callback' => array( $this, 'validate_admin' )
           ));
      });
    }

    // Public Route - Insert Request
    public function insert_request_message_route() {
      add_action('rest_api_init', function () {
       register_rest_route( 'chatster/v1', '/request/public/insert', array(
                     'methods'  => 'POST',
                     'callback' => array( $this, 'insert_request_form' ),
                     'permission_callback' => array( $this, 'validate_customer_request_form' )
           ));
      });
    }

    /**
     * Methods
     */
     public function get_admin_email() {
       if ( current_user_can( 'manage_options' ) ) {
         $current_user = wp_get_current_user();
         if ( $current_user && get_current_user_id() ) {
           return $this->admin_email = sanitize_email( $current_user->user_email );
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

     public function validate_customer_request_form( $request ) {
           $fields = array( 'customer_name', 'customer_email', 'customer_subject', 'customer_message' );
           $request['customer_name'] = isset($request['customer_name']) ? $this->validate_name( $request['customer_name'] ) : false;
           $request['customer_email'] = isset($request['customer_email']) ? $this->validate_email($request['customer_email']) : false;
           $request['customer_subject'] = isset($request['customer_subject']) ? $this->validate_subject($request['customer_subject']) : false;
           $request['customer_message'] = isset($request['customer_message']) ? $this->validate_request_msg($request['customer_message']) : false;

           foreach ($fields as $key => $field ) {
               if ( empty( $request[$field] ) ) {
                 return false;
               }
           }

           return true;

     }

     public function validate_reply_request( $request ) {

       if (  $this->validate_admin( $request ) ) {

           if ( ! $request['reply_text'] =  $this->validate_text_length($request['reply_text'], 1500 ) ) {
             return false;
           }

           if ( ! $request['request_id'] =  $this->validate_int_id($request['request_id'] ) ) {
             return false;
           }

           return true;

       }
       return false;
     }

     /**
      * Routes Callbacks
      */
     public function reply_received_request( \WP_REST_Request $data ) {
        $emailer = new Emailer;
        $result = false;
        $request = $this->get_request_by_id($data['request_id']);
        $request->reply = $data['reply_text'];
        $email_status = $emailer->send_reply_email($request);
        $email_status = true;
        if ( $email_status ) {
          $this->insert_reply( $this->admin_email, $data['reply_text'], $data['request_id'] );
          $latest_email = $this->get_latest_reply($data['request_id']);
          $latest_email->replied_at = $this->format_timezone($latest_email->replied_at);
          $result = $latest_email;
        }

        return array( 'action'=>'reply_request', 'payload'=> $result );

     }

     public function delete_received_request( \WP_REST_Request $data ) {

        $result = $this->delete_request( $data['request_id'] );
        return array( 'action'=>'delete_request', 'payload'=> $result, 'request_id'=>$data['request_id'] );

     }

     public function get_admin_replies( \WP_REST_Request $data ) {

        if ( $result = $this->get_replies( $data['request_id'] ) ) {
          foreach ($result as $value) {
            if ( $value->replied_at ) {
              $value->replied_at = $this->format_timezone($value->replied_at);
            }
          }
        }
        return array( 'action'=>'retrieve_message', 'payload'=> $result, 'request_id'=>$data['request_id'] );
     }

     public function set_request_pin( \WP_REST_Request $data ) {
        $result = $this->pin_request( $data['request_id'], $data['pinned_value'] );
        return array( 'action'=>'retrieve_message', 'payload'=> $result );
     }

     public function insert_request_form( \WP_REST_Request $data ) {
       Global $ChatsterOptions;
       $notify_request = $ChatsterOptions->get_request_option('ch_request_alert') ? 1 : 0;
       $result = $this->insert_request_data( $data['customer_name'], $data['customer_email'], $data['customer_subject'], $data['customer_message'], $notify_request );
       return array( 'action'=> 'request_form', 'payload'=> $result );
     }

     public function send_test_email( \WP_REST_Request $data ) {
       global $current_user;
       wp_get_current_user();
       $emailer = new Emailer();
       $request = new \stdClass();
       $request->email = $data['test_email'];
       $request->subject = __('Testing Chatster! Your email setup works! ', CHATSTER_DOMAIN);
       $request->name = $current_user->display_name;
       $request->message = __('Mock request message.. Customer original message will be shown here!', CHATSTER_DOMAIN );
       $request->reply =  __('This is a test email sent by', CHATSTER_DOMAIN ) . ' <i>Chatster for WooCommerce</i>!<br>'.
                          __('The plugin is working. For more testing, please read the documentation.', CHATSTER_DOMAIN) .'<br>'.
                          __('Test your website link here: ', CHATSTER_DOMAIN).get_site_url().
                          '<br><br>'.__('Thank you.', CHATSTER_DOMAIN);

       $email_status = $emailer->send_reply_email($request);

       return array( 'action'=>'reply_request', 'payload'=> $email_status );
     }

}

new RequestApiAdmin();
