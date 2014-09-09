<html>
	<head>
		<title>Eyowo Payment</title>
		<link href="../style.css" rel="stylesheet"/>
	</head>
	<body>
    <div class="mdiv">
    <img src="{$logo}"/>
   	<p>{$warning_text}<br /><a href='{$return_url}'>&sube; {$cancel_text}</a></p>
		<form action="{$eyowo_url}" method="post" id="eyowo_form" class="hidden">
			<input type="hidden" name="eyw_walletcode" value="{$business}">
			<input type="hidden" name="eyw_transactionref" value="{$eyowo_id}">	
			<input type="hidden" name="eyw_item_name_1" value="{$item_names}"> 
			<input type="hidden" name="eyw_item_description_1" value="{$description}">			 
			<input type="hidden" name="eyw_item_price_1" value="{$amount}">
            <input type="hidden" name="eyw_item_name_2" value="Shipping"> 
			<input type="hidden" name="eyw_item_description_2" value="Total Shipping cost">			 
			<input type="hidden" name="eyw_item_price_2" value="{$shipping}">
         	<p>
			{$cfn} {$cln}<Br/>
            {$cem}	<Br/>
            {$address}
            </p>
            {$comments}
          	<p>Shipping Cost: <B>NGN {$shipping}</B></p>
            <p>Total Cost: <B>NGN {$total}</B></p>
            <p>Transaction Reference: <b>{$eyowo_id}</b></p>
			<input type="image" title="Checkout with Eyowo" src="https://www.eyowo.com/images/buttons/1.png" />
			
		</form>
	</div>	
	</body>
</html>
