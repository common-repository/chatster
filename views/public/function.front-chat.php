<?php

if ( ! defined( 'ABSPATH' ) ) exit;
use Chatster\Api\GlobalApi;

function display_front_chat( $current_conv = false, $chat_available = false )  {
    Global $ChatsterOptions;
  ?>
   <div id="chatster-container" class="">
    <section id="ch-header"><span id="ch-header-text"><?php echo esc_html($ChatsterOptions->get_chat_option('ch_chat_header')); ?></span><div class="ch-arrow"><i class="ch-down"></i></div>
    </section>

    <div id="ch-main-conv-container">

      <section id="ch-chat-section" class="<?php echo ! $current_conv ? 'hidden' : '';  ?>" data-is_conv_active="<?php echo $current_conv ? '1' : '0'; ?>">
        <div id="ch-msg-container" class="ch-chat-box" data-last_msg_id="" data-conv_id="<?php echo !empty($current_conv) ? esc_attr($current_conv->id) : ''; ?>"
          data-admin_thumb_url="<?php echo !empty($current_conv) ? esc_url( get_avatar_url( $current_conv->admin_email ) ) : ''; ?>">
          <div class="ch-spacer" style="height:38px;"></div>
          <div class="ch-small-loader" ></div>
        </div>
        <div class="ch-queue-info">
            <div id="ch-inqueue" class="hidden"><?php echo esc_html__('Customers already waiting: ', CHATSTER_DOMAIN ) ?><span></span></div>
            <div id="ch-inqueue-end" class="hidden"><?php echo esc_html__('An admin will be here shortly..', CHATSTER_DOMAIN ) ?><span></span></div>
            <div id="ch-admin-info" class="hidden"><?php echo esc_html__('You are beign helped by ', CHATSTER_DOMAIN ) ?><span></span></div>
            <div id="ch-chat-unavailbale" class="hidden"><?php echo esc_html__('Sorry, we are currently unavailable.. ', CHATSTER_DOMAIN) ?><span></span></div>
            <div id="ch-chat-disconnected" class="hidden"><?php echo esc_html__('You are now disconnected..', CHATSTER_DOMAIN) ?><span></span></div>
            <div id="ch-assigned-admin" class="<?php echo ! $current_conv ? 'hidden' : '';  ?>"><?php echo esc_html__('You are chatting with ', CHATSTER_DOMAIN) ?>
              <span><?php echo !empty($current_conv) ? esc_html(ucfirst($current_conv->admin_name)) : ''; ?></span>
            </div>
        </div>
        <div class="ch-input ch-input-reply">
          <input type="text" id="ch-reply-public" class="ch-input-text" value="" placeholder="<?php echo esc_attr__( 'Your message here..', CHATSTER_DOMAIN ); ?>" autocomplete="off" maxlength="799" <?php echo ! $current_conv ? 'disabled' : '';  ?> >
          <button id="ch-reply-chat-button" class="ch-send-now"><i class="fa fa-send-o ch-send-arrow"></i></button>
        </div>
        <div id="ch-chat-select-container">
          <div id="ch-end-chat" class="ch-end-btn ch-button-global"><?php echo esc_html__( 'End Chat', CHATSTER_DOMAIN ); ?></div>
          <!-- <div id="ch-chat-msg" class="ch-send-btn ch-button-global"><?php //echo esc_html__( 'Send', CHATSTER_DOMAIN ); ?></div> -->
          <div class="ch-cancel-btn ch-button-global hidden"><i class="fa fa-arrow-circle-left" aria-hidden="true"></i>&nbsp; <?php echo esc_html__( 'Back', CHATSTER_DOMAIN ); ?></div>
        </div>

      </section>

      <section id="ch-request-form" class="hidden">
        <form id="ch-send-request-form">
          <div class="ch-queue-info"><?php echo esc_html__( 'Please fill out this form to get in touch!', CHATSTER_DOMAIN ); ?></div>
          <div class="ch-input">
            <input type="text" id="ch-customer-name" value="" placeholder="Your name" info="name" required>
          </div>
          <div class="ch-input">
            <input type="email" id="ch-customer-email" value="" placeholder="Your email" info="email" required>
          </div>
          <div class="ch-input">
            <input type="text" id="ch-customer-subject" value="" placeholder="Subject" info="subject" required >
          </div>
          <div class="ch-input">
            <textarea id="ch-customer-message" rows="4" placeholder="<?php esc_attr_e( 'Type here your message..', CHATSTER_DOMAIN ); ?>" type="text" autocomplete="off" required></textarea>
          </div>

          <div class="ch-inline-selector">
            <div class="ch-cancel-btn ch-button-global"><i class="fa fa-arrow-circle-left" aria-hidden="true"></i>&nbsp; <?php echo esc_html__( 'Back', CHATSTER_DOMAIN ); ?></div>
                      <div class="ch-confirm-sent hidden" style="color: green;"><?php esc_html_e( 'Sent', CHATSTER_DOMAIN ); ?> <i class="fa fa-check" aria-hidden="true" style="color: green;"></i></div>
                      <div class="ch-smaller-loader hidden"></div>
                      <div class="ch-error-sent hidden" style="color: red;"><?php esc_html_e( 'Try Again', CHATSTER_DOMAIN ); ?></div>
            <button id="ch-send-request" class="ch-send-btn ch-button-global" type="submit"><?php echo esc_html__( 'Send', CHATSTER_DOMAIN ); ?>&nbsp; <i class="fa fa-paper-plane" aria-hidden="true"></i></button>
          </div>

        </form>
      </section>

      <section id="ch-chat-form" class="hidden">
        <form id="ch-start-chat-form">
          <div class="ch-queue-info"><?php echo esc_html__( 'Start Chatting now!', CHATSTER_DOMAIN ); ?></div>
          <div class="ch-input">
            <input type="text" id="ch-chat-name" value="" placeholder="<?php echo esc_attr__( 'Your name', CHATSTER_DOMAIN ); ?>" info="name" maxlength="<?php echo esc_attr( GlobalApi::get_attribute('chat_name_length') ); ?>" required >
          </div>
          <div class="ch-input">
            <input type="email" id="ch-chat-email" value="" placeholder="<?php echo esc_attr__( 'Your email', CHATSTER_DOMAIN ); ?>" info="email" required >
          </div>
          <div class="ch-input">
            <textarea id="ch-chat-subject" placeholder="<?php echo esc_attr__( 'Type here your question..', CHATSTER_DOMAIN ); ?>" type="text" rows="4" maxlength="<?php echo esc_attr( GlobalApi::get_attribute('chat_subject_length') ); ?>" autocomplete="off" required ></textarea>
          </div>

          <div class="ch-inline-selector">
            <div id="ch-send-request" class="ch-cancel-btn ch-button-global"><?php echo esc_html__( 'Cancel', CHATSTER_DOMAIN ); ?></div>
            <div class="ch-smaller-loader hidden"></div>
            <input id="ch-start-chatting" class="ch-send-btn ch-button-global" type="submit" value="<?php echo esc_html__( 'Start Chatting', CHATSTER_DOMAIN ); ?>">
          </div>

        </form>
      </section>

      <section id="ch-chat-select" class="<?php echo $current_conv ? 'hidden' : '';  ?>">

        <div id="ch-bot-msg-container" class="ch-chat-box">
           <div class="ch-spacer" style="height:38px;"></div>
        </div>
        <div class="ch-queue-info"><?php echo esc_html__( 'Our Bot ', CHATSTER_DOMAIN ).$ChatsterOptions->get_bot_option('ch_bot_name').' '.esc_html__( 'is here to help you.', CHATSTER_DOMAIN ); ?></div>
        <div class="loading-dots invisible"></div>
        <div class="ch-input ch-input-reply">
          <input type="text" class="ch-input-text" id="ch-reply-bot" value="" placeholder="<?php echo esc_attr__( 'Your message here..', CHATSTER_DOMAIN ); ?>" maxlength="799" autocomplete="off" >
          <button id="ch-reply-bot-button" class="ch-send-now"><i class="fa fa-send-o ch-send-arrow"></i></button>
        </div>
        <div id="ch-select-container">
            <div id="ch-btn-chat" <?php echo ! $chat_available ? 'title="'.esc_html__( 'Chat unavailable at the moment.', CHATSTER_DOMAIN ).'"' : ''; ?> class="ch-button-global<?php echo ! $chat_available ? ' ch-unavailable' : ''; ?>"><?php echo esc_html__( 'Live Chat', CHATSTER_DOMAIN ); ?></div>
            <div id="ch-btn-request" class="ch-button-global"><?php echo esc_html__( 'Message Us', CHATSTER_DOMAIN ); ?></div>
        </div>
      </section>

    </div>

    <div id="sound"></div>

  </div>

  <div id="chatster-opener">
    <section id="ch-open-button">
      <div id="ch-open-button-block"><span><?php echo esc_html($ChatsterOptions->get_chat_option('ch_chat_intro')).' '; ?></span><i id="fa-plane-opener" class="fa fa-paper-plane" aria-hidden="true"></i></div>
      <div id="ch-red-dot-alert" class="hidden"></div>
    </section>
  </div>


<?php

}
