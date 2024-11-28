<?php
class Vcard
{
    private $conexion;
    private $nombre_tabla = "vcard";

    public $id;
    public $nombre;
    public $paterno;
    public $materno;
    public $telefono;
    public $correo;
    public $vcard;
    public $creado_en;

    public function __construct($db)
    {
        $this->conexion = $db;
    }

    // Crear vcard
    public function crearVcard()
    {
        $query = "INSERT INTO " . $this->nombre_tabla . " SET nombre=:nombre, paterno=:paterno, materno=:materno, telefono=:telefono, correo=:correo, vcard=:vcard";
        $stmt = $this->conexion->prepare($query);

        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->paterno = htmlspecialchars(strip_tags($this->paterno));
        $this->materno = htmlspecialchars(strip_tags($this->materno));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->vcard = htmlspecialchars(strip_tags($this->vcard));

        // Bind
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":paterno", $this->paterno);
        $stmt->bindParam(":materno", $this->materno);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":vcard", $this->vcard);

        if ($stmt->execute()) {
            return true;
        }
        echo $stmt->execute();
        return false;
    }

    // Leer vcards
    public function listarVcards()
    {
        $query = "SELECT * FROM " . $this->nombre_tabla;
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Eliminar vcards
    public function eliminarVcard()
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

    // Actualizar vcard
    public function actualizarVcard()
    {
        $query = "UPDATE " . $this->nombre_tabla . " SET nombre=:nombre, paterno=:paterno, materno=:materno, telefono=:telefono, correo=:correo, vcard=:vcard WHERE id=:id";
        $stmt = $this->conexion->prepare($query);

        // Limpiar datos
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->paterno = htmlspecialchars(strip_tags($this->paterno));
        $this->materno = htmlspecialchars(strip_tags($this->materno));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->vcard = htmlspecialchars(strip_tags($this->vcard));

        // Bind
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":paterno", $this->paterno);
        $stmt->bindParam(":materno", $this->materno);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":vcard", $this->vcard);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>