<?php

/**
 * Controller por defecto si no se usa el routes
 *
 */
class IndexController extends AppController
{

    public function index()
    {
        $this->subtitle = 'Medición';
        $this->title = 'Última Medición';
        $this->medicion = (new Mediciones)->find_first("order: id DESC");

    }

    // ---------- AGREGA ESTE MÉTODO ----------
    public function datos_grafica()
    {
        $fecha_limite = date('Y-m-d H:i:s', strtotime('-1 day'));
        $mediciones = (new Mediciones)->find("conditions: fecha_hora >= '$fecha_limite'", "order: fecha_hora ASC");

        $max_ph = 14;
        $max_turbidez = 100;
        $max_temp = 40;
        $max_nivel = 1;
        $max_conductividad = 3000;

        $grafica = [
            'ph' => [],
            'turbidez' => [],
            'conductividad' => [],
            'temperatura' => [],
            'nivel_agua' => [],
            'fechas' => [],
        ];

        foreach ($mediciones as $m) {
            $grafica['ph'][] = round(($m->ph / $max_ph) * 100, 2);
            $grafica['turbidez'][] = round(($m->turbidez / $max_turbidez) * 100, 2);
            $grafica['conductividad'][] = round(($m->conductividad / $max_conductividad) * 100, 2);
            $grafica['temperatura'][] = round(($m->temperatura / $max_temp) * 100, 2);
            $grafica['nivel_agua'][] = round(($m->nivel_agua / $max_nivel) * 100, 1);
            $grafica['fechas'][] = $m->fecha_hora;
        }

        header('Content-Type: application/json');
        echo json_encode($grafica);
        exit;
    }
    // -----------------------------------------

    public function login()
    {
        View::template('login');
    }
    public function register()
    {
        View::template('register');
    }
    public function recupera()
    {
        View::template('recupera');
    }
}