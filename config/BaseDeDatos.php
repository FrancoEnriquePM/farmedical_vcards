<?php
class BaseDeDatos
{
    private $host = "localhost";
    private $db_name = "farmedical_vcards";
    private $username = "root";
    private $password = "";
    public $conexion;

    public function llamarConexion()
    {
        $this->conexion = null;
        try {
            $this->conexion = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conexion->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conexion;
    }
}
