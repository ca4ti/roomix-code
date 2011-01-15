<?PHP
function Billing_Header()
{
$content="
<html>
<head>
<meta content='text/html; charset=ISO-8859-1' http-equiv='content-type'>
<title></title>
<style type='text/css'>
	body {
		border-color: #330033;
		border-width: 1px;
		width: 21cm;
		height: 29.7cm;
	}

	h1 {
		font-weight: bold;
		color: #000099;
		font-size: 16.8px;
	}

	.header td {
		border-style: solid none;
		border-color: #330033;
		border-width: 1px 0px 3px;
		border-collapse: collapse;
	}

	.header_d td {
		border-style: solid none double;
		border-color: #330033;
		border-width: 1px 0px 5px;
		border-collapse: collapse;
		}

	.line td {
		border-style: none none dotted;
		border-bottom: 1px dotted #000066;
		}
</style>
</head>
<body>"; 
return $content;
}

function Title($title)
{
	$content= "<h1>$title</h1>";
	return $content;
}

function Header_company($company,$logo,$client)
{
$content = "
<table style='text-align: left; width: 100%;' border='0' cellpadding='2' cellspacing='2'>
<tbody>
	<tr>
		<td style='vertical-align: top;'>$logo<br>$company</td>
		<td style='vertical-align: top;'><br></td>
		<td style='vertical-align: bottom;'>$client</td>
	</tr>
</tbody>
</table>
<br>";
return $content;

}

function Sale_title($title_sale)
{
$content = "
<span style='font-weight: bold;'>$title_sale</span><br>
<table style='text-align: left; width: 100%;' border='0' cellpadding='0' cellspacing='0'>
<tbody>
	<tr class='header'>
		<td style='vertical-align: top; text-align: center; font-weight: bold; width: 502px;'>Service</td>
		<td style='vertical-align: top; text-align: center; font-weight: bold; width: 70px;'>Q.T.</td>
		<td style='vertical-align: top; text-align: center; font-weight: bold; width: 70px;'>PU HT</td>
		<td style='vertical-align: top; text-align: center; font-weight: bold; width: 70px;'>VAT</td>
		<td style='vertical-align: top; text-align: center; font-weight: bold; width: 70px;'>Price</td>
	</tr>";
return $content;
}

function Sale($service, $qt, $puht, $vat, $price, $curr)
{
	$content ="
	<tr class='line'>
		<td style='vertical-align: top; width: 502px; text-align: left'>$service</td>
		<td style='vertical-align: top; width: 70px; text-align: right'>$qt</td>
		<td style='vertical-align: top; width: 70px; text-align: right'>$puht $curr</td>
		<td style='vertical-align: top; width: 70px; text-align: right'>$vat $curr</td>
		<td style='vertical-align: top; width: 70px; text-align: right'>$price $curr<br></td>
	</tr>
";

return $content;
}

function Detail_table_Title()
{
	$content = "
<span style='font-weight: bold;'>Details calls</span> :<br>
<table style='text-align: left; width: 100%;' border='0' cellpadding='0' cellspacing='0'>
<tbody>
	<tr class='header'>
		<td style='vertical-align: top; text-align: center; font-weight: bold; width: 504px;'>Date - Time - Call to</td>
		<td style='vertical-align: top; text-align: center; font-weight: bold; width: 140px;'>Call</td>
		<td style='vertical-align: top; text-align: center; font-weight: bold; width: 70px;'>Duration</td>
		<td style='vertical-align: top; text-align: center; font-weight: bold; width: 70px;'>Price</td>
	</tr>";
	return $content;
}

function Detail_table_Line($date, $call, $duration, $price, $curr)
{
	$content = "
	<tr class='line'>
		<td style='vertical-align: top; width: 504px; text-align: left'>$date</td>
		<td style='vertical-align: top; width: 140px; text-align: left'>$call</td>
		<td style='vertical-align: top; width: 70px; text-align: right'>$duration</td>
		<td style='vertical-align: top; width: 70px; text-align: right'>$price $curr</td>
	</tr>
";

	return $content;
}

function Total_Billing($ht, $vat, $total, $curr)
{
$content = "
<table style='text-align: left; width: 287px; margin-left: auto; margin-right: 0px;' border='0' cellpadding='0' cellspacing='0'>
<tbody>
	<tr class='header header_d'>
		<td style='vertical-align: top; width: 197px; text-align: right; font-weight: bold;'>&nbsp;</td>
		<td style='vertical-align: top; width: 70px; font-weight: bold;'>Total</td>
	</tr>
	<tr class='line'>
		<td style='vertical-align: top; width: 197px; text-align: right; font-weight: bold;'>H.T -</td>
		<td style='vertical-align: top; width: 70px; text-align: right;'>$ht $curr</td>
	</tr>
	<tr class='line'>
		<td style='vertical-align: top; width: 197px; text-align: right; font-weight: bold;'>VAT -</td>
		<td style='vertical-align: top; width: 70px; text-align: right;'>$vat $curr</td>
	</tr>
	<tr class='line'>
		<td style='vertical-align: top; text-align: right; font-weight: bold;'>Total -</td>
		<td style='vertical-align: top; text-align: right;'>$total $curr</td>
	</tr>
</tbody>
</table>
</body>
</html>";
return $content;
}
?>