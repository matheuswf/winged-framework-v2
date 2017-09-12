<?php
if (postset("action")) {
    $action = post("action");
    switch ($action) {
        case "install":

            $route = post("route");

            if (!file_exists(PATH_CONFIG)) {
                $std = post("standard");
                $route_content = '<?php
Winged::addroute("./' . $std . '/", [
	"index" => "./' . $std . '.php",
]);';


                $std_content = '<html>
    <head>
        <base href="<?php echo Winged::$protocol ?>"/>
        <meta name="viewport" content="width=device-width,user-scalable=0,initial-scale=0.8"/>
        <link rel="icon" href="./assets/img/fav.png"/>
        <title>Thank you for using Winged</title>
        <script type="text/javascript">var URL = "<?php echo Winged::$protocol ?>";</script>
        <link href="./winged/assets/css/reset.css" type="text/css" rel="stylesheet" charset="utf-8">
        <link href="./winged/assets/css/font-awesome.min.css" type="text/css" rel="stylesheet" charset="utf-8">
        <link href="./winged/assets/css/install.css" type="text/css" rel="stylesheet" charset="utf-8">
    </head>
    <body>
        <div class="bg"></div>
        <div class="black"></div>
        <div class="content">
            <div class="middle">
                <div class="wings"></div>
                <div class="forms">
                    <div id="lg" class="form active">
                        <div class="text">
                            Thank you for using Winged Framework
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>';

                $controller_name = explode('-', str_replace(['-', '_', ' '], '-', $std));
                array_walk($controller_name, function (&$item) {
                    $item = ucfirst($item);
                });
                $controller_name = str_replace(' ', '', implode(' ', $controller_name));

                $controller = '<?php

class ' . $controller_name . 'Controller extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->assets->site();
    }

    public function actionIndex()
    {
        $this->renderHtml("' . $std . '");
    }
}';

                $extra_conf = '<?php

class ExtraConfig
{
    /**
     * Create constants for the peculiarities of your specific project here in this class
     * Here you can also change default settings like WingedConfig::$PROPERTY_NAME, but this is not recommended 
     */
}';


                $content = '<div class="bg"></div>
<div class="black"></div>
<div class="content">
    <div class="middle">
        <div class="wings"></div>
        <div class="forms">
            <div id="lg" class="form active">
                <div class="text">
                    Thank you for using Winged Framework
                </div>
            </div>
        </div>
    </div>
</div>';

                $head_content = '<head>
    <base href="<?php echo Winged::$protocol ?>"/>
    <meta name="viewport" content="width=device-width,user-scalable=0,initial-scale=0.8"/>
    <link rel="icon" href="./assets/img/fav.png"/>
    <title>Thank you for using Winged</title>
    <script type="text/javascript">var URL = "<?php echo Winged::$protocol ?>";</script>
</head>';


                function createFile($path, $content)
                {
                    if (!file_exists($path)) {
                        $handle = fopen($path, "w+");
                        fwrite($handle, $content);
                        fclose($handle);
                    }
                }

                $paths = [
                    "root_route_folder" =>
                        [
                            "path" => "./routes/",
                        ],
                    "root_route_page" =>
                        [
                            "path" => "./routes/" . $std . ".php",
                            "function" => $route_content,
                            "condition" => $route == 1 || $route == 2 ? true : false,
                        ],
                    "root_route_std" =>
                        [
                            "path" => "./routes/routes.php",
                            "function" => $route_content,
                            "condition" => $route == 3 || $route == 4 ? true : false,
                        ],
                    "root_std" =>
                        [
                            "path" => "./" . $std . ".php",
                            "function" => $std_content,
                            "condition" => true,
                        ],
                    "extra_conf" =>
                        [
                            "path" => "./extra.config.php",
                            "function" => $extra_conf,
                            "condition" => true,
                        ],
                    "controllers" =>
                        [
                            "path" => "./controllers/",
                        ],
                    "models" =>
                        [
                            "path" => "./models/",
                        ],
                    "controller_path" =>
                        [
                            "path" => "./controllers/" . $controller_name . 'Controller.php',
                            "function" => $controller,
                            "condition" => true,
                        ],
                    "views" => [
                        "path" => "./views/",
                    ],
                    "views_path" => [
                        "path" => "./views/" . $std . ".php",
                        "function" => $content,
                        "condition" => true,
                    ],
                    "head_content_path" => [
                        "path" => "./head.content.php",
                        "function" => $head_content,
                        "condition" => true,
                    ]
                ];


                foreach ($paths as $info) {
                    $function = array_key_exists_check('function', $info);
                    $condition = array_key_exists_check('condition', $info);
                    $path = array_key_exists_check('path', $info);
                    if ($function) {
                        if ($condition) {
                            createFile($path, $function);
                        }
                    } else {

                        mkdir($path);
                    }
                }

                $dbext = "false";
                if (post("dbext") == 1) {
                    $dbext = "true";
                }

                $config_content = '<?php

/**
 * This file is responsible for the general configuration of the framework
 * all the properties in it are defined and exist. You are free to create
 * constants and properties within the WingedConfig class to use in your
 * project globally at a later time. You can also override the properties
 * within other config.php files. These files must be created inside
 * other directories if you want to overwrite some properties at runtime.
 *
 * For questions about the framework, enable the $SEE_SYSTEM_PAGE option in config.php
 * from the root to true and after enter the link <your_domain_name>/winged/api/
 */

class WingedConfig
{

    /**
     * @property $MAIN_CONTENT_TYPE string
     * set content type in header
     */
    public static $MAIN_CONTENT_TYPE = "text/html";

    /**
     * @property $HTML_CHARSET string
     * set charset for content of files
     */
    public static $HTML_CHARSET = "UTF-8";
    
    /**
     * @property $DEV bool
     * set false when you upload your project to final server
     */
    public static $DEV = true;

    /**
     * @property $DBEXT bool
     * on | off mysql extensions and all class. Inflicts DelegateQuery, QueryBuilder, CurrentDB, Connections, Database, DbDict, Models and Migrate class
     */
    public static $DBEXT = ' . $dbext . ';
    
    /**
     * @property $USE_PREPARED_STMT bool
     * on | off prepared statements
     * view more of prepared statements in
     * <your_domain_name>/winged/what_is_prepared_statement
     */
    public static $USE_PREPARED_STMT = ' . (post('use_stmt') ? post('use_stmt') : '"dbext off in installer"') . ';

    /**
     * @property $DB_DRIVER string
     * defines what type of database your project will use.
     * if your server does not support the PDO class.
     * only mysql will be available for use. To see the availability of classes and functions of your server,
     * go to <your_domain_name>/winged/available#database
     */
    public static $DB_DRIVER = ' . (post('dbtype') ? post('dbtype') : '"dbext off in installer"') . ';

    /**
     * @property $STD_DB_CLASS string
     * defines which class will be used for the interaction between PHP and the database
     */
    public static $STD_DB_CLASS = ' . (post('class') ? post('class') : '"dbext off in installer"') . ';
    
    /**
     * @property $STANDARD string
     * your main and default route for rewrite url
     */
    public static $STANDARD = "' . post('standard') . '";

    /**
     * @property $STANDARD_CONTROLLER string
     * defines the name of your primary controller when no name for controllador was found in the url
     */
    public static $STANDARD_CONTROLLER = "' . post('standard') . '";

    /**
     * @property $CONTROLLER_DEBUG bool
     * on | off erros and warning of main Controller class
     */
    public static $CONTROLLER_DEBUG = true;

    /**
     * @property $PARENT_FOLDER_MVC bool
     * on | off search for better structure MVC folder within folders defined by URL
     * !IMPORTANT: true is recommended, because it enhances the organization of your project
     */
    public static $PARENT_FOLDER_MVC = true;

    /**
     * @property $HEAD_CONTENT_PATH string
     * defines path to include in every page called in any Controller by method renderHtml()
     * this option can be rewrited with method rewriteHeadContentPath() of any Controller
     */
    public static $HEAD_CONTENT_PATH = null;

    /**
     * @property $HOST string
     * defines default server name for mysql connection
     */
    public static $HOST = "' . (post('host') ? post('host') : 'dbext off in installer') . '";

    /**
     * @property $USER string
     * default user name for mysql connection
     */
    public static $USER = "' . (post('user') ? post('user') : 'dbext off in installer') . '";

    /**
     * @property $DBNAME string
     * default database name for mysql connection
     */
    public static $DBNAME = "' . (post('dbname') ? post('dbname') : 'dbext off in installer') . '";

    /**
     * @property $PASSWORD string
     * default password for mysql connection
     */
    public static $PASSWORD = "' . (post('password') ? post('password') : '') . '";

    /**
     * @property $ROUTER string
     * defines the behavior for the treatment of url and folder layout of your project
     * constant PARENT_ROUTES_ROUTE_PHP search parent folder with name "routes" and search file "routes.php" inside this folder
     * constant PARENT_DIR_PAGE_NAME search parent folder with name "routes" and search file "<page from url>.php" inside this folder
     * constant ROOT_ROUTES_PAGE_NAME search folder with name "routes" in level of main "index.php" and search file "<page from url>.php" inside this folder
     * constant ROOT_ROUTES_ROUTE_PHP search folder with name "routes" in level of main "index.php" and search file "routes.php" inside this folder
     */
    public static $ROUTER = PARENT_ROUTES_ROUTE_PHP;

    /**
     * @property $FORCE_NOTFOUND bool
     * ignore errors on the controllers and the routes, always forcing the presentation of the page not found
     */
    public static $FORCE_NOTFOUND = true;

    /**
     * @property $TIMEZONE string
     * sets the time zone used in the entire system
     */
    public static $TIMEZONE = "America/Sao_Paulo";

    /**
     * @property $NOTFOUND string
     * defines the path to the page file not found
     */
    public static $NOTFOUND = "./404.php";

    /**
     * @property $DEBUG bool
     * on | off display errors
     */
    public static $DEBUG = true;

    /**
     * @property $NOT_WINGED bool
     * warning: this option able a not winged view mode.
     * this option read first dir pure-html in root of your project if they exists and if file exists inside it
     * ignores read for controller, restful and rewrite class if file in this dir found
     */
    public static $NOT_WINGED = true;    
    
    /**
     * @property $INTERNAL_ENCODING array
     * this property defines the internal enconding of PHP, it uses [mb] lib
     */
    public static $INTERNAL_ENCODING = "UTF-8";
    
    /**
     * @property $OUTPUT_ENCODING array
     * this property defines the html output enconding, it uses [mb] lib
     */
    public static $OUTPUT_ENCODING = "UTF-8";
    
    /**
     * @var $USE_UNICID_ON_INCLUDE_ASSETS bool
     * On some servers, especially those of productions, it is very common some cache system exists
     * for files that are always loaded on the page as files with the extension * .js, * .css, * .svg and etc..
     * Once they finish The entire production site leave this option as false
     * so that your project loads faster and offers a better end-user experience.
     */
    public static $USE_UNICID_ON_INCLUDE_ASSETS = true;
    
    /**
     * @property $INCLUDES array
     * it includes all paths that are within that variable if they exist and are a valid php file
     * util if you have two classes with same name, and autoload can\'t load these classes 
     */
    public static $INCLUDES = [
        
    ];

    /**
     * below create global variables of your project configuration which are not required by the engine
     */
}
';


                if (!file_exists(PATH_CONFIG)) {
                    $handle = fopen(PATH_CONFIG, "w+");
                    fwrite($handle, $config_content);
                    fclose($handle);
                }

                echo json_encode(array("status" => true, "text" => "Successfully installed."));

            } else {
                echo json_encode(array("status" => false, "text" => "The installation has been completed."));
            }
            break;
        default:
            echo json_encode(array("status" => false, "text" => "Action not found."));
            break;
    }
}
if (method("post")) {
    exit;
}
?>
<html>
<head>
    <base href="<?php echo Winged::$protocol ?>winged/"/>
    <link type="text/css" rel="stylesheet" href="./assets/css/reset.css"/>
    <link type="text/css" rel="stylesheet" href="./assets/css/font-awesome.min.css"/>
    <link type="text/css" rel="stylesheet" href="./assets/css/install.css"/>
    <meta name="viewport" content="width=device-width,user-scalable=0,initial-scale=0.8"/>
    <link rel="icon" href="./assets/img/fav.png"/>
    <title>Winged Install</title>
    <script type="text/javascript">var URL = "<?php echo Winged::$protocol ?>";</script>
</head>
<body>
<div class="bg"></div>
<div class="black"></div>
<div class="content">
    <div class="middle">
        <div class="wings"></div>
        <div class="forms">
            <?php
            if (!file_exists(PATH_CONFIG)) {
                ?>
                <div id="cont" class="form active">
                    <input name="action" type="hidden" value="install">
                    <form>
                        <div class="inp no radio required">
                            <div class="custom-input">
                                <div class="text-in">Route type</div>
                                <div class="control-in clearfix">
                                    <input type="radio" id="route1" name="route" value="1"/>
                                    <label for="route1"><span></span><i>Page name and parent folder.</i></label>
                                </div>
                                <div class="control-in clearfix">
                                    <input type="radio" id="route2" name="route" value="2"/>
                                    <label for="route2"><span></span><i>Page name and root folder.</i></label>
                                </div>
                                <div class="control-in clearfix">
                                    <input type="radio" id="route3" name="route" value="3"/>
                                    <label for="route3"><span></span><i>Route.php and parent folder.</i></label>
                                </div>
                                <div class="control-in clearfix">
                                    <input type="radio" id="route4" name="route" value="4"/>
                                    <label for="route4"><span></span><i>Route.php in root folder.</i></label>
                                </div>
                            </div>
                        </div>
                        <div class="inp no margin radio required">
                            <div class="custom-input">
                                <div class="text-in">Mysql class state</div>
                                <div class="control-in clearfix">
                                    <input type="radio" id="dbext_on" name="dbext" value="1"/>
                                    <label for="dbext_on"><span></span><i>On.</i></label>
                                </div>
                                <div class="control-in clearfix">
                                    <input type="radio" id="dbext_off" name="dbext" value="2"/>
                                    <label for="dbext_off"><span></span><i>Off.</i></label>
                                </div>
                            </div>
                        </div>
                        <div class="inp">
                            <div class="enf"></div>
                            <input autocomplete="off" class="txt" name="standard" type="text"
                                   placeholder="Standard page"/>
                        </div>
                        <div class="btns clearfix">
                            <div class="msg"></div>
                            <div data-to="#db_ext" class="enter to-form btn">Continue</div>
                            <div style="display: none" class="finish btn">Finish</div>
                        </div>
                    </form>
                </div>

                <div id="db_ext" class="form">
                    <form>
                        <div class="inp no radio required">
                            <div class="custom-input">
                                <div class="text-in">Use prepared statements</div>
                                <div class="control-in clearfix">
                                    <input type="radio" id="use_stmt1" name="use_stmt" value="USE_PREPARED_STMT"/>
                                    <label for="use_stmt1"><span></span><i>Yes</i></label>
                                </div>
                                <div class="control-in clearfix">
                                    <input type="radio" id="use_stmt2" name="use_stmt" value="NO_USE_PREPARED_STMT"/>
                                    <label for="use_stmt2"><span></span><i>No</i></label>
                                </div>
                            </div>
                        </div>

                        <div class="inp no radio required">
                            <div class="custom-input">
                                <div class="text-in">DB Connection Class</div>
                                <?php
                                $check = 2;
                                if (class_exists('mysqli')) {
                                    ?>
                                    <div class="control-in clearfix">
                                        <input type="radio" id="ext1" name="class" value="IS_MYSQLI"/>
                                        <label for="ext1"><span></span><i>Mysqli Class</i></label>
                                    </div>
                                    <?php
                                    $check = 1;
                                }

                                if (class_exists('PDO')) {
                                    ?>
                                    <div class="control-in clearfix">
                                        <input type="radio" id="ext2" name="class" value="IS_PDO"/>
                                        <label for="ext2"><span></span><i>PDO Class</i></label>
                                    </div>
                                    <?php
                                    $check = 0;
                                }

                                if ($check == 2) {

                                }
                                ?>
                            </div>
                        </div>

                        <div data-class="IS_MYSQLI" style="display: none" class="none inp no radio">
                            <div class="custom-input">
                                <div class="text-in">DB Type</div>
                                <?php
                                if (class_exists('mysqli')) {
                                    ?>
                                    <div class="control-in clearfix">
                                        <input type="radio" id="dbtype1" name="dbtype" value="DB_DRIVER_MYSQL"/>
                                        <label for="dbtype1"><span></span><i>DB_DRIVER_MYSQL</i></label>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>

                        <div data-class="IS_PDO" style="display: none" class="none inp no margin radio">
                            <div class="custom-input">
                                <div class="text-in">DB Type</div>
                                <?php
                                if (class_exists('PDO')) {
                                    $dd = PDO::getAvailableDrivers();
                                    $drivers = [
                                        'cubrid' => [
                                            'name' => 'DB_DRIVER_CUBRID'
                                        ],
                                        'firebird' => [
                                            'name' => 'DB_DRIVER_FIREBIRD'
                                        ],
                                        'mysql' => [
                                            'name' => 'DB_DRIVER_MYSQL'
                                        ],
                                        'sqlsrv' => [
                                            'name' => 'DB_DRIVER_SQLSRV'
                                        ],
                                        'pgsql' => [
                                            'name' => 'DB_DRIVER_PGSQL'
                                        ],
                                        'sqlite' => [
                                            'name' => 'DB_DRIVER_SQLITE'
                                        ]
                                    ];

                                    foreach ($dd as $name) {
                                        if (array_key_exists($name, $drivers)) {
                                            ?>
                                            <div class="control-in clearfix">
                                                <input type="radio" id="<?= $name ?>" name="dbtype"
                                                       value="<?= $drivers[$name]['name'] ?>"/>
                                                <label
                                                        for="<?= $name ?>"><span></span><i><?= $drivers[$name]['name'] ?></i></label>
                                            </div>
                                            <?php
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="btns clearfix">
                            <div class="msg"></div>
                            <div data-to="#db_auth" class="enter to-form btn">Continue</div>
                            <div data-to="#cont" data-act="back" class="to-form btn">Back</div>
                        </div>
                    </form>
                </div>

                <div id="db_auth" class="form">
                    <form>
                        <div style="display: none" class="none" data-type="DB_DRIVER_MYSQL">
                            <div class="inp">
                                <div class="enf"></div>
                                <input autocomplete="off" class="txt" name="host" type="text"
                                       placeholder="Host"/>
                            </div>
                            <div class="inp">
                                <div class="enf"></div>
                                <input autocomplete="off" class="txt" name="user" type="text"
                                       placeholder="User"/>
                            </div>
                            <div class="inp">
                                <div class="enf"></div>
                                <input autocomplete="off" class="txt" name="password" type="text"
                                       placeholder="Password"/>
                            </div>
                            <div class="inp">
                                <div class="enf"></div>
                                <input autocomplete="off" class="txt" name="dbname" type="text"
                                       placeholder="DB Name"/>
                            </div>
                        </div>
                        <div class="teste-db clearfix">
                            <div class="teste-msg"></div>
                            <span>teste connection<span>
                        </div>
                        <div style="display: none" class="none" data-type="DB_DRIVER_SQLITE">
                            <div class="inp">
                                <div class="enf"></div>
                                <input autocomplete="off" class="txt" name="host" type="text"
                                       placeholder="File Path"/>
                            </div>
                        </div>

                        <div class="btns clearfix">
                            <div class="msg"></div>
                            <div class="enter finish btn">Finish</div>
                            <div data-to="#db_ext" data-act="back" class="to-form btn">Back</div>
                        </div>
                    </form>
                </div>
                <?php
            } else {
                ?>
                <div id="lg" class="form active">
                    <div class="text">
                        This project use Winged Framework
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
</body>
<script src="./assets/js/jquery.js"></script>
<script src="./assets/js/install.js"></script>
</html>