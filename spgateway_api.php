<?php
function commerce_spgateway_commerce_payment_method_info() {
	$payment_methods = array();

	$payment_methods['spgateway_creditcard'] = array(
		'base'  => 'spgateway_creditcard',
		'title' => '智付通信用卡',
		'description' => '智付通信用卡',
		'short_title' => '信用卡',
		'display_title' => '信用卡',
		'terminal' => FALSE,
		'offsite' => TRUE,
		//'offsite_autoredirect' => TRUE,
		'active' => TRUE,
		'file' => 'includes/commerce_spgateway_credit_card.inc',
	);

	$payment_methods['spgateway_webatm'] = array(
		'base'  => 'spgateway_webatm',
		'title' => '智付通WEBATM',
		'description' => '智付通WEBATM',
		'short_title' => '網路銀行',
		'display_title' => '網路銀行',
		'terminal' => FALSE,
		'offsite' => TRUE,
		'offsite_autoredirect' => TRUE,
		'active' => TRUE,
		'file' => 'includes/commerce_spgateway_webatm.inc',
	);

	$payment_methods['spgateway_vacc'] = array(
		'base'  => 'spgateway_vacc',
		'title' => '智付通ATM轉帳',
		'description' => '智付通ATM轉帳',
		'short_title' => 'ATM轉帳',
		'display_title' => 'ATM轉帳',
		'terminal' => FALSE,
		'offsite' => TRUE,
		'offsite_autoredirect' => TRUE,
		'active' => TRUE,
		'file' => 'includes/commerce_spgateway_vacc.inc',
	);


	$payment_methods['spgateway_cvs'] = array(
		'base'  => 'spgateway_cvs',
		'title' => '智付通超商代碼繳費',
		'description' => '智付通超商代碼繳費',
		'short_title' => '超商代碼繳費',
		'display_title' => '超商代碼繳費',
		'terminal' => FALSE,
		'offsite' => TRUE,
		'offsite_autoredirect' => TRUE,
		'active' => TRUE,
		'file' => 'includes/commerce_spgateway_cvs.inc',
	);

	$payment_methods['spgateway_barcode'] = array(
		'base'  => 'spgateway_barcode',
		'title' => '智付通超商條碼繳費',
		'description' => '智付通超商條碼繳費',
		'short_title' => '超商條碼繳費',
		'display_title' => '超商條碼繳費',
		'terminal' => FALSE,
		'offsite' => TRUE,
		'offsite_autoredirect' => TRUE,
		'active' => TRUE,
		'file' => 'includes/commerce_spgateway_barcode.inc',
	);

	$payment_methods['spgateway_cvscom'] = array(
		'base'  => 'spgateway_cvscom',
		'title' => '智付通超商取貨付款',
		'description' => '智付通超商取貨付款',
		'short_title' => '超商取貨付款',
		'display_title' => '超商取貨付款',
		'terminal' => FALSE,
		'offsite' => TRUE,
		'offsite_autoredirect' => TRUE,
		'active' => TRUE,
		'file' => 'includes/commerce_spgateway_cvscom.inc',
	);

	$payment_methods['spgateway_pay2go'] = array(
		'base'  => 'spgateway_pay2go',
		'title' => 'Pay2go電子錢包',
		'description' => 'Pay2go電子錢包',
		'short_title' => 'Pay2go電子錢包',
		'display_title' => 'Pay2go電子錢包',
		'terminal' => FALSE,
		'offsite' => TRUE,
		'offsite_autoredirect' => TRUE,
		'active' => TRUE,
		'file' => 'includes/commerce_spgateway_pay2go.inc',
	);

	$payment_methods['spgateway_android'] = array(
		'base'  => 'spgateway_android',
		'title' => 'Google Pay',
		'description' => 'Google Pay',
		'short_title' => 'Google Pay',
		'display_title' => 'Google Pay',
		'terminal' => FALSE,
		'offsite' => TRUE,
		'offsite_autoredirect' => TRUE,
		'active' => TRUE,
		'file' => 'includes/commerce_spgateway_android.inc',
	);

	$payment_methods['spgateway_samsung'] = array(
		'base'  => 'spgateway_samsung',
		'title' => 'Samsung Pay',
		'description' => 'Samsung Pay',
		'short_title' => 'Samsung Pay',
		'display_title' => 'Samsung Pay',
		'terminal' => FALSE,
		'offsite' => TRUE,
		'offsite_autoredirect' => TRUE,
		'active' => TRUE,
		'file' => 'includes/commerce_spgateway_samsung.inc',
	);

	$payment_methods['spgateway_instflag'] = array(
		'base'  => 'spgateway_instflag',
		'title' => '智付通信用卡分期',
		'description' => '智付通信用卡分期付款',
		'short_title' => '信用卡分期',
		'display_title' => '信用卡分期',
		'terminal' => FALSE,
		'offsite' => TRUE,
		'offsite_autoredirect' => TRUE,
		'active' => TRUE,
		'file' => 'includes/commerce_spgateway_instflag.inc',
	);

	return $payment_methods;
}

function addpadding($string, $blocksize = 32) {
	$len = strlen($string);
	$pad = $blocksize - ($len % $blocksize);
	$string .= str_repeat(chr($pad), $pad);

	return $string;
}

function create_mpg_aes_encrypt ($parameter = "" , $key = "", $iv = "") {
	$return_str = '';
	if (!empty($parameter)) {
		//將參數經過 URL ENCODED QUERY STRING
		$return_str = http_build_query($parameter);
	}
	return (trim(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, addpadding($return_str), MCRYPT_MODE_CBC, $iv))));
	//return trim(bin2hex(openssl_encrypt(addpadding($return_str), 'aes-256-cbc', $key, $iv)));
}

function spgateway_transaction_encrypt($data, $p){

	$HashKey = $p['HashKey'];
	$HashIV = $p['HashIV'];

	$TradeInfo = create_mpg_aes_encrypt($data, $HashKey, $HashIV);
	$HashData = 'HashKey='.$HashKey.'&'.$TradeInfo.'&HashIV='.$HashIV;
	$TradeSha = strtoupper(hash("sha256",$HashData));

	$output['TradeInfo'] = $TradeInfo;
	$output['TradeSha'] = $TradeSha;

	return $output;
}

function hash256data_encrypt($textAES){
	$HashKey = HashKey;
	$HashIV = HashIV;
	$output = 'HashKey='.$HashKey.'&'.$textAES.'&HashIV='.$HashIV;
	return strtoupper(hash("sha256",$output));;
}

function strippadding($string) {
	$slast = ord(substr($string, -1));
	$slastc = chr($slast);
	$pcheck = substr($string, -$slast);
	if (preg_match("/$slastc{" . $slast . "}/", $string)) {
		$string = substr($string, 0, strlen($string) - $slast);
		return $string;
	} else {
		return false;
	}
}

function create_aes_decrypt($parameter = "", $key = "", $iv = "") {
	return strippadding(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, pack('H*', $parameter), MCRYPT_MODE_CBC, $iv));
	//return strippadding(openssl_decrypt(hex2bin($parameter),'AES-256-CBC', $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv));
}

function spgateway_transaction_dencrypt($TradeInfo){
	$HashKey = HashKey;
	$HashIV = HashIV;
	$output = create_aes_decrypt($TradeInfo,$HashKey, $HashIV);

	return json_decode($output, true);
}

function spgateway_key_word($key){
	$output = array(
		'Status' => '回傳狀態',
		'Message' => '回傳訊息',
		'Result' => '回傳參數',
		'MerchantID' => '商店代號',
		'Amt' => '交易金額',
		'TradeNo' => '智付通交易序號',
		'MerchantOrderNo'=> '商店訂單編號',
		'PaymentType' => '支付方式',
		'RespondType' => '回傳格式',
		'PayTime' => '支付完成時間',
		'IP' => '交易ip',
		'EscrowBank' => '款項保管銀行',
		'RespondCode' => '金融機構回應碼',
		'Auth' => '授權碼',
		'Card6No' => '卡號前六碼',
		'Card4No' => '卡號末四碼',
		'Inst' => '分期-期別',
		'InstFirst' => '分期-首期金額',
		'InstEach' => '分期-每期金額',
		'ECI' => 'eci值',
		'TokenUseStatus' => '信用卡快速結帳使用狀態',
		'RedAmt' => '紅利折抵後實際金額',
		'CardType' => '交易類別',
		'BankCode' => '金融機構代碼',
		'PayBankCode' => '付款人金融機構代碼',
		'PayerAccount5Code' => '付款人金融機構帳號末五碼',
		'CodeNo' => '繳費代碼',
		'StoreType' => '繳費門市類別',
		'ExpireDate' => '繳費截止日期',
		'ExpireTime' => '繳費截止時間',
		'Exp' => 'EXP代碼',
		'StoreID' => '繳費門市代號',
		'Barcode_1' => '第一段條碼',
		'Barcode_2' => '第二段條碼',
		'Barcode_3' => '第三段條碼',
		'PayStore' => '繳費超商',
		'P2GTradeNo' => 'p2g交易序號',
		'P2GPaymentType' => 'p2g支付方式',
		'P2GAmt' => 'p2g交易金額',
		'StoreCode' => '超商門市編號',
		'StoreName' => '超商門市名稱',
		'StoreType' => '超商類別名稱',
		'StoreAddr' => '超商門市地址',
		'TradeType' => '取件交易方式',
		'CVSCOMName' => '取貨人',
		'CVSCOMPhone' => '取貨人手機號碼'
	);
	if(!isset($output[$key])){
		$output[$key] = $key;
	}
	return $output[$key];
}

function spgateway_creditcard_redirect_form(&$p, $order, $payment_method) {
	//基本參數
	$ActionURL = $p['action'];
	$HashKey = $p['HashKey'];
	$HashIV = $p['HashIV'];

	//交易資訊
	$remote_id=date('YmdHis').$order->order_id;
	$price = $p['Amt'];
	$mail = $p['Email'];
	$ItemDesc = $p['ItemDesc'];

	//時間限制
	$TradeLimit = $p['TradeLimit'];
	$ExpireDate = '';

	//銀聯卡與紅利折抵
	//$UNIONPAY = isset($payment_method['settings']['UNIONPAY'])?$payment_method['settings']['UNIONPAY']:0;
	//$CreditRed = isset($payment_method['settings']['CreditRed'])?$payment_method['settings']['CreditRed']:0;
	$UNIONPAY = 0;
	$CreditRed = 0;

	//傳送Data
	$data = array(
		'MerchantID' => $p['MerchantID'],
		'TradeInfo' => '',
		'TradeSha' => '',
		'Version' => $p['Version'],
		'RespondType' => $p['RespondType'],
		'TimeStamp' => $p['TimeStamp'],
		'LangType' => $p['LangType'],
		'MerchantOrderNo'=> $remote_id,
		'Amt' => $price,
		'ItemDesc' => $ItemDesc,
		'TradeLimit'=> $TradeLimit,
		'ExpireDate' => $ExpireDate, //非適用即時交易
		'ReturnURL' => $p['ReturnURL'],
		'NotifyURL' => $p['NotifyURL'],
		'CustomerURL' => '', //非即時交易支付方式
		'ClientBackURL' => $p['ClientBackURL'],
		'Email' => $mail,
		'EmailModify' => 0, //不開放付款人電子郵件修改
		'LoginType' => 0, //不需要登入智付通會員
		'OrderComment' => "",
		//以下開始是付款方式的參數
		'CREDIT' => 1,
		'ANDROIDPAY' => 0,
		'SAMSUNGPAY' => 0,
		'InstFlag' => 0,
		'CreditRed' => $CreditRed,
		'UNIONPAY' => $UNIONPAY,
		'WEBATM' => 0,
		'VACC' => 0,
		'CVS' => 0,
		'BARCODE' => 0,
		'P2G' => 0,
		'CVSCOM' => 0,
	);

	//加密資料
	$encrydata = spgateway_transaction_encrypt($data, $p);
	$TradeInfo = $encrydata['TradeInfo'];
	$TradeSha = $encrydata['TradeSha'];

	$p['MerchantOrderNo'] = $data['MerchantOrderNo'];
	$p['TradeInfo'] = $TradeInfo;
	$p['TradeSha'] = $TradeSha;

	//建立交易記錄
	commerce_spgateway_create_payment_transactions($remote_id, $order, $payment_method, COMMERCE_PAYMENT_STATUS_PENDING);

	return $form;
}


?>
