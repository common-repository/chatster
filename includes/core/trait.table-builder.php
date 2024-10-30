<?php
namespace Chatster\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

trait ChatsterTableBuilder {

    private static $presence = 'chatster_presence';
    private static $presence_admin = 'chatster_presence_admin';
    private static $message = 'chatster_message';
    private static $conversation = 'chatster_conversation';
    private static $current_conversation = 'chatster_current_conversation';
    private static $ticket = 'chatster_ticket';
    private static $source_q = 'chatster_source_q';
    private static $source_a = 'chatster_source_a';
    private static $request = 'chatster_request';
    private static $reply = 'chatster_reply';
    private static $users = 'users';

    public static function get_table_name($table = '') {
      global $table_prefix;
      return $table_prefix . self::$$table;
    }

}
