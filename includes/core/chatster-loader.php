<?php


if ( ! defined( 'ABSPATH' ) ) exit;

require_once( CHATSTER_PATH . 'includes/core/action.global.php' );
require_once( CHATSTER_PATH . 'includes/core/class.notices.php' );
require_once( CHATSTER_PATH . 'includes/core/class.chat-form-serializer.php' );
require_once( CHATSTER_PATH . 'includes/core/class.cron-manager.php' );
require_once( CHATSTER_PATH . 'includes/functions.global.php' );
require_once( CHATSTER_PATH . 'includes/core/class.encrypter.php' );
require_once( CHATSTER_PATH . 'includes/activation/class.activation.php' );
require_once( CHATSTER_PATH . 'includes/activation/class.deactivation.php' );
require_once( CHATSTER_PATH . 'includes/update/class.update.php' );
require_once( CHATSTER_PATH . 'includes/options/class.add-options-bot.php' );
require_once( CHATSTER_PATH . 'includes/options/class.add-options-chat.php' );
require_once( CHATSTER_PATH . 'includes/options/class.add-options-request.php' );
require_once( CHATSTER_PATH . 'includes/options/class.add-options-bot-qa.php' );
require_once( CHATSTER_PATH . 'includes/options/class.get-options.php' );
require_once( CHATSTER_PATH . 'includes/options/class.reset-options-all.php' );
require_once( CHATSTER_PATH . 'includes/api/class.search-api-extender.php' );
require_once( CHATSTER_PATH . 'includes/api/class.chat.php' );
require_once( CHATSTER_PATH . 'includes/api/class.chat-admin.php' );
require_once( CHATSTER_PATH . 'includes/api/class.request-all.php' );
require_once( CHATSTER_PATH . 'includes/api/class.bot.php' );
require_once( CHATSTER_PATH . 'includes/core/class.add-chat-public.php' );
require_once( CHATSTER_PATH . 'views/class.display-manager.php' );

if ( is_admin() ) {
  require_once( CHATSTER_PATH . 'includes/core/class.add-admin-menu.php' );
}
