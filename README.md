# SafeUPi for WooCommerce

[![License: GPL v2](https://img.shields.io/badge/License-GPL_v2-blue.svg)](LICENSE)

**SafeUPi for WooCommerce** is a WordPress plugin that connects your store with the [SafeUPi](https://safeupi.com) SaaS platform to accept secure UPI payments. It uses a redirection flow where customers are taken to a dynamic payment page hosted by SafeUPi, then redirected back to your store after payment.

✅ Supports WooCommerce Checkout Blocks  
🔐 Secure webhook integration  
💳 No per-transaction charges — subscription-based model

---

## 🚀 Features

- Redirects customers to SafeUPi’s secure UPI payment page
- Dynamic QR code generated for each WooCommerce order
- Webhook support for payment status updates
- Admin settings for API Token and Webhook Secret
- Shows webhook URL to copy to SafeUPi Dashboard
- Compatible with classic checkout and Checkout Blocks

---

## 🧩 How It Works

1. Customer selects **SafeUPi** at WooCommerce checkout
2. Plugin creates a payment order via SafeUPi API
3. Customer is redirected to the SafeUPi payment page (QR code shown)
4. After payment, customer is returned to your WooCommerce site
5. Plugin receives webhook and updates the order status

---

## 🔧 Installation

1. Clone or download this repository:
   ````bash
   git clone https://github.com/YOUR_USERNAME/safeupi-woocommerce.git ```
   ````
2. Zip the folder
   ````bash
   cd safeupi-woocommerce
   zip -r safeupi-woocommerce.zip .```
   ````
3. Upload the zip via WordPress Admin → Plugins → Add New → Upload.
4. Activate the plugin.
5. Go to WooCommerce → Settings → Payments → SafeUPi.
6. Copy the Webhook URL from the plugin and paste it in your SafeUPi dashboard.
7. Enter your API Token and Webhook Secret from SafeUPi and save.

---

## 🧪 Requirements

| Component   | Minimum Version |
| ----------- | --------------- |
| WordPress   | 5.8+            |
| WooCommerce | 7.0+            |
| PHP         | 7.4+            |

✅ Fully tested with WooCommerce Checkout Blocks

---

## 📄 License

This plugin is licensed under the GNU General Public License v2.0 or later.
See the LICENSE file for full details.

---

## 🛟 Support

For support, bug reports, or questions, visit [https://safeupi.com](https://safeupi.com) or open an issue in this repository.

---

## 🤝 Contributing

Contributions are welcome! Feel free to fork the repo, submit pull requests, or report issues.

---

## 🌐 About SafeUPi

SafeUPi is a UPI payment SaaS platform that helps merchants accept UPI payments without the hassle of integrating complex gateways. No transaction fees — just simple subscription plans.
