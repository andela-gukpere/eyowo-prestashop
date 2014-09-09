<?php
if (!defined('_PS_VERSION_'))
  exit;
 
class Eyowo extends PaymentModule
  {
		public function __construct()
		{
			$this->name = 'eyowo';
			$this->tab = 'payments_gateways';
			$this->version = 1.1;
			$this->author = 'GodsonUkpere';
			$this->need_instance = 0;	
			$this->limited_countries=1; 
			parent::__construct();
			$this->displayName = $this->l('Eyowo');
			$this->description = $this->l('Integrate eyowo with PresatShop easily, with NGN [You will need to create this currency manually, ISO-CODE:NGN, Numeric ISO-CODE:566] currency only. Contact me for bugs http://godson.com.ng');
		}
	 
		public function install()
		{
			if (!parent::install()
			OR !$this->registerHook('payment')
			OR !$this->registerHook('paymentReturn')
			OR !$this->registerHook('shoppingCartExtra')
			OR !$this->registerHook('backBeforePayment')
			OR !$this->registerHook('paymentReturn')
			OR !$this->registerHook('rightColumn')
			OR !$this->registerHook('cancelProduct')
			OR !$this->registerHook('adminOrder'))
			return false;
		
		/* Set database */
			if (!Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'eyowo_order` (
			  `id_order` int(10) unsigned NOT NULL ,
			  `id_cust` varchar(255) NOT NULL,
			  `payment_time` int(20) unsigned NOT NULL,
			  `payment_status` varchar(255) NOT NULL,
			  `capture` int(10) unsigned NOT NULL,
			   `response_data` TEXT,
			   `eyowo_trans_id` varchar(255) NOT NULL,
			  PRIMARY KEY (`id_order`)
			) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8'))
				return false;
	
			// Set The Configuration
			Configuration::updateValue('eyw_walletcode', 'TXG6626');
			Configuration::updateValue('eyw_payment', '1');
			Configuration::updateValue('eyw_pwd', 'password');
			$email = Db::getInstance()->getValue('SELECT email FROM `'._DB_PREFIX_.'employee` WHERE id_employee > 0');
			Configuration::updateValue('eyw_mail', $email);
			return true;
		}
		public function uninstall()
		{
			Configuration::deleteByName('eyw_walletcode');
			Configuration::deleteByName('eyw_payment');
			Configuration::deleteByName('eyw_mail');
			Configuration::deleteByName('eyw_pwd');
			Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'eyowo_order`');
			return parent::uninstall();
		}
		public static function checkCurrency($currency)
		{
				$currency_code = Db::getInstance()->getValue('SELECT `iso_code_num` FROM `'._DB_PREFIX_.'currency` WHERE `id_currency` = "'.$currency.'"');
				if($currency_code !=566)exit("<body style='background:#333;font:14px Verdana;color:#555;font-weight:bolder;'><div style='margin:50px auto;box-shadow:2px 4px 16px #000;width:60%;padding:20px;border:5px solid #cecece;background:#efefef;border-radius:20px 0 20px 0'><p><img src='".Tools::getShopDomain(true, true).__PS_BASE_URI__."/img/logo.jpg"."' /><img style='float:right' src='".__PS_BASE_URI__."modules/eyowo/logo2.png'/></p><p><ul><li>Please set currency to NGN [₦]</li><li>Make sure you have a valid session</li><li>If all these are in place, try checking out again</li></ul></p><p><a href='http://".$_SERVER["HTTP_HOST"].__PS_BASE_URI__."'>&lArr; Go Back</a></p></div></body>");
	
		}
		private function _setConfigurationForm()
		{
			$eywallet=Configuration::get('eyw_walletcode');
			$eyw=Configuration::get('eyw_payment');
			$eywem = Configuration::get('eyw_mail');
			$eywpwd = Configuration::get('eyw_pwd');
			$eyws="";
			if($eyw==1)
			{
						$eyws="CHECKED";
			}
		
		$output='<table cellspacing="5" cellpadding="0" border="0" width="100%"><tr><td align="right"><img src="'.__PS_BASE_URI__.'modules/eyowo/logo.png" alt="" title="" /></td>
</tr><tr><td>';
		$output.='<p><br><form action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data"><fieldset>
		<legend><img src="'.__PS_BASE_URI__.'modules/eyowo/logo2.png" alt="Eyowo Gateway" title="eyowo gateway" /></legend>
		<p align="center"><a target="_blank" style="color:green;font:14px Tahoma;font-weight:bolder;" href="http://'.$_SERVER["HTTP_HOST"].__PS_BASE_URI__.'modules/eyowo/direct/check.php">Track your Eyowo Transactions</a></p><p align="right">Please report bugs to me @ <a href="http://godson.com.ng">GodsonUkpere</a></p>
		<p ><table cellspacing="5" cellpadding="5" border="0" align="center" style="color:#5f5f5f;font-size: 12px;font-family: Tahoma;	font-weight:  bold;"><tr><td>Eyowo Success URL</td><td><span style="color:green">http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/eyowo/direct/confirm.php?success=1</span></td></tr>
		<tr><td>Eyowo Fail URL</td><td><span style="color:red">http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/eyowo/direct/confirm.php?fail=1</span></td></tr>
		<tr><td >'.$this->l('*Eyowo Walletcode').' </td>	
          <td><INPUT NAME="eywallet" TYPE="text" id="eywallet" value="'.$eywallet.'" size="50">
	        &nbsp;&nbsp;</td>
		</tr>
		<tr><td>'.$this->l('*Sales Email').'</td><td><input type="text" name="eyw_mail" value="'.$eywem.'"/></td></tr>
		<tr><td>'.$this->l('*Password<Br/><I>set a password<br/>used to access the<br/>eyowo transactions page</i>').'</td><td><input type="text" name="eyw_pwd" value="'.$eywpwd.'"/></td></tr>
		<tr>	
		<td >'.$this->l('Eyowo Payment').'</td>	
		          <td><INPUT NAME="eyw_payment" TYPE="checkbox" id="eyw_payment" value="1" '.$eyws.'></td>	<td><INPUT style="padding:10px;border-radius:3px;cursor:pointer;" name="eySettings" TYPE="submit" id="eySettings" value="'.$this->l('Update Settings').'"><br/> </td></tr>';
			$output.='</table></fieldset></form></td></tr></table>';
		mysqli_close($con);
		$this->_html.=$output;
	}
	public function getContent()
	{
		global $currentIndex,$cookie;
		//$this->_html .= '<h2>'.$this->l('Eyowo').'</h2>';	 
		$this->_postProcess();
		$this->_setConfigurationForm();		
		return $this->_html;
		
	}
	private function _postProcess()
	{
		global $currentIndex, $cookie;
		
		if (Tools::isSubmit('eySettings'))
		{
			$eywallet = Tools::getValue('eywallet');
			$eyw = Tools::getValue('eyw_payment');
			$eyw_em = Tools::getValue ('eyw_mail');
			$eyw_pwd = Tools::getValue ('eyw_pwd');
			Configuration::updateValue('eyw_walletcode', $eywallet);
			Configuration::updateValue('eyw_payment', $eyw);
			Configuration::updateValue('eyw_mail',$eyw_em);
			Configuration::updateValue('eyw_pwd',$eyw_pwd);
			$this->_html= '<div class="conf confirm">'.$this->l('Settings updated for eyowo <B>'.$eywallet.'</B>').'</div>';
			 
		}
	}
	public $eyowo_trans_id =false;
	function hookPayment($params)
	{
		global $smarty;
		$eyw = Configuration::get('eyw_payment');		
		$eyw_wallet = Configuration::get("eyw_walletcode");
		$this->eyowo_trans_id = uniqid($eyw_wallet.'-'.rand(1000,9999).'-');
		session_start();
		$_SESSION["eyowo_trans_id"] = $this->eyowo_trans_id;
		$smarty->assign(array('this_path' => $this->_path,'eyw_payment'=> $eyw,'eyw_trans_id'=> $this->eyowo_trans_id));
		return $this->display(__FILE__, 'payment.tpl');
	}
	public function geteyowoStandardUrl()
	{
		return 'https://www.eyowo.com/gateway/pay';
	}
	public function validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod = 'Unknown', $message = NULL, $extraVars = array(), $currency_special = NULL, $dont_touch_amount = false, $secure_key = false)
	{
		if (!$this->active)
			return;
		parent::validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod, $message, $extraVars, $currency_special, $dont_touch_amount, $secure_key);
		// Track all transaction
		//$this->_saveTransaction($id_cart, $extraVars);
	}
	// Tracking all transaction
	private function _saveTransaction($id_cart, $extraVars)
	{
		$cart = new Cart((int)($id_cart));
		if (Validate::isLoadedObject($cart) AND $cart->OrderExists())
		{
		//	$responsedata= json_encode($extraVars);
		//	$id_order = Db::getInstance()->getValue('SELECT `id_order` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart` = '.(int)$cart->id);
		//	Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'eyowo_order` (`id_order`, `id_cust`, `payment_method`, `payment_status`, response_data, eyowo_trans_id) VALUES ('.(int)$id_order.', \''.pSQL($extraVars['transaction_id']).'\', \''.pSQL($extraVars['trans_type']).'\', \''.pSQL($extraVars['payment_status']).'\',\''.$responsedata.'\'.\''.$this->eyowo_trans_id.'\')');
		}
	}
	

	public function getL($key)
	{
		$translations = array(
			'payment_status' => $this->l('eyowo key \'payment_status\' not specified, can\'t control payment validity'),
			'payment' => $this->l('Payment: '),
			'custom' => $this->l('eyowo key \'custom\' not specified, cannot relay to cart'),
			'txn_id' => $this->l('eyowo key \'txn_id\' not specified, transaction unknown'),
			'cart' => $this->l('Cart not found'),
			'order' => $this->l('Order has already been placed'),
			'transaction' => $this->l('eyowo Transaction ID: '),
			'verified' => $this->l('The eyowo transaction could not be VERIFIED.'),
			'connect' => $this->l('Problem connecting to the eyowo server.'),
			'nomethod' => $this->l('No communications transport available.'),
			'Please wait, redirecting to eyowo... Thanks.' => $this->l('Please wait, redirecting to eyowo... Thanks.'),
			'Cancel' => $this->l('Cancel'),
			'My cart' => $this->l('My cart'),
			'Return to shop' => $this->l('Return to shop'),
			'eyowo error: (invalid or undefined walletcode)' => $this->l('eyowo error: (invalid or undefined walletcode)'),
			'eyowo error: (invalid address or customer)' => $this->l('eyowo error: (invalid address or customer)')
		);
    if (!isset($translations[$key]))
      return $key;
		return $translations[$key];
	}
 }

?>