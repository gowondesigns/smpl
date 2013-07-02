<?php
/* SMPL Database Classes
// 
//
//*/


static class Database
{
    private static $mainDatabaseInstance = null;
    
    
    public static function Create($databaseType = null)
    {
        if (null === $databaseType) {
            $this->mainDatabaseInstance = new Language($languageCode);
        }

        return $this->mainDatabaseInstance;
    }

}


interface iDatabase
{
    public function Foo();
    public function Bar();
}

abstract class aDatabase
{
    private $foobar;
}

class MySqlDatabase extends aDatabase implements iDatabase
{
    public function Foo();
    public function Bar();
}

?>
