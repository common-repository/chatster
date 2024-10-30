<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function display_admin_settings( $count_qa, $per_page_qa, $total_pages_qa ) {

    if ( ! current_user_can( 'manage_options' ) ) return; ?>

      <div id="ch-options-main-container" style="display: none;">

        <div id="reset-all-settings-errors">
          <?php settings_errors('chatster_reset_all'); ?>
        </div>

        <div class="ch-option-block">
          <div class="ch-option-title"><?php esc_html_e('Bot Setup', CHATSTER_DOMAIN); ?></div>
          <div id="bot-options" class="ch-option-container" style="display:none;">
            <form id="chatster-bot-options-form" action="options.php#bot-options" method="post">
            <?php
                  settings_errors('chatster_bot_options');
                  settings_fields( 'chatster_bot_options' );
                  do_ch_settings_section( 'chatster-menu' , 'ch_bot_section'); ?>
                  <div class="ch-reply-btn">
                    <?php submit_button($text = null, $type = 'primary', $name = 'submit-settings',$wrap = true, $other_attributes = ['id'=>'save-bot']); ?>
                    <p class="submit"><input type="submit" name="submit-default" class="button button-primary submit-reset" value="<?php esc_html_e('Reset Settings', CHATSTER_DOMAIN); ?>"></p>
                  </div>
            </form>
          </div>
        </div>

        <div class="ch-option-block">
          <div class="ch-option-title"><?php esc_html_e('Bot Q &amp; A', CHATSTER_DOMAIN); ?></div>
          <div id="bot-q-and-a" class="ch-option-container" style="display:none;">
            <div id="setting-error-bot_qa_message" class="notice notice-success settings-error is-dismissible hidden">
                <p><strong><?php esc_html_e('Q&A Was Reset Successfully!', CHATSTER_DOMAIN) ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">
                  <?php esc_html_e('Dismiss this notice.', CHATSTER_DOMAIN ); ?></span></button>
              </div>
            <div id="q-and-a-container">
                  <!-- Q and A Listing -->
                  <div id="q-and-a-list">
                    <div id="q-and-a-block"></div>
                    <div class="ch-small-loader"></div>
                    <div id="ch-no-q-and-a" class="hidden"><?php esc_html_e("You didn't add any Q&A yet!", CHATSTER_DOMAIN ); ?></div>
                  </div>

                  <?php
                  // Pagination
                  if ( $count_qa > $per_page_qa ) {
                        echo '<div id="ch-qa-pagination" data-page_max="'.esc_attr($total_pages_qa).'">';
                        echo paginate_links( array(
                                      'base' => add_query_arg( 'cpage', '%#%' ),
                                      'format' => '?page=%#%',
                                      'show_all' => true,
                                      'prev_text' => __('&laquo;'),
                                      'next_text' => __('&raquo;'),
                                      'total' => $total_pages_qa,
                                      'current' => 1,
                                      'type' => 'list'
                                      ));
                        echo '</div>'; } ?>
            </div>
            <form id="chatster-bot-and-a-form" action="" method="post">
            <?php ?>
              <div id="q-and-a-input" data-qa_edit_id="0" >
              <?php
                do_ch_settings_section( 'chatster-menu' , 'ch_bot_qa_section'); ?>
                <div class="ch-reply-btn" style="display:flex;">
                  <input type="submit" name="submit-settings" id="save-bot-q-and-a" class="button button-primary custom-class" value="Save Response">
                  <div class="ch-smaller-loader hidden" style="margin-left:20px;"></div>
                  <input type="submit" style="margin-left:20px;" name="submit-settings" id="cancel-bot-q-and-a" class="button button-primary custom-class hidden" value="Cancel Edit">
                  <input id="reset-bot-q-and-a" type="submit" name="submit-default" class="button button-primary" value="<?php esc_html_e('Reset Bot Q&A', CHATSTER_DOMAIN); ?>">
                </div>

              </div>
            </form>
          </div>
        </div>

        <div class="ch-option-block">
          <div class="ch-option-title"><?php esc_html_e('Chat Configuration', CHATSTER_DOMAIN); ?></div>
          <div id="chat-options" class="ch-option-container" style="display:none;">
            <div id="sound"></div>
            <form id="chatster-chat-options-form" action="options.php#chat-options" method="post">
            <?php
                settings_errors('chatster_chat_options');
                settings_fields( 'chatster_chat_options' );
                do_ch_settings_section( 'chatster-menu', 'ch_chat_section' );
                do_ch_settings_section( 'chatster-menu', 'ch_chat_admin_section' );
                ?>
                <div class="ch-reply-btn">
                  <?php submit_button($text = null, $type = 'primary', $name = 'submit-settings',$wrap = true, $other_attributes = ['id'=>'save-chat']); ?>
                  <p class="submit"><input type="submit" name="submit-default" class="button button-primary submit-reset" value="Reset Settings"></p>
                </div>
            </form>
          </div>
        </div>

        <div class="ch-option-block">

          <div class="ch-option-title"><?php echo esc_html__('Request&#47;Response', CHATSTER_DOMAIN) .' '.'<span class="mobile-removable">'.esc_html__('Configuration', CHATSTER_DOMAIN).'</span>'; ?></div>
          <div id="request-options" class="ch-option-container" style="display:none;">
            <form id="chatster-request-options-form" action="options.php#request-options" method="post">
            <?php
                settings_errors('chatster_request_options');
                settings_fields( 'chatster_request_options' );
                do_ch_settings_section( 'chatster-menu', 'ch_request_section' ); ?>
                <div class="ch-reply-btn">
                  <?php submit_button($text = null, $type = 'primary', $name = 'submit-settings',$wrap = true, $other_attributes = ['id'=>'save-request']); ?>
                  <p class="submit"><input type="submit" name="submit-default" class="button button-primary submit-reset" value="Reset Settings"></p>
                </div>
            </form>

            <form id="chatster-test-email-form" method="post">
              <?php do_ch_settings_section( 'chatster-menu', 'ch_request_test_section' ); ?>
              <div class="ch-reply-btn">
                <?php submit_button($text = esc_html__( 'Send Test Email', CHATSTER_DOMAIN ), $type = 'primary', $name = 'submit-settings',$wrap = true, $other_attributes = ['id'=>'ch-test-email']); ?>
                <div class="ch-smaller-loader hidden" style="margin-left:20px;"></div>
                <div class="ch-success hidden" style="margin-left:20px;"><?php esc_html_e('Sent Successfully!', CHATSTER_DOMAIN ); ?></div>
                <div class="ch-fail hidden" style="margin-left:20px;"><?php esc_html_e('Something went wrong.', CHATSTER_DOMAIN ); ?></div>

              </div>
            </form>

          </div>

        </div>
        <div class="ch-option-block reset-all-block">
          <form id="chatster-reset-all-settings" action="options.php" method="post">
            <?php
            settings_fields( 'chatster_reset_all' );
            submit_button($text = esc_html__( 'Reset All Configuration', CHATSTER_DOMAIN ), $type = 'primary', $name = 'submit-settings',$wrap = true, $other_attributes = ['id'=>'reset-all']);
            ch_support_page_link();
            ?>
          </form>

        </div>


      </div>


    <?php
}
