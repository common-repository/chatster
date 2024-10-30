<?php

namespace Chatster\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

class Notices {

  public function __construct() {
      add_action( 'admin_notices', array( $this, 'add_notices' ) );
  }

  protected static function get_welcome_container() {

      return [
                '<h3>'.esc_html__('Thank you and Welcome to', CHATSTER_DOMAIN ).' <i>'.esc_html__('Chatster!', CHATSTER_DOMAIN ).'</i>'.
                '<img style="max-width: 40px; vertical-align: middle; padding-bottom: 7px;" src="'. esc_url_raw( CHATSTER_URL_PATH . 'assets/img/bot-1.jpg') .'">'.'</h3>',
                '<h4 style="font-size: 1.1em;"><b>'.esc_html__('Testing:', CHATSTER_DOMAIN ).'</b></h4>'.
                 wp_kses( __('- Please use only <b>incognito windows</b> or <b>second browser</b> to test chat functionalities.<br>', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) ).
                 wp_kses( __('- API functionality <b>must be enabled!</b><br>', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) ).
                 wp_kses( __('- Email delivery only works if you have a <b>transactional email service</b>.<br>', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) ).
                 wp_kses( __('- Go to <i>Settings->Request/Response->Test Functionality</i> and verify email delivery.', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) ).
                '<h4 style="font-size: 1.1em;"><b>'.esc_html__('Suggestions:', CHATSTER_DOMAIN ).'</b></h4>'.
                 wp_kses( __('- For any questions or suggestions please visit the <a target="_blank" href="'.esc_url_raw( CHATSTER_SUPPORT_URL ).'"><b>support page.</b></a>', CHATSTER_DOMAIN ), wp_kses_allowed_html( 'post' ) )

             ];
  }

  public function add_notices() {

    $screen = get_current_screen();
    if ( isset($screen->id) && $screen->id == 'toplevel_page_chatster-menu') {

      //delete_option( 'ch_welcome_notice_viewed' );
      if ( ! get_option('ch_welcome_notice_viewed') ) {
        update_option( 'ch_welcome_notice_viewed', true );
        $welcome_notice = self::get_welcome_container();

        if (!empty($welcome_notice)) {
          echo '<div class="notice notice-success settings-error is-dismissible" style="margin-top: 29px; margin-bottom: 29px;">';
          foreach ($welcome_notice as $key => $escaped_value) {
              echo '<p>'.$escaped_value.'</p>';
          }
          echo '</div>';
        }
      }
    }

  }

}

new Notices();
