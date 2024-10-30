<?php

namespace Chatster\Options;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/includes/core/trait.bot.php' );
require_once( CHATSTER_PATH . '/includes/options/class.options-global.php' );

use Chatster\Core\BotCollection;

class ResetOptionsAll extends OptionsGlobal {
  use BotCollection;

  public static $option_group = 'chatster_reset_all';

  public function __construct() {
    add_action( 'admin_init', array( $this, 'register_settings' ) );
  }

  public static function default_values() {
      return array();
  }

  public function register_settings() {

    if ( ! current_user_can( 'manage_options' ) ) return;

    register_setting(
            self::$option_group,
            self::$option_group,
            array( $this, 'validate_options') );

    add_settings_section(
            'ch_reset_section',
            'Chatster Reset All',
             array( $this, 'description' ),
            'chatster-menu' );
  }

  public function validate_options( $input ) {
    if ( ! current_user_can( 'manage_options' ) ) return;

    // Deletes all options
    delete_option( AddOptionsBot::$option_group);
    delete_option( AddOptionsBotQA::$option_group);
    delete_option( AddOptionsChat::$option_group);
    delete_option( AddOptionsRequest::$option_group);

    // Removes all Q&A
    self::delete_all_answers();

    // Set confirmation message
    add_settings_error(
        self::$option_group,
        'success_message',
        'All Settings Have Been Reset',
        'success'
    );
    return false;
  }

}

new ResetOptionsAll;
