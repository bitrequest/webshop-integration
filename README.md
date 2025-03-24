# Bitrequest Webshop Integration 🛍️

> A simple way to integrate cryptocurrency payments into your webshop

[![Live Demo](https://img.shields.io/badge/demo-live-success)](https://www.bitrequest.io/brewery/)

## Quick Start Guide

### 1. Add Required Resources

Add these lines to your HTML file:
```html
<link rel="stylesheet" href="https://bitrequest.github.io/assets_styles_lib_bitrequest.css"/>
<script src="https://bitrequest.github.io/assets_js_lib_bitrequest_checkout.js"></script>
```

### 2. Generate Request Link

For assistance visit [https://www.bitrequest.io/request-url/](https://www.bitrequest.io/request-url/)

Example implementation:
```javascript
const payment = "nano";
const uoa = "usd";
const amount = 0.05;
const address = "nano_13kx8gfrse6q6z6djznkhj83cshih8akmjr3e3wmoabb38p6immo4mho8359";
const d = btoa(JSON.stringify({
    "t": "Example request title",
    "n": "Example request name",
    "c": 0,
    "pid": "paymentid"
}));

// Create request URL (optional: add &contactform=true for shipping details)
const request_url = "https://bitrequest.github.io/?payment=" + payment + "&uoa=" + uoa + "&amount=" + amount + "&address=" + address + "&d=" + d;
```

### 3. Add Checkout Button

Add this HTML to trigger the payment request popup:
```html
<a href="{$request_url}" class="br_checkout">Check out</a>
```

#### 4. Callback functions and post data.  
When the websocket in the bitrequest App detects an incoming transaction, the transaction data will be posted to the parent of the iframe.

**post data:**

```json
{
    "result": {
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
            "receiver": "nano_13kx8gfrse6q6z6djznkhj83cshih8akmjr3e3wmoabb38p6immo4mho8359",
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

### 5. Implement Callback Function (Update the backend / frontend).

Handle the transaction data with a callback:
```javascript
function result_callback(post_data) {
    console.log(post_data);
    // Add your custom handling logic here
}
```

> 💡 Check the repository for an example PHP backend template.

## Lightning Network Support ⚡

To handle Lightning Network payments, you'll need to [set up a proxy server](https://github.com/bitrequest/bitrequest.github.io/tree/master/proxy) connected to your Lightning node.

### Lightning Configuration

Add these parameters to your data object:
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
    "imp": "lnd",  // or "eclair" / "c-lightning" / "lnbits"
    "proxy": "{$proxy host}",  // url or lnurl (required)
    "pw": "{$your proxy api key}"  // optional
}));
```

> ⚠️ Configure your callback URL in the [proxy config file](https://github.com/bitrequest/bitrequest.github.io/tree/master/proxy) for backend updates.

## Resources
- [Live Demo](https://www.bitrequest.io/brewery/)
- [Request URL Generator](https://www.bitrequest.io/request-url/)
- [Proxy Setup Guide](https://github.com/bitrequest/bitrequest.github.io/tree/master/proxy)