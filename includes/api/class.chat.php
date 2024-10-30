<?php

namespace Chatster\Api;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/includes/core/trait.chat.php' );
require_once( CHATSTER_PATH . '/includes/api/class.global-api.php' );

use Chatster\Core\ChatCollection;
use Chatster\Core\Crypto;
use Chatster\Core\ChatFormSerializer;

class ChatApi extends GlobalApi  {
  use ChatCollection;

    protected $customer_id;
    private $customer_name;
    private $customer_email;
    private $customer_subject;
    private $assigned_admin;

    public function __construct() {
      $this->presence_route();
      $this->set_chat_form_route();
      $this->poll_ticketing_route();
      $this->poll_msg_route();
      $this->insert_msg_route();
      $this->disconnect_chat_route();
    }

    /**
     * Routes
     */
    public function presence_route() {
      add_action('rest_api_init', function () {
       register_rest_route( 'chatster/v1', '/chat/presence/customer', array(
                     'methods'  => 'POST',
                     'callback' => array( $this, 'set_presence' ),
                     'permission_callback' => array( $this, 'validate_customer' )
           ));
      });
    }

    public function set_chat_form_route() {
      add_action('rest_api_init', function () {
        register_rest_route( 'chatster/v1', '/chat/chat-form', array(
                      'methods'  => 'POST',
                      'callback' => array( $this, 'insert_form_data_db' ),
                      'permission_callback' => array( $this, 'validate_customer_form' )
            ));
      });
    }

    public function poll_ticketing_route() {
      add_action('rest_api_init', function () {
        register_rest_route( 'chatster/v1', '/chat/ticketing', array(
                      'methods'  => 'POST',
                      'callback' => array( $this, 'long_poll_ticketing' ),
                      'permission_callback' => array( $this, 'validate_customer' )
            ));
      });
    }

    public function poll_msg_route() {
      add_action('rest_api_init', function () {
        register_rest_route( 'chatster/v1', '/chat/poll', array(
                      'methods'  => 'POST',
                      'callback' => array( $this, 'long_poll_db' ),
                      'permission_callback' => array( $this, 'validate_msg_poll' )
            ));
      });
    }

    public function insert_msg_route() {
      add_action('rest_api_init', function () {
        register_rest_route( 'chatster/v1', '/chat/insert', array(
                      'methods'  => 'POST',
                      'callback' => array( $this, 'insert_msg_db' ),
                      'permission_callback' => array( $this, 'validate_message' )
            ));
      });
    }

    public function disconnect_chat_route() {
      add_action('rest_api_init', function () {
        register_rest_route( 'chatster/v1', '/chat/disconnect', array(
                      'methods'  => 'POST',
                      'callback' => array( $this, 'disconnect_chat_db' ),
                      'permission_callback' => array( $this, 'validate_customer' )
            ));
      });
    }


    /**
     * Validation Callbacks
     */

    public function validate_message( $request ) {

      if ( $this->validate_customer( $request ) ) {

          $request['new_message'] = $this->validate_request_msg( $request['new_message'], 799);
          $request['temp_id'] = $this->validate_int_id( $request['temp_id'] );
          // conv_id is checked against the table before insert -
          // Inserts by user that is not present in the conversation will not be allowed
          $request['conv_id'] = $this->validate_int_id( $request['conv_id'] );

          if ( $request['new_message'] &&
                  $request['temp_id'] &&
                     $request['conv_id'] ) {
                       return true;
          }
      }

      return false;
    }

    public function validate_msg_poll( $request ) {
      if ( $this->validate_customer( $request ) ) {

          $request['last_msg_id'] = isset( $request['last_msg_id'] ) ? intval($request['last_msg_id']) : 0;
          return true;

      }
      return false;
    }

    public function validate_customer_form( $request ) {
      if ( $this->validate_customer( $request ) ) {
          $request['customer_name'] = isset($request['customer_name']) ? $this->validate_name( $request['customer_name'] ) : false;
          $request['customer_email'] = isset($request['customer_email']) ? $this->validate_email($request['customer_email']) : false;
          $request['chat_subject'] = isset($request['chat_subject']) ? $this->validate_subject($request['chat_subject']) : false;

          return ChatFormSerializer::set_form_data($request);
      }
      return false;
    }

    /**
     * Routes Callbacks
     */
    public function set_presence( \WP_REST_Request $data ) {
        $this->insert_presence_customer( $this->customer_id );
        return array('action'=> $this->customer_id);
    }

    public function insert_form_data_db( \WP_REST_Request $data ) {
        $result = $this->insert_form_data( $this->customer_id, ChatFormSerializer::serialized_form_data(false) );
        return array( 'action'=> 'chat_form', 'payload'=> $result );
    }

    public function long_poll_ticketing( \WP_REST_Request $data ) {
      // If ALL Admins are no longer available
      if ( ! self::is_chat_available() ) {
        return array('action'=>'ticket_polling', 'payload'=> array( 'chat_active' => false ) );
      }
      // If conversation was already started and still active
      $current_conv = $this->get_active_conv_public( $this->customer_id );
      if ( $current_conv ) {
        return array('action'=>'ticket_polling', 'payload'=> array(   'conv_id' => $current_conv->id,
                                                                      'admin_name' => $current_conv->admin_name,
                                                                      'admin_thumb_url' => esc_url( get_avatar_url( $current_conv->admin_email ) )  ));
      }
      // Ticket polling system
      $queue_status = 1;
      for ($x = 0; $x <= 4; $x++) {
        // Check queue status
        $queue_total = $this->get_queue_number();
        if ( $queue_total == 0 || $queue_status == 0 ) {
          global $ChatsterOptions;
          $max_allowed = $ChatsterOptions->get_chat_option('ch_chat_max_conv');
          // Check if any admin is available and has less than n chats open
          if ( $assigned_admin = $this->find_active_admin( $max_allowed ) ) {
            if ( isset( $ticket ) ) {
              $this->delete_ticket( $ticket );
            }
            // Returns the new conversation id with admin name
            $conv_id = $this->set_new_conversation( $this->customer_id, $assigned_admin->admin_email );
            return array('action'=>'ticket_polling', 'payload'=> array( 'conv_id' => $conv_id,
                                                                        'admin_name' => ucfirst($assigned_admin->admin_name ),
                                                                        'admin_thumb_url' => esc_url( get_avatar_url( $assigned_admin->admin_email ) )  ) );
          }
        }
        // Returns the poll if no ticket issued yet and shows the queue length to the user
        if ( false === $this->get_ticket( $this->customer_id ) ) {
          $ticket = $this->set_ticket( $this->customer_id );
          $queue_status = $this->get_queue_status( $ticket );
          break;
        }
        // Gets a new ticket or refresh the same ticket updated_at
        $ticket = $this->set_ticket( $this->customer_id );
        $queue_status = $this->get_queue_status( $ticket );
        sleep(1);
      }
      // Returns the queue number of people waiting
      return array('action'=>'ticket_polling', 'payload'=> array( 'queue_status' => $queue_status ) );
    }

    public function insert_msg_db( \WP_REST_Request $data) {

      $result = $this->insert_new_message( $data['conv_id'], $this->customer_id, $data['new_message'], $data['temp_id'] );
      return array( 'action'=>'chat_insert', 'payload'=> $result, 'temp_id'=> $data['temp_id'] );
    }

    public function long_poll_db( \WP_REST_Request $data ) {
        for ($x = 0; $x <= 10; $x++) {
            $new_messages = $this->get_latest_messages_public( $this->customer_id, $data['conv_id'], $data['last_msg_id'] );
            $is_connected = $this->get_active_conv_public( $this->customer_id );
            if ( $new_messages || !$is_connected ) {
              $this->set_message_read( $this->customer_id, $data['conv_id'], $data['last_msg_id'] );
              break;
            };
            usleep(700000);
        }
        return array('action'=>'polling', 'payload'=> $new_messages, 'status'=> $is_connected );
    }

    public function disconnect_chat_db( \WP_REST_Request $data ) {
        $disconnect = $this->disconnect_chat_customer($this->customer_id);
        return array('action'=> 'diconnect', 'payload'=> $disconnect );
    }


}


new ChatApi();
