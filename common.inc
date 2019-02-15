<?php
define('COMMERCE_PAYMENT_STATUS_PENDING', 'pending');
define('COMMERCE_PAYMENT_STATUS_SUCCESS', 'success');
define('COMMERCE_PAYMENT_STATUS_FAILURE', 'failure');
define('HashKey', "GeQKWY3khCXGnSzS4kjYn9YBAGaZfZR8");
define('HashIV', "X7JTqiNUPIYXnGPN");
define('TRANSACTION_LOG', "/tmp/t");
define('ORDER_LOG', "/tmp/o");

class Order {
 var $order_number;
 var $order_id;
 var $mail;
 var $data;
 var $payment_method;
 var $amount;
 var $status;
 var $log;
 //var $payment_redirect_key;
}

class Transaction {
	var $instance_id;
	var $amount;
	var $remote_id;
	var $status;
  var $order_id;
	var $message_variables;
	var $message;
	var $payment_method;
  //var $currency_code;
}



?>
