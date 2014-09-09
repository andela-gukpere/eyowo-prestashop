<?php

include(dirname(__FILE__).'/../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../init.php');
include(dirname(__FILE__).'/../eyowo.php');
session_start();
$eyowo = new eyowo();
$cart = new Cart((int)($cookie->id_cart));

$address = new Address((int)($cart->id_address_delivery));
$country = new Country((int)($address->id_country));
$state = NULL;
if ($address->id_state)
	$state = new State((int)($address->id_state));
$customer = new Customer((int)($cart->id_customer));
$business = Configuration::get('eyw_walletcode');
$eywallet=Configuration::get('eyw_walletcode');
eyowo::checkCurrency($cart->id_currency);
//$currency_order = new Currency((int)($cart->id_currency));
/*$currency_module = $eyowo->getCurrency((int)($cart->id_currency));

if (empty($business) OR !Validate::isEmail($business))
	die($eyowo->getL('eyowo error: (invalid or undefined business account email)'));

if (!Validate::isLoadedObject($address) OR !Validate::isLoadedObject($customer) OR !Validate::isLoadedObject($currency_module))
	die($eyowo->getL('eyowo error: (invalid address or customer)'));

// check currency of payment
if ($currency_order->id != $currency_module->id)
{
	$cookie->id_currency = $currency_module->id;
	$cart->id_currency = $currency_module->id;
	$cart->update();
}
*/
/**$feepaymentseller=Configuration::get('eyowo_FREE_CLIENT');
$freeclient="N";
if($feepaymentseller==1)
	$freeclient="Y";**/

$products=$cart->getProducts();
$count=count($products);
$content="";
$names = "";
for($i=0;$i<$count;$i++)
{
 
	$parray=$products[$i];
	$content.="<p>".($i+1).". Name: ".$parray["name"];
	$content.="<br/>&nbsp;&nbsp;&nbsp;&nbsp;Quantity: ".$parray["cart_quantity"];
	$names .= $parray["name"].", ";
	$content.="<br/>&nbsp;&nbsp;&nbsp;&nbsp;Price: NGN ".number_format($parray["price"],2)." each</p>";
	 
}
$addressinfo = Db::getInstance()->getRow("SELECT alias,address1,address2,city,phone,phone_mobile,postcode,id_country,date_upd FROM `"._DB_PREFIX_."address` WHERE id_address = '".$cart->id_address_delivery."'");
$country  = Db::getInstance()->getValue("SELECT iso_code FROM "._DB_PREFIX_."country WHERE id_country=".$addressinfo["id_country"]);
$addresscontent = "Phone: ".$addressinfo["phone"]." , ".$addressinfo["phone_mobile"]."<br/><Br/>".$addressinfo["address1"]."<Br/>".$addressinfo["address2"]."<br/>Postcode: ".$addressinfo["postcode"]."<Br/>City:".$addressinfo["city"].", $country<br/><br/><i style='color:#999;font-size:10px;'>".date("r",strtotime($addressinfo["date_upd"]))."</i>";

$eyw_tr = $_SESSION["eyowo_trans_id"];

function get_url_contents($url){
        $crl = curl_init();

		$useragent="Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)";
		//"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
        curl_setopt ($crl, CURLOPT_URL,$url);
		curl_setopt($crl, CURLOPT_COOKIESESSION, true);
		//curl_setopt($crl, CURLOPT_COOKIEJAR, "./cookiej.txt");
        curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, false);
		//curl_setopt($crl, CURLOPT_HEADER, true);
		curl_setopt($crl, CURLOPT_USERAGENT,$useragent);
        //curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, 6);
        $ret = curl_exec($crl);
        curl_close($crl);
//		echo $url;
		if($ret == "")
		{
			$ret = "{'STATUS':'FAIL','STATUSREASON':'CONNECTION TIME-OUT'}";			
		}
        return $ret;
}
$res = "";
$amount = number_format((float)($cart->getOrderTotal(true, Cart::BOTH)),2,'.',',');
$shipping = number_format(Tools::ps_round((float)($cart->getOrderShippingCost()) + (float)($cart->getOrderTotal(true, Cart::ONLY_WRAPPING)), 2),2,".",",");
$total = "NGN ".number_format((float)($cart->getOrderTotal(true, Cart::BOTH)),2,".",",");
$fn = $customer->{'firstname'};
$ln =$customer->{'lastname'};
$em =$customer->{'email'};

$price_sum = "Amount: $amount<br/>Shipping: NGN <B>$shipping</b><Br/>Total <b>$total</b><Br/>";
if(isset($_GET["success"]))
{
	$url = "https://www.eyowo.com/api/gettransactionstatus?format=json&walletcode=".$eywallet."&transactionref=".$eyw_tr;
	$json = get_url_contents($url);
	$jsonv = json_decode($json);
	//var_dump($jsonv);
	$reason = $jsonv->{"STATUSREASON"};
	$status = $jsonv->{"STATUS"};
	if(stristr($jsonv->{'STATUS'},"Approved"))
	{
		//$res = "Success";
		$tref = $_SESSION["eyowo_trans_id"];
		$cont_ = "<h3>Transaction Successful</h3><p>$fn $ln<Br/>$em $addresscontent</p>";
		$upd = Db::getInstance()->Execute("update `"._DB_PREFIX_."eyowo_order` SET payment_status='".$status."' WHERE eyowo_trans_id = '$eyw_tr'");
		$res = $upd?"Transaction Complete<Br/>Status: $status<br/>
		Reason: $reason<br/>
		You will receive a confirmatory email from us shortly <a href='javascript:window.print()'>Print</a>":"DB ERROR";
		$headers = 'From:Delichris <sales@delichris.com>' . "\r\n" .
									'Reply-To: sales@delichris.com' . "\r\n" .
										'MIME-Version: 1.0'."\r\n".
										'Content-Type: text/html; charset=ISO-8859-1'."\r\n".
										'X-Mailer: PHP/' . phpversion();
		@mail($em,"Delichris | Sales","<img src='http://".$_SERVER[HTTP_HOST]."/img/logo.jpg'/><br/>".$cont_."<br/>".$content."<br/>".$price_sum."<br>Transaction Reference: <i>$tref</i>",$headers);		
		@mail(Configuration::get("eyw_mail"),"Sales to $fn $ln",$cont_."<br/>".$content."<br/>".$price_sum."Transaction Reference: <i>$tref</i>",$headers);
		$state = Configuration::get("PS_OS_PAYMENT");
		$message = "Transaction ID: $tref, Cart ID: ".$cart->id." Total Cost: ".$cart->getOrderTotal(true,Cart::BOTH)." Date: ".date("r");
		$eyowo->validateOrder((int)($cart->id), $state, (float)($cart->getOrderTotal(true, Cart::BOTH)), $eyowo->displayName, $message, NULL, (int)($cart->id_currency), false, $cart->secure_key);
	/*	$sql = 'DELETE '._DB_PREFIX_.'cart_product
  FROM '._DB_PREFIX_.'cart_product
   JOIN '._DB_PREFIX_.'cart ON '._DB_PREFIX_.'cart.id_cart = '._DB_PREFIX_.'cart_product.id_cart
WHERE '._DB_PREFIX_.'cart.id_customer = '.$cart->id_customer.'
    AND '._DB_PREFIX_.'cart.id_guest = '.$cart->id_guest.';';
	//echo $cart->id_customer." : ".$cart->id_guest;
	$sql2=	'DELETE FROM '._DB_PREFIX_.'cart WHERE '._DB_PREFIX_.'cart.id_customer = '.$cart->id_customer.' AND '._DB_PREFIX_.'cart.id_guest = '.$cart->id_guest.';';
	Db::getInstance()->Execute($sql);
	Db::getInstance()->Execute($sql2);*/
	
	}
	else
	{
		
		$upd = Db::getInstance()->Execute("update `"._DB_PREFIX_."eyowo_order` SET payment_status='".$status."' WHERE eyowo_trans_id = '$eyw_tr'");
	
		$res = "Your transaction status: <b>".$status."</b><Br/>Reason: <b>$reason</b>";
	}
}

if(isset($_GET["fail"]))
{
	$url = "https://www.eyowo.com/api/gettransactionstatus?format=json&walletcode=".$eywallet."&transactionref=".$eyw_tr;
	$json = get_url_contents($url);
	$jsonv = json_decode($json);
	$reason = $jsonv->{'STATUSREASON'};
	$status = $jsonv->{'STATUS'};
	$upd = Db::getInstance()->Execute("update `"._DB_PREFIX_."eyowo_order` SET payment_status='".$status."' WHERE eyowo_trans_id = '$eyw_tr'");
	if($upd)$res = "Your transaction status: <b>".$status."</b><Br/>Reason: <b>".$reason."</b>";else $res = "DB::ERROR";
}
$smarty->assign(array(
	'redirect_text' => $eyowo->getL('Please wait, redirecting to eyowo... Thanks.'),
	'cancel_text' => $eyowo->getL('Cancel'),
	'cart_text' => $eyowo->getL('My cart'),
	'return_text' => $eyowo->getL('Return to shop'),
	'eyowo_url' => $eyowo->geteyowoStandardUrl(),
	'address' => $addresscontent,
	'country' => $country,
	'comments' => $content,
	'state' => $state,
	'status'=>$res,
	'amount' => $amount,
	'customer' => $customer,
	'total' => $total,
	'shipping' => $shipping ,
	'discount' => $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS),
	'business' => $business,
	'description' => $description,
	'cfn'=>$customer->{'firstname'},
	'cln'=>$customer->{'lastname'},
	'cem'=>$customer->{'email'},
	'return_url'=>'<a href="http://'.$_SERVER["HTTP_HOST"].__PS_BASE_URI__.'">&larr; Go Back</a>',
	'currency_module' => $currency_module,
	'cart_id' => (int)($cart->id).'_'.pSQL($cart->secure_key),
	'products' => $cart->getProducts(),
	'eyowo_id' => $eyw_tr,
	'logo'=> Tools::getShopDomain(true, true).__PS_BASE_URI__."img/logo.jpg",
	'header' => $header,
	'url' => Tools::getShopDomain(true, true).__PS_BASE_URI__
));
$smarty->display(_PS_MODULE_DIR_.$eyowo->name.'/direct/confirm.tpl');