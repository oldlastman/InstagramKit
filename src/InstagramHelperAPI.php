<?php namespace Riedayme\InstagramKit;

Class InstagramHelperAPI
{

	public static function curl($url, $postdata = 0, $header = 0, $cookie = 0, $useragent = 0) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		if($header) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		}
		if($postdata) {
			curl_setopt($ch, CURLOPT_POST, 1);
			if ($postdata != 'empty') {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			}
		}

		if($cookie) {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		if ($useragent) {
			curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		}

		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch);
		if(!$httpcode) {
			curl_close($ch);	
			die("Response header not found"); 
		}
		else{

			$header = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
			$body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));

			curl_close($ch);

			return [
				'header' => $header,
				'body' => $body
			];
		}
	}	
	
	public static function generateDeviceId($seed)
	{
		$volatile_seed = filemtime(__DIR__);
		return 'android-'.substr(md5($seed.$volatile_seed), 16);
	}

	public static function generateSignature($data)
	{
		// ac129560d96023898d85aff6ee861218ff504ab34848a09747a3f0987439de0f
		$hash = hash_hmac('sha256', $data, 'b4946d296abf005163e72346a6d33dd083cadde638e6ad9c5eb92e381b35784a');
		return 'ig_sig_key_version=4&signed_body='.$hash.'.'.urlencode($data);
	}

	public static function generateSignatureUpload($data)
	{
		$hash = hash_hmac('sha256', $data, 'd80795fe4fb5080840d1338043cabd92e473528d434beb4eaceecc02e264843a');

		return 'ig_sig_key_version=4&signed_body='.$hash.'.'.urlencode($data);
	}

	public static function generateUUID($type)
	{
		$uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);

		return $type ? $uuid : str_replace('-', '', $uuid);
	}

    /**
     * Calculates Java hashCode() for a given string.
     *
     * WARNING: This method is not Unicode-aware, so use it only on ANSI strings.
     *
     * @param string $string
     *
     * @return int
     *
     * @see https://en.wikipedia.org/wiki/Java_hashCode()#The_java.lang.String_hash_function
     */
    public static function hashCode(
    	$string)
    {
    	$result = 0;
    	for ($i = 0, $len = strlen($string); $i < $len; ++$i) {
    		$result = (-$result + ($result << 5) + ord($string[$i])) & 0xFFFFFFFF;
    	}
    	if (PHP_INT_SIZE > 4) {
    		if ($result > 0x7FFFFFFF) {
    			$result -= 0x100000000;
    		} elseif ($result < -0x80000000) {
    			$result += 0x100000000;
    		}
    	}

    	return $result;
    }	

    /**
     * Reorders array by hashCode() of its keys.
     *
     * @param array $data
     *
     * @return array
     */
    public static function reorderByHashCode(
        array $data)
    {
        $hashCodes = [];
        foreach ($data as $key => $value) {
            $hashCodes[$key] = self::hashCode($key);
        }

        uksort($data, function ($a, $b) use ($hashCodes) {
            $a = $hashCodes[$a];
            $b = $hashCodes[$b];
            if ($a < $b) {
                return -1;
            } elseif ($a > $b) {
                return 1;
            } else {
                return 0;
            }
        });

        return $data;
    }
}