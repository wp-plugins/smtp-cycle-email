=== SMTP Cycle Email ===
Plugin URI: http://www.cybernetikz.com
Contributors: cybernetikz
Donate link: 
Tags: smtp,cycle,email,spin text,smtp cycle email,smtp email
Requires at least: 2.9.2
Tested up to: 3.5.1
Stable tag: 1.0
License: GPL2

Using this plugins, you can send email to different user using various smtp server with spinning text feature.

== Description ==

<strong>If you need paid support to get this plugin installed or to have a customized version of the plugin please send us an email to this address <a href="mailto:contact@cybernetikz.com">contact@cybernetikz.com</a></strong>.

Using this plugins, you can send email to different user using various smtp server with spinning text feature. You can set cron job from cpanel to send email automatically from you server, using your-domain/wp-content/plugins/smtp-cycle-email/cron.php this file.

Main key feature for this plugins

* SMTP servers to send email
* Use SMTP servers cycle wise
* Spinning text to generate dynamic content
* Set cron job to send schedule email

There is a publicly accessible web page file which triggers the cron script every 5 minutes. You can use <a href="http://webcron.org/">webcron.org</a> or cpanel to set cron job to simply load the page. The page, when loaded, just grabs the "scheduled" email that is to be sent out and send it out using the next-available SMTP server.

Each time the SMTP servers cycled. For example, after server 1 is used, then the next server attempted. Only use servers marked as active. If the outbound message is not successful, then mark the SMTP server as INACTIVE and reset the message to "scheduled". If the message is successful, mark the message as sent (insert the date-time) and also mark the SMTP server's `date last used` so that the next time the script runs, it knows to use the OLDEST use SMTP server and can keep cycling through them.


You can visit our site <a href="http://www.cybernetikz.com/web-development/wordpress/wordpress-plugins-smtp-cycle-email/">www.cybernetikz.com/</a>

== Installation ==

1. Download the plugin and extract the files
2. Upload "smtp-cycle-email" to your wp-content/plugins/ directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Check the "SMTP Server" Tab created by this plugins for setup.

== Frequently Asked Questions ==

= Where do I submit a question? =

This is my first attempt at a plugin, so I don't have a FAQ yet.

== Screenshots ==

1. Add SMTP server information
2. add email message
3. how to schedule message
4. status for scheduled message
4. option page layout

== Changelog ==

= 1.0.0 =
* First version.

== Thank You ==

Thank you trying this plugin!

