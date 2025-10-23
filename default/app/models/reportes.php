<?php
// Modelo mínimo para la tabla `reportes`
class Reportes extends ActiveRecord
{
    public function initialize()
    {

        $this->belongs_to('usuarios', 'model: usuarios', 'fk: usuario_id');
        $this->belongs_to('zonas', 'model: zonas', 'fk: zona_id');
    }
}