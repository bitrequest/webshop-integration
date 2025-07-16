### **Bitrequest Webshop Integration 🛍️**

> A simple way to integrate cryptocurrency payments into your webshop

[![Live Demo](https://img.shields.io/badge/demo-live-success)](https://www.bitrequest.io/brewery/)
[![GitHub stars](https://img.shields.io/github/stars/bitrequest/webshop-integration)](https://github.com/bitrequest/webshop-integration/stargazers)
[![License](https://img.shields.io/github/license/bitrequest/bitrequest.github.io)](https://github.com/bitrequest/bitrequest.github.io/blob/master/LICENSE)
[![Last Updated](https://img.shields.io/badge/last%20updated-July%202025-blue)](https://github.com/bitrequest/webshop-integration)

### **Features**
*   **Easy Integration**: Embed a simple script and CSS to enable crypto payment popups in your webshop.
*   **Multi-Currency Support**: Handles Bitcoin, Lightning, Nano, Litecoin, Dogecoin, Dash, Ethereum + ERC20/L2, Bitcoin-cash, Monero, Nimiq, and Kaspa.
*   **Secure & Lightweight**: Uses iframes with sandboxing, origin checks for postMessage, and minimal dependencies.
*   **Callbacks for Transactions**: Receive real-time updates on payments via JavaScript callbacks.
*   **Lightning Network Ready**: Proxy support for LN payments with popular implementations (LND, LNbits, etc.).
*   **Customizable**: Add contact forms, metadata, and handle backend updates seamlessly.

### **Requirements**
*   A modern browser with JavaScript enabled.
*   Access to a web server for hosting your shop (static sites like GitHub Pages work fine).
*   For Lightning: A self-hosted proxy server connected to your node (see below).
*   No server-side changes are needed for the basic setup, but a backend (e.g., PHP) is recommended for transaction handling.

### **Quick Start Guide**

**1. Add Required Resources**

Include the stylesheet and script in your HTML `<head>` or before the closing `</body>` tag:

```html
<link rel="stylesheet" href="https://bitrequest.github.io/assets_styles_lib_bitrequest.css"/>
<script src="https://bitrequest.github.io/assets_js_lib_bitrequest_checkout.js"></script>
```

**2. Generate Request Link**

Use the [Request URL Generator](https://www.bitrequest.io/request-url/) for help building URLs.

Example JavaScript to create a request URL:
```javascript
const payment = "nano";
const uoa = "usd";
const amount = 0.05;
const address = "nano_3ag4rxc33ok53no7rimms94u19duqooctrdgnxmztzhdh6aoms6kknr7h8fb"; // bitrequest representative address as a placeholder, change to your own address
const d = btoa(JSON.stringify({
    "t": "Example request title",
    "n": "Example request name",
    "c": 0,
    "pid": "paymentid"
}));

// Create request URL (optional: add &contactform=true for shipping details)
const request_url = "https://bitrequest.github.io/?payment=" + payment + "&uoa=" + uoa + "&amount=" + amount + "&address=" + address + "&d=" + d;
```

**3. Add Checkout Button**

Trigger the payment popup with a link or button:
```html
<a href="${requestUrl}" class="br_checkout">Check out</a>
```
The script will handle clicks on `.br_checkout` elements, preventing default behavior and loading the iframe securely.

**4. Handle Transaction Callbacks**

When a transaction is detected via WebSocket in the Bitrequest app, data is posted to the parent window via `postMessage`. The script includes a secure handler with origin checks.

**Example Post Data** (JSON payload):
```json
{
  "id": "result",
  "data": {
    "txdata": {
      "currencyname": "United States Dollar",
      "requestid": "1826050966",
      "cmcid": "1567",
      "payment": "nano",
      "ccsymbol": "nano",
      "iscrypto": "true",
      "amount": "0.026",
      "receivedamount": "0.026",
      "receivedcc": "0.026",
      "status": "paid",
      "txhash": "txhash",
      "receiver": "nano_3ag4rxc33ok53no7rimms94u19duqooctrdgnxmztzhdh6aoms6kknr7h8fb",
      "confirmations": "false",
      "transactiontime": "1588660654471",
      "pending": "polling"
    },
    "data": {
      "t": "Example request title",
      "n": "Example request name",
      "c": 0,
      "pid": "paymentid"
    },
    "meta": null
  }
}
```

**5. Implement Callback Function (Update the backend / frontend).**

Handle the transaction data with a callback:
```javascript
function result_callback(post_data) {
    console.log(post_data);
    // Add your custom handling logic here
}
```
> 💡 See the [example PHP backend template](https://github.com/bitrequest/webshop-integration/tree/master/webhook) in this repo for server-side handling.

### **Lightning Network Support ⚡**

For Lightning payments, set up a [proxy server](https://github.com/bitrequest/bitrequest.github.io/tree/master/proxy) connected to your LN node (e.g., LND, LNbits).

**Lightning Configuration**

Add LN-specific params to your data object:
```javascript
const payment = "bitcoin";
const uoa = "usd";
const amount = 0.05;
const address = "lnurl";  // or btc-address for hybrid
const d = btoa(JSON.stringify({
    "t": "Example request title",
    "n": "Example request name",
    "c": 1,
    "pid": "{$random payment-id}",  // required
    "imp": "lnd",  // or "c-lightning" / "lnbits"
    "proxy": "{$proxy host}",  // url or lnurl (required)
    "pw": "{$your proxy api key}"  // optional
}));
```

> ⚠️ Configure your callback URL in the [proxy config file](https://github.com/bitrequest/bitrequest.github.io/tree/master/proxy) to receive backend updates.

### **Resources**
*   [Live Demo](https://www.bitrequest.io/brewery/)
*   [Request URL Generator](https://www.bitrequest.io/request-url/)
*   [Proxy Setup Guide](https://github.com/bitrequest/bitrequest.github.io/tree/master/proxy)
*   [Main Bitrequest Repo](https://github.com/bitrequest/bitrequest.github.io)