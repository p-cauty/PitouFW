<?php


namespace PitouFW\Core;


abstract class PermanentEntity extends Entity {
    protected int $deleted = 0;

    /**
     * @return int
     */
    public function getDeleted(): int {
        return $this->deleted;
    }

    /**
     * @param int $deleted
     * @return PermanentEntity
     */
    public function setDeleted(int $deleted): PermanentEntity {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @param string $cond
     * @param array $values
     * @return static[]
     */
    public static function fetchAll(string $cond = '', array $values = []): array {
        $cond = str_starts_with($cond, "WHERE") ?
            preg_replace("/^WHERE\s/", "WHERE deleted = 0 AND ", $cond) :
            "WHERE deleted = 0 " . $cond;
        return parent::fetchAll($cond, $values);
    }

    /**
     * @param string $column
     * @param $value
     * @return bool
     */
    public static function exists(string $column, $value): bool {
        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $req = DB::get()->prepare("SELECT COUNT(*) AS nb FROM $table_name WHERE $column = ? AND deleted = 0");
        $req->execute([$value]);
        $res = $req->fetch();
        $req->closeCursor();
        return ($res['nb'] > 0);
    }

    /**
     * @param int|null $id
     * @return static|null
     */
    public static function read(?int $id): ?Entity {
        if ($id === null) {
            return null;
        }

        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $req = DB::get()->prepare("SELECT * FROM $table_name WHERE id = ? AND deleted = 0");
        $req->execute([$id]);
        $res = $req->fetch();
        return $res !== false ? self::getFilledObject($res) : null;
    }

    /**
     * @param string $column
     * @param string $value
     * @return static
     */
    public static function readBy(string $column, $value): ?Entity {
        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $req = DB::get()->prepare("SELECT * FROM $table_name WHERE $column = ? AND deleted = 0");
        $req->execute([$value]);
        $res = $req->fetch();
        $req->closeCursor();
        return $res !== false ? self::getFilledObject($res) : null;
    }

    /**
     * @param string $column
     * @param string $value
     * @return static
     */
    public static function readByOrdered(string $column, $value, string $order = 'ASC'): ?Entity {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $req = DB::get()->prepare("SELECT * FROM $table_name WHERE $column = ? AND deleted = 0 ORDER BY id $order");
        $req->execute([$value]);
        $res = $req->fetch();
        $req->closeCursor();
        return $res !== false ? self::getFilledObject($res) : null;
    }

    public static function count(string $cond = '', array $values = []): int {
        $cond = str_starts_with($cond, "WHERE") ?
            preg_replace("/^WHERE\s/", "WHERE deleted = 0 AND ", $cond) :
            "WHERE deleted = 0 " . $cond;
        return parent::count($cond, $values);
    }

    public function delete(bool $hard = false, bool $show_errors = false): void {
        if ($hard) {
            parent::delete();
            return;
        }

        $this->setDeleted(1)
            ->save($show_errors, true);
    }

    /**
     * @param string $column
     * @param $value
     */
    public static function deleteBy(string $column, $value): void {
        $classname = get_called_class();
        $table_name = $classname::getTableName();
        $req = DB::get()->prepare("UPDATE $table_name SET deleted = 1 WHERE $column = ?");
        $req->execute([$value]);
    }

    /**
     * @param int $id
     */
    public static function deleteById(int $id): void {
        self::deleteBy('id', $id);
    }
}