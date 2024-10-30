<?php

namespace Chatster\Core;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/includes/core/trait.table-builder.php' );
use Chatster\Core\ChatsterTableBuilder;

trait ChatCollection {
  use ChatsterTableBuilder;

/**
 * Static Methods
 */

  protected static function get_admin_status( $admin_email ) {
    global $wpdb;
    $wp_table_presence_admin = self::get_table_name('presence_admin');

    $sql = " SELECT is_active FROM $wp_table_presence_admin WHERE admin_email = %s ";
    $sql = $wpdb->prepare( $sql, $admin_email );

    $is_active = $wpdb->get_var( $sql );
    wp_reset_postdata();

    return $is_active ? true : false;
  }

  protected static function get_current_customer_conv( $customer_id ) {
    global $wpdb;
    $wp_table_conversation = self::get_table_name('conversation');
    $Table_Users = self::get_table_name('users');

    $sql = " SELECT c.*, u.user_nicename as admin_name FROM $wp_table_conversation as c
             INNER JOIN $Table_Users as u ON c.admin_email = u.user_email
             WHERE c.customer_id = %s AND c.is_connected = 1 LIMIT 1 ";

    $sql = $wpdb->prepare( $sql, $customer_id );

    $conversation = $wpdb->get_results( $sql );
    wp_reset_postdata();

    return ! empty( $conversation ) ? array_shift($conversation) : false;
  }

  protected static function is_chat_available() {
    global $wpdb;
    $wp_table_presence_admin = self::get_table_name('presence_admin');

    $sql = " SELECT COUNT(*) FROM $wp_table_presence_admin WHERE is_active = 1 AND last_presence >= NOW() - INTERVAL 20 MINUTE ";

    $result = $wpdb->get_var( $sql );
    wp_reset_postdata();

    return ! empty( $result ) ? true : false;
  }

  public static function link_builder($string) {
    $url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
    $string = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $string);
    return $string;
  }

/**
 * Api Methods
 */

  protected function construct_msg_links( $payload ) {
    foreach ( $payload as $key=>$field ) {
      if (!empty($field['product_ids'])) {
        $constructed_links = [];
        $link_ids = unserialize($field['product_ids']);
        // Builds the product or page link info for each message.
        foreach ($link_ids as $id) {

          $excerpt = get_the_excerpt( $id );
          $excerpt = strlen($excerpt) > 180 ? trim(substr($excerpt, 0, 180))." ..." : $excerpt;

          if ( $product = wc_get_product($id) ) {

            $link = [  "type" => "product",
                       "id" => esc_attr( $id ),
                       "title"=> esc_html( $product->get_title() ),
                       "link"=> esc_url( get_post_permalink( $id ) ) ,
                       "thumbnail"=> esc_url( get_the_post_thumbnail_url($id, 'thumbnail') ),
                       "excerpt"=> esc_html( $excerpt ),
                       "product_type" => esc_html($product->get_type()),
                       "available" => esc_attr( $product->is_in_stock())
                    ];
            $constructed_links []= $link;

          } elseif ( get_post_status($id) ) {

            $link = [  "type" => "post",
                       "id" => esc_attr( $id ),
                       "title"=> esc_html( get_the_title( $id ) ),
                       "link"=> esc_url( get_post_permalink( $id ) ),
                       "thumbnail"=> esc_url( get_the_post_thumbnail_url( $id , 'thumbnail' )),
                       "excerpt"=> esc_html( $excerpt )
                    ];

            $constructed_links []= $link;
          }

          wp_reset_postdata();
        }
        $payload[$key]['product_ids'] = $constructed_links;
      }

      if( isset($field['message'])) {
        $payload[$key]['message'] = self::link_builder( wp_kses( $field['message'], wp_kses_allowed_html( 'post' ) ) );
      }

    }
    return $payload;
  }

  protected function construct_chat_form( $payload ) {
    foreach ( $payload as $key=>$field ) {
      if (!empty($field['form_data'])) {
        $form_data = unserialize($field['form_data']);
        foreach ($form_data as $fkey => $value) {
          $form_data[$fkey] = esc_html($value);
        }
        $payload[$key]['form_data'] = $form_data;
      }
    }
    return $payload;
  }

  protected function insert_presence_customer( $customer_id ) {
    global $wpdb;
    $wp_table_presence = self::get_table_name('presence');
    $sql = " INSERT INTO $wp_table_presence ( customer_id ) VALUES( %s ) ON DUPLICATE KEY UPDATE last_presence = DEFAULT ";
    $sql = $wpdb->prepare( $sql, $customer_id );

    $result = $wpdb->get_results( $sql );
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;

  }

  protected function insert_presence_admin( $admin_email ) {
    global $wpdb;
    $wp_table_presence_admin = self::get_table_name('presence_admin');
    $sql = " INSERT INTO $wp_table_presence_admin ( admin_email ) VALUES( %s ) ON DUPLICATE KEY UPDATE last_presence = DEFAULT ";
    $sql = $wpdb->prepare( $sql, $admin_email );

    $result = $wpdb->get_results( $sql );
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;


  }

  protected function change_admin_status( $admin_email, $status = false ) {
    global $wpdb;
    $wp_table_presence_admin = self::get_table_name('presence_admin');
    $safe_status = $status ? 1 : 0;

    $sql = " INSERT INTO $wp_table_presence_admin ( admin_email, is_active ) VALUES( %s, $safe_status ) ON DUPLICATE KEY UPDATE last_presence = DEFAULT, is_active = %d ";
    $sql = $wpdb->prepare( $sql, $admin_email, $safe_status );

    $result = $wpdb->query( $sql );
    wp_reset_postdata();

    return $result;
  }

  protected function get_all_convs_admin( $admin_email, $last_conv_poll = 0 ) {
    global $wpdb;
    $wp_table_conversation = self::get_table_name('conversation');
    $wp_table_message = self::get_table_name('message');
    $wp_table_presence = self::get_table_name('presence');
    $Table_Users = self::get_table_name('users');

    $sql = " SELECT  c.*, u.user_nicename as reg_customer_name, p.last_presence, p.form_data, COUNT(m.id) as not_read,
                     CONVERT_TZ( c.created_at, @@session.time_zone, '+00:00') as created_at
             FROM $wp_table_conversation as c
             INNER JOIN $wp_table_presence as p ON p.customer_id = c.customer_id
             LEFT JOIN $wp_table_message as m ON m.conv_id = c.id AND m.is_read = false
             LEFT JOIN $Table_Users as u ON c.customer_id = u.user_email
             WHERE admin_email = %s
             AND c.id > %d
             AND p.last_presence >= NOW() - INTERVAL 60 MINUTE
             AND c.is_connected = TRUE
             GROUP BY c.id
             ORDER BY c.created_at ASC
             LIMIT 25 ";

    $sql = $wpdb->prepare( $sql, $admin_email, $last_conv_poll );
    $result = $wpdb->get_results( $sql, ARRAY_A );
    wp_reset_postdata();

    return ! empty( $result ) ? $this->construct_chat_form($result) : false;

  }

  protected function get_disconnected_convs( $admin_email, $conv_ids ) {
    global $wpdb;
    $wp_table_conversation = self::get_table_name('conversation');
    $prep_sql_list = '';
    $sql_values = array();

    foreach ($conv_ids as $key => $value) {
        $prep_sql_list .= ' %d,';
        $sql_values []= $value;
    }
    $prep_sql_list = rtrim( $prep_sql_list, "," );

    $sql = " SELECT id FROM $wp_table_conversation
             WHERE id IN( $prep_sql_list ) AND is_connected = FALSE
             LIMIT 20 ";

    $sql = $wpdb->prepare( $sql, $sql_values );
    $result = $wpdb->get_results( $sql, ARRAY_A );
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;

  }


  protected function get_current_conv_public( $admin_email, $customer_id ) {

    global $wpdb;
    $wp_table_conversation = self::get_table_name('conversation');

    $sql = " SELECT id FROM $wp_table_conversation WHERE customer_id = %s AND admin_email = %s AND is_connected = TRUE ";

    $sql = $wpdb->prepare( $sql, $customer_id, $admin_email );
    $result = $wpdb->get_var($sql);
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;
  }

  protected function get_active_conv_public( $customer_id ) {

    global $wpdb;
    $wp_table_conversation = self::get_table_name('conversation');
    $Table_Users = self::get_table_name('users');

    $sql = " SELECT c.id, u.user_nicename as admin_name
    FROM $wp_table_conversation as c
    INNER JOIN $Table_Users as u ON c.admin_email = u.user_email
    WHERE c.customer_id = %s AND c.is_connected = TRUE LIMIT 1 ";

    $sql = $wpdb->prepare( $sql, $customer_id );
    $result = $wpdb->get_results($sql);
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;
  }

  protected function get_latest_messages( $conv_id = 0, $last_msg_id = 0, $user_id = '' ) {

    global $wpdb;
    $wp_table_message = self::get_table_name('message');
    $wp_table_conversation = self::get_table_name('conversation');

    $sql = " SELECT mm.*
             FROM (  SELECT m.id, m.temp_id, m.message, IF( m.author_id = %s , TRUE, FALSE ) AS is_author, c.id as conv_id, m.product_ids, m.created_at as created_at
                     FROM $wp_table_message as m
                     INNER JOIN $wp_table_conversation as c ON c.id = m.conv_id
                     WHERE conv_id = %d AND ( customer_id = %s OR admin_email = %s ) AND m.id > %d
                     ORDER BY m.created_at DESC
                     LIMIT 25 )
             AS mm ORDER BY mm.created_at ASC ";

    $sql = $wpdb->prepare( $sql, $user_id, $conv_id, $user_id, $user_id, $last_msg_id );
    $result = $wpdb->get_results($sql, ARRAY_A);
    wp_reset_postdata();

    return ! empty( $result ) ? $this->construct_msg_links( $result ) : false;
  }

  protected function get_unread_messages( $admin_email ) {

    global $wpdb;
    $wp_table_message = self::get_table_name('message');
    $wp_table_presence = self::get_table_name('presence');
    $wp_table_conversation = self::get_table_name('conversation');

    $sql = " SELECT c.id, COUNT(m.id) as not_read
             FROM  $wp_table_conversation as c
             INNER JOIN $wp_table_presence as p ON p.customer_id = c.customer_id
             LEFT JOIN $wp_table_message as m ON m.conv_id = c.id AND m.is_read = false
             WHERE admin_email = %s
             AND c.is_connected = TRUE
             GROUP BY c.id
             HAVING not_read > 0
             ORDER BY c.created_at ASC
             LIMIT 20 ";

    $sql = $wpdb->prepare( $sql, $admin_email );
    $result = $wpdb->get_results($sql, ARRAY_A );
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;
  }

  protected function get_latest_messages_public( $customer_id = '', $conv_id, $last_msg_id = 0 ) {

    global $wpdb;
    $wp_table_message = self::get_table_name('message');
    $wp_table_conversation = self::get_table_name('conversation');

     $sql = " SELECT mm.*
              FROM (
                      SELECT m.id, m.temp_id, m.message, IF( m.author_id = %s , TRUE, FALSE ) AS is_author, m.product_ids, m.created_at as created_at
                      FROM $wp_table_message as m
                      INNER JOIN $wp_table_conversation as c ON c.id = m.conv_id
                      WHERE  ( c.customer_id = %s AND c.id = %d ) AND c.is_connected = true AND m.id > %d
                      ORDER BY m.created_at DESC
                      LIMIT 25 )
              AS mm ORDER BY mm.created_at ASC ";

    $sql = $wpdb->prepare( $sql, $customer_id, $customer_id, $conv_id, $last_msg_id );
    $result = $wpdb->get_results($sql, ARRAY_A);
    wp_reset_postdata();

    return ! empty( $result ) ? $this->construct_msg_links( $result ) : false;
  }

  protected function insert_new_message( $conv_id, $admin, $msg, $temp_id, $message_links = [], $is_admin = false ) {
    global $wpdb;
    $message_links = !empty($message_links) ? serialize($message_links) : null;
    $is_admin_safe = $is_admin ? 1 : 0;
    $sql = " CALL chatster_insert( %d, %s, %s, %d, %s, $is_admin_safe ) ";
    $sql = $wpdb->prepare( $sql, $conv_id, $admin, $msg, $temp_id, $message_links );

    $result = $wpdb->get_results($sql);
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;
  }

  protected function set_message_read( $user, $conv_id, $msg_id ) {
    global $wpdb;
    $wp_table_message = self::get_table_name('message');
    $wp_table_conversation = self::get_table_name('conversation');

    $sql = " UPDATE $wp_table_message as m
             INNER JOIN $wp_table_conversation as c ON c.id = m.conv_id
             SET m.is_read = TRUE
             WHERE m.id > %d
             AND c.id = %d
             AND ( c.customer_id = %s OR c.admin_email = %s )
             AND m.author_id <> %s ";

    $parameters = array($msg_id, $conv_id, $user, $user, $user );
    $sql = $wpdb->prepare( $sql, $parameters );
    $result = $wpdb->get_results($sql);
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;
  }

  protected function insert_form_data( $customer_id, $form_data ) {
     global $wpdb;
     $wp_table_presence = self::get_table_name('presence');
     $sql = " INSERT INTO $wp_table_presence ( customer_id, form_data ) VALUES( %s, %s ) ON DUPLICATE KEY UPDATE last_presence = DEFAULT, form_data = %s ";
     $sql = $wpdb->prepare( $sql, $customer_id, $form_data, $form_data );

     $result = $wpdb->query( $sql );
     wp_reset_postdata();

     return ! empty( $result ) ? true : false;


  }

  protected function disconnect_chat( $conv_id ) {
    global $wpdb;
    $wp_table_conversation= self::get_table_name('conversation');

    $sql = " UPDATE $wp_table_conversation
             SET is_connected = FALSE
             WHERE id = %d
             LIMIT 1 ";

    $sql = $wpdb->prepare( $sql, $conv_id );

    $result = $wpdb->query($sql);
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;
  }

  protected function disconnect_chat_customer( $customer_id ) {
    global $wpdb;
    $wp_table_conversation= self::get_table_name('conversation');

    $sql = " UPDATE $wp_table_conversation
             SET is_connected = FALSE
             WHERE customer_id = %s ";

    $sql = $wpdb->prepare( $sql, $customer_id );

    $result = $wpdb->query($sql);
    wp_reset_postdata();

    return ! empty( $result ) ? $result : $customer_id;
  }

  // Ticketing system

  protected function set_ticket( $customer_id ) {
    global $wpdb;
    $wp_table_ticket = self::get_table_name('ticket');

    $sql = " INSERT INTO $wp_table_ticket ( customer_id ) VALUES( %s ) ON DUPLICATE KEY UPDATE updated_at = DEFAULT ";
    $sql = $wpdb->prepare( $sql, $customer_id );
    $wpdb->query( $sql );

    return ! empty( $wpdb->insert_id ) ? $wpdb->insert_id : $this->get_ticket( $customer_id );
  }

  protected function get_ticket( $customer_id ) {
    global $wpdb;
    $wp_table_ticket = self::get_table_name('ticket');

    $sql = " SELECT id FROM $wp_table_ticket WHERE customer_id = %s ";
    $sql = $wpdb->prepare( $sql, $customer_id );
    $result = $wpdb->get_var( $sql );
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;

  }

  protected function delete_ticket( $ticket_id ) {
    global $wpdb;
    $wp_table_ticket = self::get_table_name('ticket');
    $sql = " DELETE FROM $wp_table_ticket WHERE id <= %d ";
    $sql = $wpdb->prepare( $sql, $ticket_id );

    $result = $wpdb->query( $sql );
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;

  }

  protected function get_queue_status( $ticket_id ) {
    global $wpdb;
    $wp_table_ticket = self::get_table_name('ticket');

    $sql = " SELECT COUNT(*) as count
             FROM $wp_table_ticket
             WHERE id < %d
             AND updated_at >= NOW() - INTERVAL 3 MINUTE ";

    $sql = $wpdb->prepare( $sql, $ticket_id );

    $result = $wpdb->get_var( $sql );
    wp_reset_postdata();

    return ! empty( $result ) ? $result : 0;

  }

  protected function get_queue_number() {
    global $wpdb;
    $wp_table_ticket = self::get_table_name('ticket');
    $sql = " SELECT COUNT(*) as count FROM $wp_table_ticket WHERE updated_at >= NOW() - INTERVAL 3 MINUTE ";
    $result = $wpdb->get_var( $sql );
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;

  }

  protected function find_active_admin( $max_allowed = 20 ) {
    global $wpdb;
    $wp_table_presence_admin = self::get_table_name('presence_admin');
    $wp_table_conversation = self::get_table_name('conversation');
    $Table_Users = self::get_table_name('users');

    $sql = " SELECT COALESCE( COUNT(c.id), 0 ) as count, p.admin_email as admin_email, u.user_nicename as admin_name
             FROM $wp_table_presence_admin as p
             INNER JOIN $Table_Users as u ON p.admin_email = u.user_email
             LEFT JOIN $wp_table_conversation as c ON p.admin_email = c.admin_email  AND c.is_connected = TRUE
             WHERE p.is_active = true
             AND p.last_presence >= NOW() - INTERVAL 20 MINUTE
             GROUP BY admin_email
             HAVING count < %d
             ORDER BY count ASC
             LIMIT 1 ";

    $sql = $wpdb->prepare( $sql, $max_allowed );
    $result = $wpdb->get_results($sql);
    wp_reset_postdata();

    return ! empty( $result ) ? array_shift( $result ) : false;
  }

  protected function set_new_conversation( $customer_id, $admin_email ) {
    global $wpdb;
    $wp_table_conversation= self::get_table_name('conversation');

    $sql = " INSERT INTO $wp_table_conversation ( customer_id, admin_email ) VALUES ( %s, %s ) ";
    $sql = $wpdb->prepare( $sql, $customer_id, $admin_email );
    $wpdb->query($sql);
    $conv_id = $wpdb->insert_id;
    wp_reset_postdata();

    return ! empty( $conv_id ) ? $conv_id : false;
  }

/**
 * Cron Jobs
 */
  protected function remove_old_convs( $interval = 60 ) {
    global $wpdb;
    $wp_table_conversation = self::get_table_name('conversation');
    $wp_table_presence = self::get_table_name('presence');

    $sql = " DELETE c.* FROM $wp_table_conversation as c
    INNER JOIN $wp_table_presence as p ON c.customer_id = p.customer_id
    WHERE c.is_connected = FALSE AND ( c.updated_at < NOW() - INTERVAL %d MINUTE ) ";

    $sql = $wpdb->prepare( $sql, $interval );
    $result = $wpdb->query($sql);
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;
  }

  protected function disconnect_old_convs( $interval = 60 ) {
    global $wpdb;
    $wp_table_conversation = self::get_table_name('conversation');
    $wp_table_presence = self::get_table_name('presence');

    $sql = " UPDATE $wp_table_conversation as c
    INNER JOIN $wp_table_presence as p ON c.customer_id = p.customer_id
    SET c.is_connected = FALSE
    WHERE ( p.last_presence < NOW() - INTERVAL %d MINUTE ) OR ( c.updated_at < NOW() - INTERVAL %d MINUTE ) ";

    $sql = $wpdb->prepare( $sql, array( $interval, $interval) );
    $result = $wpdb->query($sql);
    wp_reset_postdata();

    return ! empty( $result ) ? $result : false;
  }

  protected function set_admin_offline( $interval = 5 ) {
    global $wpdb;
    $wp_table_presence_admin = self::get_table_name('presence_admin');

    $sql = " UPDATE $wp_table_presence_admin SET is_active = false WHERE ( last_presence < NOW() - INTERVAL %d MINUTE )  ";
    $sql = $wpdb->prepare( $sql, array($interval));

    $result = $wpdb->query( $sql );
    wp_reset_postdata();

    return $result;
  }

}
