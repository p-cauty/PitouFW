<?php

namespace PitouFW\Core;

use ReflectionClass;
use ReflectionProperty;

class Utils {
    /**
     * @param string $string
     * @return string
     */
    public static function fromSnakeCaseToCamelCase(string $string): string {
		return preg_replace_callback("#_([a-z0-9])#", function (array $matches): string {
			return strtoupper($matches[1]);
		}, ucfirst($string));
	}

    /**
     * @param $data
     * @return array|mixed|string
     * @throws \ReflectionException
     */
    public static function secure($data) {
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$data[$k] = self::secure($data[$k]);
			}
        } elseif (is_object($data)) {
			foreach ($data as $k => $v) {
				$data->$k = self::secure($data->$k);
			}
			$classname = get_class($data);
			$ref = new ReflectionClass($classname);
			$props = $ref->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED);
			foreach ($props as $prop) {
				$getter = 'get'.self::fromSnakeCaseToCamelCase($prop->getName());
				$setter = 'set'.self::fromSnakeCaseToCamelCase($prop->getName());
				if (method_exists($data, $getter) && method_exists($data, $setter)) {
					$data->$setter(self::secure($data->$getter()));
				}
			}
        } else {
			$data = htmlentities($data);
        }

        return $data;
    }

    /**
     * @param string $string
     * @return string
     */
    public static function str2hex(string $string): string {
        $hex = '';
        for ($i=0; $i<strlen($string); $i++){
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0'.$hexCode, -2);
        }
        return $hex;
    }

    /**
     * @param string $hex
     * @return string
     */
    public static function hex2str(string $hex): string {
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }

    /**
     * @param string $string
     * @param string $delimiter
     * @return string
     */
    public static function slugify(string $string, string $delimiter = '-'): string
    {
        $oldLocale = setlocale(LC_ALL, '0');
        setlocale(LC_ALL, 'en_US.UTF-8');
        $clean = strtr(utf8_decode($string), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $clean);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower($clean);
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
        $clean = trim($clean, $delimiter);
        setlocale(LC_ALL, $oldLocale);
        return $clean;
    }

    /**
     * @return bool
     */
    public static function isInternalCall(): bool
    {
        return isset($_SERVER['HTTP_X_ACCESS_TOKEN']) && $_SERVER['HTTP_X_ACCESS_TOKEN'] === INTERNAL_API_KEY;
    }

    /**
     * @param string $ip
     * @return string
     */
    public static function expandIPV6(string $ip): string {
        $is_ipv6 = str_contains($ip, ':');
        if (!$is_ipv6) {
            return $ip;
        }

        $hex = bin2hex(inet_pton($ip));
        return implode(':', str_split($hex, 4));
    }

    /**
     * @param string $ip
     * @param int $blocksCnt
     * @return string
     */
    public static function truncateIPV6(string $ip, int $blocksCnt): string {
        $is_ipv6 = str_contains($ip, ':');
        if (!$is_ipv6) {
            return $ip;
        }

        $full_length_ip = self::expandIPV6($ip);
        $blocks = explode(':', $full_length_ip);
        return implode(':', array_slice($blocks, 0, count($blocks) - $blocksCnt));
    }

    /**
     * @param string $ip
     * @return string
     */
    public static function slugifyIp(string $ip): string {
        $is_ipv6 = str_contains($ip, ':');
        return $is_ipv6 ?
            str_replace(':', '_', $ip) :
            str_replace('.', '_', $ip);
    }

    /**
     * @param string $ip
     * @return string
     */
    public static function parseIpForAntispam(string $ip): string {
        return self::slugifyIp(self::truncateIPV6($ip, 4));
    }

    /**
     * @param int $length
     * @return string|null
     */
    public static function generateToken(int $length = 64): ?string {
        if ($length % 4 !== 0) {
            return null;
        }

        $bytes_number = 0.75 * $length;
        return str_replace('+', '', str_replace('/', '', base64_encode(openssl_random_pseudo_bytes($bytes_number))));
    }

    /**
     * @param int|null $time
     * @return string
     */
    public static function datetime(?int $time = null): string {
        return date('Y-m-d H:i:s', $time === null ? time() : $time);
    }
}
