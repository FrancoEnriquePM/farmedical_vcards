<?php

require_once("../../../vendor/autoload.php");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Usuario
{
    private $conexion;
    private $nombre_tabla = "usuario";

    public $id;
    public $nombre;
    public $apellido;
    public $usuario;
    public $contrasena;
    public $rol;
    public $creado_en;

    public function __construct($db)
    {
        $this->conexion = $db;
    }

    // Crear usuario
    public function crearUsuario()
    {
        $query = "INSERT INTO " . $this->nombre_tabla . " SET nombre=:nombre, apellido=:apellido, usuario=:usuario, contrasena=:contrasena, rol=:rol";
        $stmt = $this->conexion->prepare($query);

        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->usuario = htmlspecialchars(strip_tags($this->usuario));
        $this->contrasena = htmlspecialchars(strip_tags($this->contrasena));
        $this->rol = htmlspecialchars(strip_tags($this->rol));

        // Bind
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":usuario", $this->usuario);
        $stmt->bindParam(":contrasena", $this->contrasena);
        $stmt->bindParam(":rol", $this->rol);

        if ($stmt->execute()) {
            return true;
        }
        echo $stmt->execute();
        return false;
    }

    // Leer usuarios
    public function listarUsuarios()
    {
        $query = "SELECT id, nombre, apellido, usuario, creado_en, rol FROM " . $this->nombre_tabla;
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function eliminarUsuario()
    {
        $query = "DELETE FROM " . $this->nombre_tabla . " WHERE id=:id";
        $stmt = $this->conexion->prepare($query);

        // Limpiar dato
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function actualizarUsuario()
    {
        $query = "UPDATE " . $this->nombre_tabla . " SET nombre=:nombre, apellido=:apellido, usuario=:usuario, contrasena=:contrasena, rol=:rol WHERE id=:id";
        $stmt = $this->conexion->prepare($query);

        // Limpiar datos
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->usuario = htmlspecialchars(strip_tags($this->usuario));
        $this->contrasena = htmlspecialchars(strip_tags($this->contrasena));
        $this->rol = htmlspecialchars(strip_tags($this->rol));

        // Bind
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":usuario", $this->usuario);
        $stmt->bindParam(":contrasena", $this->contrasena);
        $stmt->bindParam(":rol", $this->rol);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function verificarUsuario()
    {
        $query = "SELECT id, nombre, contrasena, rol FROM " . $this->nombre_tabla . " WHERE usuario = :usuario LIMIT 1";
        $stmt = $this->conexion->prepare($query);

        // Limpiar datos
        $this->usuario = htmlspecialchars(strip_tags($this->usuario));

        // Bind
        $stmt->bindParam(":usuario", $this->usuario);

        try {
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // decodificamos la contraseña rescatada
                $contrasenaDecodificada = base64_decode($this->contrasena);

                // Verificar contraseña con hash
                if (password_verify($contrasenaDecodificada, $usuario['contrasena'])) {
                    // Generar el token JWT
                    $key = "auth_token_para_farmedical_vcards"; // Asegúrate de mantener esta clave segura y privada
                    $payload = [
                        "iat" => time(), // Hora en que se emitió
                        "exp" => time() + (60 * 60), // Expira en 1 hora
                        "data" => [
                            "id" => $usuario['id'],
                            "nombre" => $usuario['nombre'],
                            "rol" => $usuario['rol']
                        ]
                    ];

                    $jwt = JWT::encode($payload, $key, 'HS256');

                    return [
                        "nombre" => $usuario['nombre'],
                        "rol" => $usuario['rol'],
                        "token" => $jwt
                    ];
                } else {
                    return false; // Contraseña incorrecta
                }
            } else {
                return false; // Usuario no encontrado
            }
        } catch (PDOException $e) {
            throw new Exception("Error al verificar el usuario: " . $e->getMessage());
        }
    }
}
