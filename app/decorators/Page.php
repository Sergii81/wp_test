<?php

namespace codingninjas;

use \Exception;

class Page
{
    /**
     * Input data
     * @var array
     */
    protected $data;

    /**
     * Page constructor.
     * @param $data
     * @throws Exception
     */
    public function __construct($data)
    {
        if (!is_array ($data)) {
            throw new Exception('$data must be an array');
        }

        $this->data = $data;
    }

    /**
     * Get input data
     * @return array
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Get page content
     * @return mixed|string
     */
    public function content()
    {
        $content = App::get($this->data, 'content');
        return apply_filters ('cn_page_content', $content, $this);
    }

    /**
     * Get current route
     * @return mixed|string
     */
    public function route()
    {
        return App::$route;
    }

    /**
     * Get page title
     * @return mixed|string
     */
    public function title()
    {
        $title = App::get($this->data, 'title', ucfirst ($this->route ()));
        return apply_filters ('cn_page_title', $title, $this);
    }

    /**
     * Get logo
     * @return string
     */
    public function logo()
    {
        $logo = App::view (
            ['logo.php'],
            [
                'url' => home_url ('/dashboard'),
                'image' => App::$app_url.'/assets/images/logo.png'
            ]
        );

        return apply_filters ('cn_page_logo_html', $logo, $this);
    }

    /**
     * Get html of menu
     * @return string
     */
    public function menu()
    {
        $menu = App::get($this->data, 'menu', []);

        if (!$menu) return '';

        foreach ($menu as $ident => &$item) {
            $route = trim($ident, '/');

            if ($this->route() == $route) {
                $item['active'] = true;
            }
        }

        $menu = App::view (
            ['menu.php'],
            ['menu' => $menu]
        );

        return apply_filters ('cn_page_menu_html', $menu, $this);
    }

    /**
     * Update input data
     * @param $data
     * @throws Exception
     */
    public function update($data)
    {
        if (!is_array ($data)) {
            throw new Exception('$data must be an array');
        }

        $this->data = array_merge ($this->data, $data);
    }
}