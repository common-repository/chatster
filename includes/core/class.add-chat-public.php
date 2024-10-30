<?php

namespace Chatster\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds the interactive chat form
 */
class ChatPublic
{

  private static $text_size_percent = [
                                        'small'=> 85,
                                        'medium'=> 100,
                                        'large'=> 120
                                      ];
  private static $width_percent = [
                                        'small'=> 98,
                                        'medium'=> 100,
                                        'large'=> 106
                                      ];

  function __construct() {
      add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_js' ) , 30 );
      add_action( 'wp_footer', array( 'Chatster\Views\DisplayManager', 'public_chat_view'));
  }

  private function calc_font( $value, $size ) {
    if ( !empty(self::$text_size_percent[$size]) ) {
      $percent = self::$text_size_percent[$size];
      $new_size = ( $value  * $percent ) / 100;
      return strval(number_format($new_size, 2, '.', '') + 0);
    }
    return $value;
  }

  private function calc_width( $value, $size ) {
    if ( !empty(self::$width_percent[$size]) ) {
      $percent = self::$width_percent[$size];
      $new_size = ( $value  * $percent ) / 100;
      return strval(number_format($new_size, 2, '.', '') + 0);
    }
    return $value;
  }

  private function get_custom_css() {
    global $ChatsterOptions;

    // Chat Header Color
    $bg_color = $ChatsterOptions->get_chat_option('ch_chat_header_back_color');
    $text_color = $ChatsterOptions->get_chat_option('ch_chat_header_text_color');
    $custom_css  = "#chatster-container #ch-header, .ch-button-global:not(.ch-unavailable) { ";
    $custom_css .= "background-color: ".esc_attr( $bg_color )."; ";
    $custom_css .= "color: ".esc_attr( $text_color )."; ";
    $custom_css .= "}";

    $custom_css .= "#ch-open-button::after { ";
    $custom_css .= "border-top-color: ".esc_attr( $bg_color )." !important; ";
    $custom_css .= "}";

    $custom_css .= "#chatster-opener #ch-open-button, .ch-button-global:not(.ch-unavailable) { ";
    $custom_css .= "background-color: ".esc_attr( $bg_color )." !important; ";
    $custom_css .= "color: ".esc_attr( $text_color )." !important; ";
    $custom_css .= "}";

    // Pseudo-element Chat Arrow
    if ( $ChatsterOptions->get_chat_option('ch_chat_screen_position') == 'left' ) {
      $custom_css .= "#ch-open-button::after { ";
      $custom_css .= " left: 33px !important;";
      $custom_css .= "}";
    } else {
      $custom_css .= "#ch-open-button::after { ";
      $custom_css .= " left: 70% !important;";
      $custom_css .= "}";
    }

    // Chat Text Size ( proportinally calculated )
    $txt_size = $ChatsterOptions->get_chat_option('ch_chat_text_size');
    $header_size = $this->calc_font('16.8', $txt_size);
    $opening_btn_size = $this->calc_font('15', $txt_size);
    $global_btn_size = $this->calc_font('15.7', $txt_size);
    $context_size = $this->calc_font('14.1', $txt_size);
    $info_text = $this->calc_font('14', $txt_size);

    $custom_css .= "#chatster-container #ch-header { ";
    $custom_css .= " font-size: ".esc_attr( $header_size )."px !important; ";
    $custom_css .= "}";

    $custom_css .= "#ch-open-button-block { ";
    $custom_css .= " font-size: ".esc_attr( $opening_btn_size )."px !important; ";
    $custom_css .= "}";

    $custom_css .= ".ch-single-message, #ch-reply-bot, .ch-input input, .ch-input textarea { ";
    $custom_css .= " font-size: ".esc_attr( $context_size )."px !important; ";
    $custom_css .= "}";

    $custom_css .= ".ch-queue-info{ ";
    $custom_css .= " font-size: ".esc_attr( $info_text )."px !important; ";
    $custom_css .= "}";

    $custom_css .= ".ch-button-global { ";
    $custom_css .= " font-size: ".esc_attr( $global_btn_size )."px !important; ";
    $custom_css .= "}";

    // Chat Window width based on txt_size
    $chat_width = $this->calc_width('380', $txt_size);
    $custom_css .= "#chatster-container{ ";
    $custom_css .= " width: ".esc_attr( $chat_width )."px !important; ";
    $custom_css .= "}";

    return $custom_css;

  }

  protected function get_JS_translation() {
    return [
         'open' => ucfirst(esc_html__('open', CHATSTER_DOMAIN)),
        ];
  }

  public function enqueue_front_js() {
    global $ChatsterOptions;
    wp_enqueue_script( 'chatster-public', CHATSTER_URL_PATH . 'assets/js/chat-public.js',  array('jquery'), 1.0, true);
    wp_enqueue_script( 'chatster-public-sound', CHATSTER_URL_PATH . 'assets/js/chat-sound.js',  array('jquery'), 1.0, true);
    wp_localize_script( 'chatster-public', 'chatsterDataPublic', array(
      'api_base_url' => esc_url_raw( rest_url('chatster/v1') ),
      'nonce' => wp_create_nonce( 'wp_rest' ),
      'no_image_link' => CHATSTER_URL_PATH . 'assets/img/no-image.jpg',
      'sound_file_path' => CHATSTER_URL_PATH . 'assets/sound/when',
      'chat_position' => esc_js( $ChatsterOptions->get_chat_option('ch_chat_screen_position') ),
      'bot_img_path' => CHATSTER_URL_PATH . 'assets/img/' . esc_js( $ChatsterOptions->get_bot_option( 'ch_bot_image' )). '.jpg',
      'chat_sound_vol' => (( 1 / 50 ) * intval($ChatsterOptions->get_chat_option( 'ch_chat_volume' ))),
      'chat_static_string' => [ 'bot_intro'=> nl2br(esc_html( $ChatsterOptions->get_bot_option( 'ch_bot_intro' ))),
                                'bot_followup'=> nl2br(esc_html( $ChatsterOptions->get_bot_option( 'ch_bot_followup' ))),
                                'bot_nomatch'=> nl2br(esc_html( $ChatsterOptions->get_bot_option( 'ch_bot_nomatch' )))
                              ],
      'translation' => $this->get_JS_translation()
    ) );
    if ( !wp_style_is( 'fontawesome' ) && $ChatsterOptions->get_chat_option( 'ch_chat_fontawesome' ) ) {
        wp_enqueue_style( 'fontawesome', CHATSTER_FONTAWESOME_URL, false, '4.7.0' );
    }
    wp_enqueue_style( 'chatster-google-fonts', CHATSTER_GOOGLE_FONTS_URL, false );
    wp_enqueue_style( 'chatster-loader-pbl', CHATSTER_URL_PATH . 'assets/css/style-loaders.css');
    wp_enqueue_style( 'chatster-public', CHATSTER_URL_PATH . 'assets/css/style-public.css');
    wp_enqueue_style( 'chatster-public-mobile', CHATSTER_URL_PATH . 'assets/css/style-public-mobile.css');
    wp_add_inline_style( 'chatster-public', $this->get_custom_css() );

  }

}

new ChatPublic();
