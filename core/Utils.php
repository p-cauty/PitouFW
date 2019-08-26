<?php

namespace PitouFW\Core;

use ReflectionClass;
use ReflectionProperty;

class Utils {
	public static function time(): int {
		$jet_lag = 0;
		return time() + (3600 * $jet_lag); 
	}

	public static function fromSnakeCaseToCamelCase(string $string): string {
		return preg_replace_callback("#_([a-z0-9])#", function (array $matches): string {
			return strtoupper($matches[1]);
		}, ucfirst($string));
	}

	public static function secure($data) {
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$data[$k] = self::secure($data[$k]);
			}
			return $data;
		}
		elseif (is_object($data)) {
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
			return $data;
		}
		elseif (!is_string($data)) {
			return $data;
		}
		else {
			$data = htmlentities($data);
			return $data;
		}
	}

    public static function str2hex($string) {
        $hex = '';
        for ($i=0; $i<strlen($string); $i++){
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0'.$hexCode, -2);
        }
        return $hex;
    }

    public static function hex2str($hex) {
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }
}