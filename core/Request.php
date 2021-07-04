<?php

namespace PitouFW\Core;

class Request {
	private static ?Request $instance = null;
	private array $args;
	private ?string $lang = null;

	private function __construct() {
		$this->args = isset($_GET['arg']) ? explode('/', $_GET['arg']) : ['home'];
        $this->args = isset($GLOBALS['argc']) ? array_slice($GLOBALS['argv'], 1) : $this->args;

        if (in_array($this->args[0], ACCEPTED_LANGUAGES)) {
            $this->lang = $this->args[0];
            unset($this->args[0]);
            $this->args = array_values($this->args);

            if (count($this->args) === 0 || $this->args[0] === '') {
                $this->args[0] = 'home';
            }
        }
	}

	public static function get(): Request {
		if (self::$instance == null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function getArg(int $i): string {
		if (count($this->args) > $i) {
			return $this->args[$i];
		}

		return '';
	}

    public function getArgs(): array {
        return $this->args;
    }

    public function getLang(): ?string {
        return $this->lang;
	}

    public function getRoute(bool $with_lang = false): string {
        if ($with_lang) {
            return '/' . $_GET['arg'];
        }

        $route = '/' . implode('/', $this->getArgs());
        return preg_replace("#^\/home#", '/', $route);
    }
}

function webroot(): string {
    $lang_route = Request::get()->getLang() !== null ? Request::get()->getLang() . '/' : '';
    return WEBROOT . $lang_route;
}
