<?php
function commerce_payment_transaction_new($method_id = '', $order_id = 0) {
	$transaction = new Transaction(); 
	$transaction->payment_method = $method_id;
	$transaction->order_id = $order_id;
	return $transaction;
}

function commerce_payment_transaction_save($transaction) {
	ob_start();
	$df = fopen(TRANSACTION_LOG, 'a');
	$data = array($transaction->instance_id, $transaction->amount, $transaction->remote_id, $transaction->status, $transaction->order_id, $transaction->message_variables, $transaction->message, $transaction->payment_method);
	fputcsv($df, $data);
	fclose($df);
	return ob_get_clean();
}

function commerce_payment_order_save($order) {
	ob_start();
	$df = fopen(ORDER_LOG, 'a');
	$data = array($order->order_id, $order->order_number,  $order->mail,  $order->data,  $order->payment_method,  $order->amount, $order->status, $order->log);
	fputcsv($df, $data);
	fclose($df);
	return ob_get_clean();
}

/**
 * 建立付款記錄的函數，可以方便的為訂單建立付款記錄
 * @param string $remote_id 付款記錄編號
 * 這個id將會跟金流公司進行資料的對應，由於不一定一筆訂單一次付款就成功，因此這個部分將會以一筆訂單會有很多付款記錄為考量
 *
 * @param object $order 訂單物件
 *
 * @param Array $payment_method 付款方式資料
 *
 * @param string $status 預設的付款記錄狀態
 *  COMMERCE_PAYMENT_STATUS_PENDING 暫存/初始化
 *  COMMERCE_PAYMENT_STATUS_SUCCESS 成功
 *  COMMERCE_PAYMENT_STATUS_FAILURE 失敗
 * @see 各種交易紀錄狀態
 * http://api.drupalhelp.net/api/commerce/modules--payment--commerce_payment.module/7
 *
 * @return object $transaction
 *  交易記錄物件
 */
function commerce_spgateway_create_payment_transactions($remote_id, $order, $payment_method, $status = COMMERCE_PAYMENT_STATUS_PENDING){

	$transaction = commerce_payment_transaction_new($payment_method['method_id'], $order->order_id);
	$transaction->instance_id = $payment_method['instance_id'];
	$transaction->amount = $order->amount;
	$transaction->remote_id = $remote_id;
	$transaction->status = $status;
	commerce_payment_transaction_save($transaction);

	return $transaction;
}

function get_file_lines($file) {
	//https://stackoverflow.com/questions/2162497/efficiently-counting-the-number-of-lines-of-a-text-file-200mb
	$linecount = 0;

	if (file_exists($file)) {
		$handle = fopen($file, "r");
		while(!feof($handle)){
			$line = fgets($handle);
			$linecount++;
		}
		fclose($handle);
	}

	return $linecount;
}

function gen_order($amount) {
	$order = new Order(); 
	$order->order_number = $_SERVER['REQUEST_TIME'];
	$order->order_id = get_file_lines(ORDER_LOG);
	$order->mail = "test@test.com";
	$order->payment_method = "spgateway_creditcard";
	$order->amount = $amount;
	$order->status = COMMERCE_PAYMENT_STATUS_PENDING;
	commerce_payment_order_save($order);
	return $order;
}

function get_transaction_by_id($remote_id) {
	$csvData = file_get_contents(TRANSACTION_LOG);
	$lines = explode(PHP_EOL, $csvData);
	$is_find = false;
	$l = array();
	foreach ($lines as $line) {
		$l = str_getcsv($line);
		if ($l[2] == $remote_id) {
			$is_find = true;
			break;
		}
	}

	if ($is_find) {
		$transaction = new Transaction(); 
		$transaction->instance_id = $l[0];
		$transaction->amount = $l[1];
		$transaction->remote_id = $l[2];
		$transaction->status = $l[3];
		$transaction->order_id = $l[4];
		$transaction->message_variables = $l[5];
		$transaction->message = $l[6];
		$transaction->payment_method = $l[7];
		return $transaction;
	}

	error_log(__LINE__ . ":" . __FUNCTION__);
	die(__LINE__ . ":" . __FUNCTION__);
}

function commerce_spgateway_update_payment_transactions($status, $receive){
	//取得訂單id
	$order_id = substr($receive['Result']['MerchantOrderNo'], 14);
	$remote_id = $receive['Result']['MerchantOrderNo'];

	$transaction = get_transaction_by_id($remote_id);
	$transaction->status = $status;

	//將紀錄存下來到變數裡面
	$transaction->message_variables = json_encode($receive['Result']);
	//將紀錄存到Message裡面
	$transaction->message = $receive['Message']."</br>";

	foreach($receive['Result'] as $key => $value){
		$transaction->message .= sprintf("%s:%s </br>",spgateway_key_word($key),$value);
	}
	commerce_payment_transaction_save($transaction);

	return $transaction;
}

function _commerce_order_update_status($order_id, $status, $log = "") {
	$csvData = file_get_contents(ORDER_LOG);
	$lines = explode(PHP_EOL, $csvData);
	$array = array();
	$i = 0;
	foreach ($lines as $line) {
		echo "r:" . $i++;
		$l = str_getcsv($line);
		if ($l[0] == $order_id) {
			$l[6] = $status;
		}
		$array[] = $l;
	}

	$i = 0;
	$df = fopen(ORDER_LOG, 'w');
	foreach ($array as $line) {
		echo "w:" . $i++;
		fputcsv($df, $line);
	}

	$stat = fstat($df);
	ftruncate($df, $stat['size']-1);

	fclose($df);
}

function commerce_checkout_complete($order_id) {
	_commerce_order_update_status($order_id, COMMERCE_PAYMENT_STATUS_SUCCESS);
}

function commerce_order_status_update($order_id, $log = '') {
	_commerce_order_update_status($order_id, COMMERCE_PAYMENT_STATUS_FAILURE, $log);
}

function payment_background_notify(&$p) {
	if(empty($p['Status']) or empty($p['TradeInfo']) or empty($p['TradeSha'])) {
		return;
	}
	//確認是否為智付通回傳
	$TradeInfo = $p['TradeInfo'];
	$TradeSha = $p['TradeSha'];

	if($TradeSha!=hash256data_encrypt($TradeInfo)){
		return;
	}

	$result = spgateway_transaction_dencrypt($TradeInfo);
	$r = $result['Result'];
	//error_log(json_encode($result));
	//error_log("=>" . $r['MerchantOrderNo']);

	$order_id = substr($r['MerchantOrderNo'], 14);
	if($result['Status']=='SUCCESS'){
		//更新付款記錄
		commerce_spgateway_update_payment_transactions(COMMERCE_PAYMENT_STATUS_SUCCESS, $result);
		//完成訂單
		commerce_checkout_complete($order_id);
	}
	else{
		//訂單付款失敗，重新回到結帳畫面
		$order = commerce_order_status_update($order_id, $log = '付款錯誤重新回到結帳頁面');

		//更新付款記錄
		commerce_spgateway_update_payment_transactions(COMMERCE_PAYMENT_STATUS_FAILURE, $result);
	}
}


?>
