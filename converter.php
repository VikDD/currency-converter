<?php
define('SOURSE_PATH', '');

function converter($value, $from_multiple = 'RUB', $to_multiple = 'RUB', $sourse = '') {
	if (!get_from_cache($from_multiple, $to_multiple)) {
		if (file_exists(SOURSE_PATH.'/'.$sourse.'.php')) {
			include SOURSE_PATH.'/'.$sourse.'.php';
		} else {
			$context = stream_context_create(array(
				'http'=> array(
					'timeout' => 1
				)
			));

			$path = file_get_contents('http://www.cbr-xml-daily.ru/daily.xml', false, $context); // http://www.cbr.ru/scripts/XML_daily.asp

			$xml = new SimpleXMLElement($path);

			if ($from_multiple !== 'RUB') {
				$from_multiple = $xml->xpath("//Valute/CharCode[text()='$from_multiple']/following-sibling::Value");
				$from_multiple = $from_multiple[0];
			}

			if ($to_multiple !== 'RUB') {
				$to_multiple = $xml->xpath("//Valute/CharCode[text()='$to_multiple']/following-sibling::Value");
				$to_multiple = $to_multiple[0];
			}
		}

		set_to_cache($from_multiple, $to_multiple);
	}

	$final_multiple = get_from_cache($from_multiple, $to_multiple);

	return $value * $final_multiple;
}

function get_from_cache($from_multiple, $to_multiple) {
	$memcache_obj = memcache_connect('localhost', 11211);

	$key = md5($from_multiple.$to_multiple);

	return memcache_get($memcache_obj, $key);
}

function set_to_cache($from_multiple, $to_multiple) {
	$memcache_obj = memcache_connect('localhost', 11211);

	$key = md5($from_multiple.$to_multiple);

	$final_multiple = $from_multiple / $to_multiple;

	memcache_set($memcache_obj, $key, $final_multiple, 0, 43200);
}
?>