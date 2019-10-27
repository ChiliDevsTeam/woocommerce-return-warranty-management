=== Return and Warranty Management System for WooCommerce ===
Contributors: wpeasysoft
Tags: WooCommerce Return, RMA, Warranty Management, Product Return System, WooCommerce Product Warranty Management
Requires at least: 4.4
Tested up to: 5.2.4
WC requires at least: 3.0
WC tested up to: 3.7.0
Requires PHP: 5.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Return and Warranty Management (RMA) System for WooCommerce

== Description ==
WooCommerce Return and Warranty management system (RMA) plugin allows eCommerce storeowners to process return and warranty. The WooCommerce RMA plugin makes helps manage customers’ request for product return. It’s a simple, yet powerful tool for your WooCommerce workflow.

> [Demo](http://demo.wpeasysoft.com/rma/) | [Get Premium Version](https://wpeasysoft.com/downloads/woocommerce-return-warranty-management/)  |  [Support](https://wpeasysoft.com/account/tickets/)

With an easy-to-use interface, an Admin can change warranty options endlessly, or close all return and warranty requests at once. eCommerce storeowners have to deal with returns and refunds on a daily basis. Customers are likely to return a product they purchased for refund or request you to replace the original product.

Then, you will have to replace or provide a refund for the returned product. When you are considering an eCommerce business model, you have to design your website including warranty, refund, or no warranty options.

There comes Return and Warranty management system. With this plugin, managing refund or replacement requests and processing warranty have never been easier!

= WooCommerce RMA Basic Features =

* Process warranties for your simple products
* Display warranty management details on the product single page
* Define warranty periods and terms for products individually or globally
* Define warranty cost for each product
* Replace and refund manually
* Feature Simple product for return and refund of the vast majority of your products
* Admin can create request form for cusomter using request form builder ( Availble fields: Text, Textarea, Select, Checkbox, HTML ) [**New**]
* Give your customers a way to Create New Request via request form which is created by admin
* Add notes for Admin on each request

= WooCommerce RMA [Premium](https://wpeasysoft.com/downloads/woocommerce-return-warranty-management/) Features =

* Set Variable product warranty for different variations
* Instant refund from return requests. Admin will be able to process refund for your products instantly from request table.
* Refund as a **Store Credits**. Admin can send coupon to the customer in their billing email equivalent to request amount in exchange of their previous purchase.
* Store Admin can message smoothly with your customers regarding products replacement or refund.
* Add some extra fields for customer request form builder ( Like: Image uplaod field, Number, Multiselect, Multicheckbox, HTML ) [**New**]

*Check the [Premium](https://wpeasysoft.com/downloads/woocommerce-return-warranty-management/) features in short video*

[youtube https://www.youtube.com/watch?v=shKx__iLVwg]

= How will WooCommerce Return and Warranty benefit you? =

* You’ll be able to manage warranty and return system in your WooCommerce shop.
* WooCommerce Return and Warranty allows you to manage WooCommerce warranties easily while maintaining your company’s return policy and standards.
* You will be able to process warranty requests for your products both individually or all at once.
* Setting warranty period, value, and duration is super easily
* Customer will easily be able to request and manage refund/replacement from their account.
* You can create a custom warranty statuses for convenience using filters.
* Status will help you track progress of the request.
* Finally, this plugin will contribute to making your eCommerce store a successful one!

= How Does This Plugin Work? =

After activating your plugin, you will notice a sub-menu named “Return Request” in your dashboard. This sub-menu expands into two pages “Requests” and “Settings”. The Settings has three tab options – General, Default Warranty, and Frontend.

On the General tab in the Settings page, there are two groups – Order Status to Allow Warranty Request and Returned Status. “Order Status to Allow Warranty Request” allows you to set a condition, for which your customer will be eligible to make a warranty request. “Returned Status” shows the status of a new request. New requests will be termed “New”.

On to the Default Warranty tab, you can set Label, Type, and Add-On Warranty. Label carries the name to show in place of Warranty. Type has three options – No Warranty, Included Warranty, and Price base warranty.

= Contribute =
If you find bugs, plase make issues on [Github](https://github.com/wpeasysoft/woocommerce-return-warranty-management). Any pull requests are welcomed.

= Author =
Created by [wpeasysoft](https://wpeasysoft.com)

== Installation ==

The installation of WooCommerce Return and Warranty is very simple. If you have previously installed any WordPress plugin, installing this will be the same as well! Yes, WooCommerce Return and Warranty is a WooCommerce extension, so you need to install and activate WooCommerce on your site.
Once you have installed and activated WooCommerce in your system, now install this plugin and activate it. If you do not have WooCommerce installed on your system, this plugin won’t work and will show an error message.

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

== Frequently Asked Questions ==

= Q. From which page the customer can send a return request? =
A.  First, the customer needs to go to the My Account page. There, he’ll see an “Order Listing” option. From the Order Listing page, the customer can send a return request.

= Q. Will the customers receive email notification? =
A. Yes, they will. After the admin updates the return request, the customer will get an email notification.

= Q. Can the admin add price-based warranty to the product? =
A. Yes, he can. For this, the admin needs to set the warranty type to Price Base Warranty”. Under this setting, the admin can easily add price-based warranties to the products.

== Screenshots ==
1. Main Settings
2. Default Warranty Settings
3. Frontend Settings
4. Individual Product settings
5. Admin request page lising
6. Admin request details view
7. Customer request order list
8. Customer request page
9. Customer request details view

== Changelog ==

v1.1.4 -> October 27, 2019
--------------------------------------------
- [new]   Admin can set multiple email in new request email template
- [new]   Added request type column in request lists

v1.1.3 -> October 02, 2019
--------------------------------------------
- [new]   Added shortcode supports `[warranty-requests per_page=20 order_by='id', order='desc']` for showing warranties
- [fix]   Fix field rendering issue in request warranty

v1.1.2 -> August 26, 2019
--------------------------------------------
- [fix]   Same filed duplication issue in request form builder
- [fix]   Form builder settings not rendering appropriately for some fileds
- [fix]   Compatibility with WC v3.7.0

v1.1.1 -> August 19, 2019
--------------------------------------------
- [fix]   Transaltions issues for mismatch plugin slug
- [fix]   Added some core hooks for extending functionalities
- [fix]   Request listing ordering(ASC/DESC) issues in admin all request page
- [fix]   Status filter broken link in all requests page
- [tweak] Remove unwanted logger codes

v1.1.0 -> July 30, 2019
--------------------------------------------
- [new]   Added request form builder for admin. When admin can create request form for customers
- [fix]   Admin notes table name issue
- [fix]   Change textdomain `wc-return-warranty-management` to `wc-return-warranty`
- [fix]   Warranty create issues for extra meta key values
- [tweak] Added admin menu filter and scripts loaded
- [tweak] Change grunt support to npm webpack mix

v1.0.2 -> June 14, 2019
--------------------------------------------
- [fix] Product warranty data not saving if admin want to override default warranty data
- [fix] Remove unnessary order status for creating return request in admin settings
- [fix] Add some missing textdomain
- [tweak] Refactor some codes

v1.0.1 -> June 1, 2019
--------------------------------------------
- [fix]   Default warranty value showing error when install first time and not save any data fixed
- [fix]   Added some filter and hooks for extending return types
- [fix]   Item price not showing accurately in single warranty request page
- [tweak] Change Return and Request menu position

= v1.0.0 -> 22 April, 2019 =
Initial version released

== Upgrade Notice ==
No upgrade notice
