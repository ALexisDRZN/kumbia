<?php
class GraficasController extends AppController
{
    public function index()
    {
        $this->mediciones = (new Mediciones())->find("order: fecha_hora DESC");

        $fecha_hoy = date('Y-m-d');
        $fecha_90_dias = date('Y-m-d', strtotime('-60 days'));

        $medicion_mas_antigua = (new Mediciones)->find_first("order: fecha_hora ASC");
        if ($medicion_mas_antigua) {
            $fecha_mas_antigua = date('Y-m-d', strtotime($medicion_mas_antigua->fecha_hora));
            $fecha_inicio = ($fecha_mas_antigua > $fecha_90_dias) ? $fecha_mas_antigua : $fecha_90_dias;
        } else {
            $fecha_inicio = $fecha_90_dias;
        }

        $mediciones = (new Mediciones)->find("conditions: fecha_hora >= '$fecha_inicio' AND fecha_hora <= '$fecha_hoy'", "order: fecha_hora ASC");

        $grafica = [
            'ph' => [],
            'turbidez' => [],
            'conductividad' => [],
            'temperatura' => [],
            'nivel_agua' => [],
            'fechas' => [],
        ];

        $meses = [
            'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr',
            'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
            'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic'
        ];
        $inicio_pretty = date('d M, Y', strtotime($fecha_inicio));
        $hoy_pretty = date('d M, Y', strtotime($fecha_hoy));
        foreach ($meses as $ing => $esp) {
            $inicio_pretty = str_replace($ing, $esp, $inicio_pretty);
            $hoy_pretty = str_replace($ing, $esp, $hoy_pretty);
        }
        $this->rango_fechas = "$inicio_pretty - $hoy_pretty";

        $max_ph = 14;
        $max_turbidez = 100;
        $max_conductividad = 3000;
        $max_temp = 40;
        $max_nivel = 1;

        foreach ($mediciones as $m) {
            $grafica['ph'][] = round(($m->ph / $max_ph) * 100, 2);
            $grafica['turbidez'][] = round(($m->turbidez / $max_turbidez) * 100, 2);
            $grafica['conductividad'][] = round(($m->conductividad / $max_conductividad) * 100, 2);
            $grafica['temperatura'][] = round(($m->temperatura / $max_temp) * 100, 2);
            $grafica['nivel_agua'][] = round(($m->nivel_agua / $max_nivel) * 100, 2);
            // Formato eje X
            $date = date('M-d', strtotime($m->fecha_hora));
            foreach ($meses as $ing => $esp) {
                $date = str_replace($ing, $esp, $date);
            }
            $grafica['fechas'][] = $date;
        }

        $this->grafica = json_encode($grafica);
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_hoy;
        $this->mediciones = $mediciones;


        $ultimo_index = count($grafica['ph']) - 1;
        $ph_percent = $grafica['ph'][$ultimo_index];
        $turbidez_percent = $grafica['turbidez'][$ultimo_index];
        $conductividad_percent = $grafica['conductividad'][$ultimo_index];
        $temperatura_percent = $grafica['temperatura'][$ultimo_index];
        $nivel_agua_percent = $grafica['nivel_agua'][$ultimo_index];

        $this->ph_percent = $ph_percent;
        $this->turbidez_percent = $turbidez_percent;
        $this->conductividad_percent = $conductividad_percent;
        $this->temperatura_percent = $temperatura_percent;
        $this->nivel_agua_percent = $nivel_agua_percent;
    }
}