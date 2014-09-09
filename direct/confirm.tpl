<html>
	<head>
		<title>Eyowo Payment</title>
		<link href="../style.css" rel="stylesheet"/>
	</head>
	<body>
    <div class="mdiv">
    <img src="{$logo}"/>
   			<p>{$return_url}</p>
			<p>{$status}</p>
         	<p>
			{$cfn} {$cln}<Br/>
            {$cem}<Br/>{$address}
            </p>
            {$comments}
  	         <p>Shipping Cost: <B>NGN {$shipping}</B></p>
            <p>Total Cost: <B>{$total}</B></p>
            <p>Transaction Reference: <b>{$eyowo_id}</b></p>
			<div><div class="seal">
                
					<script type="text/javascript" src="https://www.eyowo.com/javascripts/getseal.js"></script>
                    <script>display("black", "medium");</script>
               
            </div>
            </div>
            
	</div>	
	</body>
</html>

