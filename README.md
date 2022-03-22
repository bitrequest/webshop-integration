# Webshop integration (Beta)  
Live demo: [https://www.bitrequest.io/brewery/](https://www.bitrequest.io/brewery/)

### Steps
#### 1. Add the following resources to your html.
    <link rel="stylesheet" href="https://bitrequest.github.io/assets_styles_lib_bitrequest.css"/>
    <script src="https://bitrequest.github.io/assets_js_lib_jquery-3.6.0.min.js"></script>
    <script src="https://bitrequest.github.io/assets_js_lib_bitrequest_checkout.js"></script>

You can skip the jquery file if you already use jquery on your site.  
Or you can use your own jquery reference instead.

#### 2. Generate a request link (example).  
For assistance visit [https://www.bitrequest.io/request-url/](https://www.bitrequest.io/request-url/)  

    var payment = "nano";
    var uoa = "usd";
    var amount = 0.05;
    var address = "nano_13kx8gfrse6q6z6djznkhj83cshih8akmjr3e3wmoabb38p6immo4mho8359";
    var d = btoa(JSON.stringify({
        "t": "Example request title",
        "n": "Example request name",
        "c": 0,
        "pid": "paymentid"
    }));
    // optional: set &contactform=true to show contact/shipping form
    var request_url = "https://bitrequest.github.io/?payment=" + payment + "&uoa=" + uoa + "&amount=" + amount + "&address=" + address + "&d=" + d;

#### 3. Checkout link.  
Every link with class .br_checkout will trigger the payment request popup. Make sure the href attribute has the correctly formatted request link.

    <a href="{$request_url}" class="br_checkout">Check out</a>

#### 4. Callback functions and post data.  
When the websocket in the bitrequest App detects an incoming transaction, the transaction data will be posted to the parent of the iframe.

**post data:**

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

#### 5. Update the backend / frontend.  
You can catch the postdata with the following callback function and update your backend and/or frontend accordingly:

    function result_callback(post_data) {
        console.log(post_data);
    }

There's an example backend php template included in this repository.

## Lightning requests

For lightning request you will need to setup a [proxy server](https://github.com/bitrequest/bitrequest.github.io/tree/master/proxy) and connect to your lightning node.

Add the following key / values to your data parameter:

    var payment = "bitcoin";
    var uoa = "usd";
    var amount = 0.05;
    var address = "lnurl" (lightning only) or a btc-address (hybrid);
    var d = btoa(JSON.stringify({
        "t": "Example request title",
        "n": "Example request name",
        "c": 1,
        "pid": {$random payment-id}} (required),
        "imp": "lnd" / "eclair" / "c-lightning" / "lnbits" (required),
	    "proxy": {$proxy host} (url or lnurl) (required),
	    "pw": {$your proxy api key} (optional)
    }));

You can set a callback url in the [proxy config file](https://github.com/bitrequest/bitrequest.github.io/tree/master/proxy) to receive updates on your back-end.  

There's an example backend php template included in this repository.