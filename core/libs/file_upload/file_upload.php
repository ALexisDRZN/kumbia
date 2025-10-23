<?php
/**
 * KumbiaPHP web & app Framework
 *
 * @category   Kumbia
 * @package    FileUpload
 */

/**
 * Clase para manejo de subida de archivos
 */
class FileUpload
{
    /**
     * Directorio base de storage
     */
    private static $storageDir = null;

    /**
     * Inicializa el directorio de storage
     */
    private static function init()
    {
        if (self::$storageDir === null) {
            // Usar ruta absoluta del directorio público
            $publicDir = dirname($_SERVER['SCRIPT_FILENAME']) . '/';
            self::$storageDir = $publicDir . 'storage/';

            Logger::info('FileUpload: Inicializando rutas', [
                'PUBLIC_PATH' => PUBLIC_PATH,
                'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'],
                'public_dir' => $publicDir,
                'storage_dir' => self::$storageDir,
                'storage_exists' => is_dir(self::$storageDir)
            ]);

            if (!is_dir(self::$storageDir)) {
                $created = mkdir(self::$storageDir, 0755, true);
                Logger::info('FileUpload: Creando directorio storage base', [
                    'directory' => self::$storageDir,
                    'created' => $created,
                    'error' => $created ? null : error_get_last()
                ]);
            }
        }
    }

    /**
     * Sube un archivo asociado a una instancia de modelo
     *
     * @param array $file Archivo del formulario ($_FILES['campo'])
     * @param object $instance Instancia del modelo
     * @return string|false Ruta del archivo o false si falla
     */
    public static function upload($file, $instance)
    {
        Logger::info('FileUpload: Iniciando subida de archivo', [
            'file' => $file
        ]);

        if (!isset($file['tmp_name'])) {
            Logger::error('FileUpload: No se encontró tmp_name en el archivo');
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            Logger::error('FileUpload: Error en la subida', [
                'error_code' => $file['error'],
                'error_message' => self::getUploadErrorMessage($file['error'])
            ]);
            return false;
        }

        self::init();

        $modelClass = strtolower(get_class($instance));
        $id = $instance->id;

        Logger::info('FileUpload: Datos del modelo', [
            'modelClass' => $modelClass,
            'id' => $id
        ]);

        if (!$id) {
            Logger::error('FileUpload: El modelo no tiene ID');
            return false;
        }

        // Eliminar archivo anterior si existe
        self::delete($instance);

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = $id . '.' . $extension;

        $targetDir = self::$storageDir . $modelClass . '/';
        $targetPath = $targetDir . $fileName;

        Logger::info('FileUpload: Rutas calculadas', [
            'targetDir' => $targetDir,
            'targetPath' => $targetPath,
            'fileName' => $fileName
        ]);

        if (!is_dir($targetDir)) {
            Logger::info('FileUpload: Verificando antes de crear directorio', [
                'storage_dir' => self::$storageDir,
                'target_dir' => $targetDir,
                'storage_exists' => is_dir(self::$storageDir),
                'parent_dir' => dirname($targetDir),
                'parent_exists' => is_dir(dirname($targetDir))
            ]);

            // Normalizar ruta para Windows
            $normalizedDir = str_replace('/', DIRECTORY_SEPARATOR, $targetDir);

            $created = mkdir($normalizedDir, 0755, true);
            Logger::info('FileUpload: Creando directorio', [
                'directory' => $targetDir,
                'normalized' => $normalizedDir,
                'created' => $created,
                'last_error' => error_get_last()
            ]);

            if (!$created) {
                Logger::error('FileUpload: No se pudo crear el directorio', [
                    'directory' => $targetDir,
                    'normalized' => $normalizedDir,
                    'storage_dir' => self::$storageDir,
                    'last_error' => error_get_last()
                ]);
                return false;
            }
        }

        Logger::info('FileUpload: Intentando mover archivo', [
            'from' => $file['tmp_name'],
            'to' => $targetPath,
            'tmp_exists' => file_exists($file['tmp_name']),
            'target_dir_writable' => is_writable($targetDir)
        ]);

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            Logger::info('FileUpload: Archivo subido exitosamente', [
                'path' => $targetPath,
                'size' => filesize($targetPath)
            ]);
            return $modelClass . '/' . $fileName;
        }

        Logger::error('FileUpload: Falló move_uploaded_file', [
            'tmp_name' => $file['tmp_name'],
            'target_path' => $targetPath,
            'tmp_exists' => file_exists($file['tmp_name']),
            'target_dir_exists' => is_dir($targetDir),
            'target_dir_writable' => is_writable($targetDir)
        ]);

        return false;
    }

    /**
     * Obtiene la ruta completa de un archivo
     *
     * @param object $instance Instancia del modelo
     * @return string|null
     */
    public static function getPath($instance)
    {
        self::init();

        $modelClass = strtolower(get_class($instance));
        $id = $instance->id;

        if (!$id) {
            return null;
        }

        $pattern = self::$storageDir . $modelClass . '/' . $id . '.*';
        $files = glob($pattern);

        return !empty($files) ? $files[0] : null;
    }

    /**
     * Obtiene la URL pública de un archivo
     *
     * @param object $instance Instancia del modelo
     * @return string|null
     */
    public static function getUrl($instance)
    {
        $modelClass = strtolower(get_class($instance));
        $id = $instance->id;

        if (!$id) {
            return null;
        }

        $pattern = dirname($_SERVER['SCRIPT_FILENAME']) . '/storage/' . $modelClass . '/' . $id . '.*';
        $files = glob($pattern);

        if (!empty($files)) {
            $relativePath = 'storage/' . $modelClass . '/' . basename($files[0]);
            return PUBLIC_PATH . $relativePath;
        }

        return null;
    }

    /**
     * Elimina un archivo
     *
     * @param object $instance Instancia del modelo
     * @return boolean
     */
    public static function delete($instance)
    {
        $filePath = self::getPath($instance, $fieldName);

        if ($filePath && file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    /**
     * Verifica si existe un archivo
     *
     * @param object $instance Instancia del modelo
     * @return boolean
     */
    public static function exists($instance)
    {
        $filePath = self::getPath($instance, $fieldName);
        return $filePath && file_exists($filePath);
    }

    /**
     * Obtiene el mensaje de error de subida
     *
     * @param int $errorCode
     * @return string
     */
    private static function getUploadErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'El archivo excede upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede MAX_FILE_SIZE del formulario',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta directorio temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir archivo en disco',
            UPLOAD_ERR_EXTENSION => 'Extensión de PHP detuvo la subida'
        ];

        return isset($errors[$errorCode]) ? $errors[$errorCode] : 'Error desconocido: ' . $errorCode;
    }
}