<?php

use Utilitools\VirtualObject;

class DummyVO
{
    use VirtualObject;

    public string $f_id = 'id';

    public string $f_name = 'name';

    public string $f_role = 'role';

    public string $tableName = 'dummy';

    /**
     * creates table for virtual object existence
     *
     * @return  DummyVO
     */
    public
    function createTable () : DummyVO
    {
        $this->database->query(
            "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
                `{$this->f_id}` INTEGER PRIMARY KEY AUTOINCREMENT,
                `{$this->f_name}` TEXT NOT NULL,
                `{$this->f_role}` TEXT NOT NULL
            )"
        );

        return $this;
    }
}
