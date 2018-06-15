<?php

namespace app\models;


use Exception;

class UploadException extends Exception
{
    public function __construct($code) {
        $message = $this->codeToMessage($code);
        parent::__construct($message, $code);
    }

    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                //$message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                $message = "El archvo es demasiado grande, asegurese de que no tenga filas en blanco al final del archivo, revise con block de notas";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                //$message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                $message = "El archivo es demasiado grande para subir via formulario, contacte al soporte";
                break;
            case UPLOAD_ERR_PARTIAL:
                //$message = "The uploaded file was only partially uploaded";
                $message = "Hubo un problema al cargar, solo se ha cargado el archivo parcialmente";
                break;
            case UPLOAD_ERR_NO_FILE:
                //$message = "No file was uploaded";
                $message = "El archivo no se pudo subir, contacte a soporte";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                //$message = "Missing a temporary folder";
                $message = "No se encuentra la carpeta temporal, contacte a soporte";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                //$message = "Failed to write file to disk";
                $message = "No se pudo guardar el archivo, contacte a soporte";
                break;
            case UPLOAD_ERR_EXTENSION:
                //$message = "File upload stopped by extension";
                $message = "Se detuvo la carga producto de una extension";
                break;

            default:
                //$message = "Unknown upload error";
                $message = "Error Desconocido, contacte a soporte";
                break;
        }
        return $message;
    }
}