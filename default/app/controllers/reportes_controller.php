<?php
class ReportesController extends AppController
{
    // Lista simple de reportes y zonas para la vista
    public function index()
    {
        //$reportes = Load::model('reportes')->find($id);
        $this->reportes = (new Reportes)->find("order: id DESC");
        // cargar zonas si existe el modelo
        $this->zonas = class_exists('Zonas') ? (new Zonas)->find() : [];
    }

    public function guardar()
    {

        $params = Input::post('reporte');

        $isAnon = isset($params['anonimo']) && ((string)$params['anonimo'] === '1' || (string)$params['anonimo'] === 'on');


        $userId = Session::get('user_id');
        if (!$userId) {
            Flash::error('Debes iniciar sesión.');
            return Redirect::to('index/login/');
        }
        $params['usuario_id'] = $userId;

        if ($isAnon) {
            $params['user_2'] = Session::get('nombre_usuario');
            $params['reportado_por'] = 'Anonimo';
        } else {
            $params['user_2'] = $userId;
            if (isset($params['reportado_por'])) {
                unset($params['reportado_por']);
            }
        }
        /*
        if (empty($params['lat']) || empty($params['lng'])) {
            Flash::error('Ubicación requerida.');
            return Redirect::to('/reportes');
        }
        */
        if (empty($params['zona_id'])) {
            Flash::error('Debes seleccionar una zona.');
            return Redirect::to('/reportes/');
        }

        $reporte = new Reportes($params);
        if (!$reporte->create()) {
            Flash::error('No se pudo crear el reporte.');
            return Redirect::to('/reportes/');
        }

        require_once CORE_PATH . 'libs/file_uploader/simple_uploader.php';


        if (!empty($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $rutaPublica = SimpleUploader::upload($_FILES['archivo'], $reporte);
            if ($rutaPublica) {
                // opcional: guardar la ruta relativa o basename en la BD
                $reporte->foto = basename($rutaPublica); // o $rutaPublica si prefieres
                $reporte->save();
                Flash::info('Archivo subido correctamente.');
            } else {
                Flash::warning('La subida falló. Revisa permisos, extensión o tamaño.');
            }
        }


        Flash::valid('Reporte guardado correctamente.');
        return Redirect::to('/reportes/');
    }
}