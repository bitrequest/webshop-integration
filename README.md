# Webshop integration  
Live demo: [https://www.bitrequest.io/webshop-prototype/](https://www.bitrequest.io/webshop-prototype/)

### Steps
#### 1. Add the following resources to you html.
    <link rel="stylesheet" href="https://app.bitrequest.io/assets/styles/lib/bitrequest.css"/>
    <script src="https://app.bitrequest.io/assets/js/lib/jquery-3.3.1.min.js"></script>
    <script src="https://app.bitrequest.io/assets/js/lib/bitrequest_checkout.js"></script>

You can skip the jquery file if you already use jquery on your site.  
Or you can use your own jquery reference instead.

#### 2. Generate a request link (example).  
For assistance visit [https://www.bitrequest.io/create-request-link/](https://www.bitrequest.io/create-request-link/)  

    var payment = 'nano';
    var uoa = 'usd';
    var amount = 0.05;
    var address = 'nano_13kx8gfrse6q6z6djznkhj83cshih8akmjr3e3wmoabb38p6immo4mho8359';
    var d = btoa(JSON.stringify({
        't': 'Example request title',
        'n': 'Example request name',
        'c': 0,
        'pid': "paymentid"
    }));
    var request_url = 'https://app.bitrequest.io/?payment=' + payment + '&uoa=' + uoa + '&amount=' + amount + '&address=' + address + '&d=' + d;

#### 3. Checkout link.  
Every link with class .br_checkout will trigger the payment terminal popup. Make sure the href attribute has the correctly formatted request link.

    <a href="{$request_url}" target="_top" class="br_checkout">Check out</a>

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
           "recievedamount": "0.026",
           "recievedcc": "0.026",
           "status": "paid",
           "txhash": "txhash",
           "reciever": "nano_13kx8gfrse6q6z6djznkhj83cshih8akmjr3e3wmoabb38p6immo4mho8359",
           "confirmations": "false",
           "transactiontime": "1588660654471"
        },
        "data": {
           "t": "Example request title",
           "n": "Example request name",
           "wh": "{webhook url}"
        },
        "meta": null
    }

#### 5. Update the backend / frontend.  
You can catch the postdata with the following callback function and update your backend and/or frontend accordingly:

    function result_callback(post_data) {
        console.log(post_data);
    }

There's an example backend php template included in this repository.