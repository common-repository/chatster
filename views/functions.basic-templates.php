<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists('ch_support_page_link')) {
  function ch_support_page_link() { ?>
      <a style="margin-left: 20px;" target="_blank" href="<?php echo esc_url_raw( CHATSTER_SUPPORT_URL ); ?>"><b><?php echo ucfirst(esc_html__('support page', CHATSTER_DOMAIN )); ?></b></a>
  <?php
  }
}

if ( ! function_exists('ch_small_loader')) {
  function ch_small_loader() {
    return '<div class="ch-small-loader hidden"></div>';
  }
}
