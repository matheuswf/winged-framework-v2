<?phpuse Winged\Http\Session;use Winged\Http\Cookie;use Winged\Winged;use Winged\Validator\Validator;/** * Class Login */class Login extends \Usuarios{    /**     * @var $lastUser Login     */    public $repeat;    /**     * @var null | Usuarios     */    private static $lastUser = null;    public function initSession()    {        Session::always('TYPE', $this->session_namespace);        Session::always('ID_USUARIO', $this->primaryKey());        Session::always('EMAIL', $this->email);        Session::always('NOME', $this->nome);        Cookie::always('TYPE', $this->session_namespace, 7);        Cookie::always('ID_USUARIO', $this->primaryKey(), 7);        Cookie::always('EMAIL', $this->email, 7);        Cookie::always('NOME', $this->nome, 7);    }    /**     * @return Login | Usuarios     */    public static function current()    {        if (self::$lastUser !== null) {            return self::$lastUser;        }        return (new Login());    }    /**     * @return bool     */    public static function currentIsAdm()    {        if (self::$lastUser !== null) {            return self::$lastUser->session_namespace === 'ADM' ? true : false;        }        return false;    }    /**     * @param array $ignore     *     * @return bool     */    public static function permission($ignore = [])    {        if (in_array(Winged::$controller_action, $ignore)) return true;        if (($login = self::viewSession()) !== false) {            return $login;        }        return false;    }    /**     * @return bool     */    public static function permissionAdm()    {        if (($login = self::viewSession()) !== false) {            if ($login->session_namespace == 'ADM') {                return $login;            }        }        return false;    }    /**     * @return bool | Login     */    private static function makeNewUser()    {        if (Session::get('ID_USUARIO') || Cookie::get('ID_USUARIO')) {            $id = Session::get('ID_USUARIO') !== false ? Session::get('ID_USUARIO') : Cookie::get('ID_USUARIO');            if ($id) {                $login = new Login();                $login->autoLoadDb($id);                if ($login->primaryKey()) {                    self::$lastUser = $login;                    return $login;                }            }        }        return false;    }    /**     * @return bool|Login     */    private static function viewSession()    {        if (Session::get('ID_USUARIO')) {            Cookie::always('TYPE', Session::get('TYPE'), 7);            Cookie::always('ID_USUARIO', Session::get('ID_USUARIO'), 7);            Cookie::always('EMAIL', Session::get('EMAIL'), 7);            Cookie::always('NOME', Session::get('NOME'), 7);        }        if (Cookie::get('ID_USUARIO')) {            Session::always('TYPE', Cookie::get('TYPE'));            Session::always('ID_USUARIO', Cookie::get('ID_USUARIO'));            Session::always('EMAIL', Cookie::get('EMAIL'));            Session::always('NOME', Cookie::get('NOME'));        }        if (Session::get('ID_USUARIO') || Cookie::get('ID_USUARIO')) {            if (($login = self::makeNewUser()) !== false) {                return $login;            }        }        return false;    }    /**     * @return string     */    public static function getLetters()    {        $exp = explode(' ', self::current()->nome);        $name = '';        $x = 0;        foreach ($exp as $nam) {            $name .= trim($nam)[0];            $x++;            if ($x === 1) {                break;            }        }        return $name;    }    /**     *     */    public static function destroySession()    {        Session::remove('TYPE');        Session::remove('ID_USUARIO');        Session::remove('EMAIL');        Session::remove('NOME');        Cookie::remove('TYPE');        Cookie::remove('ID_USUARIO');        Cookie::remove('EMAIL');        Cookie::remove('NOME');    }    /**     * @return array     */    public function behaviors()    {        return [            'senha' => function () {                if ($this->senha != "") {                    return md5($this->senha);                }                $this->unload('senha');                $this->senha = null;            },            'repeat' => function () {                if ($this->repeat != "") {                    return md5($this->repeat);                }                return null;            },        ];    }    /**     * @return array     */    public function rules()    {        return [            'email' => [                'required' => true,                'email' => true,            ],            'senha' => [                'required' => true,                'length' => [                    function ($senha, $length) {                        if (Session::get('action') == 'insert') {                            return Validator::lengthLargerOrEqual($this->backup('senha'), $length);                        }                        return true;                    },                    [                        6                    ]                ]            ],            'repeat' => [                'safe'            ],        ];    }    /**     * @return array     */    public function messages()    {        return [            'email' => [                'required' => 'Esse campo é obrigatório',                'email' => 'Insira um e-mail válido',            ],            'senha' => [                'required' => 'Esse campo é obrigatório',                'length' => 'Esse campo deve ter no minimo 6 caracteres',            ]        ];    }    /**     * @return array     */    public function labels()    {        return [        ];    }}