<?php
include(dirname(__FILE__).'/../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../init.php');
include(dirname(__FILE__).'/../eyowo.php');

$eyowo = new eyowo();
$cart = new Cart((int)($cookie->id_cart));

$eywem = Configuration::get('eyw_mail');
$eywpwd = Configuration::get('eyw_pwd');

$pw = $_SERVER["PHP_AUTH_PW"];
$usr = $_SERVER["PHP_AUTH_USER"];

if($pw !=$eywpwd || $usr != $eywem)
{
	header('WWW-Authenticate: Basic Realm="Eyowo transaction tracking panel"');
	header("HTTP/1.1 401 Unauthorized");
	exit("Invalid login credentials");	
}

$tref = $_GET["tref"];
$eywallet=Configuration::get('eyw_walletcode');
		$output = "";
		function get_url_contents($url)
		{
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
	if(!isset($tref))
	{
		$con = mysqli_connect(_DB_SERVER_,_DB_USER_,_DB_PASSWD_,_DB_NAME_);
		$q = mysqli_query($con,"SELECT * FROM `"._DB_PREFIX_."eyowo_order` ORDER BY id_order DESC");
		while($r = mysqli_fetch_array($q))
		{
			$output .= "<div style='margin:10px;padding:10px;border:1px solid #cecece;background:#efefef;'><p style='float:left;'>$r[0]</p>$r[1] Date: ".date('r',$r[2])." <br/>Status: <i style='color:red'>$r[3]</i><br/>Transaction reference: <a href='http://".$_SERVER["HTTP_HOST"].__PS_BASE_URI__."modules/eyowo/direct/check.php?tref=$r[6]' target='_blank'>$r[6]</a></div>";	
		}	
	}
	else
	{
		$tref = Db::getInstance()->escape($tref);
		$out = Db::getInstance()->getRow("SELECT id_order,id_cust,payment_status,payment_time,response_data FROM `"._DB_PREFIX_."eyowo_order` WHERE eyowo_trans_id='$tref'");
		if($out)
		{
			$url = "https://www.eyowo.com/api/gettransactionstatus?format=json&walletcode=".$eywallet."&transactionref=".$tref;
			$json = get_url_contents($url);
			$jsonv = json_decode($json);
			$reason = $jsonv->{"STATUSREASON"};
			$status = $jsonv->{"STATUS"};
			$output.="<p><a href='javascript:print()'>&para; Print</a> <b>&prod;</b> <a href='http://".$_SERVER["HTTP_HOST"].__PS_BASE_URI__."modules/eyowo/direct/check.php'>&lArr; Go Back</a></p><p>Current Status: <b>$status</b><br/>Current Status Reason: <b>$reason</b></p> ".$out["id_cust"]." "."<p>Payment time: ".date("r",$out["payment_time"])."</p><p>Payment Status: ".$out['payment_status']."</p><h3>Purchases</h3> ".$out["response_data"];
		}
	}
		$eyowo_trans_id = $_SESSION['eyowo_trans_id'];
		$smarty->assign(array(

	'cem'=>$customer->{'email'},
	'description' => $description,
	/**'freeclient' => $freeclient,**/
	'currency_module' => $currency_module,
	'cart_id' => (int)($cart->id).'_'.pSQL($cart->secure_key),
	'products' => $cart->getProducts(),
	'eyowo_id' => $eyowo_trans_id,
	'header' => $header,
	'output'=>$output,
	'logo2'=>__PS_BASE_URI__."modules/eyowo/logo2.png",
	'logo'=> Tools::getShopDomain(true, true).__PS_BASE_URI__."img/logo.jpg",
	'url' => Tools::getShopDomain(true, true).__PS_BASE_URI__
));
$smarty->display(_PS_MODULE_DIR_.$eyowo->name.'/direct/check.tpl');
?>