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

        $fecha_limite = date('Y-m-d H:i:s', strtotime('-3 months'));
        $mediciones = (new Mediciones)->find("conditions: fecha_hora >= '$fecha_limite'", "order: fecha_hora ASC");

        // Definimos valores máximos para normalizar
        $max_ph = 14; // pH máximo
        $max_turbidez = 100; // Turbidez máxima esperada
        $max_temp = 40; // Temperatura máxima esperada (°C)
        $max_nivel = 2; // Nivel de agua máxima esperada (m)
        $max_conductividad = 3000; // Conductividad máxima esperada (µS/cm)

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
            $grafica['nivel_agua'][] = round(($m->nivel_agua / $max_nivel) * 100, 2);
            $grafica['fechas'][] = $m->fecha_hora;
        }

        $this->grafica = json_encode($grafica);
    }


    public function login()
    {
        View::template('login'); // <--- Esto es lo importante
    }
    public function register()
    {
        View::template('register'); // <--- Esto es lo importante
    }
    public function recupera()
    {
        View::template('recupera'); // <--- Esto es lo importante
    }

}
