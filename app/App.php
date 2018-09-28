<?php
namespace codingninjas;

use \Exception;

class App
{
    /**
     * Instance of App
     * @var null
     */
    public static $instance = null;

    /**
     * Plugin main file
     * @var
     */
    public static $main_file;

    /**
     * Path to app folder
     * @var string
     */
    public static $app_path;

    /**
     * Url to app folder
     * @var string
     */
    public static $app_url;

    /**
     * Current route
     * @var
     */
    public static $route;

    /**
     * App constructor.
     * @param $main_file
     */
    public function __construct($main_file)
    {
        self::$main_file = $main_file;
        self::$app_path = dirname ($main_file).'/app';
        self::$app_url = plugin_dir_url( $main_file ).'app';
        spl_autoload_register(array(&$this, 'autoloader'));

        $this->initRoute();
        $this->initActions();
        $this->initFilters();
    }

    /** Run App
     * @param $main_file
     * @return App|null
     */
    public static function run($main_file)
    {
        if (!self::$instance) {
            self::$instance = new self($main_file);
        }

        return self::$instance;
    }

    /**
     * Init current route
     */
    private function initRoute()
    {
        $route = $_SERVER['REQUEST_URI'];
        $params =  $_SERVER['QUERY_STRING'];

        if ($params) {
            $route = str_replace ('?'.$params, '', $route);
        }

        $route = trim ($route, '/');

        $wp_home_url = home_url ();
        $components = parse_url($wp_home_url);
        $wp_instance_path = '';
        if (array_key_exists ('path', $components)) {
            $wp_instance_path = $components['path'];
            $wp_instance_path = trim ($wp_instance_path, '/');
        }

        if ($wp_instance_path) {
            $len = mb_strlen ($wp_instance_path);
            $route = substr($route, $len);
            $route = trim ($route, '/');
        }

        self::$route = $route;
    }

    /**
     * Init wp actions
     */
    private function initActions()
    {
        add_action( 'init', array(&$this, 'onInitPostTypes'));

        if ($this->hasRoute (self::$route)) {
            add_action('template_redirect', array(&$this, 'onDisplayRoutesContent'), 0, 0);
            add_action( 'wp_enqueue_scripts', array($this, 'onClearThemeStyles'), 20);
            add_action( 'wp_enqueue_scripts', array($this, 'onClearThemeStyles'), 20);
            add_action( 'wp_enqueue_scripts', array($this, 'onInitScripts'), 21);
            add_action( 'wp_enqueue_scripts', array($this, 'onInitStyles'), 21);
            add_action( 'after_setup_theme', array($this, 'onRemoveAdminBar'), 10);
        }
        add_action( 'admin_notices', array($this, 'onDisplayAdminNotice'));
    }

   /**
     * Display notice in admin
     */
    public function onDisplayAdminNotice()
    {
        echo self::view(
            ['admin', 'notice.php'],
            [
                'logo' => self::$app_url.'/assets/images/logo.png'
            ]
       );
    }

    /**
     * Init wp filters
     */
    private function initFilters()
    {
        if (!$this->hasRoute (self::$route)) {
            return;
        }

        add_filter('template_include', array($this, 'blockDefaultTemplates'));
        add_action( 'body_class', array($this, 'onRemoveBodyErrorClass'), 20);
    }
    /**
     * Remove admin bar
     */
    public function onRemoveAdminBar()
    {
        show_admin_bar(false);
   }


    /**
     * Remove error class from plugin routes
     * @param $classes
     * @return mixed
     */
    public function onRemoveBodyErrorClass($classes)
    {
        foreach ($classes as $key => $class) {
            if ($class == 'error404') {
                unset($classes[$key]);
            }
        }

        return $classes;
    }

    /**
     * Disable theme main styles
     */
    public function onClearThemeStyles()
    {
        $wp_styles = $GLOBALS['wp_styles'];
        $wp_styles->queue = [];
    }

    /**
     * Init js scripts
     */
    public function onInitScripts()
    {
        wp_enqueue_script('jquery');

        wp_enqueue_script(
            'bootstrap',
            self::$app_url.'/vendor/bootstrap/js/bootstrap.min.js',
            ['jquery'],
            '3.3.7',
            true
        );

        wp_enqueue_script(
            'metisMenu',
            self::$app_url.'/vendor/metisMenu/metisMenu.min.js',
            ['jquery'],
            '1.1.3',
            true
        );

        wp_enqueue_script(
            'sb-admin-2',
            self::$app_url.'/assets/js/sb-admin-2.js',
            ['jquery'],
            '3.3.7',
            true
        );

        wp_enqueue_script(
            'html5shiv',
            'https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js'
        );

        wp_enqueue_script(
            'respond',
            'https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js'
        );
        wp_script_add_data( 'respond', 'conditional', 'lt IE 9' );
        wp_script_add_data( 'html5shiv', 'conditional', 'lt IE 9' );
    }

    /**
     * Init styles
     */
    public function onInitStyles()
    {
        wp_enqueue_style(
            'bootstrap',
            self::$app_url.'/vendor/bootstrap/css/bootstrap.min.css'
        );

        wp_enqueue_style(
            'metisMenu',
            self::$app_url.'/vendor/metisMenu/metisMenu.min.css'
        );

        wp_enqueue_style(
            'sb-admin-2',
            self::$app_url.'/assets/css/sb-admin-2.css'
        );

        wp_enqueue_style(
            'font-awesome',
            self::$app_url.'/vendor/font-awesome/css/font-awesome.min.css'
        );
    }

    /**
     * Pages routing
     * @return bool|string
     * @throws Exception
     */
    public function onDisplayRoutesContent()
    {
        $callback = $this->getCurrentRouteCallback();

        if (!$callback) {
            return false;
        }

        if (!is_array ($callback)) {
            throw new Exception('Callback must be an array');
        }

        if (!is_callable ($callback)) {
            throw new Exception('Not found callback '. json_encode ($callback));
        }

        status_header(200);

        $data = [
            'menu' => $this->getMenu()
        ];

        $page = new Page($data);
        $page = call_user_func_array($callback, [$page]);

        echo self::view(
            ['layouts', 'main.php'],
            [
                'page' => $page
            ]
        );
        exit();
    }

    /**
     * List of menu
     * @return mixed|void
     */
    private function getMenu()
    {
        $menu = [
            '/dashboard' => [
                'title' => __('Dashboard', 'cn'),
                'icon' => 'fa-dashboard'
            ],
            '/tasks' => [
                'title' => __('Tasks', 'cn'),
                'icon' => 'fa-table'
            ],
            '/addNewTask' => [
                'title' => __('Add New Task', 'cn'),
                'icon' => 'fa-table'
            ],
            
            
        ];

        return apply_filters ('cn_menu', $menu, self::$route);
    }

    /**
     * Block default template for plugin custom routes
     * @param $path
     * @return bool
     */
    public function blockDefaultTemplates($path)
    {

        if (!$this->hasRoute(self::$route)) {
            return $path;
        }

        return false;
    }

    /**
     * Get callback for current route
     * @return bool
     */
    protected function getCurrentRouteCallback()
    {
        $routes = $this->getRoutes();

        foreach ($routes as $key => $value) {
            $key = trim($key, '/');

            if ($key == self::$route) {
                return $value;
            }
        }

        return false;
    }

    /**
     * Check the existence of the route
     * @param $route
     * @return bool
     */
    private function hasRoute($route)
    {
        $routes = $this->getRoutes();

        foreach ($routes as $key => $value) {
            $key = trim($key, '/');

            if ($key == $route) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get list of available routes
     * @return mixed|void
     */
    protected function getRoutes()
    {
        $controller = new ControllerPages();

        $routes = [
            '/tasks/' => array(&$controller, 'tasks'),
            '/dashboard/' => array(&$controller, 'dashboard'),
            '/addNewTask/' => array(&$controller, 'addNewTask'),

        ];

        return apply_filters ('cn_routes', $routes, self::$route);
    }

    /**
     * Init post type task
     */
    public function onInitPostTypes()
    {
        $labels = array(
            'name'               => __( 'Tasks', 'cn' ),
            'singular_name'      => __( 'Task',  'cn' ),
            'menu_name'          => __( 'Tasks', 'cn' ),
            'name_admin_bar'     => __( 'Task',  'cn' ),
            'add_new'            => __( 'Add New', 'cn' ),
            'add_new_item'       => __( 'Add New Task', 'cn' ),
            'new_item'           => __( 'New Task', 'cn' ),
            'edit_item'          => __( 'Edit Task', 'cn' ),
            'view_item'          => __( 'View Task', 'cn' ),
            'all_items'          => __( 'All Tasks', 'cn' ),
            'search_items'       => __( 'Search Tasks', 'cn' ),
            'parent_item_colon'  => __( 'Parent Tasks:', 'cn' ),
            'not_found'          => __( 'No tasks found.', 'cn' ),
            'not_found_in_trash' => __( 'No tasks found in Trash.', 'cn' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'task' ),
            'menu_icon'            => 'dashicons-index-card',
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor' )
        );

        register_post_type( Task::POST_TYPE, $args );
    }


    /**
     * Render template
     * @param $path
     * @param array $params
     * @return string
     * @throws Exception
     */
    public static function view($path, $params = [])
    {
        if (is_array ($path)) {
            $path = implode('/', $path);
        }

        $file = self::getViewPath($path);

        if (!file_exists ($file)) {
            throw new Exception('View not found '.$file);
        }

        if ($params) {
            extract ($params);
        }

        ob_start ();

        include $file;

        $content = ob_get_clean ();

        return $content;
    }

    /**
     * Get template path
     * @param string $file
     * @return string
     */
    private static function getViewPath($file = '')
    {
        $path = self::$app_path.'/views/'.$file;

        return apply_filters ('cn_view_path', $path, $file, self::$route);
    }

    /**
     * Classes autoloader
     * @param $class
     * @return mixed
     */
    public function autoloader($class)
    {
        $folders = [
            'decorators',
            'controllers',
            'tables',
            'models'
        ];

        $parts = explode ('\\',$class);
        array_shift ($parts);
        $class_name = array_shift ($parts);

        foreach ($folders as $folder) {
           $file = self::$app_path.'/'.$folder.'/'.$class_name.'.php';
           if (!file_exists ($file)) {
              continue;
           }

           return require_once $file;

           if (!class_exists ($class)) {
               continue;
           }
        }
    }

    /**
     * Method for check and get value from array
     * @param $arr
     * @param $key
     * @param string $default
     * @return mixed|string
     * @throws Exception
     */
    public static function get($arr, $key, $default = '')
    {
        if (!is_array ($arr)) {
            throw new Exception('$arr must be an array');
        }

        return (array_key_exists ($key, $arr)) ? $arr[$key] : $default;
    }

    /**
     * Convert array to string
     * @param $arr
     * @param string $pattern
     * @return string
     */
    public static function join($arr, $pattern = '%s="%s"')
    {
        $result = array();

        foreach ($arr as $key => $value) {
            if (!$value) {
                continue;
            }
            $result[] = sprintf($pattern, $key, $value);
        }

        return implode(' ', $result);
    }
}