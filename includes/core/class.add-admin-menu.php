<?php

namespace Chatster\Core;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/includes/core/trait.chat.php' );
require_once( CHATSTER_PATH . '/includes/core/class.emailer.php' );

use Chatster\Core\Emailer;
use Chatster\Core\ChatCollection;

/**
 * Adds the Admin Menu
 */
class AdminMenu
{
  use ChatCollection;

  function __construct() {
      add_action( 'admin_menu', array( $this, 'add_menu_page' ), 20);
      add_action( 'admin_menu', array( $this, 'change_menu_order' ), 99);
      add_action( 'admin_enqueue_scripts', array( $this, 'add_global_script_admin' ), 99);
  }

  protected function get_JS_translation() {
    return [
         'created' => esc_html__('Started', CHATSTER_DOMAIN),
         'hours_plus' => esc_html__('more than one hour ago', CHATSTER_DOMAIN),
         'hour' => esc_html__('hour ago', CHATSTER_DOMAIN),
         'minutes' => esc_html__('minutes ago', CHATSTER_DOMAIN),
         'minute' => esc_html__('minute ago', CHATSTER_DOMAIN),
         'now' => esc_html__('just now', CHATSTER_DOMAIN),
         'edit' => esc_html__('Edit', CHATSTER_DOMAIN),
         'delete' => esc_html__('Delete', CHATSTER_DOMAIN),
         'reset' => esc_html__('Reset settings?', CHATSTER_DOMAIN),
         'reset_all' => esc_html__('Reset All settings?', CHATSTER_DOMAIN),
         'disconnect' => esc_html__('Disconnect', CHATSTER_DOMAIN),
         'admin' => esc_html__('Replied by admin', CHATSTER_DOMAIN)
        ];
  }

  public function add_menu_page() {

      $menu_page = add_menu_page( 'Chatster',
                                  $this->get_menu_title_link(),
                                  'manage_options',
                                  'chatster-menu',
                                  array( 'Chatster\Views\DisplayManager', 'find_admin_view'), 'dashicons-format-status'
      );

      add_action('admin_print_scripts-'.$menu_page, function() {

          Global $ChatsterOptions;
          $current_tab = isset( $_GET['chtab'] ) ? sanitize_text_field( $_GET['chtab'] ) : false;

          // Enqueue Styles
          wp_enqueue_style( 'wp-color-picker' );
          wp_enqueue_style( 'chatster-css-autocomplete', CHATSTER_URL_PATH . 'assets/css/chat-autocomplete.css');
          wp_enqueue_style( 'chatster-css-admin', CHATSTER_URL_PATH . 'assets/css/style-admin.css');
          wp_enqueue_style( 'chatster-css-admin-settings', CHATSTER_URL_PATH . 'assets/css/style-settings.css');
          wp_enqueue_style( 'chatster-css-admin-loaders', CHATSTER_URL_PATH . 'assets/css/style-loaders.css');
          // Enqueue Mobile Styles
          wp_enqueue_style( 'chatster-css-admin-mobile', CHATSTER_URL_PATH . 'assets/css/style-admin-mobile.css');
          // Enqueue Scripts
          wp_enqueue_script( 'chatster-general', CHATSTER_URL_PATH . 'assets/js/general-admin.js',  array('jquery'), 1.0, true);
          if ( $current_tab == 'request' ) {
            wp_enqueue_script( 'chatster-request-admin', CHATSTER_URL_PATH . 'assets/js/request-admin.js',  array('jquery'), 1.0, true);
          }
          elseif ( $current_tab == 'settings' ) {
            wp_enqueue_script( 'chatster-settings-admin', CHATSTER_URL_PATH . 'assets/js/settings-admin.js',  array('jquery', 'wp-color-picker'), 1.0, true);
          } else {
            wp_enqueue_script( 'chatster-chat-admin', CHATSTER_URL_PATH . 'assets/js/chat-admin.js',  array('jquery'), 1.0, true);
            wp_enqueue_script( 'chatster-autocomplete-core', CHATSTER_AUTOCOMPLETE_URL, array(), 1.0, true );
            wp_enqueue_script( 'chatster-autocomplete-admin', CHATSTER_URL_PATH . 'assets/js/chat-autocomplete.js',  array('jquery'), 1.0, true);
          }
          // Localize JS vars
          wp_localize_script( 'chatster-general', 'chatsterDataAdmin', array(
            'api_base_url' => esc_url_raw( rest_url('chatster/v1') ),
            'wp_api_base_url' => esc_url_raw( get_rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'no_image_link' => CHATSTER_URL_PATH . 'assets/img/no-image.jpg',
            'chat_sound_file_path' => CHATSTER_URL_PATH . 'assets/sound/when',
            'conv_sound_file_path' => CHATSTER_URL_PATH . 'assets/sound/new-conv',
            'chat_sound_vol' => (( 1 / 50 ) * intval($ChatsterOptions->get_chat_option( 'ch_chat_volume_admin' ))),
            'default_header_img_url' => Emailer::DEFAULT_HEADER_IMG,
            'remove_offline_conv' => esc_js( $ChatsterOptions->get_chat_option( 'ch_chat_remove_offline_conv' )),
            'go_to_hide_unreplied' =>  esc_url_raw( add_query_arg(array('unreplied'=>'true')) ),
            'go_to_show_unreplied' =>  esc_url_raw( remove_query_arg('unreplied', false) ),
            'translation' => $this->get_JS_translation()
            )
          );
      });

  }

  public function get_menu_title_link() {
    $is_hidden = 'hidden';
    $current_admin = wp_get_current_user();
    if ( self::get_admin_status( $current_admin->user_email ) ) {
      $is_hidden = '';
    }
    $title  = '<span id="chatster-menu-link">'. __( 'Chatster', CHATSTER_DOMAIN ).'</span>&nbsp;';
    $title .= '<span class="active-convs-link '. $is_hidden .'">'. esc_html__( 'Online', CHATSTER_DOMAIN ) .'</span>';
    return $title;
  }

  public function change_menu_order() {
    global $menu;

    foreach ($menu as $key => $array) {
      if ( $array[3] == 'Analytics' ) { $analytics_pos = $key; }
      if ( $array[3] == 'Chatster' ) { $chatster_pos = $key; }
    }

    if ( isset($analytics_pos) ) {
      $x = 1;
      while( $x <= count($menu)  ) {
          if ( ! isset($menu[$analytics_pos + $x]) && isset($menu[$chatster_pos])) {
            $menu[$analytics_pos + $x] = $menu[$chatster_pos];
            unset($menu[$chatster_pos]);
            break;
          }
          $x++;
      }
    }
  }

  public function add_global_script_admin() {

    wp_enqueue_style( 'chatster-css-admin-global', CHATSTER_URL_PATH . 'assets/css/style-admin-global.css');

    // Not used as an input
    $current_tab = isset( $_GET['chtab'] ) ? sanitize_text_field( $_GET['chtab'] ) : false;
    $current_page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : false;

    if ( $current_page == 'chatster-menu' && ( !$current_tab || $current_tab == 'chat' ) ) {
      return;
    }

    wp_enqueue_script( 'chatster-global', CHATSTER_URL_PATH . 'assets/js/global-admin.js',  array('jquery'), 1.0, true);
    wp_localize_script( 'chatster-global', 'chatsterDataAdmin', array(
      'api_base_url' => esc_url_raw( rest_url('chatster/v1') ),
      'wp_api_base_url' => esc_url_raw( get_rest_url() ),
      'nonce' => wp_create_nonce( 'wp_rest' )
      )
    );

  }

}

new AdminMenu();
