<?php

class Database
{
    private static ?PDO $instance = null;

    private string $host = 'localhost';
    private string $dbname = 'rydenyte';
    private string $username = 'RydenyteAdministratorOfficial@1';
    private string $password = 'Orbit-Cactus7!Velvet&Harbor92@WorkIsHardStuff';
    private string $charset = 'utf8mb4';

    private function __construct() {}

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $db = new self();

            $dsn = "mysql:host={$db->host};dbname={$db->dbname};charset={$db->charset}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
            ];

            try {
                self::$instance = new PDO(
                    $dsn,
                    $db->username,
                    $db->password,
                    $options
                );
            } catch (PDOException $e) {
                die("db connection failed.");
            }
        }

        return self::$instance;
    }
}