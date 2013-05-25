Wordpress Mail Spooling
=======================

This plugin hooks the wordpress mail function and instead of sending mails
out, mails are saved into the database.
In the background, a cronjob sends the mails out. This takes the time-consuming
process of mail sending out of the webserver

Setup
-----
* Install the plugin like any wordpress plugin
* Setup a cronjob, similar to the following line in `/etc/crontab`:

    `*/2 * * * * www-data php /var/www/wp-content/plugins/mail-spooling/mail-send-daemon.php > /dev/null 2>&1`
    
* Make sure the user executing the cronjob (www-data in our example) can write to the plugin directory

Attention
---------
* This MIGHT break mails with attachments. We haven't tried this.
* This MIGHT break in future WordPress versions.
* This SHOULD only work on Linux servers
