<?php

namespace Chatster\Options;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/includes/options/class.add-options-bot.php' );
require_once( CHATSTER_PATH . '/includes/options/class.add-options-bot-qa.php' );
require_once( CHATSTER_PATH . '/includes/options/class.add-options-chat.php' );
require_once( CHATSTER_PATH . '/includes/options/class.add-options-request.php' );

class GetOptions {

  private static $bot_options;
  private static $bot_qa_options;
  private static $chat_options;
  private static $request_options;

  public function __construct() {
    self::$bot_options = get_option( AddOptionsBot::$option_group, AddOptionsBot::default_values() ) + AddOptionsBot::default_values();
    self::$bot_qa_options = get_option( AddOptionsBotQA::$option_group, AddOptionsBotQA::default_values() ) + AddOptionsBotQA::default_values();
    self::$chat_options = get_option( AddOptionsChat::$option_group, AddOptionsChat::default_values() ) + AddOptionsChat::default_values();
    self::$request_options = get_option( AddOptionsRequest::$option_group, AddOptionsRequest::default_values() ) + AddOptionsRequest::default_values();
  }

  public function get_bot_option( String $option_name ) {
    return !empty(self::$bot_options[$option_name]) ? self::$bot_options[$option_name] : false;
  }

  public function get_bot_qa_option( String $option_name ) {
    return !empty(self::$bot_qa_options[$option_name]) ? self::$bot_qa_options[$option_name] : false;
  }

  public function get_chat_option( String $option_name ) {
    return !empty(self::$chat_options[$option_name]) ? self::$chat_options[$option_name] : false;
  }

  public function get_request_option( String $option_name ) {
    return !empty(self::$request_options[$option_name]) ? self::$request_options[$option_name] : false;
  }

}

// USE AS GLOBAL
$ChatsterOptions = new GetOptions();
