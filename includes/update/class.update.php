<?php

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/includes/core/trait.table-builder.php' );
use Chatster\Core\ChatsterTableBuilder;


class ChatsterUpdateLoader  {

  use ChatsterTableBuilder;

  public static function init_update() {
     if ( ! current_user_can( 'manage_options' ) ) return;
      return  self::update_db();
  }

  public static function update_db() {

    global $wpdb;
    $version = get_option('chatster_version');

    if ( !$version ) {

      // Update DB

    }
 
    // Future versions check template
    // if ( $version < '1.0.2' ) {
    //
    // }

    update_option( 'chatster_version', CHATSTER_VERSION );

  }

}

add_action( 'plugins_loaded', array( 'ChatsterUpdateLoader', 'init_update' ) );
