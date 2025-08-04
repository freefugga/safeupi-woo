=== SafeUPi for WooCommerce ===
Contributors: safeupi  
Tags: upi, woocommerce, qr code, saas, api, redirect, payment gateway, india, checkout blocks  
Requires at least: 5.8  
Tested up to: 6.5  
Requires PHP: 7.4  
Stable tag: 1.0.0  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Accept UPI payments in WooCommerce using SafeUPi’s SaaS service. Customers are redirected to a secure payment page with a dynamic QR code and then returned after payment. Works with Checkout Blocks.

== Description ==

**SafeUPi for WooCommerce** integrates your store with [SafeUPi.com](https://safeupi.com) — a SaaS platform that enables secure **UPI payments via redirection**. 

When a customer selects SafeUPi as the payment method at checkout, the plugin:
- Creates a payment order via SafeUPi API
- Redirects the customer to SafeUPi's hosted payment page (with dynamic UPI QR code)
- After payment, SafeUPi redirects the customer back to your WooCommerce store
- Order status is updated automatically via webhook

> 🔒 Secure & fast payments via UPI  
> 🔄 Fully supports WooCommerce Checkout Blocks  
> 💼 No per-transaction fee — works with SafeUPi’s subscription model  

== Features ==

- Redirects customers to a secure UPI payment page hosted by SafeUPi
- Dynamic QR code generation via API
- Secure webhook handling with secret validation
- Admin settings for API Token, Webhook Secret, and environment
- Displays return link or automatic redirect after payment
- Fully compatible with classic and block-based checkout flows

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/safeupi-woocommerce` directory, or install via the WordPress plugin installer.
2. Activate the plugin through the 'Plugins' screen.
3. Go to `WooCommerce → Settings → Payments → SafeUPi`.
4. Copy the **Webhook URL** displayed there and paste it into your [SafeUPi Dashboard](https://safeupi.com).
5. Enter your **API Token** and **Webhook Secret** (also from your SafeUPi account).
6. Save changes and enable the payment method.

== Frequently Asked Questions ==

= Is this a payment gateway? =  
Yes & No, in a sense — SafeUPi acts like a lightweight UPI payment gateway using redirection, but we do not receive any kind of payment. All the payments are routed to your connected merchant provider with SafeUPi.

= Do I need to generate QR codes manually? =  
No. The plugin redirects users to a SafeUPi-hosted page where the QR is generated dynamically.

= Is it free to use? =  
The plugin is free, but SafeUPi is a **subscription-based service**. There are no per-transaction charges.

= Does it work with Google Pay, PhonePe, Paytm, etc.? =  
Yes. SafeUPi generates QR codes that work with all UPI-enabled apps.

= Does it support WooCommerce Checkout Blocks? =  
Yes, it's fully compatible.

== Screenshots ==

1. Admin panel to enter API Token and Webhook Secret
2. Checkout page showing SafeUPi selected as payment method
3. Redirected SafeUPi payment page with dynamic QR code
4. Successful return to the WooCommerce thank you page

== Changelog ==

= 1.0.0 =
* Initial release
* Order creation and redirection to SafeUPi payment page
* Webhook handling and auto order status update
* Checkout Blocks compatible

== Upgrade Notice ==

= 1.0.0 =
Initial release. Connect your store to SafeUPi and accept secure UPI payments via redirection flow.

== License ==

This plugin is licensed under the **GPLv2 or later**.  
See [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html) for details.
