<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title> Log Voucher - <?php echo $uid; ?></title>
<style media="all">

	#logo { margin:0 0 0 75px;}
	#logotext{ font-size:12px; text-align:center; margin:0; }
	p { margin:0; padding:0; font-size:11px;}
	#pono{ font-size:18px; padding:0; margin:0 5px 10px 0; text-align:left;}
	
	table.product
	{ border-collapse:collapse; width:100%; }
	
	table.product,table.product th
	{	border: 1px solid black; font-size:13px; font-weight:bold; padding:4px 0 4px 0; }
	
	table.product,table.product td
	{	border: 1px solid black; font-size:12px; font-weight:normal; padding:3px 0 3px 0; text-align:center; }
	
	table.product td.left { text-align:left; padding:3px 5px 3px 10px; }
	table.product td.right { text-align:right; padding:3px 10px 3px 5px; }
	
</style>
    
<script type="text/javascript">
    function closeWindow() {
        setTimeout(function() {
        window.close();
        }, 60000);
        }
</script>    
</head>

<body onLoad="closeWindow()">

<div style="width:750px; font-family:Arial, Helvetica, sans-serif; font-size:12px;"> 
	
	<h2 style="font-size:18px; font-weight:normal; text-align:center; text-decoration:underline;"> LOG VOUCHER </h2> 
    <div style="clear:both; "></div> 
	
	<div style="width:350px; border:0px solid #000; float:left;">
		<table style="font-size:11px;">
            <tr> <td> Log-ID </td> <td>:</td> <td> <?php echo $uid; ?> </td> </tr>
			<tr> <td> Username </td> <td>:</td> <td> <?php echo $username; ?> </td> </tr>
            <tr> <td> Date Time </td> <td>:</td> <td> <?php echo $dates.' - '.$time; ?> </td> </tr>
		</table>
	</div>
	
	<div style="width:200px; border:0px solid red; float:right;">
		<table style="font-size:11px;">
			<tr> <td> Component </td> <td>:</td> <td> <?php echo $modul; ?> </td> </tr>
			<tr> <td> Activity </td> <td>:</td> <td> <?php echo $activity; ?> </td> </tr>
            <tr> <td> Field-ID </td> <td>:</td> <td> <?php echo $field; ?> </td> </tr>
		</table>
	</div>
	
	<div style="clear:both; "></div>
	
	<div style="margin:3px 0 0 0; border-bottom:0px dotted #000;">
		
		<table class="product">

<tr> <th> Previous Val </th> <th> Changed Val </th> </tr>
<tr> <td class="left"> <?php if ($prev){ foreach ($prev as $key => $value) {  echo $key.' : '.$value.'<br>'; } } ?> </td> 
     <td class="left"> <?php foreach ($desc as $key => $value) {  echo $key.' : '.$value.'<br>'; } ?> </td> 
</tr>
		</table>
		
		<div style="clear:both; "></div>
	
</div>
    </div>
</body>
</html>
