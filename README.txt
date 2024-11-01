=== WordPress Active Campaign API plugin ===

Author: Trumani
Plugin URI: https://trumani.com
Contributors: trumani
Tags: activecampaign
Stable Tag: trunk
Version: 1.1.7.1
Tested up to: 5.4
License: https://www.gnu.org/licenses/gpl.html

Stop WordPress Spam dead in its tracks

== Description ==

The WP ActiveCampaign API plugin integrates with Active Campaign's API which utilizes the site and event tracking and serves as a base plugin for other plugins, including our MemberMouse connector.

<h3>Requirements </h3>

* Site Tracking and Event Tracking are enabled
* Active Campaign Url
* API Key
* Event Key
* Event ID
* Domain being whitelisted

*All of this can be manage from your site tracking and event tracking page. Which is under integration— in the sidebar's other option.*

<h3>Identifying Users</h3>

In order to associate page visits and events with contacts in ActiveCampaign as soon as possible simply include the email parameter in links to your website or on thank you pages.

In the case of a visit by email:
yoursite.com/?email=%EMAIL%

Or you can pass it immediately upon subscription to your thank you page yoursite.com/thank-you/?email=%EMAIL%

I do both so that I can make sure I’m tracking users.

Once you do this the plugin will create a cookie for that visitor and that cookie can be used later by our other plugins (or by yours!) for a variety of things.

<h3>Creating Events</h3>

We wanted to make tracking events on a page really easy so we added this function to the WP2AC plugin:

ac_events('event','value').

This makes adding events to link or button clicks or anywhere else that you can use javascript really easy.

For example if you wanted to track an event when someone clicks on a link
	
onclick="ac_events('Event','Value');"

You can also use this in other places like to track video actions which is something we do with Wistia in the following example.

In this example we fire the event “Watched 60 Seconds!” with the value “What a guy” when the user has watched 60 seconds of a video.

<script>
window._wq = window._wq || [];
 
wistiaEmbed.bind("secondchange", function (s) {
  if(s === 60) {
    ac_events('Watched 60 seconds!', 'What a guy');
  }
});
</script>

Anywhere you can use Javascript you can use “ac_events();”

Premium Options
