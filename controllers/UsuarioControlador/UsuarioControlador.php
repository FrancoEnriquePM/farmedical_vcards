<?php
require_once '../../../config/BaseDeDatos.php';
require_once '../../../models/Usuario.php';

class UsuarioControlador
{
    private $db;
    private $usuario;

    public function __construct()
    {
        $database = new BaseDeDatos();
        $this->db = $database->llamarConexion();
        $this->usuario = new Usuario($this->db);
    }

    // Crear usuario
    public function crearUsuario()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($data->password == $data->confirmPassword) {
            $this->usuario->nombre = $data->nombre;
            $this->usuario->apellido = $data->apellido;
            $this->usuario->usuario = $data->usuario;
            $this->usuario->contrasena = password_hash($data->password, PASSWORD_DEFAULT); // Hashing de contraseña
            $this->usuario->rol = $data->rol;

            try {
                if ($this->usuario->crearUsuario()) {
                    echo json_encode(["message" => "Usuario creado."]);
                } else {
                    echo json_encode(["message" => "No se pudo crear el usuario."]);
                }
            } catch (PDOException $e) {
                echo json_encode(["message" => "Error al crear usuario: " . $e->getMessage()]);
            }
        } else {
            echo json_encode(["message" => "Las contraseñas no coinciden."]);
        }
    }

    // Leer usuarios
    public function listarTodo()
    {
        $stmt = $this->usuario->listarUsuarios();
        $usuarios_arr = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $usuario_item = ["id" => $id, "nombre" => $nombre, "apellido" => $apellido, "usuario" => $usuario, "creado_en" => $creado_en, "rol" => $rol];
            array_push($usuarios_arr, $usuario_item);
        }
        echo json_encode($usuarios_arr);
    }

    public function eliminarUsuario()
    {
        $data = json_decode(file_get_contents("php://input"));
        $this->usuario->id = $data->id;

        if ($this->usuario->eliminarUsuario()) {
            echo json_encode(["message" => "Usuario eliminado."]);
        } else {
            echo json_encode(["message" => "No se pudo eliminar el usuario."]);
        }
    }

    public function actualizarUsuario()
    {
        $data = json_decode(file_get_contents("php://input"));

        if ($data->password == $data->confirmPassword) {
            $this->usuario->id = $data->id;
            $this->usuario->nombre = $data->nombre;
            $this->usuario->apellido = $data->apellido;
            $this->usuario->usuario = $data->usuario;
            $this->usuario->contrasena = password_hash($data->password, PASSWORD_DEFAULT); // Hashing de contraseña
            $this->usuario->rol = $data->rol;

            try {
                if ($this->usuario->actualizarUsuario()) {
                    echo json_encode(["message" => "Usuario actualizado."]);
                } else {
                    echo json_encode(["message" => "No se pudo actualizar el usuario."]);
                }
            } catch (PDOException $e) {
                // Captura la excepción si el correo ya existe
                if ($e->getCode() == 23000) {  // Código 23000 corresponde a la violación de integridad
                    echo json_encode(["message" => "El correo ya existe."]);
                } else {
                    // Otras excepciones
                    echo json_encode(["message" => "Error al actualizar usuario: " . $e->getMessage()]);
                }
            }
        } else {
            echo json_encode(["message" => "Las contraseñas no coinciden."]);
        }
    }

    // Crear usuario
    public function verificarCredenciales()
    {
        $data = json_decode(file_get_contents("php://input"));
        $this->usuario->usuario = $data->usuario;
        $this->usuario->contrasena = $data->contrasena;

        try {
            $resultado = $this->usuario->verificarUsuario();

            if ($resultado) {
                // Usuario encontrado y contraseña correcta
                echo json_encode([
                    "message" => "Usuario encontrado.",
                    "usuario" => $resultado // Devuelve datos como ID y nombre
                ]);
            } else {
                echo json_encode([
                    "message" => "Usuario o contraseña incorrectos."
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                "message" => "Error al verificar credenciales.",
                "error" => $e->getMessage()
            ]);
        }
    }
}
