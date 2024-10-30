<?php

namespace Chatster\Options;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/includes/options/class.options-global.php' );

class AddOptionsChat extends OptionsGlobal {

  public static $success_set = false;
  public static $option_group = 'chatster_chat_options';
  public static $fields_maxlength = [
                                      'ch_chat_header' => 90,
                                      'ch_chat_intro' => 100
                                    ];

  public function __construct() {
    add_action( 'admin_init', array( $this, 'register_chat_settings' ) );
  }

  public static function default_values() {

      return array(
          'ch_chat_header' => esc_html__('Chat or get in touch!', CHATSTER_DOMAIN),
          'ch_chat_intro' => esc_html__('Contact Us', CHATSTER_DOMAIN),
          'ch_chat_header_back_color' => '#04346D',
          'ch_chat_header_text_color' => '#FAFAFA',
          'ch_chat_screen_position' => 'right',
          'ch_chat_text_size' => 'medium',
          'ch_chat_volume' => 25,
          'ch_chat_fontawesome' => true,
          'ch_chat_volume_admin' => 25,
          'ch_chat_max_conv' => 10,
          'ch_chat_auto_offline' => 10,
          'ch_chat_remove_offline_conv_int' => 5,
          'ch_chat_remove_offline_conv' => true
      );

  }

  public static function get_options_select($option_name) {

      switch ($option_name) {

          case 'ch_chat_max_conv':
              return array(
                  '5 '.esc_html__('Customers', CHATSTER_DOMAIN) =>  5,
                  '10 '.esc_html__('Customers', CHATSTER_DOMAIN) => 10,
                  '15 '.esc_html__('Customers', CHATSTER_DOMAIN) => 15,
                  '20 '.esc_html__('Customers', CHATSTER_DOMAIN) => 20,
                  '25 '.esc_html__('Customers', CHATSTER_DOMAIN) => 25
              );
              break;

          case 'ch_chat_auto_offline':
              return array(
                  '3 '.esc_html__('Minutes', CHATSTER_DOMAIN)  => 3,
                  '5 '.esc_html__('Minutes', CHATSTER_DOMAIN)  => 5,
                  '10 '.esc_html__('Minutes', CHATSTER_DOMAIN) => 10,
                  '15 '.esc_html__('Minutes', CHATSTER_DOMAIN) => 15
              );
              break;

          case 'ch_chat_remove_offline_conv_int':
              return array(
                  '3 '.esc_html__('Minutes', CHATSTER_DOMAIN)  => 3,
                  '5 '.esc_html__('Minutes', CHATSTER_DOMAIN)  => 5,
                  '10 '.esc_html__('Minutes', CHATSTER_DOMAIN) => 10
              );
              break;
      }

  }

  public static function get_options_radio($option_name) {

      switch ($option_name) {

          case 'ch_chat_text_size':
              return array(
                  'small'=> '<h5>'.esc_html__('Small Text', CHATSTER_DOMAIN).'</h5>',
                  'medium'=> '<h4>'.esc_html__('Medium Text', CHATSTER_DOMAIN).'</h4>',
                  'large'=> '<h3>'.esc_html__('Large Text', CHATSTER_DOMAIN).'</h3>'
              );
              break;
          case 'ch_chat_screen_position':
              return array(
                  'left'=> esc_html__('Left Side of the screen', CHATSTER_DOMAIN),
                  'right'=> esc_html__('Right Side of the screen', CHATSTER_DOMAIN)
              );
              break;
      }

  }

  public function register_chat_settings() {

    if ( ! current_user_can( 'manage_options' ) ) return;

        register_setting(
                'chatster_chat_options',
                'chatster_chat_options',
                 array( $this, 'validate_chat_options') );

        add_settings_section(
                'ch_chat_section',
                 esc_html( 'Front Chat Settings', CHATSTER_DOMAIN ),
                 array( $this, 'description' ),
                'chatster-menu' );

        add_settings_section(
                'ch_chat_admin_section',
                 esc_html( 'Admin Chat Settings', CHATSTER_DOMAIN ),
                 array( $this, 'description' ),
                'chatster-menu' );

        // --- ch_chat_section ---

        add_settings_field(
                'ch_chat_header_back_color',
                '',
                 array( $this, 'color_picker_field_callback'),
                'chatster-menu',
                'ch_chat_section',
                ['id'=>'ch_chat_header_back_color',
                 'label'=> esc_html( 'Header/Button Background', CHATSTER_DOMAIN ),
                 'description'=> esc_html( 'Message stated at the top of the chat.', CHATSTER_DOMAIN )] );

        add_settings_field(
                'ch_chat_header_text_color',
                '',
                 array( $this, 'color_picker_field_callback'),
                'chatster-menu',
                'ch_chat_section',
                ['id'=>'ch_chat_header_text_color',
                 'label'=> esc_html( 'Contrast Text Color', CHATSTER_DOMAIN ),
                 'description'=> esc_html( 'Message stated at the top of the chat.', CHATSTER_DOMAIN )] );

       add_settings_field(
               'ch_chat_intro',
               '',
                array( $this, 'text_field_callback'),
               'chatster-menu',
               'ch_chat_section',
               ['id'=>'ch_chat_intro',
                'label'=> esc_html( 'Chat Intro', CHATSTER_DOMAIN ),
                'description'=> esc_html( 'The main link that opens the chat. (Contact Us, Chat, Send a message, etc.)', CHATSTER_DOMAIN )] );

       add_settings_field(
               'ch_chat_header',
               '',
                array( $this, 'text_field_callback'),
               'chatster-menu',
               'ch_chat_section',
               ['id'=>'ch_chat_header',
                'label'=> esc_html( 'Chat Header', CHATSTER_DOMAIN ),
                'description'=> esc_html( 'Message stated at the top of the chat.', CHATSTER_DOMAIN )] );

       add_settings_field(
               'ch_chat_text_size',
               '',
                array( $this, 'radio_field_callback'),
               'chatster-menu',
               'ch_chat_section',
               ['id'=>'ch_chat_text_size',
                'label'=> esc_html( 'Text Size', CHATSTER_DOMAIN ),
                'description'=> ''] );

       add_settings_field(
               'ch_chat_fontawesome',
               '',
                array( $this, 'switch_field_callback'),
               'chatster-menu',
               'ch_chat_section',
               ['id'=>'ch_chat_fontawesome',
                'label'=> esc_html( 'Enable Fontawesome', CHATSTER_DOMAIN ),
                'description'=> esc_html( 'Fontawesome icons will be displayed in the front chat.', CHATSTER_DOMAIN )] );

        add_settings_field(
                'ch_chat_volume',
                '',
                 array( $this, 'range_field_callback'),
                'chatster-menu',
                'ch_chat_section',
                ['id'=>'ch_chat_volume',
                 'label'=> esc_html( 'New Message Sound', CHATSTER_DOMAIN ),
                 'description'=> esc_html( 'Chat will emit a sound when the customer receives a new message.', CHATSTER_DOMAIN )] );

        add_settings_field(
               'ch_chat_screen_position',
               '',
                array( $this, 'radio_field_callback'),
               'chatster-menu',
               'ch_chat_section',
               ['id'=>'ch_chat_screen_position',
                'label'=> esc_html( 'Position', CHATSTER_DOMAIN ),
                'description'=> ''] );

        // --- ch_chat_admin_section ---
        add_settings_field(
                'ch_chat_max_conv',
                '',
                 array( $this, 'option_field_callback'),
                'chatster-menu',
                'ch_chat_admin_section',
                ['id'=>'ch_chat_max_conv',
                 'label'=> esc_html( 'Max number of conversations', CHATSTER_DOMAIN ),
                 'description'=> esc_html( 'Requests received after the limit is reached will be put on hold.', CHATSTER_DOMAIN )] );

        add_settings_field(
                'ch_chat_auto_offline',
                '',
                 array( $this, 'option_field_callback'),
                'chatster-menu',
                'ch_chat_admin_section',
                ['id'=>'ch_chat_auto_offline',
                 'label'=> esc_html( 'Auto Offline Admin', CHATSTER_DOMAIN ),
                 'description'=> wp_kses( __('Will automatically switch the current admin to offline mode when "conversation" screen is not open.
                 <br/>You can choose how long before that happens.', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) )] );

       add_settings_field(
               'ch_chat_remove_offline_conv_int',
               '',
                array( $this, 'option_field_callback'),
               'chatster-menu',
               'ch_chat_admin_section',
               ['id'=>'ch_chat_remove_offline_conv_int',
                'label'=> esc_html( 'Auto Disconnect Convs', CHATSTER_DOMAIN ),
                'description'=> wp_kses( __( 'Automatically disconnects conversations that have been inactive <br>for a selected amount of time.', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) )] );

        add_settings_field(
                'ch_chat_remove_offline_conv',
                '',
                 array( $this, 'switch_field_callback'),
                'chatster-menu',
                'ch_chat_admin_section',
                ['id'=>'ch_chat_remove_offline_conv',
                 'label'=> esc_html( 'Remove Disconnected Convs', CHATSTER_DOMAIN ),
                 'description'=> esc_html( 'Automatically removes conversations from the chat that have been disconnected from either side.', CHATSTER_DOMAIN )] );

         add_settings_field(
                 'ch_chat_volume_admin',
                 '',
                  array( $this, 'range_field_callback'),
                 'chatster-menu',
                 'ch_chat_admin_section',
                 ['id'=>'ch_chat_volume_admin',
                  'label'=> esc_html( 'Conversation Sounds', CHATSTER_DOMAIN ),
                  'description'=> esc_html( 'Select the volume level for the "Admin Chat" sound effects.', CHATSTER_DOMAIN )] );


  }

  public function validate_chat_options( $input ) {
    if ( ! current_user_can( 'manage_options' ) ) return;

    if ( !empty($input['default_settings']) &&
            "reset" === $input['default_settings'] ) {
      delete_option( 'chatster_chat_options' );
      add_settings_error(
          'chatster_chat_options', // Setting slug
          'success_message',
           esc_html__('Chatster Chat settings have been reset!', CHATSTER_DOMAIN),
          'success'
      );
      return false;
    }

    $err_msg = '';
  	$options = get_option( static::$option_group , static::default_values() ) + static::default_values();

    foreach (array( 'ch_chat_intro', 'ch_chat_header' ) as $value) {
      if ( isset($input[$value]) ) {
        $input[$value] = sanitize_text_field( $input[$value] );
        if ( !is_string($input[$value]) || strlen($input[$value]) > self::get_maxlength($value) ) {
          $max_lenght = intval( strlen($input[$value]) -  self::get_maxlength($value) ) ;
          $input[$value] = isset($options[$value]) ? $options[$value] : '';
          if ( $max_lenght ) {
            $err_msg .= sprintf( esc_html( _n( 'A field text exceeds %d character', 'A field text exceeds %d characters', $max_lenght, CHATSTER_DOMAIN ) ), number_format_i18n( $max_lenght ) ).'<br>';
          }
        }
      }
    }

    foreach (array( 'ch_chat_header_back_color', 'ch_chat_header_text_color' ) as $value) {
     if ( isset($input[$value])) {
       $input[$value] = sanitize_hex_color( $input[$value] );
       if ( empty($input[$value]) ) {
         $input[$value] = $options[$value];
         $err_msg .= esc_html__('Wrong Hex color', CHATSTER_DOMAIN ).'<br>';
       }
     }
    }

    foreach (array( 'ch_chat_volume', 'ch_chat_volume_admin' ) as $value) {
      if ( isset($input[$value]) ) {
        $intval = intval($input[$value]);
        if ( $intval >= 0 && $intval <= 50 ) {
          $input[$value] = $intval;
        } else {
          $input[$value] = $options[$value];
          $err_msg .= esc_html__('Wrong Volume Setting', CHATSTER_DOMAIN ).'<br>';
        }
      }
    }

    foreach (array( 'ch_chat_screen_position', 'ch_chat_text_size' ) as $value) {
      if ( isset($input[$value]) ) {
        $current_input = $input[$value];
        $input[$value] = $options[$value];

        $attr_array = array_keys(self::get_options_radio($value));
        if ( in_array( $current_input, $attr_array ) ) {
          $array_key = array_search($current_input, $attr_array);
          if ( $array_key !== false ) {
            $input[$value] = $attr_array[$array_key];
          }
        }
      }
    }

    foreach (array( 'ch_chat_max_conv', 'ch_chat_auto_offline', 'ch_chat_remove_offline_conv_int' ) as $value) {
      if ( isset($input[$value]) ) {
        $current_input = intval($input[$value]);
        $input[$value] = $options[$value];
        $attr_array = array_values(self::get_options_select($value));
        if ( in_array( $current_input, $attr_array ) ) {
          $array_key = array_search($current_input, $attr_array);
          if ( $array_key !== false ) {
            $input[$value] = $attr_array[$array_key];
          }
        }
      }
    }

    foreach( array( 'ch_chat_fontawesome', 'ch_chat_remove_offline_conv') as $value ) {
      if ( !empty($input[$value]) &&  $input[$value] == 'on' ) {
        $input[$value] = true;
      } else {
        $input[$value] = false;
      }
    }

    $this->add_success_message( $err_msg );

    return $input;
  }

}

new AddOptionsChat;
