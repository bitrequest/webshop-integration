### **Bitrequest Webshop Integration**

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
*   **Customizable**: Add contact forms, metadata, and handle backend updates seamlessly.

### **Requirements**
*   A modern browser with JavaScript enabled.
*   Access to a web server for hosting your shop (static sites like GitHub Pages work fine).
*   For Lightning: A self-hosted proxy server connected to your node (see below).
*   No server-side changes are needed for the basic setup, but a backend (e.g., PHP) is recommended for transaction handling.

### **Quick Start Guide**

**1. Add Required Resources**

Include the stylesheet and script in your HTML `<head>` or before the closing `</body>` tag (`async`/`defer` are fine):

```html
<link rel="stylesheet" href="https://bitrequest.github.io/assets_styles_lib_bitrequest.css"/>
<script src="https://bitrequest.github.io/assets_js_lib_bitrequest_checkout.js"></script>
```

**2. Generate Request Link**

Use the [Request URL Generator](https://www.bitrequest.io/request-url/) for help building URLs. All options are listed under [Request parameters](#request-parameters).

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
```

**3. Add Checkout Button**

Give a link the `br_checkout` class:
```html
<a href="https://bitrequest.github.io/?payment=..." class="br_checkout">Check out</a>
```
Clicking it opens the payment popup.

**4. Handle Transaction Callbacks**

When the checkout detects a transaction, it sends the data to your page and calls `result_callback`. This happens on every status change:
*   `pending`: transaction seen, waiting for the confirmations set with `c`.
*   `paid`: payment complete.
*   `insufficient`: amount too low; the customer can still top up.

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

Define `result_callback` anywhere on your page (load order doesn't matter):
```javascript
function result_callback(post_data) {
    // update your page, notify your backend
    if (post_data.txdata.status === "paid") {
        setTimeout(closeframe, 2500); // close the payment popup
    }
}
```
Close the popup on `paid` only, so it keeps watching while a payment is `pending` and the customer can top up when it is `insufficient`.

Before you go live, read [SECURITY.md](SECURITY.md) on how to verify payments.

### **Notification Webhook (optional)**

[`webhook/index.php`](webhook/index.php) e-mails you each payment update that [`index.html`](index.html) posts to it.

1.  Copy `webhook/` to your PHP server.
2.  Set `RECIPIENT` and `SENDER` (an address on your own domain) at the top of `webhook/index.php`.
3.  Post results to it as JSON, see `notify_backend()` in [`index.html`](index.html).
4.  Shop on a different domain than the webhook (e.g. GitHub Pages)? Add the shop's origin to `ALLOWED_ORIGINS`, e.g. `["https://yourshop.github.io"]`.

Test from a served page, not a local file: requests from `file://` pages are refused (the preflight fails with a 405).

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

**Server-side callbacks (optional)**

Your proxy can also post invoice updates straight to the webhook:

1.  Set `CALLBACK_TOKEN` in `webhook/index.php` to 32+ random characters (e.g. `openssl rand -hex 32`).
2.  In the proxy's `config.php`, set `"callback_url" => "https://yourshop.com/webhook/?token=<CALLBACK_TOKEN>"`.

### **Monero**

To detect Monero payments, add your private viewkey as `vk` in `d`. Without it the payment isn't detected. Read [SECURITY.md](SECURITY.md#monero-viewkey) before you do.

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
| `imp`, `proxy`, `pw` | Lightning, see [Lightning](#lightning-network-support). |

### **Resources**
*   [Live Demo](https://www.bitrequest.io/brewery/)
*   [Request URL Generator](https://www.bitrequest.io/request-url/)
*   [Proxy Setup Guide](https://github.com/bitrequest/bitrequest.github.io/tree/master/proxy)
*   [Main Bitrequest Repo](https://github.com/bitrequest/bitrequest.github.io)