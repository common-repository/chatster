<?php

namespace Chatster\Options;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/includes/options/class.options-global.php' );

class AddOptionsRequest extends OptionsGlobal {

  public static $option_group = 'chatster_request_options';

  public function __construct() {
    add_action( 'admin_init', array( $this, 'register_settings' ) );
  }

  public static function default_values() {
      return array(
          'ch_response_header_url' => '',
          'ch_response_forward' => false,
          'ch_response_forward_email' => '',
          'ch_request_alert' => false,
          'ch_request_alert_email' => '',
          'ch_request_test_email' => ''
      );

  }

  public function register_settings() {

    if ( ! current_user_can( 'manage_options' ) ) return;

    register_setting(
            self::$option_group,
            self::$option_group,
            array( $this, 'validate_options') );

    add_settings_section(
            'ch_request_section',
             esc_html__( 'Chatster Request Settings', CHATSTER_DOMAIN ),
             array( $this, 'description' ),
            'chatster-menu' );

    add_settings_section(
            'ch_request_test_section',
             esc_html__( 'Test Functionality', CHATSTER_DOMAIN ),
             array( $this, 'description' ),
            'chatster-menu' );

    add_settings_field(
            'ch_response_header_url',
            '',
             array( $this, 'text_field_callback'),
            'chatster-menu',
            'ch_request_section',
            ['id'=>'ch_response_header_url',
             'label'=> esc_html__('Email Header Image', CHATSTER_DOMAIN ),
             'placeholder' => 'https://..',
             'description'=> wp_kses( __('Your response email can display an header image.<br>
                              Go to Media -> Library -> Add New, then copy and paste the link in this field.<br>
                              (Optimal aspect ratio: 600 X 230 px.)', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) ) ]
                            );

    add_settings_field(
            'ch_response_forward',
            '',
             array( $this, 'switch_field_callback'),
            'chatster-menu',
            'ch_request_section',
            ['id'=>'ch_response_forward',
             'class'=> 'ch-field-switcher',
             'label'=> esc_html__('Enable Reply Forward', CHATSTER_DOMAIN )]
                      );
                      
    add_settings_field(
            'ch_response_forward_email',
            '',
             array( $this, 'email_field_callback'),
            'chatster-menu',
            'ch_request_section',
            ['id'=>'ch_response_forward_email',
             'label'=> '',
             'class'=> 'ch-field-switchable',
             'required'=> true,
             'placeholder' => esc_html__('Replies will be sent to: your@email.com', CHATSTER_DOMAIN ),
             'description'=> wp_kses( __('If your WordPress website sends email from an email address you don\'t check daily, <br>
                              with this option you can redirect customer replies to an account of your choice.<br><br>
                              Customers replying your initial response email sent from the <i>"Received Messages"</i> section <br>
                              and all future back and forth emails will be routed to this email address instead.', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) )]
                            );
    add_settings_field(
            'ch_request_alert',
            '',
             array( $this, 'switch_field_callback'),
            'chatster-menu',
            'ch_request_section',
            ['id'=>'ch_request_alert',
             'class'=> 'ch-field-switcher',
             'label'=> esc_html__('Enable Email Alert', CHATSTER_DOMAIN )]
                            );
    add_settings_field(
            'ch_request_alert_email',
            '',
             array( $this, 'email_field_callback'),
            'chatster-menu',
            'ch_request_section',
            ['id'=>'ch_request_alert_email',
             'class'=> 'ch-field-switchable',
             'label'=> '',
             'required'=> true,
             'placeholder' => esc_html__('Alerts sent to: your@email.com', CHATSTER_DOMAIN ),
             'description'=> wp_kses( __('Receive an email alert when a new request is submitted.<br>
                              (Wordpress will check for new requests every hour.)', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) )]
                            );

    add_settings_field(
            'ch_request_test_email',
            '',
             array( $this, 'email_field_callback'),
            'chatster-menu',
            'ch_request_test_section',
            ['id'=>'ch_request_test_email',
             'label'=> esc_html__('Enter an Email Address.', CHATSTER_DOMAIN ),
             'required'=> true,
             'placeholder' => esc_html__('Ex: your@email.com', CHATSTER_DOMAIN ),
             'description'=> wp_kses( __('You will receive a mock email to check functionalities.<br>
                              (Depending on your server and service status it may take <br>
                               a few minutes to receive the email. Also check your "junk folder".)', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) )]
                            );

  }

  public function validate_options( $input ) {
    if ( ! current_user_can( 'manage_options' ) ) return;

    if ( !empty($input['default_settings']) &&
            "reset" === $input['default_settings'] ) {
      delete_option( self::$option_group );
      add_settings_error(
          self::$option_group, // Setting slug
          'success_message',
           esc_html__('Chatster Request settings have been reset!', CHATSTER_DOMAIN ),
          'success'
      );
      return false;
    }

    $err_msg = '';
    $input['ch_request_test_email'] = '';
  	$options = get_option( static::$option_group , static::default_values() ) + static::default_values();

    if ( isset($input['ch_response_header_url']) &&
            is_string($input['ch_response_header_url']) ) {

      $input['ch_response_header_url'] = esc_url_raw($input['ch_response_header_url']);

    } else {
      $input['ch_response_header_url'] = $options['ch_response_header_url'];
      $err_msg .= esc_html__('Wrong URL submitted', CHATSTER_DOMAIN ) . '<br>';
    }

    foreach( array( 'ch_response_forward', 'ch_request_alert') as $value ) {
      $field_name = $value . '_email';
      if ( !empty($input[$value]) &&  $input[$value] == 'on' &&
          !empty($input[$field_name]) && is_email($input[$field_name]) ) {
        $input[$value] = true;
        $input[$field_name] = is_email($input[$field_name]);
      } else {
        $input[$value] = false;
        $input[$field_name] =  $options[$field_name];
      }
    }

    // Test Email is a Mock option
    $input['ch_request_test_email'] = '';

    $this->add_success_message( $err_msg );
    return $input;
  }

}

new AddOptionsRequest;
