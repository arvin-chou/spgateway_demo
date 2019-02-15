<?php
require('common.inc');
require('spgateway_api.php');
require('db.php');
?>

<?php if(!isset($_POST['action'])):?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
ul {
  list-style: none;
}

ul li.step::before {
  content: "\2022";
  color: red;
  font-weight: bold;
  display: inline-block; 
  width: 1em;
  margin-left: -1em;
}
ul li {
  color: #8080803d;
}

</style>
</head>
<body>
<ul>
  <li>Step1: encode data</li>
  <li>Step2: prepare to submit</li>
  <li>Step3: return</li>
</ul>
<form action="#" method="post" id="spgateway-creditcard-redirect-form" accept-charset="UTF-8">
<div class="help"> setting <br/>
<label for="action">action</label> <input id="action" name="action" value="https://ccore.spgateway.com/MPG/mpg_gateway"><br/>
<input type="hidden" id="HashKey" name="HashKey" value="<?=HashKey?>"><br/>
<input type="hidden" id="HashIV" name="HashIV" value="<?=HashIV?>"><br/>
</div>

<div><div class="help"><br/>
  Use the button below to proceed to the payment server.</div>
<label for="MerchantID">MerchantID</label> <input id="MerchantID" name="MerchantID" value="MS15752051"><br/>
<label for="TradeInfo">TradeInfo</label> <input id="TradeInfo" name="TradeInfo" value=""><br/>
<label for="TradeSha">TradeSha</label> <input id="TradeSha" name="TradeSha" value=""><br/>
<label for="Version">Version</label> <input id="Version" name="Version" value="1.5"><br/>
<label for="RespondType">RespondType</label> <input id="RespondType" name="RespondType" value="JSON"><br/>
<label for="TimeStamp">TimeStamp</label> <input id="TimeStamp" name="TimeStamp" value="1550070706"><br/>
<label for="LangType">LangType</label> <input id="LangType" name="LangType" value="zh-tw"><br/>
<input type="hidden" id="MerchantOrderNo" name="MerchantOrderNo" value="" >
<label for="Amt">Amt</label> <input id="Amt" name="Amt" value="200"><br/>
<label for="ItemDesc">ItemDesc</label> <input id="ItemDesc" name="ItemDesc" value="test module"><br/>
<label for="TradeLimit">TradeLimit</label> <input id="TradeLimit" name="TradeLimit" value="600"><br/>
<label for="ReturnURL">ReturnURL</label> <input id="ReturnURL" name="ReturnURL" value="http://611595fd.ngrok.io/commerce_kickstart-7.x-1.54/checkout/8"><br/>
<label for="NotifyURL">NotifyURL</label> <input id="NotifyURL" name="NotifyURL" value="http://611595fd.ngrok.io/commerce_kickstart-7.x-1.54/checkout/8/spgateway-notify"><br/>
<label for="ClientBackURL">ClientBackURL</label> <input id="ClientBackURL" name="ClientBackURL" value="http://611595fd.ngrok.io/commerce_kickstart-7.x-1.54/checkout/8"><br/>
<label for="Email">Email</label> <input id="Email" name="Email" value="test@gmail.com"><br/>
<label for="CREDIT">CREDIT</label> <input id="CREDIT" name="CREDIT" value="1"><br/>
<input type="submit" id="edit-submit" name="op" value="Go" class="form-submit">
</div></form>

<script   src="https://code.jquery.com/jquery-1.12.4.min.js"   integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="   crossorigin="anonymous"></script>
<script> 
$(document).ready(function() {
	jQuery.fn.deserialize = function (data) {
		var f = this,
			map = {},
			find = function (selector) { return f.is("form") ? f.find(selector) : f.filter(selector); };
		//Get map of values
		jQuery.each(data.split("&"), function () {
			var nv = this.split("="),
				n = decodeURIComponent(nv[0]),
				v = nv.length > 1 ? decodeURIComponent(nv[1]) : null;
			if (!(n in map)) {
				map[n] = [];
			}
			map[n].push(v);
    });
    //Set values for all form elements in the data
    jQuery.each(map, function (n, v) {
      find("[name='" + n + "']").val(v);
    });
    //Clear all form elements not in form data
    find("input:text,select,textarea").each(function () {
      if (!(jQuery(this).attr("name") in map)) {
        jQuery(this).val("");
      }
    });
    find("input:checkbox:checked,input:radio:checked").each(function () {
      if (!(jQuery(this).attr("name") in map)) {
        this.checked = false;
      }
    });
    return this;
  };

  var set_li_highlight = function($selector) {
    $('li').css('color', '').removeClass('step');
    $selector.css('color', 'black').addClass('step');
  };

  var getURLParameter = function(url, name) {
    return (RegExp(name + '=' + '(.+?)(&|$)').exec(url)||[,null])[1];
  };

  var request;
  var base = location.origin  + location.pathname;

  $('#ReturnURL').val(base + "?action=return");
  $('#NotifyURL').val(base + "?action=notify"); 
  $('#ClientBackURL').val(base + "?action=clientback");

  if ('return' == getURLParameter(location.href, "action")) {
    set_li_highlight($('li:eq(2)'));
  }
  else {
    set_li_highlight($('li:eq(0)'));
  }

  $("form").submit(function( event ) {
    // setup some local variables
    var $form = $(this);
    var action = $form.attr("action");
    if (action == "#") {
      event.preventDefault();

      // Abort any pending request
      if (request) {
        request.abort();
      }

      // Let's select and cache all the fields
      var $inputs = $form.find("input, select, button, textarea");

      // Serialize the data in the form
      var serializedData = $form.serialize();

      // Let's disable the inputs for the duration of the Ajax request.
      // Note: we disable elements AFTER the form data has been serialized.
      // Disabled form elements will not be serialized.
      $inputs.prop("disabled", true);

      // Fire off the request to /form.php
      request = $.ajax({
      url: window.location.pathname,
        type: "post",
        data: serializedData
      });

      // Callback handler that will be called on success
      request.done(function (response, textStatus, jqXHR){
        //response = JSON.stringify(response);
        // Log a message to the console
        console.log("TradeInfo:", response['TradeInfo']);
        $form.deserialize($.param(response));
        $('label, input:not([type=submit]), div.help').hide();
        $('br').remove();
        set_li_highlight($('li:eq(1)'));
        $form.attr("action", response['action']);
      });

      // Callback handler that will be called on failure
      request.fail(function (jqXHR, textStatus, errorThrown){
        // Log the error to the console
        console.error(
          "The following error occurred: "+
          textStatus, errorThrown
        );
      });

      // Callback handler that will be called regardless
      // if the request failed or succeeded
      request.always(function () {
        // Reenable the inputs
        $inputs.prop("disabled", false);
      });
    }
  });
});
</script>
</body>
</html>
<?php else:?>
<?php endif?>

<?php
if (isset($_GET['action'])) {
  //<! 3. for notify callback
  if ($_GET['action'] == "notify") {
    //error_log(json_encode($_POST));
    //error_log(json_encode($_GET));
    payment_background_notify($_POST);
  }
}

if (isset($_POST['action'])) {
  //<! 1 save transaction data

  //https://gist.github.com/odan/c1dc2798ef9cedb9fedd09cdfe6e8e76
  $order = gen_order($_POST['Amt']);
  $payment_methods = commerce_spgateway_commerce_payment_method_info();
  $payment_method['instance_id'] = $order->payment_method;

  spgateway_creditcard_redirect_form($_POST, $order, $payment_method);

  //<! 2. return encrypt data and prepare post it
  header('Content-Type: application/json');
  echo json_encode($_POST);
  exit;
}
?>
