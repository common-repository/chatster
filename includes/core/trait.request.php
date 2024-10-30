<?php

namespace Chatster\Core;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/includes/core/trait.table-builder.php' );
use Chatster\Core\ChatsterTableBuilder;

trait RequestCollection {
  use ChatsterTableBuilder;

  public static $per_page_request = 5;
/**
 * Static Requests
 */
  protected static function count_all_requests( $unreplied_only = true ) {
      global $wpdb;
      $wp_table_request = self::get_table_name('request');
      $wp_table_reply = self::get_table_name('reply');

      $sql  = " SELECT COUNT( DISTINCT r.id ) AS count FROM $wp_table_request AS r ";
      $sql .= " LEFT JOIN $wp_table_reply AS rp ON r.id = rp.request_id ";
      if ( $unreplied_only ) {
        $sql .= " WHERE rp.created_at IS NULL ";
      }

      $count = $wpdb->get_var( $sql );
      return !empty($count) ? $count : false;
  }

  protected static function get_all_requests( $current_page = 1, $per_page = 8, $order_by = 'created_at', $order = 'ASC', $unreplied_only = true ) {

      global $wpdb;
      $wp_table_request = self::get_table_name('request');
      $wp_table_reply = self::get_table_name('reply');
      $Table_Users = self::get_table_name('users');
      $offset = ( $current_page - 1 ) * $per_page;

      $sql  = " SELECT  r.*, CONVERT_TZ( r.created_at, @@session.time_zone, '+00:00') as created_at,
                        r.message as request_message,
                        rp.admin_email as last_replied_by,
                        u.user_nicename as admin_name,
                        MAX(CONVERT_TZ( rp.created_at, @@session.time_zone, '+00:00')) as replied_at
                FROM $wp_table_request AS r
                LEFT JOIN (
                            SELECT subr.* FROM $wp_table_reply as subr
                            INNER JOIN ( SELECT request_id , MAX(created_at) as created_at FROM $wp_table_reply GROUP by request_id ) as subrp
                            ON subrp.created_at = subr.created_at
                          )
                AS rp ON r.id = rp.request_id
                LEFT JOIN $Table_Users as u ON rp.admin_email = u.user_email ";

      if ( $unreplied_only ) {
        $sql .= " WHERE rp.created_at IS NULL OR ( r.is_flagged = true ) ";
      }
      $sql .= " GROUP BY r.id ";
      $sql .= " ORDER BY is_flagged DESC, ". esc_sql($order_by)." ".esc_sql($order). "
                LIMIT %d, %d ";

      $sql = $wpdb->prepare( $sql, array( $offset, $per_page ) );
      $result = $wpdb->get_results( $sql);

      return ! empty( $result ) ? $result : false;
  }

 /**
  * Api Methods
  */
   protected function insert_reply( $admin_email, $message, $request_id ) {
     global $wpdb;
     $wp_table_reply = self::get_table_name('reply');

     $sql = " INSERT INTO $wp_table_reply ( request_id, admin_email, message ) VALUES( %d, %s, %s ) ";
     $sql = $wpdb->prepare( $sql, array( $request_id, $admin_email, $message ) );

     $wpdb->query( $sql );
     wp_reset_postdata();

     return ! empty( $wpdb->insert_id ) ? $wpdb->insert_id : false;
   }

   protected function get_replies( $request_id ) {
     global $wpdb;
     $wp_table_reply = self::get_table_name('reply');
     $Table_Users = self::get_table_name('users');

     $sql  = " SELECT r.*, CONVERT_TZ( r.created_at, @@session.time_zone, '+00:00') as replied_at, u.user_nicename as admin_name
               FROM $wp_table_reply as r
               LEFT JOIN $Table_Users as u ON r.admin_email = u.user_email
               WHERE r.request_id = %d
               ORDER BY replied_at ASC ";

     $sql = $wpdb->prepare( $sql, $request_id);
     $result = $wpdb->get_results( $sql);

     return ! empty( $result ) ? $result : false;
   }

   protected function get_latest_reply( $request_id ) {
     global $wpdb;
     $wp_table_reply = self::get_table_name('reply');
     $Table_Users = self::get_table_name('users');

     $sql  = " SELECT r.*, CONVERT_TZ( r.created_at, @@session.time_zone, '+00:00') as replied_at, u.user_nicename as admin_name
               FROM $wp_table_reply as r
               LEFT JOIN $Table_Users as u ON r.admin_email = u.user_email
               WHERE r.request_id = %d
               ORDER BY replied_at DESC
               LIMIT 1 ";

     $sql = $wpdb->prepare( $sql, $request_id);
     $result = $wpdb->get_results( $sql);

     return ! empty( $result ) ? array_shift($result) : false;
   }

   protected function delete_request( $request_id ) {
     global $wpdb;
     $wp_table_request = self::get_table_name('request');

     $sql = " DELETE FROM $wp_table_request WHERE id = %d ";
     $sql = $wpdb->prepare( $sql, $request_id );

     $result = $wpdb->query( $sql );
     wp_reset_postdata();

     return ! empty( $result ) ? $result : false;
   }

   protected function get_request_by_id( $request_id ) {
     global $wpdb;
     $wp_table_request = self::get_table_name('request');

     $sql  = " SELECT * FROM $wp_table_request WHERE id = %d ";
     $sql = $wpdb->prepare( $sql, $request_id);
     $result = $wpdb->get_results( $sql);

     return ! empty( $result ) ? array_shift($result) : false;
   }

   protected function pin_request($request_id, $pin_value) {
     global $wpdb;
     $wp_table_request = self::get_table_name('request');
     $pin_value = $pin_value ? 1 : 0;

     $sql = " UPDATE $wp_table_request SET is_flagged = %d WHERE id = %d ";
     $sql = $wpdb->prepare( $sql, $pin_value, $request_id );
     $wpdb->query( $sql );
     $sql = " SELECT is_flagged FROM $wp_table_request WHERE id = %d ";
     $sql = $wpdb->prepare( $sql, $request_id );
     $result = $wpdb->get_var( $sql );
     wp_reset_postdata();

     return ! empty( $result ) ? $result : 0;
   }

   // Public Insert
   protected function insert_request_data( $name, $email, $subject, $message, $notify_entry = 0 ) {
      global $wpdb;
      $wp_table_request = self::get_table_name('request');
      $sql = " INSERT INTO $wp_table_request ( name, email, subject, message, notify_entry ) VALUES( %s, %s, %s, %s ,%d ) ";
      $parameters = array( $name, $email, $subject, $message, $notify_entry );
      $sql = $wpdb->prepare( $sql, $parameters);

      $result = $wpdb->query( $sql );
      wp_reset_postdata();

      return ! empty( $result ) ? true : false;
   }

 /**
  * Cron methods
  */
  protected function get_notify_entry_request() {
    global $wpdb;
    $wp_table_request = self::get_table_name('request');

    $sql  = " SELECT COUNT(*) FROM $wp_table_request WHERE notify_entry = true ";
    $result = $wpdb->get_var( $sql);

    return ! empty( $result ) ? $result : false;
  }

  protected function clear_all_notify_entry_request() {
    global $wpdb;
    $wp_table_request = self::get_table_name('request');

    $sql  = " UPDATE $wp_table_request SET notify_entry = false WHERE id > 0 ";
    $result = $wpdb->query( $sql);

    return ! empty( $result ) ? $result : false;
  }

}
