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
 
/*$currency_order = new Currency((int)($cart->id_currency));
$currency_module = $eyowo->getCurrency((int)($cart->id_currency));

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

eyowo::checkCurrency($cart->id_currency);
$products=$cart->getProducts();
$count=count($products);
$content="";
$names = "";
for($i=0;$i<$count;$i++)
{
 
	$parray=$products[$i];
	$content.="<p>Name: ".$parray["name"];
	$content.="<br/>Quantity: ".$parray["cart_quantity"];
	$names .= $parray["name"].", ";
	$content.="<br/>Price: NGN ".number_format($parray["price"],2)." each</p>";
	 
}
$addressinfo = Db::getInstance()->getRow("SELECT alias,address1,address2,city,phone,phone_mobile,postcode,id_country,date_upd FROM `"._DB_PREFIX_."address` WHERE id_address = '".$cart->id_address_delivery."'");
$country  = Db::getInstance()->getValue("SELECT iso_code FROM "._DB_PREFIX_."country WHERE id_country=".$addressinfo["id_country"]);
$addresscontent = "Phone: ".$addressinfo["phone"]." , ".$addressinfo["phone_mobile"]."<br/><Br/>".$addressinfo["address1"]."<Br/>".$addressinfo["address2"]."<br/>Postcode: ".$addressinfo["postcode"]."<Br/>City:".$addressinfo["city"].", $country<br/><br/><i style='color:#999;font-size:10px;'>".date("r",strtotime($addressinfo["date_upd"]))."</i>";
$amount = 100 * ($cart->getOrderTotal(true, Cart::BOTH));
//$amout = str_replace(".","",$amount);
//$amout = str_replace(",","",$amount);
//$amount = $amount."00";
$shipping = 100 * Tools::ps_round((float)($cart->getOrderShippingCost()) + (float)($cart->getOrderTotal(true, Cart::ONLY_WRAPPING)), 2);
//$shipping = str_replace(".","",$shipping);
//$shipping = $shipping."00";
$total = (float)($cart->getOrderTotal(true, Cart::BOTH));
$total = number_format($total,2,".",",");
$eyowo_trans_id = $_SESSION["eyowo_trans_id"];
$smarty->assign(array(
	'redirect_text' => $eyowo->getL('Please wait, redirecting to eyowo... Thanks.'),
	'warning_text'=>$eyowo->getL('You are about to continue to payement on eyowo, you may cancel at this point?'),
	'cancel_text' => $eyowo->getL('Cancel'),
	'cart_text' => $eyowo->getL('My cart'),
	'return_text' => $eyowo->getL('Return to shop'),
	'eyowo_url' => $eyowo->geteyowoStandardUrl(),
	'address' => $addresscontent,
	'country' => $country,
	'comments' => $content,
	'state' => $state,
	'item_names'=>$names,
	'amount' => $amount,
	'customer' => $customer,
	'total' => $total,
	'shipping' => $shipping,
	'discount' => $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS),
	'business' => $business,
	'cfn'=>$customer->{'firstname'},
	'cln'=>$customer->{'lastname'},
	'cem'=>$customer->{'email'},
	'description' => $description,
		'return_url'=>'http://'.$_SERVER["HTTP_HOST"].__PS_BASE_URI__,
	/**'freeclient' => $freeclient,**/
	'currency_module' => $currency_module,
	'cart_id' => (int)($cart->id).'_'.pSQL($cart->secure_key),
	'products' => $cart->getProducts(),
	'eyowo_id' => $eyowo_trans_id,
	'header' => $header,
	'logo'=> Tools::getShopDomain(true, true).__PS_BASE_URI__."img/logo.jpg",
	'url' => Tools::getShopDomain(true, true).__PS_BASE_URI__
));

	$id_order = Db::getInstance()->getValue('SELECT `id_order` FROM `'._DB_PREFIX_.'eyowo_order` WHERE `eyowo_trans_id` = "'.$eyowo_trans_id.'"');
	$cust_d = "<P>".$customer->{'firstname'}." ".$customer->{'lastname'}."<Br/>".$customer->{'email'}." </P>";
	if(!$id_order)
	{
	$con = mysqli_connect(_DB_SERVER_,_DB_USER_,_DB_PASSWD_,_DB_NAME_);
	//$query = mysqli_query($con,"INSERT INTO `"._DB_PREFIX_."eyowo_order` VALUES (".(int)$cart->{'id'}.", '".mysqli_real_escape_string($con,$cust_d)."', '".date("U")."','".pSQL("pending")."','<br />".mysqli_real_escape_string($con,$content)."','".$eyowo_trans_id."'");
	$dbv = $content."<p>Shipping: <B>".number_format($shipping,2,".",",")."</b></p><p>Total: <b>".$total."</b></p>";
	$insert = Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'eyowo_order` (`id_order`, `id_cust`, `payment_time`, `payment_status`, `response_data`, `eyowo_trans_id`) VALUES ('.(int)$cart->{'id'}.', \''.mysqli_real_escape_string($con,$cust_d).'\', \''.date("U").'\', \''.("pending").'\',\''.mysqli_real_escape_string($con,$dbv).'\',\''.$eyowo_trans_id.'\')');
	mysqli_close($con);
	}
	//if (is_file(_PS_THEME_DIR_.'modules/eyowo/direct/redirect.tpl'))
	//	$smarty->display(_PS_THEME_DIR_.'modules/'.$eyowo->name.'/direct/redirect.tpl');
	//else
		$smarty->display(_PS_MODULE_DIR_.$eyowo->name.'/direct/redirect.tpl');
