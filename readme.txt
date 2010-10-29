=== DukaPress ===
Contributors: parshwa, moshthepitt
Donate link: http://dukapress.org/about/
Tags: shopping cart, web shop, cart, shop, Worldpay, Paypal, Alertpay, paypal, e-commerce, ecommerce, MPESA, ZAP, yuCash, Mobile Payments,online duka, duka, online shop, JQZoom, Multiple Currencies

Requires at least: 2.9.2
Tested up to: 3.0.1
Stable tag: 1.3.2.1

DukaPress is an open source e-commerce solution built for Wordpress.

== Description ==

DukaPress is open source software that can be used to build online shops quickly and easily. DukaPress is built on top of Wordpress, a world class content management system. DukaPress is built to be both simple and elegant yet powerful and scalable.

Main Features:

* You can sell tangible regular products;
* You can sell tangible products with selectable options (size, colour, etc);
* You can sell Digital products;
* Choose between a normal shop mode and a catalogue mode;
* Numerous payment processing options including Paypal, Alertpay and Mobile Phone payments;
* Ability to work with multiple currencies
* Printable invoices;
* One-page checkout;
* Elegant discount coupon management;
* A myriad of shipping processing options;
* Printable invoices;
* Custom GUI (Graphical User Interface) for product management;

View more features: [DukaPress](http://dukapress.org/ "Your favorite e-commerce software")

DukaPress Documentation: [DukaPress Documentation](http://dukapress.org/docs/ "Your favorite e-commerce software documentation")

View a DukaPress Demo Shop: [DukaPress Demo](http://dukapress.org/demo/ "Your favorite e-commerce software")

Due to public demand, you can get an absolutely free DukaPress hosted (just sign up and start selling in five minutes, no fuss) shop here: [Our Duka](http://ourduka.com/ "Get a free online shop")

== Installation ==

1. Upload the DukaPress folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Make sure your web host allows scripts like timthum.php to run on your site.  If they don't, your DukaPress images will be broken.
1. After this, visit the [DukaPress documentation](http://dukapress.org/docs/ "DukaPress documentation")


== Frequently Asked Questions ==

= What does DukaPress mean? =

'Duka' is the Swahili word for shop.  Loosely, DukaPress means "shoppress".  We like to think it means the most complete and usable e-commerce solution for wordpress, though.

= What don't the images work? =

Please read this first: [We live in a bad world](http://dukapress.org/blog/2010/09/23/we-live-in-a-bad-world/ "Why images don't work").

Unfortunately, for security reasons, web hosts sometimes disable timthumb from working.  This is the script that handles images in DukaPress.  To fix this, kindly ask your webhost to allow timthumb to work.  All the good webhosts will do this for you in two minutes!

[Look here for hosts that we know work with DukaPress.](http://dukapress.org/download/)

= Why is the make payment button not working? =

Nine out of ten times, this is because there is a javascript error somewhere on your site.  The first place to look is your theme - try and run DukaPress using the default WordPress theme to confirm if it is your theme that is failing you.

= Why doesn't DukaPress work for me?  It seems to work for everyone else =

No.  Nothing is wrong with you. :)

We test DukaPress on a large number of different server set-ups and envrionments and we are satisfied that it does work in these environments.  However, the number of different environments 'out there' is infinite and we cannot possibly test on every single environment.  If everything that you try fails to work, perharps you should move your site to one of the more common web hosts?  [Look here for hosts that we know work with DukaPress.](http://dukapress.org/download/)


= Why isn't the Grid Display working? =

You currently HAVE to have at least one custom field per prodct in order for those products to show up in the grid display properly.

= Why isn't the "Inquiry"/"Catalogue mode" working properly? =

Right now, it works very similarly to the regular shop mode - i.e. when people click on the "Inquire" button, it adds the product(s) to the shopping cart.  When the site vistors go to checkout, they will then be presented with a form which they fill to inquire about products in the cart.  You therefore HAVE to have your shopping cart widget displaying somewhere for the "Inquire" button to work.


= Why is nothing happening when I press "Add to cart"?  Why is the AJAX not working? =

The cart should be inside DIV with class="dpsc-shopping-cart".

Just put the cart code inside DIV tags to look like:
div class="dpsc-shopping-cart">cart code</div


== Screenshots ==

[View Screenshots](http://www.flickr.com/photos/moshthepitt/sets/72157624534741496/ "DukaPress screenshots")

== Changelog ==

= 1.3.2.1 =
* A quick bug fix release for a bug that affected the WordPress post edit screen.

= 1.3.2 =
* A quick bug fix release. This fix removes the hard coded image dimensions in the dukapress.js file.

= 1.3.1 =
* A quick bug fix release to fix a JQuery bug in 1.3.0

= 1.3.0 =
* Crushed even more annoying little bugs
* Improved DukaPress UI
* Made it possible to define the sizes of images displayed by DukaPress
* Improved the mobile payment processor to be able to handle any system in the world
* Enabled DukaPress to work with multiple currencies
* Added JQZoom suuport
* Added a simple way to notify customers that something was added to the cart


= 1.2.1 =
* Made the currency symbol viewable on grid view and single product pages
* Crushed a lot of annoying little bugs


= 1.2.0 =
* Added support for custom post types
* Added GUI to product management so that one does not have to use custom fields
* Fixed a bug whereby one could not update the number of itens in the cart on the checkout page
* Fixed some wordings on the emails that DukaPress sends out

= 1.0.1 =
* Added pagination to Grid View
* Fixed a bug affecting stock/inventory management- buyers now cannot buy out of stock items
* A bit of the code is now modularised

= 1.0 =
* Initial Release

== Upgrade Notice ==

= 1.3.2.1 =
Nothin' to see here, bug crushing.

= 1.3.2 =
Some bugs crushed.  Aren't you glad we're takign care of you?  Keep 'em bugs away, I say.

= 1.3.1 =
A quick bug fix release to fix a JQuery bug in 1.3.0

= 1.3.0 =
Moving along swiftly on our quest to be the best WordPress e-commerce tool.  This version not only fixes pesky bugs, it adds a ton of new features and improvements!

= 1.2.1 =
Just a nice little bug fix release!

= 1.2.0 =
If you did not love us before today, then you simply must have a look at our offerings!  Intorducing a custom product management GUI and support for custom post types.

= 1.0.1 =
DukaPress just got better!  We fixed some bugs and added one new feature. Yay!

= 1.0 =
DukaPress is brand new.  As such you won't be upgrading but joining our handsomely awesome family.