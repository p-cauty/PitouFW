<?php
/**
 * Created by PhpStorm.
 * User: peter_000
 * Date: 16/06/2016
 * Time: 19:59
 */

namespace PitouFW\Core;

use ReflectionClass;

abstract class Entity {
    protected int $id = 0;

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $id
     * @return static
     */
    public function setId(int $id): Entity {
        $this->id = $id;
        return $this;
    }

    public static abstract function getTableName(): string;

    private static function getSetterName(string $column): string {
        return 'set'.Utils::fromSnakeCaseToCamelCase($column);
    }

    private static function getGetterName(string $column): string {
        return 'get'.Utils::fromSnakeCaseToCamelCase($column);
    }

    /**
     * @param array $rep
     * @return static
     */
    private static function getFilledObject(array $rep): Entity {
        $classname = get_called_class();
        $res = new $classname();
        foreach ($rep as $key => $value) {
            if (!is_numeric($key)) {
                $setter = self::getSetterName($key);
                $value = (@unserialize($value) === false) ? $value : unserialize($value);
                $res->$setter($value);
            }
        }

        return $res;
    }

    public static function fetchAll(string $cond = '', array $values = []): array {
        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $cond = ($cond != '') ? ' '.$cond : '';
        $req = DB::get()->prepare("SELECT * FROM $table_name$cond");
        $req->execute($values);
        $res = [];
        $i = 0;
        while ($rep = $req->fetch()) {
            $res[$i] = self::getFilledObject($rep);
            $i++;
        }
        $req->closeCursor();

        return $res;
    }

    private function create(bool $show_errors = false): int {
        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $ref = new ReflectionClass($classname);
        $props = $ref->getProperties();

        $columns = [];
        $values = [];
        $qms = [];
        foreach ($props as $prop) {
            $getter = self::getGetterName($prop->getName());

            $val = $this->$getter();
            if ($val === null) {
                continue;
            }

            $columns[] = $prop->getName();
            if (is_array($val) || is_object($val)) {
                $val = serialize($val);
            }

            $values[] = $val;
            $qms[] = '?';
        }
        $columns = implode(', ', $columns);
        $qms = implode(', ', $qms);

        $req = DB::get()->prepare("INSERT INTO $table_name ($columns) VALUES ($qms)");
        $req->execute($values);
        if ($show_errors) {
            var_dump($req->errorInfo());
        }

        return DB::get()->lastInsertId();
    }

    public static function exists(string $column, $value): bool {
        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $req = DB::get()->prepare("SELECT COUNT(*) AS nb FROM $table_name WHERE $column = ?");
        $req->execute([$value]);
        $res = $req->fetch();
        $req->closeCursor();
        return ($res['nb'] > 0);
    }

    /**
     * @param int $id
     * @return static
     */
    public static function read(int $id): Entity {
        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $req = DB::get()->prepare("SELECT * FROM $table_name WHERE id = ?");
        $req->execute([$id]);
        $res = $req->fetch();
        return self::getFilledObject($res);
    }

    /**
     * @param string $column
     * @param string $value
     * @return static
     */
    public static function readBy(string $column, $value): Entity {
        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $req = DB::get()->prepare("SELECT * FROM $table_name WHERE $column = ?");
        $req->execute([$value]);
        $res = $req->fetch();
        $req->closeCursor();
        return self::getFilledObject($res);
    }

    public static function count(string $cond = '', array $values = []): int {
        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $req = DB::get()->prepare("SELECT COUNT(*) AS nb FROM $table_name $cond");
        $req->execute($values);
        $res = $req->fetch();
        $req->closeCursor();
        return $res['nb'];
    }

    private function update(bool $show_errors = false) {
        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $id = $this->getId();
        $ref = new ReflectionClass($classname);
        $props = $ref->getProperties();

        $values = [];
        $qms = [];
        foreach ($props as $prop) {
            $getter = self::getGetterName($prop->getName());
            $val = $this->$getter();
            $qms[] = $prop->getName()." = ?";
            $values[] = $val;
        }
        $qms = implode(', ', $qms);

        $req = DB::get()->prepare("UPDATE $table_name SET $qms WHERE id = ?");
        $req->execute(array_merge($values, [$id]));
        if ($show_errors) {
            var_dump($req->errorInfo());
        }
    }

    public function save(bool $show_errors = false): ?int {
        if ($this->getId() === 0) {
            return $this->create($show_errors);
        } else {
            $this->update($show_errors);
        }

        return null;
    }

    public function delete(): void {
        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $id = $this->getId();
        $req = DB::get()->prepare("DELETE FROM $table_name WHERE id = ?");
        $req->execute([$id]);
    }

    public static function deleteBy(string $column, $value): void {
        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $req = DB::get()->prepare("DELETE FROM $table_name WHERE $column = ?");
        $req->execute([$value]);
    }

    public static function deleteById(int $id): void {
        self::deleteBy('id', $id);
    }
}
