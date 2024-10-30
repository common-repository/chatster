<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function display_admin_request( $requests, $total_pages, $current_page, $per_page, $count, $unreplied_only ) {
    if ( ! current_user_can( 'manage_options' ) ) return; ?>

      <div class="wrap" style="display: none;"><?php

      /**
       * If No Results
       */
      ?>
        <table id="ch-no-results" class="wp-list-table widefat fixed striped posts <?php echo !$requests ? '' : 'hidden'; ?>" width="100%">
          <tbody id="the-list"><tr><td style="vertical-align: middle; text-align: center;">
            <div class="alert-view-no-results"><?php echo esc_html__('No Messages yet', CHATSTER_DOMAIN); ?></div></td></tr>
          </tbody>
        </table>
        <div style="margin-top: 20px;" class="<?php echo !$requests ? '' : 'hidden'; ?>" ><?php ch_support_page_link(); ?></div>
        <?php if ( !$requests ) return; ?>

      <!-- Show replied requests by default -->
      <div id="show-replied" class="switch-container">
      <label class="switch">
        <input type="checkbox" <?php echo $unreplied_only ? '' : 'checked'; ?>>
        <span class="slider round slider-groupby"></span>
      </label>
      <span> &nbsp; <?php echo esc_html__('Show Replied Messages', CHATSTER_DOMAIN); ?></span>
      </div>

      <!-- Table Header -->
      <table id="ch-request-list" class="wp-list-table widefat fixed striped posts">
        <thead>
          <tr id="ch-full-screen">
            <th scope="col" class="ch-th-title" style="width:30%" ><div><?php echo esc_html__('User Name', CHATSTER_DOMAIN); ?></div></th>
            <th scope="col" class="ch-th-title" style="width:20%" ><div><?php echo esc_html__('Subject', CHATSTER_DOMAIN); ?></div></th>
            <th scope="col" class="ch-th-title" style="width:15%" ><div><?php echo esc_html__('Date Received', CHATSTER_DOMAIN); ?></div></th>
            <th scope="col" class="ch-th-title" style="width:20%" ><div><?php echo esc_html__('Last Replied', CHATSTER_DOMAIN); ?></div></th>
            <th scope="col" class="ch-th-title" style="width:10%;text-align:center;" ><div><?php echo esc_html__('Pinned', CHATSTER_DOMAIN); ?></div></th>
          </tr>
          <tr id="ch-mobile-screen">
            <th scope="col" class="ch-th-title" style="width:100%" ><div><?php echo esc_html__('Message Data', CHATSTER_DOMAIN); ?></div></th>
          </tr>
        </thead>

        <tbody id="the-list">
        <?php

        /**
        * Loops through the received Requests
        */
        foreach ($requests as $request ) {

            echo '<tr id="request-'.esc_attr( $request->id ).'" data-request_id="'.esc_attr( $request->id ).'" class="edit author-self type-product hentry request-row">';
            // Requester and reply section
            echo '<td style="width:40%;"><div class="ch-requester-name">' . esc_html( $request->name ) .'</div><div>' . esc_html( $request->email ) .'</div></td>';
            // Request Subject
            echo '<td style="width:20%;"><div class="ch-requester-subject">' . esc_html( $request->subject ) .'</div></td>';
            // Request Received DateTime
            $dt = new DateTime("now", chatter_get_timezone() );
            $dt->setTimestamp(strtotime($request->created_at));
            echo '<td style="width:15%;"><div><span title="'.esc_attr( $dt->format('F d, Y h:i A') ) .'">'. esc_html( $dt->format('F d, Y h:i A') ) . '</span></div></td>';
            echo '<td style="width:20%;">';
            // Request Replied DateTime ( if replied )
            if ( $request->replied_at ) {
              echo '<div class="ch-replied-at">';
              $dt = new DateTime("now", chatter_get_timezone() );
              $dt->setTimestamp(strtotime($request->replied_at));
              echo '<div title="'.esc_attr( $dt->format('F d, Y h:i A') ) .'">'. esc_html( $dt->format('F d, Y h:i A') ) . '</div>';
              echo '<div title="'. esc_html( $request->last_replied_by ) .'">'. esc_html__('By: ', CHATSTER_DOMAIN ). esc_html( $request->last_replied_by ) . '</div>';
              echo '</div></td>';
            } else {
              echo '<div></div></td>';
            }
            // Request is flagged
            $unflagged = !$request->is_flagged ? 'unflagged' : 'flagged';
            $flag_url = CHATSTER_URL_PATH . 'assets/img/red-pin.png';
            echo '<td style="width:10%;"  class="pinned-flag '. $unflagged .'" data-flag_status="'.esc_attr( $request->is_flagged ).'"><div style="text-align:center">';
            echo '<img  draggable="false" title="Pin it" src="'.esc_url_raw( $flag_url ).'" alt="Pinned" style="max-width:16px;">';
            echo '</div></td>';
            echo '</tr>';
            // Zebra pattern matching row
            echo '<tr id="striped-separator-'.esc_attr( $request->id ).'" class="hidden"></tr>';
            // Hidden reply section
            echo '<tr id="reply-section-'.esc_attr( $request->id ).'" data-request_id="'.esc_attr( $request->id ).'" class="edit type-product status-publish has-post-thumbnail hentry product_cat-posters">';
            echo '<td style="width:100%;" colspan="5">';
            echo '<div class="ch-smaller-loader" style="display:none;"></div>';
            echo '<div class="row-actions" data-request_id="'.esc_attr( $request->id ).'">';
            echo '<span class="reply" ><a href="" aria-label="Reply">'.( empty($request->replied_at) ? esc_html__( 'Reply', CHATSTER_DOMAIN ) : esc_html__( 'Show&#47;Reply', CHATSTER_DOMAIN )  ).'</a> |</span> ';
            echo '<span class="delete"><a href="" class="submitdelete" aria-label="Delete">'.esc_html__( 'Delete', CHATSTER_DOMAIN ).'</a></span></div>';
            echo '<div class="reply-container hidden">';
            echo '<div class="reply-message"><span class="reply-message-intro">'.esc_html( $request->name ).' '.esc_html__( 'asks', CHATSTER_DOMAIN).': </span><br>'.esc_html( $request->message ).'</div>';
            echo !empty($request->replied_at) ? ch_small_loader() : '';
            echo '<div class="reply-all-container"></div>
                    <div class="ch-reply-input">
                    <form class="ch-reply-form" data-request_id="'.esc_attr( $request->id ).'" >
                      <textarea required placeholder="'.esc_html__( 'Type here your message..', CHATSTER_DOMAIN ).'" type="text" rows="6" maxlength="799"></textarea>
                      <div class="ch-btn-reply">
                        <div class="ch-smaller-loader hidden" style="margin-right: 20px;"></div>
                        <input type="submit" value="'.esc_html__( 'Send Email', CHATSTER_DOMAIN ).'">
                      <div>
                    </form>
                    </div>
                  </div>
                  </td>';
            echo '</tr>';
         }

        ?>
        </tbody>
        </table>
        <?php
        /**
        * Pagination Block
        */
        if ( $count > $per_page ) {
          echo '<div class="ch-pagination">';
          echo paginate_links( array(
                  'base' => add_query_arg( 'cpage', '%#%' ),
                  'format' => '?page=%#%',
                  'prev_text' => __('&laquo;'),
                  'next_text' => __('&raquo;'),
                  'total' => $total_pages,
                  'current' => $current_page,
                  'type' => 'list'
          ));
          echo '</div>';
        } ?>

        <div style="margin-top: 20px;" ><?php ch_support_page_link(); ?></div>
 
      <!-- Closes Wrap - Main Div -->
      </div>

<?php
}
