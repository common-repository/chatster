<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function display_admin_chat( $admin_status ) {
    if ( ! current_user_can( 'manage_options' ) ) return; ?>

    <div class="wrap" style="display: none;">

      <div id="online-switch-container">
        <div class="onoffswitch">
            <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="chatster-chat-switch" <?php echo $admin_status ? 'checked' : ''; ?>>
            <label class="onoffswitch-label" for="chatster-chat-switch">
                <span class="onoffswitch-inner"></span>
                <span class="onoffswitch-switch"></span>
            </label>
        </div>
        <div id="switch-loader" class="ch-smaller-loader hidden"></div>
      </div>

      <div id="ch-conversation-container">

            <div id="conversations-block" data-last_conv_id="" class="ch-fancy-scroll">
              <div id="ch-load-conv-container">
                <div id="ch-empty-conv-msg"><?php esc_html_e( 'Your conversations will be shown here..' , CHATSTER_DOMAIN ); ?></div>
                <div id="ch-roller-container" class="<?php echo !$admin_status ? 'hidden' : ''; ?>">
                  <div class="ch-roller" ><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                </div>
              </div>

            </div>

            <div id="ch-reply-block" class="ch-fancy-scroll">
                <div id="ch-message-board" data-conv_id=""  data-last_msg_id="" data-customer_id="">
                </div>
                <div id="ch-no-message-overlay"><?php esc_html_e( 'Current conversation will be shown here.' , CHATSTER_DOMAIN ); ?></div>
                <div id="ch-loading-conversation" class="hidden"><div class="ch-smaller-loader"></div></div>

            </div>

       </div>

          <div id="ch-queue-counter">
            <div class="ch-singular hidden">
              <?php echo sprintf(esc_html__("There is %s customer waiting in line", CHATSTER_DOMAIN), '<span></span>'); ?>
            </div>
            <div class="ch-plural hidden">
              <?php echo sprintf(esc_html__("There are %s customers waiting in line", CHATSTER_DOMAIN), '<span></span>'); ?>
            </div>
          </div>

       <div id="ch-attachments">
       </div>

       <div class="ch-input">
         <textarea id="ch-reply" class="disabled" placeholder="Type here your message.." type="text" rows="1" maxlength="799" disabled></textarea>
       </div>

       <div class="ch-input-link">
         <img class="paper-clip" title="<?php esc_html_e( 'Attach a link to a page or product.', CHATSTER_DOMAIN ); ?>" src="<?php echo esc_url( CHATSTER_URL_PATH . 'assets/img/paper-clip.jpg' );?>">
         <input id="ch-reply-link" class="ch-chat-autocomplete" placeholder="Find a product or page.." type="text" maxlength="40">
       </div>

       <?php ch_support_page_link(); ?>
       
       <!-- Sounds -->
       <div id="chat-sound"></div>
       <div id="conv-sound"></div>

    </div>

    <?php
}
