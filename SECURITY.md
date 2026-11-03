### **Security**

> What the integration protects against, and what is still up to you

Setup is covered in the [README](README.md). This file covers how to verify payments, what each option exposes, and how to report a vulnerability.

### **The callback is not proof of payment**

Bitrequest is non-custodial: the checkout watches the blockchain from your customer's browser. The result callback is a signal from that browser, and anyone can trigger it with made-up data (the browser console is enough).

**Before you ship an order, confirm the payment in your wallet**, or verify it against your own node:
*   The transaction exists.
*   It went to your receiving address.
*   The amount is right. Unless the request has `exact`, the checkout accepts up to 3% less.
*   It has enough confirmations.

For a small shop, checking your wallet before fulfilling is a perfectly good way to run. Use the callback to update the page and to notify yourself, never to mark an order as paid.

### **Checkout script**

*   The payment popup runs in a sandboxed iframe.
*   The script validates the URL of each `.br_checkout` link before opening it.
*   Messages from the popup are only accepted from the app's exact origin.

### **Notification webhook**

[`webhook/index.php`](webhook/index.php) is a notification, not verification. Every mail sent from the browser is marked `UNVERIFIED`.

Built-in abuse protection:
*   `POST` with a JSON body only, from your own origin (or `ALLOWED_ORIGINS`) only. Other websites can't make visitors' browsers post to it: JSON requires a CORS preflight, which the webhook only answers for allowed origins, and the `Origin` header is checked.
*   `Origin: null` is always refused, because any website can produce it. This is why a local `file://` page can't post to it.
*   16 KB body limit. Only known fields are read, and they are mailed as plain text, so no HTML or header injection.
*   The same notification is mailed only once.
*   Unverified notifications are capped at 20 per hour (`MAX_MAILS_PER_HOUR`). Someone can use up that cap to suppress unverified mails for an hour, which is one more reason your wallet is the source of truth.

### **Lightning**

**Proxy API key (`pw`)**

`pw` ends up in your page source, so every visitor can read it. It keeps casual traffic off your proxy; don't reuse it as a secret anywhere else.

**Server-side callbacks**

Unlike the browser callback, a proxy callback comes from your own node. Token-checked callbacks (`CALLBACK_TOKEN`) are not marked unverified and are exempt from the hourly cap. Still:
*   The invoice amount is requested by the checkout in the customer's browser, so check `amount_paid` (msat) against the order.
*   The proxy reports a paid invoice when its status is checked, which the checkout does while it is open. Don't treat a missing callback as "not paid".

### **Monero viewkey**

To detect Monero payments, the checkout needs your private viewkey (`vk` in `d`). The viewkey is then readable in your page source and reveals every incoming payment to that address (it can't spend), so use a dedicated account for your shop.

### **Content Security Policy**

If your shop sends a CSP, allow the script, the stylesheet and the iframe, plus `connect-src 'self'` for the webhook:
```
script-src 'self' https://bitrequest.github.io; style-src 'self' https://bitrequest.github.io; frame-src https://bitrequest.github.io; connect-src 'self'
```
Inline scripts like the example in `index.html` also need a nonce or hash.

### **Self-hosting the script**

To avoid loading a third-party script on your checkout page, copy `assets_js_lib_bitrequest_checkout.js` and `assets_styles_lib_bitrequest.css` to your own server and tell the script where the app lives:
```html
<link rel="stylesheet" href="/js/assets_styles_lib_bitrequest.css"/>
<script src="/js/assets_js_lib_bitrequest_checkout.js" data-origin="https://bitrequest.github.io"></script>
```
Without `data-origin`, the script assumes the app is served from the same origin as the script (for self-hosted Bitrequest instances) and rejects `bitrequest.github.io` links. Updates to the script are then up to you.

### **Reporting a vulnerability**

Please don't report security issues in public GitHub issues. E-mail [security@bitrequest.io](mailto:security@bitrequest.io) instead, with:
*   What you found and where (file, URL or component).
*   Steps to reproduce, or a proof of concept.
*   The impact as you see it.

You'll get a reply once the report has been reviewed. Please keep the issue private until a fix is released, so shops using the integration have time to update.