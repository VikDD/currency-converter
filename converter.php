<?php
define('SOURSE_PATH', '');

function converter($value, $from_currency = 'RUB', $to_currency = 'RUB', $sourse = '') {
	$from_currency_multiple = 1;
	$to_currency_multiple = 1;
	$final_multiple = get_from_cache($from_currency, $to_currency);
	if ($final_multiple) {
		$ext_file = SOURSE_PATH.'/'.$sourse;
		if (file_exists($ext_file)) {
			include $ext_file;
		} else {
			$context = stream_context_create(array(
				'http' => array(
					'timeout' => 1
				)
			));

			$url = 'http://www.cbr-xml-daily.ru/daily.xml'; // http://www.cbr.ru/scripts/XML_daily.asp

			$path = file_get_contents($url, false, $context);

			$xml = new SimpleXMLElement($path);

			if ($from_currency !== 'RUB') {
				$from_currency_multiple = $xml->xpath("//Valute/CharCode[text()='$from_currency']/following-sibling::Value");
				$from_currency_multiple = $from_currency_multiple[0];
			}

			if ($to_currency !== 'RUB') {
				$to_currency_multiple = $xml->xpath("//Valute/CharCode[text()='$to_currency']/following-sibling::Value");
				$to_currency_multiple = $to_currency_multiple[0];
			}
		}

		set_to_cache($from_currency, $to_currency, $from_currency_multiple, $to_currency_multiple);
	}	

	return $value * $final_multiple;
}

function get_from_cache($from_currency, $to_currency) {
	$memcache_obj = memcache_connect('localhost', 11211);

	$key = md5($from_currency.' '.$to_currency);

	return memcache_get($memcache_obj, $key);
}

function set_to_cache($from_currency, $to_currency, $from_currency_multiple, $to_currency_multiple) {
	$memcache_obj = memcache_connect('localhost', 11211);

	$key = md5($from_currency.' '.$to_currency);

	$final_multiple = $from_currency_multiple / $to_currency_multiple;
	memcache_set($memcache_obj, $key, $final_multiple, 0, 43200);
}
?>
