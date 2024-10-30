<?php

namespace Chatster\Views;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/views/functions.basic-templates.php' );
require_once( CHATSTER_PATH . '/includes/core/trait.request.php' );
require_once( CHATSTER_PATH . '/views/admin/function.header.php' );
require_once( CHATSTER_PATH . '/views/admin/function.chat.php' );
require_once( CHATSTER_PATH . '/views/admin/function.request.php' );
require_once( CHATSTER_PATH . '/views/admin/function.settings.php' );
require_once( CHATSTER_PATH . '/views/public/function.front-chat.php' );

use Chatster\Core\ChatCollection;
use Chatster\Core\RequestCollection;
use Chatster\Core\BotCollection;

/**
 * Shows admin pages and front chat
 */
class DisplayManager
{
  use ChatCollection;
  use RequestCollection;
  use BotCollection;

    public static function find_admin_view() {
      if ( ! current_user_can( 'manage_options' ) ) return;

      // GET request - parmeters
      $order = array('ASC', 'DESC');
      $tab = !empty($_GET['chtab']) ? $_GET['chtab'] : '';
      $cpage = !empty($_GET['cpage']) ? filter_var($_GET['cpage'], FILTER_VALIDATE_INT ) : 1;
      $order = isset($_GET['order']) && in_array(Strtoupper($_GET['order']), $order) ? $order[array_search(Strtoupper($_GET['order']), $order)] : 'DESC';
      $unreplied_only = isset($_GET['unreplied']) && $_GET['unreplied'] == true ? true : false;

      $current_admin = wp_get_current_user();
      display_admin_header( $tab );

      switch ( $tab ) {
          case 'request':
              // Pagination
              $count = self::count_all_requests( $unreplied_only );
              $current_page = ( $cpage > 0 && $cpage <= ceil( $count / self::$per_page_request ) ) ? $cpage : 1;
              $total_pages = ceil($count / self::$per_page_request);
              // Db requests query
              $requests = self::get_all_requests( $current_page, self::$per_page_request, $order_by = 'created_at', $order, $unreplied_only );
              display_admin_request( $requests, $total_pages, $current_page, self::$per_page_request, $count, $unreplied_only );
              break;
          case 'settings':
              $count_qa = self::get_answer_count();
              $total_pages_qa = ceil($count_qa / self::$per_page_qa);
              display_admin_settings($count_qa, self::$per_page_qa, $total_pages_qa);
              break;
          default:
              /* Admin Chat */
              $admin_status = self::get_admin_status( $current_admin->user_email );
              display_admin_chat( $admin_status );
              break;
      }

    }

    public static function public_chat_view() {
      $customer_id = self::get_customer_id();
      $current_conv = $customer_id ? self::get_current_customer_conv( $customer_id ) : false;
      $chat_available = self::is_chat_available();
      display_front_chat( $current_conv, $chat_available );
    }

    private static function get_customer_id() {

      $current_user = wp_get_current_user();
      if ( $current_user && get_current_user_id() ) {
        return sanitize_email( $current_user->user_email );
      }

      if ( isset($_COOKIE['ch_ctmr_id'])) {
        $customer_id = sanitize_text_field( base64_decode($_COOKIE['ch_ctmr_id']) );
      }

      return !empty( $customer_id ) ? $customer_id : false;

    }

}
