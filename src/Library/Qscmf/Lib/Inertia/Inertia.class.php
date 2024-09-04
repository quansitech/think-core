<?php

namespace Qscmf\Lib\Inertia;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Think\Think;
use Think\View;

class Inertia
{
    private static $instance;

    private function __construct()
    {

    }

    protected function reset()
    {
        if (in_array(strtolower(MODULE_NAME), C('BACKEND_MODULE'))) {
            // 后台
            C('INERTIA.ssr_url', '');
            C('INERTIA.hot_file', WWW_DIR . '/Public/backend-hot');
            C('INERTIA.build_path', WWW_DIR . '/Public/backend/build');
            C('INERTIA.base_path', 'backend/');
            C('INERTIA.root_view', T('Admin@default/common/inertia_layout'));
        } else {
            // 前台
            C('INERTIA.hot_file', WWW_DIR . '/Public/hot');
            C('INERTIA.build_path', WWW_DIR . '/Public/build');
            C('INERTIA.base_path', '');
            C('INERTIA.root_view', T('Home@../app'));
        }
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        self::$instance->reset();
        return self::$instance;
    }

    protected function version()
    {
        if (!file_exists(C('INERTIA.build_path') . '/manifest.json')) {
            return '';
        }
        return md5_file(C('INERTIA.build_path') . '/manifest.json');
    }

    protected function inertiaXhr($component, $props, $version)
    {
        header('Content-Type:application/json');
        header('X-Inertia:true');
        header('X-Inertia-Version:' . $version);
        header('vary: X-Inertia');
        qs_exit(json_encode([
            'component' => $component,
            'props' => $props,
            'url' => $_SERVER['REQUEST_URI'],
            'version' => $version,
        ]));
    }

    protected function handleProps($props)
    {
        $header = getallheaders();
        $only = array_filter(explode(',', $header['X-Inertia-Partial-Data']));
        $except = array_filter(explode(',', $header['X-Inertia-Partial-Except']));

        $props = $only ? Arr::only($props, $only) : $props;

        if ($except) {
            Arr::forget($props, $except);
        }
        return $props;
    }

    public function render($component, $props, $rootView = '')
    {
        if (!$rootView) {
            $rootView = C('INERTIA.root_view');
        }

        $version = self::version();
        $props['errors'] = (object)[];
        $props = $this->handleProps($props);

        $headers = getallheaders();
        if ($headers['X-Inertia']) {
            $this->inertiaXhr($component, $props, $version);
            return;
        }

        $page = [
            'component' => $component,
            'props' => $props,
            'url' => $_SERVER['REQUEST_URI'],
            'version' => $version,
        ];

        $ssr_data = [];
        if (C('INERTIA.ssr_url')) {
            $res = $this->ssrRender($page);
            $ssr_data = $res;
        }

        /** @var View $view */
        $view = Think::instance(View::class);

        $view->assign('page', $page);
        $view->assign('ssr_data', $ssr_data);


        $view->display($rootView);
    }

    protected function ssrRender($page)
    {
        $url = str_replace('/render', '', C('INERTIA.ssr_url')) . '/render';

        $client = new Client();

        try {
            $response = $client->post($url, [
                'json' => $page
            ]);
            $res = (string)$response->getBody()->getContents();
            $res = json_decode($res, true);
        } catch (Exception $e) {
            return null;
        }

        if (is_null($response)) {
            return null;
        }

        return [
            'head' => implode("\n", $res['head']),
            'body' => $res['body'],
        ];
    }
}