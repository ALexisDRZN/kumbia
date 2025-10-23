<?php
/**
 * SimpleUploader - implementación minimalista para subir archivos
 * Guarda en: public/storage/<model>/<id>.<ext>
 *
 * NOTAS:
 * - No usa Logger ni arrays en logs (usa error_log si es necesario).
 * - Devuelve ruta relativa tipo "storage/model/id.ext" o false en error.
 */

class SimpleUploader
{
    // Ruta base pública (usada para devolver URLs)
    private static function publicBase()
    {
        // dirname($_SERVER['SCRIPT_FILENAME']) normalmente apunta a public/
        return rtrim(dirname($_SERVER['SCRIPT_FILENAME']), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    private static function storageBase()
    {
        return self::publicBase() . 'storage' . DIRECTORY_SEPARATOR;
    }

    // Sube el archivo. Recibe $_FILES['campo'] y la instancia del modelo (con id)
    public static function upload(array $file, $instance)
    {
        // Validaciones básicas
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        $model = strtolower(get_class($instance));
        $id = $instance->id ?? null;
        if (!$id) {
            return false; // necesita id
        }

        // extensión segura (sanitizar)
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $ext = strtolower(preg_replace('/[^a-z0-9]+/i', '', $ext));
        if ($ext === '') return false;

        // Validar extensiones permitidas
        $allowed = ['jpg','jpeg','png','gif','webp','pdf'];
        if (!in_array($ext, $allowed)) {
            return false;
        }

        // Tamaño máximo (ejemplo: 5MB imágenes, 10MB pdf)
        $max = in_array($ext, ['pdf']) ? 10*1024*1024 : 5*1024*1024;
        if (isset($file['size']) && $file['size'] > $max) {
            return false;
        }

        $dir = self::storageBase() . $model . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                error_log("SimpleUploader: no se pudo crear directorio $dir");
                return false;
            }
        }

        // Nombre final: id.ext (reemplaza archivo anterior)
        $safeName = $id . '.' . $ext;
        $dest = $dir . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            error_log("SimpleUploader: move_uploaded_file falló dest=$dest");
            return false;
        }
        @chmod($dest, 0644);

        // devolver ruta relativa pública (para guardar en BD o usar en vistas)
        $relative = 'storage/' . $model . '/' . $safeName;
        return $relative;
    }

    // Devuelve URL pública (o null)
    public static function getUrl($instance)
    {
        $model = strtolower(get_class($instance));
        $id = $instance->id ?? null;
        if (!$id) return null;

        $pattern = self::storageBase() . $model . DIRECTORY_SEPARATOR . $id . '.*';
        $files = glob($pattern);
        if (empty($files)) return null;

        $relative = 'storage/' . $model . '/' . basename($files[0]);
        return rtrim(dirname($_SERVER['SCRIPT_FILENAME']), '/') . '/' . $relative;
    }

    // Comprueba existencia
    public static function exists($instance)
    {
        $model = strtolower(get_class($instance));
        $id = $instance->id ?? null;
        if (!$id) return false;
        $pattern = self::storageBase() . $model . DIRECTORY_SEPARATOR . $id . '.*';
        $files = glob($pattern);
        return !empty($files);
    }

    // Elimina archivo si existe
    public static function delete($instance)
    {
        $path = self::getPath($instance);
        if ($path && file_exists($path)) {
            return unlink($path);
        }
        return false;
    }

    // Path absoluto del archivo en disco (internal)
    private static function getPath($instance)
    {
        $model = strtolower(get_class($instance));
        $id = $instance->id ?? null;
        if (!$id) return null;
        $pattern = self::storageBase() . $model . DIRECTORY_SEPARATOR . $id . '.*';
        $files = glob($pattern);
        return !empty($files) ? $files[0] : null;
    }
}