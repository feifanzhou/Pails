<?php
  // http://webcheatsheet.com/PHP/get_current_page_url.php
  function current_page_name() {
    return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
  }
  function current_page_path() {
    return $_SERVER["REQUEST_URI"];
  }
  function current_page_URL() {
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
      $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } 
    else {
      $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
  }

  function json_success() {     // http://stackoverflow.com/a/2887675/472768
    $json = '{ "success": "1" }';
    echo $_POST['callback'] . '(' . $json . ')';
  }

  // TODO: Clean up duplicate code
  function show_json($value, $skip_fields=[]) {
    $r = [];  // Always return a JSON array, even if it's one element
    // The view always expects a list of things to display
    if (is_array($value)) { // If an array of objects is passed in
      foreach ($value as $v) {  // Loop through each object
        $kv = [];   // Turn object into associative array
        foreach ($v as $k => $value)  // Get object's properties as key-value pairs
          if (!in_array($k, $skip_fields))  // If key is not in $skip_fields
            $kv[$k] = $value;   // Add key-value pair to associative array
        $r[] = $kv; // Add object to final array
      }
    }
    else {  // Do everthing like above, but no main array to loop through
      $kv = [];
      foreach ($value as $k => $v)
        if (!in_array($k, $skip_fields))
          $kv[$k] = $v;
      $r[] = $kv;
    }
    return json_encode($r);
  }

  function sql_date_to_timestamp($sql_date) {
    // http://stackoverflow.com/a/4577805/472768
    return strtotime($sql_date);
  }
  
  function get_curl($url) {  
    if(function_exists('curl_init')) {  
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_URL,$url);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
        curl_setopt($ch, CURLOPT_HEADER, 0);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);   
        $output = curl_exec($ch);  
        echo curl_error($ch);  
        curl_close($ch);  
        return $output;  
    } else{  
        return file_get_contents($url);  
    }
  }
	
	function parse_signed_request($signed_request, $secret) {
	  list($encoded_sig, $payload) = explode('.', $signed_request, 2); 
	
	  // decode the data
	  $sig = base64_url_decode($encoded_sig);
	  $data = json_decode(base64_url_decode($payload), true);
	
	  if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
		error_log('Unknown algorithm. Expected HMAC-SHA256');
		return null;
	  }
	
	  // Adding the verification of the signed_request below
	  $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
	  if ($sig !== $expected_sig) {
		error_log('Bad Signed JSON signature!');
		return null;
	  }
	
	  return $data;
	}
	
	function base64_url_encode($input) {
	  return strtr(base64_encode($input), '+/=', '-_,');
	}
	
	function base64_url_decode($input) {
	  return base64_decode(strtr($input, '-_,', '+/='));
	}
	
	function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
	{
		$str = '';
		$count = strlen($charset);
		while ($length--) {
			$str .= $charset[mt_rand(0, $count-1)];
		}
		return $str;
	}
	
	function get_string_between($string, $start, $end){
		$string = " ".$string;
		$ini = strpos($string,$start);
		if ($ini == 0) return "";
		$ini += strlen($start);
		$len = strpos($string,$end,$ini) - $ini;
		return substr($string,$ini,$len);
	}
	
	function xmlEscape($string) {
		return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $string);
	}
	
	function uctitle($string) {
		$donotcap = array('a','an','and','any','as','at','aboard','about','above','across','after','against','along','amid','among','around','but','by','before','behind','below','beneath','beside','besides','between','beyond','concerning','considering','despite','down','during','else','except','excluding','for','following','from','if','in','inside','into','like','minus','nor','near','of','on','or','off','opposite','outside','over','past','per','plus','regarding','round','so','save','since','than','the','through','to','toward','towards','under','underneath','unlike','until','up','upon','versus','via','with','within','without');
		$string =strtolower($string);
		$words = explode(' ', $string);
		foreach ($words as $key => $word){
			if ($key == 0 || !in_array($word, $donotcap)) 
				$words[$key] = ucwords($word);
		}
		$string = implode(' ', $words);
		return $string;
	} 
?>