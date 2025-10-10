<?php
class Grafica extends ActiveRecord
{
    // Ejemplo: un método para recuperar datos de la BD o procesar información
    public static function datos()
    {
        // Retorna un array con datos de ejemplo
        return [
            ['label' => 'Enero', 'valor' => 30],
            ['label' => 'Febrero', 'valor' => 40],
            ['label' => 'Marzo', 'valor' => 25],
        ];
    }
}