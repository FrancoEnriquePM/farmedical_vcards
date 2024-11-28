<?php
require __DIR__ . '../../phpqrcode/qrlib.php';
require_once '../../../config/BaseDeDatos.php';
require_once '../../../models/Vcard.php';

class VcardControlador
{
    private $db;
    private $vcard;

    public function __construct()
    {
        $database = new BaseDeDatos();
        $this->db = $database->llamarConexion();
        $this->vcard = new Vcard($this->db);
    }

    public function generarVcard($name, $phone, $email)
    {
        // Sanitizar los datos de entrada para evitar inyección de código
        $name = htmlspecialchars($name);
        $phone = htmlspecialchars($phone);
        $email = htmlspecialchars($email);

        // Verificar si el número de teléfono tiene un formato válido
        if (!preg_match("/^\+?[0-9]+$/", $phone)) {
            // Si el número de teléfono no tiene un formato válido, devuelve un mensaje de error
            return "Error: El número de teléfono tiene un formato no válido.";
        }

        // Verificar si el correo electrónico tiene un formato válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Si el correo electrónico no tiene un formato válido, devuelve un mensaje de error
            return "Error: El correo electrónico tiene un formato no válido.";
        }

        // Generar el contenido vCard
        $vcard = "BEGIN:VCARD\n";
        $vcard .= "VERSION:3.0\n";
        $vcard .= "FN:$name\n";
        $vcard .= "TEL;TYPE=CELL:$phone\n";
        $vcard .= "EMAIL;TYPE=INTERNET:$email\n";
        $vcard .= "END:VCARD\n";

        return $vcard;
    }

    public function crearVcard()
    {
        $data = json_decode(file_get_contents("php://input"));

        $this->vcard->nombre = $data->nombre;
        $this->vcard->paterno = $data->paterno;
        $this->vcard->materno = $data->materno;
        $this->vcard->telefono = $data->telefono;
        $this->vcard->correo = $data->correo;

        try {
            // Generar el contenido vCard
            $vcardContent = $this->generarVcard($data->nombre . ' ' . $data->paterno . ' ' . $data->materno, $data->telefono, $data->correo);
            // Ruta donde se guardará el archivo QR
            $qrPath = 'qr_vcards/';
            // Nombre del archivo QR
            $qrFileName = $data->nombre . '_' . $data->paterno . '_' . $data->materno . '.png';
            $this->vcard->vcard = $qrFileName;
            // Ruta completa del archivo QR
            $qrFilePath = $qrPath . $qrFileName;
            // Generar el código QR si no hay errores
            if (strpos($vcardContent, 'Error:') === false) {
                // Generar el código QR
                
                QRcode::png($vcardContent, $qrFilePath, QR_ECLEVEL_Q, 9, 4);

                if ($this->vcard->crearVcard()) {
                    echo json_encode(["message" => "Vcard creado."]);
                } else {
                    echo json_encode(["message" => "No se pudo crear el vcard."]);
                }
            } else {
                // Mostrar el mensaje de error si hubo algún problema con los datos de entrada
                echo json_encode(["message" => $vcardContent]);
                exit; // Terminar la ejecución del script
            }
        } catch (PDOException $e) {
            echo json_encode(["message" => "Error al crear vcard: " . $e->getMessage()]);
        }
    }

    // Listar todas las Vcards
    public function listarTodasLasVcards()
    {
        $stmt = $this->vcard->listarVcards();
        $vcards_arr = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $vcard_item = ["id" => $id, "nombre" => $nombre, "paterno" => $paterno, "materno" => $materno, "telefono" => $telefono, "correo" => $correo, "vcard" => $vcard];
            array_push($vcards_arr, $vcard_item);
        }
        echo json_encode($vcards_arr);
    }

    // Eliminar una vcard
    public function eliminarVcard()
    {
        $data = json_decode(file_get_contents("php://input"));
        $this->vcard->id = $data->id;
        $this->vcard->vcard = $data->vcard;

        if (file_exists("qr_vcards/$data->vcard") && unlink("qr_vcards/$data->vcard")) {
            if ($this->vcard->eliminarVcard()) {
                echo json_encode(["message" => "Vcard eliminada."]);
            } else {
                echo json_encode(["message" => "No se pudo eliminar el registro de la vCard en la base de datos."]);
            }
        } else {
            echo json_encode(["message" => "No se pudo eliminar el archivo vCard."]);
        }
    }

    // Actualizar una vcard
    public function actualizarVcard()
    {
        $data = json_decode(file_get_contents("php://input"));
        $this->vcard->id = $data->id;
        $this->vcard->nombre = $data->nombre;
        $this->vcard->paterno = $data->paterno;
        $this->vcard->materno = $data->materno;
        $this->vcard->telefono = $data->telefono;
        $this->vcard->correo = $data->correo;

        try {
            // Generar el contenido vCard
            $vcardContent = $this->generarVcard($data->nombre . ' ' . $data->paterno . ' ' . $data->materno, $data->telefono, $data->correo);
            // Ruta donde se guardará el archivo QR
            $qrPath = 'qr_vcards/';
            // Nombre del archivo QR
            $qrFileName = $data->nombre . '_' . $data->paterno . '_' . $data->materno . '.png';
            $this->vcard->vcard = $qrFileName;
            // Ruta completa del archivo QR
            $qrFilePath = $qrPath . $qrFileName;
            // Generar el código QR si no hay errores
            if (strpos($vcardContent, 'Error:') === false) {
                if (file_exists("qr_vcards/$data->vcard_anterior") && unlink("qr_vcards/$data->vcard_anterior")) {
                    // Generar el código QR
                    QRcode::png($vcardContent, $qrFilePath, QR_ECLEVEL_Q, 9, 4);

                    if ($this->vcard->actualizarVcard()) {
                        echo json_encode(["message" => "Vcard actualizada."]);
                    } else {
                        echo json_encode(["message" => "No se pudo actualizar la vcard."]);
                    }
                } else {
                    echo json_encode(["message" => "No se pudo eliminar el archivo vCard."]);
                }
            } else {
                // Mostrar el mensaje de error si hubo algún problema con los datos de entrada
                echo json_encode(["message" => $vcardContent]);
                exit; // Terminar la ejecución del script
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
    }
}
