<?php

namespace Chatster\Core;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/includes/core/trait.chat.php' );
require_once( CHATSTER_PATH . '/includes/core/trait.request.php' );
require_once( CHATSTER_PATH . '/includes/core/class.emailer.php' );

use Chatster\Core\Emailer;
use Chatster\Core\ChatCollection;
use Chatster\Core\RequestCollection;

class CronManager {
  use ChatCollection;
  use RequestCollection;

  public function __construct() {
    add_filter( 'cron_schedules', array($this, 'add_intervals'), 10);
    add_action( 'chatster_remove_old_convs', array($this, 'cron_remove_old_convs'));
    add_action( 'chatster_update_presence', array($this, 'cron_update_presence'));
    add_action( 'chatster_check_new_requests', array($this, 'cron_check_new_requests'));
  }

  public function add_intervals($schedules) {

    if(!isset($schedules["every_three_mins"])){
      $schedules["every_three_mins"] = array(
          'interval' => 3*60,
          'display' => __('Once every 3 minutes'));
    }
    return $schedules;
  }

  public function cron_remove_old_convs() {
    Global $ChatsterOptions;
    $interval = intval( $ChatsterOptions->get_chat_option('ch_chat_remove_offline_conv_int') );
    $this->remove_old_convs();
    $this->disconnect_old_convs($interval);
  }

  public function cron_update_presence() {
    Global $ChatsterOptions;
    $interval = intval( $ChatsterOptions->get_chat_option('ch_chat_auto_offline') );
    $this->set_admin_offline($interval);
  }

  public function cron_check_new_requests() {
    Global $ChatsterOptions;
    $request_count = $this->get_notify_entry_request();
    if ( $request_count ) {
      $this->clear_all_notify_entry_request();

      if ( ! $ChatsterOptions->get_request_option('ch_request_alert') ) return;

      $request = new \stdClass();
      $request->name = $request->message = '';
      $request->email = $ChatsterOptions->get_request_option('ch_request_alert_email');
      $request->subject = esc_html__('New Request received on', CHATSTER_DOMAIN).' '.ucfirst(esc_html(get_bloginfo( 'name' )));
      $request->reply  =  esc_html__('Hello', CHATSTER_DOMAIN).',<br>';
      $request->reply .=  esc_html__('You have received ', CHATSTER_DOMAIN ).
                          sprintf( esc_html( _n( '%s new request', '%s new requests', $request_count, CHATSTER_DOMAIN ) ), number_format_i18n( $request_count ) ).' '.
                          esc_html__('on', CHATSTER_DOMAIN) .' '.ucfirst(esc_html(get_bloginfo( 'name' ))).'<br><br>'.
                          esc_html__('To login to your website go here:', CHATSTER_DOMAIN).'<br>'.esc_url(wp_login_url()).'<br><br><br>'.
                          '<h5 style="font-size: 14px;">'.esc_html__('Thank you for using Chatster!', CHATSTER_DOMAIN).'</h5>';

      $emailer = new Emailer();
      return $emailer->send_notification_email($request);
    }
  }

}

new CronManager();
