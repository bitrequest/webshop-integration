### **Bitrequest Webshop Integration 🛍️**

> A simple way to integrate cryptocurrency payments into your webshop

[![Live Demo](https://img.shields.io/badge/demo-live-success)](https://www.bitrequest.io/brewery/)
[![GitHub stars](https://img.shields.io/github/stars/bitrequest/webshop-integration)](https://github.com/bitrequest/webshop-integration/stargazers)
[![License](https://img.shields.io/github/license/bitrequest/webshop-integration)](https://github.com/bitrequest/webshop-integration/blob/master/LICENSE)
[![Last commit](https://img.shields.io/github/last-commit/bitrequest/webshop-integration)](https://github.com/bitrequest/webshop-integration/commits/master)

### **Features**
*   **Easy Integration**: Embed a simple script and CSS to enable crypto payment popups in your webshop.
*   **Multi-Currency Support**: Handles Bitcoin, Lightning, Nano, Litecoin, Dogecoin, Dash, Ethereum + ERC20/L2, Bitcoin-cash, Monero, Nimiq, and Kaspa.
*   **Secure & Lightweight**: Sandboxed iframe, exact origin checks for postMessage, no dependencies.
*   **Callbacks for Transactions**: Receive real-time updates on payments via JavaScript callbacks.
*   **Lightning Network Ready**: Proxy support for LN payments with popular implementations (LND, Core-lightning, LNbits, Spark, NWC).
*   **Customizable**: Add contact forms, metadata, and e-mail notifications.

### **⚠️ The callback is not proof of payment**
Bitrequest is non-custodial: the checkout watches the blockchain from your customer's browser. The result callback is a signal from that browser, and anyone can trigger it with made-up data (the browser console is enough).

**Before you ship an order, confirm the payment in your wallet** (transaction, receiving address, amount, confirmations), or verify it against your own node. For a small shop, checking your wallet before fulfilling is a perfectly good way to run. Use the callback to update the page and to notify yourself, never to mark an order as paid.

### **Requirements**
*   A modern browser with JavaScript enabled.
*   Any web server for your shop. Static sites like GitHub Pages work fine for the checkout itself.
*   Optional: PHP with `mail()` for the notification webhook.
*   For Lightning: a self-hosted proxy server connected to your node (see below).

### **Quick Start Guide**

**1. Add Required Resources**

Include the stylesheet and script in your HTML `<head>` or before the closing `</body>` tag (`async`/`defer` are fine):

```html
<link rel="stylesheet" href="https://bitrequest.github.io/assets_styles_lib_bitrequest.css"/>
<script src="https://bitrequest.github.io/assets_js_lib_bitrequest_checkout.js"></script>
```

**2. Generate Request Link**

Use the [Request URL Generator](https://www.bitrequest.io/request-url/) for help building URLs, and see [Request parameters](#request-parameters) below.

```javascript
const payment = "nano";
const uoa = "usd";
const amount = 0.05;
const address = "nano_3ag4rxc33ok53no7rimms94u19duqooctrdgnxmztzhdh6aoms6kknr7h8fb"; // bitrequest representative address as a placeholder, change to your own address
const d = b64encode_json({
    "t": "Example request title",
    "n": "Example request name",
    "c": 0,
    "pid": "order-1001" // your order id, unique per order
});

// Create request URL (optional: add &contactform=true for shipping details)
const request_url = "https://bitrequest.github.io/?payment=" + payment + "&uoa=" + uoa + "&amount=" + amount + "&address=" + address + "&d=" + d;

// UTF-8 safe, URL-safe base64. Plain btoa() throws on characters like € or 中.
function b64encode_json(obj) {
    const bytes = new TextEncoder().encode(JSON.stringify(obj));
    let binary = "";
    for (const byte of bytes) {
        binary += String.fromCharCode(byte);
    }
    return btoa(binary).replace(/\+/g, "-").replace(/\//g, "_");
}
```

**3. Add Checkout Button**

Trigger the payment popup with a link that has the `br_checkout` class:
```html
<a href="https://bitrequest.github.io/?payment=..." class="br_checkout">Check out</a>
```
The script handles clicks on `.br_checkout` elements (including clicks on icons or other elements inside them), validates the URL and opens it in a sandboxed iframe.

**4. Handle Transaction Callbacks**

When the checkout detects a transaction it posts the data to your page via `postMessage`. The script checks the message origin and calls `result_callback`. It is called on every status change: `pending` (transaction seen, waiting for the confirmations set with `c`), `paid` and `insufficient` (amount too low; the customer can still top up).

**Example Post Data**:
```json
{
  "txdata": {
    "currencyname": "United States Dollar",
    "requestid": "4b1d2c9e0f",
    "cmcid": 1567,
    "payment": "nano",
    "ccsymbol": "xno",
    "iscrypto": false,
    "amount": "0.05",
    "receivedamount": 0.05,
    "receivedcc": 0.026,
    "fiatvalue": 0.05,
    "status": "paid",
    "txhash": "txhash",
    "receiver": "nano_3ag4rxc33ok53no7rimms94u19duqooctrdgnxmztzhdh6aoms6kknr7h8fb",
    "confirmations": 0,
    "set_confirmations": 0,
    "transactiontime": 1588660654471,
    "pending": "polling",
    "lightning": false,
    "erc20": false,
    "ethereum_l2s": []
  },
  "data": {
    "t": "Example request title",
    "n": "Example request name",
    "c": 0,
    "pid": "order-1001"
  },
  "meta": null
}
```
*   `lightning` is an object with invoice details for Lightning payments.
*   `meta` holds your own `m` parameter, decoded.
*   With `contactform=true` a `contact` object is added: `name`, `address`, `zipcode`, `city`, `country`, `email`.
*   Value types can differ per coin, so cast them on your server.

**5. Implement Callback Function**

Define `result_callback` anywhere on your page. It overrides the placeholder in the script, whatever the load order:
```javascript
function result_callback(post_data) {
    // update your page, notify your backend
    if (post_data.txdata.status === "paid") {
        setTimeout(closeframe, 2500); // close the payment popup
    }
}
```
Only close the popup on `paid`. Closing it on `pending` stops the checkout before the payment is confirmed, and on `insufficient` it takes away the customer's chance to top up.

### **Notification Webhook (optional)**

[`webhook/index.php`](webhook/index.php) e-mails you each result that [`index.html`](index.html) posts to it. It is a **notification, not verification**: every mail from the browser is marked `UNVERIFIED`.

1.  Copy `webhook/` to your PHP server.
2.  Set `RECIPIENT` and `SENDER` (an address on your own domain) at the top of `webhook/index.php`.
3.  Post results to it as JSON, see `notify_backend()` in [`index.html`](index.html).
4.  Shop on a different domain than the webhook (e.g. GitHub Pages)? Add the shop's origin to `ALLOWED_ORIGINS`, e.g. `["https://yourshop.github.io"]`.

Test from a served page, not a local file: a `file://` page sends `Origin: null`, which is always refused (any website can produce it), so its preflight fails with a 405.

Built-in abuse protection:
*   `POST` with a JSON body only, from your own origin (or `ALLOWED_ORIGINS`) only. Other websites can't make visitors' browsers post to it: JSON requires a CORS preflight, which the webhook only answers for allowed origins, and the `Origin` header is checked.
*   16 KB body limit. Only known fields are read, and they are mailed as plain text, so no HTML or header injection.
*   The same notification is mailed only once.
*   Unverified notifications are capped at 20 per hour (`MAX_MAILS_PER_HOUR`). Someone can use up that cap to suppress unverified mails for an hour, which is one more reason your wallet is the source of truth.

### **Lightning Network Support ⚡**

For Lightning payments, set up a [proxy server](https://github.com/bitrequest/bitrequest.github.io/tree/master/proxy) connected to your LN node (LND, Core-lightning, LNbits, Spark, NWC).

**Lightning Configuration**

Add LN-specific params to your data object:
```javascript
const payment = "bitcoin";
const uoa = "usd";
const amount = 0.05;
const address = "lnurl";  // or btc-address for hybrid
const d = b64encode_json({
    "t": "Example request title",
    "n": "Example request name",
    "c": 1,
    "pid": "order-1001",  // required, unique per order
    "imp": "lnd",  // or "c-lightning" / "lnbits" / "spark" / "nwc"
    "proxy": "https://yourproxy.com",  // url or lnurl (required)
    "pw": "your proxy api key"  // optional
});
```

> ⚠️ `pw` ends up in your page source, so every visitor can read it. It keeps casual traffic off your proxy; don't reuse it as a secret anywhere else.

**Server-side callbacks**

Your proxy can post invoice updates straight to your server via `callback_url` in the [proxy config file](https://github.com/bitrequest/bitrequest.github.io/tree/master/proxy). Unlike the browser callback, this comes from your own node. To use it with the webhook:

1.  Set `CALLBACK_TOKEN` in `webhook/index.php` to 32+ random characters (e.g. `openssl rand -hex 32`).
2.  In the proxy's `config.php`, set `"callback_url" => "https://yourshop.com/webhook/?token=<CALLBACK_TOKEN>"`.

Token-checked callbacks are not marked unverified and are exempt from the hourly cap. The invoice amount is requested by the checkout in the customer's browser, so still check `amount_paid` (msat) against the order. The proxy reports a paid invoice when its status is checked, which the checkout does while it is open. Don't treat a missing callback as "not paid".

### **Monero**

To detect Monero payments, the checkout needs your private viewkey: add it as `vk` in `d`. Without it the payment isn't detected. The viewkey is then readable in your page source and reveals every incoming payment to that address (it can't spend), so use a dedicated account for your shop.

### **Request parameters**

**URL parameters**

| Parameter | Description |
| --- | --- |
| `payment` | Coin, e.g. `bitcoin`, `litecoin`, `nano`, `monero`, `ethereum`. Use the [generator](https://www.bitrequest.io/request-url/) for exact names, including ERC-20 tokens. |
| `uoa` | Unit of account: a fiat code (`usd`, `eur`, …) or the coin's symbol to price in crypto. |
| `amount` | Amount in `uoa`. |
| `address` | Your receiving address, or `lnurl` for Lightning only. |
| `d` | Request data, base64-encoded JSON (see below). |
| `m` | Optional metadata of your own, base64-encoded JSON. Returned as `meta` in the result. |
| `contactform` | `true` shows a contact/shipping form; the result then includes `contact`. |
| `exact` | When present, the amount must match exactly. By default the checkout accepts up to 3% less. |

**`d` keys**

| Key | Description |
| --- | --- |
| `t` | Request title. |
| `n` | Requester name, shown to the customer. |
| `c` | Confirmations required before `paid`. `0` or omitted: the app's default for that coin. |
| `pid` | Your order id, returned as `data.pid`. Unique per order; required for Lightning. |
| `l2` | ERC-20 on a layer 2: `[0]` Arbitrum One, `[1]` Polygon PoS, `[2]` BNB Smart Chain, `[3]` Base. |
| `vk` | Monero private viewkey, see [Monero](#monero). |
| `imp`, `proxy`, `pw` | Lightning, see [Lightning](#lightning-network-support-). |

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

### **Resources**
*   [Live Demo](https://www.bitrequest.io/brewery/)
*   [Request URL Generator](https://www.bitrequest.io/request-url/)
*   [Proxy Setup Guide](https://github.com/bitrequest/bitrequest.github.io/tree/master/proxy)
*   [Main Bitrequest Repo](https://github.com/bitrequest/bitrequest.github.io)
