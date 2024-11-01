===wp-easybooking===
Contributors: Panos Lyrakis
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=L8SHL3M7CYVCJ
Tags: booking, reservation, hotel, multihotel, multilingual, billing management, multi hotel
Requires at least: 3.4
Tested up to: 3.5
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

wp-easybooking plugin adds multiple-hotel and booking management features.

== Description ==

The **wp-easybooking** plugin gives you the ability to create and manage multiple hotels, hotel owners and bookings. 

It supports any language with the help of the free qTranslate plugin. Also provides an area at the administration panel so you can translate every message of the widget, for each language enabled. No .po files, and no .mo files, so no need for Poedit...  

Gets currencies rates from the European Central Bank and converts all prices automatically. 

The wp-easybooking widget is included so users may search for hotels and make their bookings. The search form includes an ajax pop up window with suggestions of Cities, Countries or Hotels that match the search term.

Users that have completed a booking receive a 4 digit PIN by email so that they may view their booking on-line.

Users do not need to register in order to make a booking.

Five booking statuses : Pending, Confirmed, Canceled, Completed and Expired. Hotel owners (and the administrator) may change the statuses of each booking, and confirm payed amount. 

Users get informed about any balance (if they have not payed the full booking amount) from the booking's page.

Guests (users) may pay through bank and paypal.

A premium version also provides package deals (or business packs). In this way the administrator knows the amount that each hotel owes to him. Charges get calculated automatically, based on the package deal, which can be a periodical charge (e.g each month), a percentage of bookings cost, or a combination of both of these charging methods. 

In short the free version provides the following features:
 
1.Unlimited Hotels,
2.Unlimited Hotel Owners (each owner can owe unlimited hotels),
3.Hotel Owners have a separate role (Businessman) to log in to the administration area, and manage their hotels and bookings,
4.Multilingual, with a translation menu for all messages of the widget (no .mo or .po files needed!),
5.Currency conversions (Automatically),
6.Unlimited bookings,
7.Bookings management (Statuses and payment balance),
8.Unlimited room types for each hotel,
9.A list of countries, regions and cities is included,
10.The admin can add ass many new cities needed, fast and easy and also translate it at once,
11.Search and booking widget (Users can search by location or hotel name),
12.Search by date only for available rooms depending on the number of adults, children and babies,
13.No need for users to register in order to make their booking,
14.Booking number and PIN sent for each booking for on-line access to the booking view page (which displays the booking's details),
15.Guests may pay through Bank or Paypal (paypal payment gateway is included!)

 

For detailed documentation please read the instructions.pdf file or visit the plugin's website <http://wp-easybooking.com>.

For any questions contact us at support@wp-easybooking.com

== Installation ==

1. Unzip the file you downloaded (wp-easybooking.1.0.1.zip).
2. Upload the 'wp-easybooking' folder to the '/wp-content/plugins/' directory. 
3. Activate the 'Easy Booking' plugin through the 'Plugins' menu in WordPress.
4. Activate the 'Easy Booking Widget' widget through the 'Plugins' menu in WordPress.

*Please make sure that you first activate the plugin and then activate the widget.*

You can find more information here <http://wp-easybooking.com/content/6-install-plugin>, and instructions on how to set up the plugin here <http://wp-easybooking.com/content/7-settings>. You can also read this information at the instructions.pdf file.


== Screenshots ==

1. The list of Hotels (Businesses). The admin has immediate access to each business's area.
2. Business edit area.
3. Booking management page
4. Availability page
5. Search form.
6. Guests can view their booking detail and status on-line by adding their booking number and PIN.
7. Search results.
8. Hotel page.
9. Booking page.

== Frequently Asked Questions ==
= When I activate the plugin and widget, there are several pages added at the menu bar. But when I click on them nothing appears. =
The plugin creates these several pages in order to use them for displaying specific content. 
In the instructions.pdf (or at <http://wp-easybooking.com>) you will see that you have to create a new menu (from Appearance/ Menus), and add only the specific pages or post you want, excluding the pages the plugin created.
In some themes, these pages will still be visible at the menu bar. In that case you have to install a plugin to exclude pages from the manu bar. There are several free plugins to do that.

= When activating the widget, the text in the buttons and labels of the search form appears in many languages, and it is not readable. =
This is because the plugin comes with some translations. This will be fixed once you install the qTranslate plugin.

= Why do I see 'No report available' at the Balance column at the Businesses List? =
The free version does not calculate the fee that Hotel Owners owe you. That's why it can not display any report.

= How can I add a new language? =
From the qTranslate settings.

= How can I translate the messages of the widget to a language I added, or change the translations that already exist? =
From the 'Translations' menu of the Easy Booking plugin. All messages are categorised depending on the page they appear. 
So press on 'Search Form' to translate the messages that appear on the search form, at every language, 'Search Results Page' for messages that appear on the results page etc...  

= Are there any other plugins that extend this plugin's functionality? =
There are some planned to be ready within the next few months (Rating, Hotels offers etc.), but they will not be free. They will be announced at <http://wp-easybooking.com>.

= How can I change the colours and the way the pages are displayed? =
Most of the colours can be changed from the 'eb_widgetStyle.css' file, but you have to be familiar with css. 
There will be support packs, with low costs, available soon at <http://wp-easybooking.com>, which you can purchase. 
If you do not find any pack that fits your needs I would be more than happy if you contact me at support@wp-easybooking.com.

= How can I get any help if I face any problem? =
Visit <http://wp-easybooking.com> for complete documentation. If you still have any problems or need extra functionality please contact me at support@wp-easybooking.com.

More FAQ at <http://wp-easybooking.com/content/21-faq>.


== Changelog ==
= 1.0.3 =
* Correction at "Bookings" menu to be visible, by changing the old "$current_user" to "get_userdata()".  

= 1.0.2=
* Currency Converter minor bug fix.

= 1.0.1 =
* Base plugin dir changed to wp-easybooking (from wp_easybooking)
* Enabled selection of default packages, in order the prices of rooms to be calculated correctly from the widget. 

= 1.0.0 =
* Fixed search result links to include date ranges and number of guests. 
* Added google map feature.

= 0.5 =
* Business man role added.
* Currency conversion added.

== Upgrade Notice ==

= 1.0 =
Version 0.5 and previous can not be downloaded any more or supported.

= 0.5 =
This version fixes a security related bug.  Upgrade immediately.