<?php
class ReportesController extends AppController
{
    public function index()
    {
        $this->subtitle = 'Reportes';
        $this->title    = 'Nuevo reporte';
        $this->zonas    = (new Zonas)->find();

        if (!Input::hasPost('reporte')) return;

        $p = Input::post('reporte');

        $r = new Reportes();
        $r->usuario_id  = (empty($p['anonimo']) && Session::get('usuario_id')) ? Session::get('usuario_id') : null;
        $r->zona_id     = (int)($p['zona_id'] ?? 0);
        $r->contexto    = mb_substr(trim($p['contexto'] ?? ''), 0, 255);
        $r->causa       = mb_substr(trim($p['causa'] ?? ''), 0, 255);
        $r->ubicacion   = $p['ubicacion'] ?? null;
        if (property_exists($r, 'lat')) $r->lat = $p['lat'] ?? null;
        if (property_exists($r, 'lng')) $r->lng = $p['lng'] ?? null;
        $r->fecha_hora  = $p['fecha_hora'] ?? date('Y-m-d H:i:s');

        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $nombre = 'r_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
            $dir = APP_PATH . 'public' . DS . 'uploads' . DS . 'reportes' . DS;
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $nombre)) {
                $r->foto = $nombre;
            }
        }

        if ($r->create()) {
            return Redirect::to('index/reportes/');
        }

        Flash::error('No se pudo guardar el reporte');
    }
}