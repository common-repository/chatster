=== Chatster ===
Contributors: frankspress
Donate link: https://paypal.me/frankspress
Tags: woocommerce, chat, bot, message, contact, question
Requires at least: 4.9
Tested up to: 5.5.1
Stable tag: 1.0.1
WC requires at least: 3.5
WC tested up to: 4.6.1
Requires PHP: 7.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Customizable real time chat with messaging system and BOT helper.

== Description ==

<h4>Introduction</h4>

<p><i>Chatster</i> is a WooCommerce extension that allows real time chat with visiting users, supports your website growth with a BOT that will try to answer your customer questions and allows users to send messages and ask questions. <br>
</p>

<p>The plugin is customizable, you can choose chat colors, bot image, screen position and much more.. Plenty of options are available for the front chat as well as the admin area.</p>

<p>The chat system will balance the load of customers across all admins that have selected to be online.
You can select the maximum number of users you want to chat with simultaneously and <i>Chatster</i> will create a queue, inform the customers of their queue status and display in the admin chat the total number of user waiting.
One of the cool features is the possibility to search product and pages without leaving conversation and attach them directly to the chat, where the customer can see and even follow the link.
</p>

<p>The Bot helper will be your delegate when you are not around. You can program questions and answers. He will read the customer question, research the best match and pull the most appropriate answer.
You can also adjust sensitivity and enable deep-search mode and personalize its responses.</p>

<p>If the customer requires a direct interaction and the chat is offline, the message me button is one click away. They can simply fill out the small form and send the question or comment through and it will be posted in the <i>Chatster</i> messaging section.
<i>Chatster</i> can also alert you of new messages received, with an email sent to your favorite email address. You can then reply the message from <i>Chatster</i> and set up a reply-to email to continue the correspondence.</p>

<h4>Notes</h4>
<p>This plugin uses WordPress <b>REST API</b> and it must be <b>enabled</b> for this to work.</p>
<p><b>No External Service</b> Required to run this application. This plugin runs exclusively on your machine and you won't require any subscription.</p>
<p>This plugin uses <b>Cookies</b>. Please remember that it's your responsibility to abide any laws and regulations within your country or jurisdiction ( e.g. EU Cookie laws ).</p>

== Installation ==

<h4>To Install</h4>

<p>
1. Upload the plugin files to the `/wp-content/plugins` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Chatster then click Settings to configure the plugin
4. Open each setting section and change options according to your preference and needs.
</p>

<h4>To Configure</h4>

<p>It's crucial to have WordPress <b>REST API enabled</b>. The functionality is enabled by default but some security plugins and others may have disabled it.
Verify by simply switching to "Online" in the conversation tab and opening an <b>incognito window</b>; from there start a live chat session.
Do Not try live chat with your own account, it simply will show all messages on the same side of the conversation, since you are talking to yourself. Use an incognito window instead!</p>

<p>Strongly suggested to have a <b>"transactional emails service"</b>. This will allow you to send individual emails and respond customers using <i>Chatster</i> with your custom email template.
To test, go to 'Settings' then 'Request/Response' and finally 'Test Functionality'.</p>

== Frequently Asked Questions ==

= Do I have to configure anything to use it? =
Yes! The chat system should work out of the box but your BOT must be configured with your answers to specific questions. Please follow the instructions on how to install and configure.

= I don't seem to receive any test email. Is MailChimp a Transactional Mail Service? =
No it is not. MailChimp is used for campaign emails. Transactional means on request. Some have free ( and limited ) accounts, such as SendGrid.
You can also install plugins such as "WP Mail SMTP" and configure the one you have.

= I'm testing the chat and all the messages seems to be on the same side of the conversation.. What is going on? =
You need to open the chat in an incognito window while you have the admin section on your local window.
If you are logged in with the same account, you are basically the same person ( same credentials ) talking to yourself.
Therefore all the messages will be shown on the same side.

= I can't find the answer to my question, how can I get help? =
You can use the [support page](https://wordpress.org/support/plugin/chatster) and I will be glad to help.

== Screenshots ==

1. Front Live Chat.
1. Chat Collapsed.
1. Admin Chat Console.
1. Product Lookup.
1. Received Messages.
1. Bot Setup Settings.
1. Q and A Insert Screen.
1. Public Chat Settings.
1. Admin Chat Settings.
1. All Settings.
1. Received Messages.
1. Email Layout.
1. Email Response Configuration.
1. Chat Color Styling example.

== Changelog ==

= 1.0.1 =
* Removes jQuery deprecations.

= 1.0.0 =
* Initial release.
