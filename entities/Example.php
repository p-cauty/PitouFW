<?php
/**
 * Created by PhpStorm
 * User: peter_000
 * Date: 18/11/2018
 * Time: 14:19
 */
namespace PitouFW\Entity;

use PitouFW\Core\Resourceable;

class Example implements Resourceable, \JsonSerializable {
	private $id;
	private $name;
	private $secret;

	public function __construct($id = 0, $name = '', $secret = '') {
		$this->id = $id;
		$this->name = $name;
		$this->secret = $secret;
	}

    function jsonSerialize() {
        $it = clone $this;
        unset($it->secret);
        return get_object_vars($it);
    }

    public static function getTableName(): string {
		return 'example';
	}

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id) {
        $this->id = $id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getSecret(): string {
        return $this->secret;
    }

    public function setSecret(string $secret) {
        $this->secret = $secret;
    }
}
