<?php

date_default_timezone_set('America/Chicago');

require_once("../php-include/incl.yahoo.historical.data.php");
require_once("../php-include/incl.ta.eickhoff.php");

$style = '
<html><head>
<style type="text/css"> 
BODY {
  font: 9pt "OCR A Extended", Courier, System, Monaco, Helvetica;
  color: #FFFFFF;
  background-color: #000000;
}
TABLE {
  font: 9pt "OCR A Extended", Courier, System, Monaco, Helvetica;
  border-collapse: collapse;
  border: 1px solid #666666;
}
TD {
  border: 1px solid #666666;
  color: #FFFFFF;
}
TH {
  border: 1px solid #666666;
  color: #333333;
  background-color: #AAAAAA;
}
A {
  color: white;
 }
</style>
</head>
<body>
';

$in = $_REQUEST['backtest'];
//$arr_testdata = split(/\r\n/, $in);
$arr_testdata = preg_split('/\r\n/', $in);

$lines = $arr_testdata;
if ($lines < 2) {
	$arr_testdata = preg_split('/\r/', $in);
}
$lines = $arr_testdata;
if ($lines < 2) {
	$arr_testdata = preg_split('/\n/', $in);
}

$additionalDaysBack = 0;

#my ($label, $days, $column, %smaLabel, %smaDays, %smaColumn);
$buy_if = $buy_msg = $sell_if = $sell_msg = $buy_if_org = $sell_if_org = null;
$display = $start_date = $end_date = null;

$buys = $sells = [];

$sma_label = $sma_days = $sma_column = $sma_decimal = [];
$ema_label = $ema_days = $ema_column = $ema_decimal = [];

$mfi = $mfi_label = $mfi_days = $mfi_column = $mfi_decimal = [];
$rsi = $rsi_label = $rsi_days = $rsi_column = $rsi_decimal = [];

$adx_label = $adx_label_dp = $adx_label_dm = $adx_days = $adx_column = $adx_decimal = [];
$bol_label_mid = $bol_label_upper = $bol_label_lower = $bol_days = $bol_mult = $bol_column = $bol_decimal = [];
$macd_label = $macd_sig_label = $macd_div_label = $macd_days_slow = $macd_days_fast = $macd_days_signal = $macd_column = $macd_decimal = [];
$candle_label_color = $candle_label_patterns = $candle_close = [];


$pc_label_high = [];

$symbol = "";
$commission = $original_amount = $account = $cash = $custom_defined = $formatted_date = $chartStart = null;

$summary = 0;
$brief = 0;
$pin = '0000';

foreach ($arr_testdata as $ele) {

  $ele = preg_replace('/^ /', '', $ele);
  $ele = preg_replace('/ $/', '', $ele);
  
  if (preg_match('/^CANDLE/', $ele)) {
	$str = preg_replace('/\s+/', '', $ele);
	preg_match('/^CANDLE\((.*),(.*),(.*)\)/', $str, $mg);
    $candle_label_color[$mg[1]] = $mg[1];
    $candle_label_trend[$mg[1]] = $mg[2];
    $candle_label_patterns[$mg[1]] = $mg[3];
    $candle_close[$mg[1]] = 'CLOSE';
    $custom_defined .= "<tr><th>CANDLE</th><td>{$ele}</td></tr>";
	
	if (5 > $additionalDaysBack) {
		$additionalDaysBack = 5;
	}
	
  }
  
  if (preg_match('/^BOLLINGER/', $ele)) {
	$str = preg_replace('/\s+/', '', $ele);
	preg_match('/^BOLLINGER\((.*),(.*),(.*),(.*),(.*),(.*)\)/', $str, $mg);
    $bol_label_mid[$mg[1]] = $mg[1];
    $bol_label_upper[$mg[1]] = $mg[2];
    $bol_label_lower[$mg[1]] = $mg[3];
    $bol_days[$mg[1]] = $mg[4];
    $bol_mult[$mg[1]] = $mg[5];
    $bol_column[$mg[1]] = 'CLOSE';
    $bol_decimal[$mg[1]] = $mg[6];
    $custom_defined .= "<tr><th>BOLLINGER</th><td>{$ele}</td></tr>";
	
	if ($mg[4] > $additionalDaysBack) {
		$additionalDaysBack = $mg[4];
	}
	
  }
	
  if (preg_match('/^PRICECHANNEL/', $ele)) {
	$str = preg_replace('/\s+/', '', $ele);
	preg_match('/^PRICECHANNEL\((.*),(.*),(.*),(.*),(.*),(.*)\)/', $str, $mg);
    $pc_label_high[$mg[1]] = $mg[1];
    $pc_label_low[$mg[1]] = $mg[2];
    $pc_days[$mg[1]] = $mg[3];
    $pc_col_high[$mg[1]] = $mg[4];
    $pc_col_low[$mg[1]] = $mg[5];
    $pc_decimal[$mg[1]] = $mg[6];
    $custom_defined .= "<tr><th>PRICECHANNEL</th><td>{$ele}</td></tr>";
	
	if ($mg[3] > $additionalDaysBack) {
		$additionalDaysBack = $mg[3];
	}	
  }
  
  if (preg_match('/^SMA/', $ele)) {
	$str = preg_replace('/\s+/', '', $ele);
	preg_match('/^SMA\((.*),(.*),(.*),(.*)\)/', $str, $mg);	
    $sma_label[$mg[1]] = $mg[1];
    $sma_days[$mg[1]] = $mg[2];
    $sma_column[$mg[1]] = $mg[3];
    $sma_decimal[$mg[1]] = $mg[4];
    $custom_defined .= "<tr><th>SMA</th><td>{$ele}</td></tr>";
	
	if ($mg[2] > $additionalDaysBack) {
		$additionalDaysBack = $mg[2];
	}	
  }

  if (preg_match('/^EMA/', $ele)) {
	$str = preg_replace('/\s+/', '', $ele);
	preg_match('/^EMA\((.*),(.*),(.*),(.*)\)/', $str, $mg);	
    $ema_label[$mg[1]] = $mg[1];
    $ema_days[$mg[1]] = $mg[2];
    $ema_column[$mg[1]] = $mg[3];
    $ema_decimal[$mg[1]] = $mg[4];
    $custom_defined .= "<tr><th>EMA</th><td>{$ele}</td></tr>";
	
	if ($mg[2] > $additionalDaysBack) {
		$additionalDaysBack = $mg[2];
	}	
  }

  if (preg_match('/^MFI/', $ele)) {
	$str = preg_replace('/\s+/', '', $ele);
	preg_match('/^MFI\((.*),(.*)\)/', $str, $mg);		
    $mfi_label[$mg[1]] = $mg[1];
    $mfi_days[$mg[1]] = $mg[2];
    $custom_defined .= "<tr><th>MFI</th><td>{$ele}</td></tr>";
	
	if ($mg[2] > $additionalDaysBack) {
		$additionalDaysBack = $mg[2];
	}	
  }   

  if (preg_match('/^RSI/', $ele)) {
	$str = preg_replace('/\s+/', '', $ele);
	preg_match('/^RSI\((.*),(.*),(.*)\)/', $str, $mg);		
    $rsi_label[$mg[1]] = $mg[1];
    $rsi_days[$mg[1]] = $mg[2];
    $rsi_column[$mg[1]] = 'CLOSE';
    $rsi_decimal[$mg[1]] = $mg[3];
    $custom_defined .= "<tr><th>RSI</th><td>{$ele}</td></tr>";
	
	if ($mg[2] > $additionalDaysBack) {
		$additionalDaysBack = $mg[2];
	}	
  }  
  
  if (preg_match('/^ADX/', $ele)) {
	$str = preg_replace('/\s+/', '', $ele);
	preg_match('/^ADX\((.*),(.*),(.*),(.*),(.*)\)/', $str, $mg);		
    $adx_label[$mg[1]] = $mg[1];
    $adx_label_dp[$mg[1]] = $mg[2];
    $adx_label_dm[$mg[1]] = $mg[3];
    $adx_days[$mg[1]] = $mg[4];
    $adx_column[$mg[1]] = 'CLOSE';
    $adx_decimal[$mg[1]] = $mg[5];
    $custom_defined .= "<tr><th>ADX</th><td>{$ele}</td></tr>";
	
	if ($mg[4] > $additionalDaysBack) {
		$additionalDaysBack = $mg[4];
	}	
  }  
  
  if (preg_match('/^MACD/', $ele)) {
	$str = preg_replace('/\s+/', '', $ele);
	preg_match('/^MACD\((.*),(.*),(.*),(.*),(.*),(.*),(.*)\)/', $str, $mg);	
    $macd_label[$mg[1]] = $mg[1];
    $macd_sig_label[$mg[1]] = $mg[2];
    $macd_div_label[$mg[1]] = $mg[3];
    $macd_days_fast[$mg[1]] = $mg[4];
    $macd_days_slow[$mg[1]] = $mg[5];
    $macd_days_signal[$mg[1]] = $mg[6];
    $macd_column[$mg[1]] = 'CLOSE';
    $macd_decimal[$mg[1]] = $mg[7];
    $custom_defined .= "<tr><th>MACD</th><td>{$ele}</td></tr>";
	
	if (($mg[4] + $mg[5] + $mg[6]) > $additionalDaysBack) {
		$additionalDaysBack = $mg[4] + $mg[5] + $mg[6];
	}	
  }

  if (preg_match('/^SYMBOL/', $ele)) {
	preg_match('/^SYMBOL (.*)/', $ele, $mg);		
	$arr_symbols = preg_split('/, */', $mg[1]);
  }

  if (preg_match('/^COMMISSION/', $ele)) {
	preg_match('/^COMMISSION (.*)/', $ele, $mg);		
    $commission = $mg[1];
  }

  if (preg_match('/^SUMMARY/', $ele)) {	  
    $summary = 1;
  }
  
  if (preg_match('/^BRIEF/', $ele)) {	
    $brief = 1;
  }

  if (preg_match('/^ACCOUNT/', $ele)) {
	preg_match('/^ACCOUNT (.*)/', $ele, $mg);		
    $account    = $mg[1];
    $original_amount = $account;
  }

  if (preg_match('/^DISPLAY/', $ele)) {
	preg_match('/^DISPLAY (.*)/', $ele, $mg);	
    $display = $mg[1];
  }
  
  if (preg_match('/^PIN/', $ele)) {
	preg_match('/^PIN (.*)/', $ele, $mg);	
    $pin = $mg[1];
  }
  
  if (preg_match('/^END_DATE/', $ele)) {
	preg_match('/^END_DATE (.*)/', $ele, $mg);			
    $end_date = $mg[1];
	
	preg_match('/(\d\d\d\d)(\d\d)(\d\d)/', $end_date, $mg);	
	$formatted_date = $mg[1] . '-' . $mg[2] . '-' . $mg[3];
	$formatted_yahoo_end_date = $mg[2] . '/' . $mg[3] . '/' . $mg[1];

	//$chartStart = date("Ymd", strtotime("{$formatted_date} 12pm -90 days"));
  }
  
  if (preg_match('/^START_DATE/', $ele)) {
	preg_match('/^START_DATE (.*)/', $ele, $mg);			
    $start_date = $mg[1];
	
	preg_match('/(\d\d\d\d)(\d\d)(\d\d)/', $start_date, $mg);	
	$formatted_date = $mg[1] . '-' . $mg[2] . '-' . $mg[3];
	$formatted_yahoo_start_date = $mg[2] . '/' . $mg[3] . '/' . $mg[1];
	$chartStart = date("Ymd", strtotime("{$formatted_date} 12pm"));
  }  
  
  if (preg_match('/^BUYIF/', $ele)) {
	preg_match('/^BUYIF (.*) THEN "(.*)"$/', $ele, $mg);		
    $buy_if =  $mg[1];
    $buy_if_org =  $mg[1];
    $buy_msg = $mg[2];
    
	//build correct day indexing
	
	// https://rextester.com/l/perl_online_compiler
	// (patterns[0] =~ /R\+/ && rsi5[0] <= 35)
	
	// (patterns[$i + 0] =~ /R\+/ && rsi5[$i + 0] <= 35)
	// (@$patterns[$i + 0] =~ /R\+/ && rsi5[$i + 0] <= 35)
	// (@$patterns[$i + 0] =~ /R\+/ && @$rsi5[$i + 0] <= 35)	
	
	// convert index expressions into index and variable: rsi14[0] ==> rsi14[$i + 0]
	$buy_if = preg_replace('/\[/', '[$i + ', $buy_if);

	// escape '+' inside search pattern (+ ==> \+)
	$buy_if = preg_replace_callback('/(\'.*\')/', 'esc', $buy_if);

	$buy_if = preg_replace_callback('/\(([a-zA-Z])/', 'grp_c1', $buy_if);
    $buy_if = preg_replace_callback('/ ([a-zA-Z])/', 'grp_c2', $buy_if);
	$buy_if = preg_replace('/\$match/', 'preg_match', $buy_if);
	$buy_if = preg_replace('/{/', '(', $buy_if);
	$buy_if = preg_replace('/}/', ')', $buy_if);
	//echo $buy_if . "<br>";
  }
	

  if (preg_match('/^SELLIF/', $ele)) {
	preg_match('/^SELLIF (.*) THEN "(.*)"$/', $ele, $mg);	
    $sell_if =  $mg[1];
    $sell_if_org =  $mg[1];
    $sell_msg = $mg[2];
	
    // build correct day indexing
	$sell_if = preg_replace('/\[/', '[$i + ', $sell_if);
	$sell_if = preg_replace_callback('/(\'.*\')/', 'esc', $sell_if);
	$sell_if = preg_replace_callback('/\(([a-zA-Z])/', 'grp_c1', $sell_if);
	$sell_if = preg_replace_callback('/ ([a-zA-Z])/', 'grp_c2', $sell_if);
	$sell_if = preg_replace('/\$match/', 'preg_match', $sell_if);
	$sell_if = preg_replace('/{/', '(', $sell_if);
	$sell_if = preg_replace('/}/', ')', $sell_if);	
	//echo $sell_if;
  }
  
}

// escape '+' inside search pattern (+ ==> \+)
function esc($matches) {
	$text = $matches[1];
	$text = preg_replace('/\+/', '\+', $text);
	return $text;
}

function grp_c1($matches) {
  	return '($'. $matches[1];
}

function grp_c2($matches) {
  	return ' $' . $matches[1];
}

if (! $pin || $pin != date("ii")) {
	echo "Unauthorized.</body></html>";
	exit;
}

//close(IN);


$COLUMNS = null;
$COLUMNS['DATE'] = 0; 
$COL['DATE'] = 'Date';
$COLUMNS['OPEN'] = 1; 
$COL['OPEN'] = 'Open';
$COLUMNS['HIGH'] = 2; 
$COL['HIGH'] = 'High';
$COLUMNS['LOW'] = 3; 
$COL['LOW'] = 'Low';
$COLUMNS['CLOSE'] = 4; 
$COL['CLOSE'] = 'Close';
$COLUMNS['VOLUME'] = 5; 
$COL['VOLUME'] = 'Volume';
//$COLUMNS['ADJCLOSE'] = 6; 
//$COL['ADJCLOSE'] = 'Adj';
$COLUMNS['SYMBOL'] = $symbol; 
$COL['SYMBOL'] = 'symbol';

$additionalDaysBack = floor($additionalDaysBack * 1.7);

foreach ($arr_symbols as $symbol) {
	
	$account = $original_amount;

	$ascending = yahoo(array(
		"symbol" => $symbol, 
		"start" => $formatted_yahoo_start_date, 
		"end" => $formatted_yahoo_end_date, 
		"offset" => $additionalDaysBack,
		"columns" => array(
			//"d", "o", "h", "l", "c", "v", "a"
			"d", "o", "h", "l", "a", "v"
		)
	));
	
	//$DATE = $OPEN = $HIGH = $LOW = $CLOSE = $VOLUME = $ADJCLOSE = $startDate = null;
	$DATE = $OPEN = $HIGH = $LOW = $CLOSE = $VOLUME = $startDate = null;
	for ($i = 0; $i < count($ascending); $i++) {
		
		//list ($DATE[$i], $OPEN[$i], $HIGH[$i], $LOW[$i], $CLOSE[$i], $VOLUME[$i], $ADJCLOSE[$i]) = preg_split('/,/', $ascending[$i]);		
		list ($DATE[$i], $OPEN[$i], $HIGH[$i], $LOW[$i], $CLOSE[$i], $VOLUME[$i]) = preg_split('/,/', $ascending[$i]);

		if ($i == 0) {
			preg_match('/(\d\d\d\d)-(\d\d)-(\d\d)/', $DATE[$i], $mg);	
			$startDate = $mg[1] . $mg[2] . $mg[3];
		}
	}
	
	
	$c3 = $c4 = 'green';
	
	// test if BUYIF if valid	
	try {
		// Using '@' to suppress "Notice: Undefined variable: patterns"
		$result = @eval ($buy_if . "; return true;");
	} 
	catch (Throwable $t) {
		$result = false;
	}	
	if (! $result) {
		$c3 = 'red';
	}
	
	// test if SELLIF if valid
	try {
		$result = @eval($sell_if . "; return true;");
	} 
	catch (Throwable $t) {
		$result = false;
	}
	if (! $result) {
		$c4 = 'red';
	}
	$eickhoff_chart = "https://www.eskimo.com/~home/eickhoff-stock-charts/eickhoff.stock.charts.php?symbol={$symbol}&start={$chartStart}&end={$end_date}&vol=y&height=400&lowerheight=150&bol=20,2&sma1=50&sma2=200&macd=12,26,9&rsi=14&mfi=14&adx=14&col=18,12,15&crop=y&adl=y&thick=1&fib=y&cand=y";
	
	$arr_headers = preg_split('/, */', $display); 
	
	$headers = "<table>" .
		"<tr><th style=\"font: 12pt;\">SYMBOL</th><td><a target='_blank' href='{$eickhoff_chart}'>{$symbol}</a></td></tr>" . 
		"<tr><th>START_DATE</th><td>{$start_date}</td></tr>" .
		"<tr><th>END_DATE</th><td>{$end_date}</td></tr>" .
		"<!-- <tr><th>additionalDaysBack</th><td>{$additionalDaysBack}</td></tr> -->" .
		"<tr><th>ACCOUNT</th><td>\$" . number_format($original_amount) . "</td></tr>" . 
		"<tr><th>COMMISSION</th><td>\${$commission}</td></tr>" . 
		"{$custom_defined}<tr><th>BUY_IF</th><td><font color='{$c3}'>{$buy_if_org}</font></td></tr>" .
		"<tr><th>SELL_IF</th><td><font color='{$c4}'>{$sell_if_org}</font></td></tr>" .
		"</table>" .
		"</br><br/>\n" .
		"<table><tr><th width=80px>" . join('</th><th width=75px>', $arr_headers) . "</th>";

	$initial_buy = 0;
	
	$tech_list = [];
	
	// Price Channels
	foreach ($pc_label_high as $ele => $value) {
		$pc_h = @ ${$ele};  // Undefined variable: (for the label)
		$pc_l = $pc_label_low[$ele];  
		$colH = $pc_col_high[$ele];
		$colL = $pc_col_low[$ele];
		// convert label into a variable
		$pc_high = [];
		$pc_low = [];
		PriceChannels($ascending, $pc_days[$ele], $COLUMNS[$colH], $COLUMNS[$colL], $pc_decimal[$ele]);
		
		${$ele} = $pc_high;
		${$pc_l} = $pc_low;
		
		array_push ($tech_list, ${$ele});
		array_push ($tech_list, ${$pc_l});
		$headers .= "<th>" . $ele . "</th><th>" . $pc_l . "</th>";
	}
	
	// Bollinger
	foreach ($bol_label_mid as $ele => $value) {
		$b_mid = @ ${$ele};  // Undefined variable: (for the label)
		$b_upper = $bol_label_upper[$ele];  
		$b_lower = $bol_label_lower[$ele]; 
		$col = $bol_column[$ele];
		// convert label into a variable
		Bollinger($ascending, $bol_days[$ele], $bol_mult[$ele], $COLUMNS[$col], $bol_decimal[$ele]);
		${$ele} = $middle;
		${$b_upper} = $upper;
		${$b_lower} = $lower;
		array_push ($tech_list, ${$ele});
		array_push ($tech_list, ${$b_upper});
		array_push ($tech_list, ${$b_lower});
		$headers .= "<th>" . $ele . "</th><th>" . $b_upper . "</th><th>" . $b_lower . "</th>";
	}
	// SMA
	foreach ($sma_label as $ele => $value) {
		$col = $sma_column[$ele];
		${$ele} = SMA($ascending, $sma_days[$ele], $COLUMNS[$col], $sma_decimal[$ele]);
		array_push ($tech_list, ${$ele});
		$headers .= "<th>" . $ele . "</th>";
	}
	
	// EMA
	foreach ($ema_label as $ele => $value) {
		$col = $ema_column[$ele];
		// convert label into a variable
		${$ele} = EMA($ascending, $ema_days[$ele], $COLUMNS[$col], $ema_decimal[$ele]);
		array_push ($tech_list, ${$ele});
		$headers .= "<th>" . $ele . "</th>";
	}

	// MFI
	foreach ($mfi_label as $ele => $value) {
		// $ascending: "d", "o", "h", "l", "c", "v", "a"
		// $ascending:  0,   1,   2,   3,   4,   5,   6

		$mfi = [];

		// function MFI ($array_ref, $days, $index_high, $index_low, $index_close, $index_vol)
		MFI($ascending, $mfi_days[$ele], 2, 3, 4, 5); // populates $mfi

		// convert label into a variable and store the mfi results
		${$ele} = $mfi;

		array_push ($tech_list, ${$ele});
		$headers .= "<th>" . $ele . "</th>";		
	}		

	// RSI
	foreach ($rsi_label as $ele => $value) {
		$col = $rsi_column[$ele];

		$rsi = [];
		RSI($ascending, $rsi_days[$ele], $COLUMNS[$col], $rsi_decimal[$ele]); // populates $rsi

		// convert label into a variable and store the rsi results
		${$ele} = $rsi;

		array_push ($tech_list, ${$ele});
		$headers .= "<th>" . $ele . "</th>";		
	}

	// Candles
	foreach ($candle_label_color as $ele => $value) {
		$c_color = $candle_label_color[$ele];  
		$c_trend = $candle_label_trend[$ele];  
		$c_patterns = $candle_label_patterns[$ele]; 
		$col = $candle_close[$ele];

		// $ascending: "d", "o", "h", "l", "c", "v", "a"
		list (${$ele}, ${$c_trend}, ${$c_patterns}) = Candles($ascending, 1, $COLUMNS[$col], 2, 3);
		array_push ($tech_list, ${$ele});
		array_push ($tech_list, ${$c_trend});
		array_push ($tech_list, ${$c_patterns});
		$headers .= "<th>" . $ele . "</th><th>" . $c_trend . "</th><th>" . $c_patterns . "</th>";
	}

	// ADX
	foreach ($adx_label as $ele => $value) {
		$col = $adx_column[$ele];
		// convert label into a variable
		$dmlabel = $adx_label_dm[$ele];
		$dplabel = $adx_label_dp[$ele];
		
		ADX($ascending, $adx_days[$ele], $COLUMNS[$col], $COLUMNS['HIGH'], $COLUMNS['LOW'], $adx_decimal[$ele]);
		
		${$ele} = $adx;
		${$dplabel} = $DI_plus;
		${$dmlabel} = $DI_minus;
		
		array_push($tech_list, ${$ele});
		array_push($tech_list, ${$dplabel});
		array_push($tech_list, ${$dmlabel});
		$headers .= "<th>" . $ele . "</th><th>" . $dplabel . "</th><th>" . $dmlabel . "</th>";
	}
	
	// MACD
	foreach ($macd_label as $ele => $value) {
		$col = $macd_column[$ele];
		// convert label into a variable
		$siglabel = $macd_sig_label[$ele];
		$divlabel = $macd_div_label[$ele];
		MACD($ascending, $macd_days_fast[$ele], $macd_days_slow[$ele], $macd_days_signal[$ele], $COLUMNS[$col], $macd_decimal[$ele]);

		// MACD globals: $macd, $macd_ema, $divergence
		${$ele} = $macd;
		${$siglabel} = $macd_ema;
		${$divlabel} = $divergence;
		
		array_push($tech_list, ${$ele});
		array_push($tech_list, ${$siglabel});
		array_push($tech_list, ${$divlabel});
		$headers .= "<th>" . $ele . "</th><th>" . $siglabel . "</th><th>" . $divlabel . "</th>";
	}



	echo $style;
	if ($brief == 0) {
		echo $headers . "<th>Action</th><th>Gain</th><th>Total Gain</th></tr>\n";
	}

	$state = $buy_amt = $sell_amt = $this_gain = $c1 = $c2 = $buy_days = $sell_flag = $outcome = $apct = null;
	$gain = 0;

	for ($i = 0; $i < count($ascending); $i++) {
		
		//list ($Date, $Open, $High, $Low, $Close, $Volume, $Adj) = preg_split('/,/', $ascending[$i]);
		list ($Date, $Open, $High, $Low, $Close, $Volume) = preg_split('/,/', $ascending[$i]);
		$date = $Date;
		
		// skip all of the pre start date data that was used to get the technical analysis data up to speed befor the start date
		if (strtotime($Date) < strtotime($start_date)) {			
			continue;
		}

		//$line = "";

		//print "$Date|$Adj|$Volume|";
		$row = "<tr>";
		
		foreach ($arr_headers as $hd) {
			$variable = $COL[$hd];
			$var = ${$variable};
			if ($hd == "VOLUME") {
				$row .= "<td valign=top>" . number_format(sprintf('%1.0f', $var)) . "</td>";
			}
			else if ($hd == "DATE") {
				$row .= "<td valign=top>{$var}</td>";
			}
			else {
				$row .= "<td valign=top>" . sprintf('%1.2f', $var) . "</td>";
			}
		}

		// output the column data for Technicals defined in the config (in order listed in config)
		foreach ($tech_list as $t) {
			$row .= "<td valign=top>" . $t[$i] . "</td>";
		}

		$buy_if_result = false;
		
		try {
			$buy_if_result = @eval("return(" . $buy_if . ");");
		} 
		catch (Throwable $t) {
			$buy_if_result = false;
		}
		
		
		$sell_if_result = false;
		
		try {
			$sell_if_result = @eval("return(" . $sell_if . ");");
		} 
		catch (Throwable $t) {
			$sell_if_result = false;
		}		

		// test the buy formula
		if ($buy_if_result && $state != 'BUY') {
			$initial_buy = 1;
			$state = "BUY";
			if ($Date == $formatted_date) {
				array_push($buys, $symbol);
			}
			//list ($Date, $Open, $High, $Low, $Close, $Volume, $Adj) = preg_split('/,/', $ascending[$i + 1]);
			list ($Date, $Open, $High, $Low, $Close, $Volume) = preg_split('/,/', $ascending[$i + 1]);
			$buy_amt = sprintf('%1.2f', ($High + $Low) / 2);
			if ($buy_amt != 0) {
				$shares = intval(($account - $commission) / $buy_amt);
			}
			$cost = sprintf('%1.2f',($shares * $buy_amt) + $commission);
			$cash = sprintf('%1.2f',$account - $cost);
			$buy_days = 0;
			if ($brief == 0) {
				echo "$row<td><font color='#EEF093'><b>$buy_msg</b></font></td><td></td><td></td></tr>\n";
			}
		}
		// test the sell formula. "initial_buy": can't start with a sell; a sell only follows a buy; sell cannot be tbe same day when a buy is Bought
		else if ($sell_if_result && $initial_buy && $state != 'SELL' && $buy_days != 1) {
			if ($date == $formatted_date) {
				array_push($sells, $symbol);
			}
			//list ($Date, $Open, $High, $Low, $Close, $Volume, $Adj) = preg_split('/,/', $ascending[$i + 1]);
			list ($Date, $Open, $High, $Low, $Close, $Volume) = preg_split('/,/', $ascending[$i + 1]);
			$sell_amt = sprintf('%1.2f', ($High + $Low) / 2);
			$this_gain = (($sell_amt - $buy_amt) / $buy_amt  ) * 100;
			$gain = sprintf('%1.2f', ($gain + $this_gain));
			$this_gain  = sprintf('%1.2f', $this_gain);
			$c1 = 'red'; 
			$c2 = 'red';
			if ($gain > 0)       { $c2 = 'green'; }
			if ($this_gain > 0) { $c1 = 'green'; }  
			if ($gain == 0)       { $c2 = 'yellow'; }
			if ($this_gain == 0) { $c1 = 'yellow'; }

			$outcome = sprintf('%1.2f', ($shares * $sell_amt) - $commission);
			$account = $outcome + $cash;
			$apct = sprintf('%1.2f', (($account - $original_amount) / $original_amount *100 )); 
			$sell_flag = 1;
			if ($brief == 0) {
				echo "$row<td><font color='#BBBBFF'><b>$sell_msg</b></font></td><td></td><td></td></tr>\n";
			}
			$state = "SELL";
		}
		else if ($state == 'BUY') {
			$buy_days++;
			if ($buy_days == 1) {
				if ($brief == 0) {
					echo "$row<td><font color='#EEF093'>&nbsp;Bought $shares @ \$" . number_format($buy_amt) . "<br>&nbsp;Cost: \$" . number_format($cost) . "<br>&nbsp;Cash: \$" . number_format($cash) . "</font></td><td></td><td></td></tr>\n";
				}
			}
			else {
				if ($summary == 0 && $brief == 0) {
					echo "$row<td><font color='#EEF093'>&nbsp;&nbsp;hold</font></td><td></td><td></td></tr>\n";
				}
			}
		}
		else {
			// $line .= "|";
			if ($sell_flag == 1) {
				$sell_flag = 0;
				if ($sell_amt == 0) {
					if ($brief == 0) {
						echo "$row<td><font color='#BBBBFF'>Sold $shares @ TBD</font></td><td></font></td><td></td></tr>\n";
					}
				} 
				else {
					if ($brief == 0) {
						echo "$row<td><font color='#BBBBFF'>&nbsp;Sold $shares @ \$" . number_format($sell_amt) . "<br>&nbsp;Account: \$" . number_format($account) . "<br>&nbsp;Percent: {$apct} %</font></td><td><font color=$c1>{$this_gain}%</font></td><td><font color=$c2>{$gain}%</font></td></tr>\n";
					}
				}
			}
			else {
				if ($summary == 0 && $brief == 0) {
					echo "$row<td></td><td></td><td></td></tr>\n";
				}
			}
		}
		// print "</tr>\n";
	}
	if ($brief == 0) {
		echo "</table><br/><hr><br/>\n\n";
	}
}

if ($brief == 1) {
	echo "Symbols tested: ", join(',', $arr_symbols), "<br><br>\n";
}
echo "Buys issued on $formatted_date: ", join(',', $buys), "<br><br>\n";
echo "Sells issued on $formatted_date: ", join(',', $sells), "<br><br>\n";
echo "</body>\n</html>\n";
exit;

?>
