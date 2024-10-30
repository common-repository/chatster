<?php

namespace Chatster\Options;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/includes/options/class.options-global.php' );

class AddOptionsBot extends OptionsGlobal {

  public static $success_set = false;
  public static $option_group = 'chatster_bot_options';
  public static $fields_maxlength = [
                                      'ch_bot_nomatch' => 350,
                                      'ch_bot_followup' => 350,
                                      'ch_bot_intro' => 350,
                                      'ch_bot_name' => 15
                                    ];

  public function __construct() {
    add_action( 'admin_init', array( $this, 'register_bot_settings' ) );
  }

  public static function default_values() {

      return array(
          'ch_bot_name' => 'Chatster',
          'ch_bot_intro' => esc_html__( 'Hi!! How can I help you today?', CHATSTER_DOMAIN ),
          'ch_bot_followup' => esc_html__( 'If you have any other questions please feel free to ask.', CHATSTER_DOMAIN ),
          'ch_bot_nomatch' => esc_html__( "Sorry, I couldn't find what you're looking for..
                                Please try again", CHATSTER_DOMAIN ),
          'ch_bot_deep_search' => true,
          'ch_bot_product_lookup' => false,
          'ch_bot_image' => 'bot-1'
      );

  }

  public static function get_options_radio($option_name) {

      switch ($option_name) {

          case 'ch_bot_image':
              return array(
                  'bot-1'=>  CHATSTER_URL_PATH . 'assets/img/',
                  'bot-2'=>  CHATSTER_URL_PATH . 'assets/img/',
                  'bot-3'=>  CHATSTER_URL_PATH . 'assets/img/',
                  'bot-4'=>  CHATSTER_URL_PATH . 'assets/img/',
                  'bot-5'=>  CHATSTER_URL_PATH . 'assets/img/',
                  'bot-6'=>  CHATSTER_URL_PATH . 'assets/img/'
              );
              break;
      }

  }

  public function register_bot_settings() {

    if ( ! current_user_can( 'manage_options' ) ) return;

    register_setting(
            'chatster_bot_options',
            'chatster_bot_options',
             array( $this, 'validate_bot_options') );

    add_settings_section(
            'ch_bot_section',
             esc_html('Chatster Bot Settings', CHATSTER_DOMAIN),
             array( $this, 'description' ),
            'chatster-menu' );

    add_settings_field(
            'ch_bot_name',
            '',
             array( $this, 'text_field_callback'),
            'chatster-menu',
            'ch_bot_section',
            ['id'=>'ch_bot_name',
             'label'=> esc_html('Bot Name', CHATSTER_DOMAIN),
             'description'=> esc_html__( 'Give your bot your favorite name.', CHATSTER_DOMAIN) ] );

    add_settings_field(
            'ch_bot_image',
            '',
             array( $this, 'radio_img_field_callback'),
            'chatster-menu',
            'ch_bot_section',
            ['id'=>'ch_bot_image',
             'label'=> esc_html('Bot Image', CHATSTER_DOMAIN),
             'description'=> esc_html__( 'Give your bot a friendly image', CHATSTER_DOMAIN) ] );

    add_settings_field(
            'ch_bot_intro',
            '',
             array( $this, 'textarea_field_callback'),
            'chatster-menu',
            'ch_bot_section',
            ['id'=>'ch_bot_intro',
             'label'=> esc_html('Bot introductory sentence.', CHATSTER_DOMAIN),
             'description'=> wp_kses( __('Bot introductory sentece used when the chat is initially displayed.
                              <br><span class="ch-field-descr-extra">(Each line break is shown as separate message)</span>', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) )] );

     add_settings_field(
             'ch_bot_followup',
             '',
              array( $this, 'textarea_field_callback'),
             'chatster-menu',
             'ch_bot_section',
             ['id'=>'ch_bot_followup',
              'label'=> esc_html('Follow-up question', CHATSTER_DOMAIN),
              'description'=> wp_kses( __('The bot sentece that follows a successfull reply.
                               <br><span class="ch-field-descr-extra">(Each line break is shown as separate message)</span>', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) )] );

     add_settings_field(
             'ch_bot_nomatch',
             '',
              array( $this, 'textarea_field_callback'),
             'chatster-menu',
             'ch_bot_section',
             ['id'=>'ch_bot_nomatch',
              'label'=> esc_html('Nothing found response', CHATSTER_DOMAIN),
              'description'=> wp_kses( __('When no answer is found the bot will use this sentence.
                               <br><span class="ch-field-descr-extra">(Each line break is shown as separate message)</span>', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) )] );

     add_settings_field(
             'ch_bot_deep_search',
             '',
              array( $this, 'switch_field_callback'),
             'chatster-menu',
             'ch_bot_section',
             ['id'=>'ch_bot_deep_search',
              'label'=> esc_html('Enable Deep Search', CHATSTER_DOMAIN),
              'description'=> wp_kses( __('BOT will search full text in both questions and answers. <br>When not enabled it will only search among the saved questions.', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) )] );

     // add_settings_field(
     //         'ch_bot_product_lookup',
     //         '',
     //          array( $this, 'switch_field_callback'),
     //         'chatster-menu',
     //         'ch_bot_section',
     //         ['id'=>'ch_bot_product_lookup',
     //          'label'=> 'Enable Product Lookup',
     //          'description'=> 'Matching product links with thumbnail will be listed along with the found answer.'] );

  }

  public function validate_bot_options( $input ) {
    if ( ! current_user_can( 'manage_options' ) ) return;

    if ( !empty($input['default_settings']) &&
            "reset" === $input['default_settings'] ) {
      delete_option( 'chatster_bot_options' );
      add_settings_error(
          'chatster_bot_options', // Setting slug
          'success_message_reset',
           esc_html__( 'BOT settings have been reset!', CHATSTER_DOMAIN ),
          'success'
      );
      return false;
    }

    $err_msg = '';
  	$options = get_option( static::$option_group , static::default_values() ) + static::default_values();

    foreach (array( 'ch_bot_name' ) as $value) {
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

    foreach (array( 'ch_bot_intro', 'ch_bot_followup','ch_bot_nomatch' ) as $value) {
      if ( isset($input[$value]) ) {
        $input[$value] = sanitize_textarea_field($input[$value]);
        if ( !is_string($input[$value]) || strlen($input[$value]) > self::get_maxlength($value) ) {
          $max_lenght = intval( strlen($input[$value]) -  self::get_maxlength($value) ) ;
          $input[$value] = isset($options[$value]) ? $options[$value] : '';
          if ( $max_lenght ) {
            $err_msg .= sprintf( esc_html( _n( 'A field text exceeds %d character', 'A field text exceeds %d characters', $max_lenght, CHATSTER_DOMAIN ) ), number_format_i18n( $max_lenght ) ).'<br>';
          }
        }
      }
    }

    foreach (array( 'ch_bot_deep_search', 'ch_bot_product_lookup' ) as $value) {
      if ( isset($input[$value]) ) {
        $input[$value] = rest_sanitize_boolean( $input[$value] );
      } else {
        $input[$value] = null;
      }
    }

    foreach (array( 'ch_bot_image' ) as $value) {
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

    $this->add_success_message( $err_msg );
    return $input;
  }

}

new AddOptionsBot;
