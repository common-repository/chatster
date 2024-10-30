<?php

namespace Chatster\Core;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( CHATSTER_PATH . '/includes/core/trait.table-builder.php' );
use Chatster\Core\ChatsterTableBuilder;

trait BotCollection {
  use ChatsterTableBuilder;

  public static $per_page_qa = 3;

  /**
   * Static Methods
   */
   protected static function get_answer_count() {
     global $wpdb;
     $wp_table_source_a = self::get_table_name('source_a');

     $sql = " SELECT COUNT(*) FROM $wp_table_source_a ";

     $count = $wpdb->get_var( $sql );

     return ! empty( $count ) ? intval($count) : false;
   }


  /**
   * Api Methods
   */

    protected function search_full_text( $question, $deep_search = false, $search_expanded = false, $answer_limit = 1, $excluded_ids = array() ) {
      global $wpdb;
      $wp_table_source_q = self::get_table_name('source_q');
      $wp_table_source_a = self::get_table_name('source_a');
      $search_type = $search_expanded ? " WITH QUERY EXPANSION " : " IN NATURAL LANGUAGE MODE ";

      $params = array( $question, $question );

      if ( $deep_search ) {

          $sql = " SELECT DISTINCT t_search.*, ( t_search.question_score + t_search.answer_score ) as global_score FROM
                          ( SELECT  a.id , a.answer, q.question,
                                    SUM( MATCH (q.question) AGAINST ( %s $search_type ) ) as question_score,
                                    SUM( MATCH (a.answer) AGAINST ( %s $search_type ) ) as answer_score
                            FROM $wp_table_source_q as q
                            INNER JOIN $wp_table_source_a as a ON a.id = q.answer_id
                            GROUP BY a.id
                            ORDER BY question_score DESC, answer_score DESC ) AS t_search
                    HAVING global_score > 0 ";

      } else {

           $sql = " SELECT DISTINCT a.id , a.answer, q.question, SUM( MATCH (q.question) AGAINST ( %s $search_type ) ) as question_score
                    FROM $wp_table_source_q as q
                    INNER JOIN $wp_table_source_a as a ON a.id = q.answer_id
                    WHERE MATCH (q.question) AGAINST ( %s $search_type )
                    GROUP by a.id
                    ORDER BY question_score DESC ";

      }

      if ( !empty($excluded_ids) ) {
        $sql_values = array();
        $prep_sql_list = '';
        foreach ($excluded_ids as $key => $value) {
            $prep_sql_list .= ' %d,';
            array_push($params, $value);
        }
        $prep_sql_list = rtrim( $prep_sql_list, "," );
        $sql .= " AND a.id NOT IN ( $prep_sql_list ) ";
      }
      array_push($params, $answer_limit);

      $sql .= " LIMIT %d ";

      $sql = $wpdb->prepare( $sql, $params );
      $result = $wpdb->get_results( $sql );

      return ! empty( $result ) ? $result : false;
    }

    protected function update_answer( String $answer, Int $answer_id ) {
      global $wpdb;
      $wp_table_source_a = self::get_table_name('source_a');

      $sql = " UPDATE $wp_table_source_a SET answer = %s WHERE id = %d ";

      $sql = $wpdb->prepare( $sql, array( $answer, $answer_id ) );
      $result = $wpdb->query( $sql );

      return ! empty( $result ) ? $result : false;
    }

    protected function insert_answer( String $answer ) {
      global $wpdb;
      $wp_table_source_a = self::get_table_name('source_a');

      $sql = " INSERT INTO $wp_table_source_a ( answer ) VALUES ( %s ) ";

      $sql = $wpdb->prepare( $sql, array($answer) );
      $result = $wpdb->query( $sql );

      return ! empty( $wpdb->insert_id ) ? $wpdb->insert_id : false;
    }

    protected function insert_questions( Array $questions, Int $answer_id ) {
      global $wpdb;
      $wp_table_source_q = self::get_table_name('source_q');

      $sql = " INSERT INTO $wp_table_source_q ( answer_id, question ) VALUES ";
      $params = array();
      $place_holders = array();
      foreach($questions as $key => $question) {
          array_push( $params, $answer_id, $question );
          $place_holders []= " ( %d, %s ) ";
      }

      $sql .= implode(', ', $place_holders);
      $sql = $wpdb->prepare( $sql, $params );
      $result = $wpdb->query( $sql );

      return ! empty( $result ) ? $result : false;
    }

    protected function delete_answer( Int $answer_id ) {
      global $wpdb;
      $wp_table_source_a = self::get_table_name('source_a');

      $sql = " DELETE FROM $wp_table_source_a WHERE id = %d ";

      $sql = $wpdb->prepare( $sql, array($answer_id) );
      $result = $wpdb->query( $sql );

      return ! empty( $result ) ? $result : false;
    }

    protected function delete_all_questions( Int $answer_id ) {
      global $wpdb;
      $wp_table_source_q = self::get_table_name('source_q');

      $sql = " DELETE FROM $wp_table_source_q WHERE answer_id = %d ";

      $sql = $wpdb->prepare( $sql, array($answer_id) );
      $result = $wpdb->query( $sql );

      return ! empty( $result ) ? $result : false;
    }

    protected function get_answer( Int $answer_id ) {
      global $wpdb;
      $wp_table_source_a = self::get_table_name('source_a');

      $sql = " SELECT * FROM $wp_table_source_a WHERE id = %d ";

      $sql = $wpdb->prepare( $sql, array($answer_id) );
      $result = $wpdb->get_results( $sql );

      return ! empty( $result ) ? $result : false;
    }

    protected function get_all_answers( Int $page, Int $count ) {
      global $wpdb;
      $wp_table_source_a = self::get_table_name('source_a');

      $offset = ( $page - 1 ) * self::$per_page_qa;

      $sql = " SELECT * FROM $wp_table_source_a ORDER BY created_at DESC LIMIT %d, %d ";

      $sql = $wpdb->prepare( $sql, array( $offset, self::$per_page_qa ) );
      $result = $wpdb->get_results( $sql );

      return ! empty( $result ) ? $result : false;
    }

    protected function get_all_questions( $answers ) {
      global $wpdb;
      $wp_table_source_q = self::get_table_name('source_q');

      $params = array();
      $place_holders = array();
      foreach($answers as $key => $answer) {
          array_push( $params, $answer->id );
          $place_holders []= "%d";
      }

      $sql = " SELECT * FROM $wp_table_source_q ";
      $sql .= " WHERE answer_id IN (".implode(', ', $place_holders).") ";

      $sql = $wpdb->prepare( $sql, $params );
      $result = $wpdb->get_results( $sql );

      return ! empty( $result ) ? $result : false;

    }

    protected function delete_all_answers() {
      global $wpdb;
      $wp_table_source_a = self::get_table_name('source_a');

      $sql = " DELETE FROM $wp_table_source_a WHERE id > 0 ";
      $result = $wpdb->query( $sql );

      return ! empty( $result ) ? $result : false;
    }

    protected function reset_qa_all() {
      global $wpdb;
      $wp_table_source_a = self::get_table_name('source_a');

      $sql = " DELETE FROM $wp_table_source_a WHERE id > 0 ";

      $result = $wpdb->query( $sql );

      return ! empty( $result ) ? $result : false;
    }

}
