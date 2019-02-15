# spgateway demo

this is a standalone demo for [NewebPay(Spgateway)](https://www.newebpay.com/) that third-party cash flow and logistics company in taiwan, refer by [durpal plugin](https://www.hellosanta.com.tw/blog/introduce-using-commerce-spgateway-module) 

# Characteristics
  - standalone
  - use csv format as database

# Quick start

you MUST regist a account in [NewebPay(Spgateway)](https://www.newebpay.com/website/Page/content/register) and get HashKey/HashIV/MerchantID

You can replace HashKey/HashIV variables in `common.inc`
>define('HashKey', "GeQKWY3khCXGnSzS4kjYn9YBAGaZfZR8");
>define('HashIV', "X7JTqiNUPIYXnGPN");

and csv path
>define('TRANSACTION_LOG', "/tmp/t");
>define('ORDER_LOG', "/tmp/o");

once run it under your web server, you can see as below(step 1):
![step 1](https://raw.githubusercontent.com/arvin-chou/spgateway_demo/master/step1.png) and you can directly press `Go` buttom to next step(step 2)

notice that 
  - replace your MerchantID in `MerchantID` field 
  - the default `action` value is test website, please replace it under procution
  - TradeInfo/TradeSha is generated automatically
  - if you under `localhost` domain, please us [ngrok](https://ngrok.com/) to explose your website
  - `CREDIT` field MUST be 1(only support creditcard payment in `payment_methods`)

in step 2, we generate TradeInfo/TradeSha automatically and you could press `Go` to send data to Spgateway gateway by `POST` method, please refer as following:
![step 2](https://raw.githubusercontent.com/arvin-chou/spgateway_demo/master/step2.png) 

Then you could fill test data to simulate transaction and just press button to send 
![mpg_gateway](https://raw.githubusercontent.com/arvin-chou/spgateway_demo/master/mpg_gateway.png)

then you can see the processing animation and it also return trasaction data by `POST` to your `NotifyURL` filed 
![processing](https://raw.githubusercontent.com/arvin-chou/spgateway_demo/master/processing.png)

finally return to `ReturnURL` as below:
![step 3](https://raw.githubusercontent.com/arvin-chou/spgateway_demo/master/step3.png) 


### Todos

 - we use mcrypt_encrypt/mcrypt_decrypt to implement aes encrypt/decrypt, please replace it with openssl_encrypt/openssl_decrypt relatively
 - support multi `payment_methods`

License
----

MIT
