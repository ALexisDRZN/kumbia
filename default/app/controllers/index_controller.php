<?php
class IndexController extends AppController
{
    public function index()
    {
        $this->subtitle = 'Medición';
        $this->title = 'Última Medición';
        $this->medicion = (new Mediciones)->find_first("order: id DESC");
    }

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

    public function login()
    {
        View::template('login');

        if (Input::hasPost('usuario')) {
            $usuario = Input::post('usuario');

            // Verifica que ambos campos estén llenos
            if (empty($usuario['email']) || empty($usuario['password'])) {
                Flash::error('Debes ingresar tu email y contraseña');
                return;
            }

            // Busca usuario por email
            $user = (new Usuarios)->find_first("email = '{$usuario['email']}'");

            if (!$user) {
                Flash::error('El email ingresado no está registrado');
                return;
            }

            // Verifica la contraseña
            if (!password_verify($usuario['password'], $user->password)) {
                Flash::error('La contraseña es incorrecta');
                return;
            }
            Session::set('user_id', $user->id);
            Session::set('nombre_usuario', $user->username);
            $this->nombreUsuarioReal = $user->username;

            return Redirect::to('index/');

        }

    }

    public function register()
    {
        View::template('register');

        if (Input::hasPost('usuario')) {
            $usuario = new Usuarios(Input::post('usuario'));

            // Validaciones
            if (empty($usuario->username) || empty($usuario->email) || empty($usuario->password)) {
                Flash::error('Todos los campos son obligatorios');
                return;
            }
            if (!filter_var($usuario->email, FILTER_VALIDATE_EMAIL)) {
                Flash::error('El email no es válido');
                return;
            }
            if (!Input::post('terminos')) {
                Flash::error('Debes aceptar los términos');
                return;
            }

            Flash::info("Email recibido: '" . $usuario->email . "'");

            $usuario->email = trim($usuario->email);

            if (empty($usuario->email)) {
                Flash::error('El email es obligatorio');
                return;
            }

            $existe = (new Usuarios)->find_first("email = '{$usuario->email}'");
            if ($existe) {
                Flash::error('El email ya está registrado');
                return;
            }

            // Hashea la contraseña
            $usuario->password = password_hash($usuario->password, PASSWORD_DEFAULT);

            if ($usuario->save()) {
                //Flash::success('¡Registro exitoso!');
                return Redirect::to('/index/login/');
            } else {
                Flash::error('No se pudo registrar. Intenta de nuevo.');
            }
        }
    }

    public function recupera()
    {
        View::template('recupera');
    }

    public function logout()
    {
        if (method_exists('Session', 'delete')) {
            Session::delete('nombre_usuario');
        } else {
            if (isset($_SESSION['nombre_usuario'])) {
                unset($_SESSION['nombre_usuario']);
            }
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        @session_destroy();
        return Redirect::to('index/');
    }
}