<?php

namespace Qscmf\Lib\Inertia;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Think\Think;
use Think\View;


/**
 * @method static void render($component, $props, $rootView = '')
 * @method static void share($key, $value)
 */
class Inertia
{
    private static $instance;

    protected $sharedProps = [];

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
            if (C('ANTD_ADMIN_NEW_LAYOUT')) {
                C('INERTIA.root_view', T('Admin@default/common/inertia_blank_layout'));
            } else {
                C('INERTIA.root_view', T('Admin@default/common/inertia_layout'));
            }
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
        $allHeaders = getallheaders();
        if ($version != $allHeaders['X-Inertia-Version'] && IS_GET) {
            send_http_status(409);
            header('X-Inertia-Location:' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            return;
        }

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
        $props = array_merge($this->sharedProps, $props);

        $header = getallheaders();
        $only = array_filter(explode(',', $header['X-Inertia-Partial-Data']));
        $except = array_filter(explode(',', $header['X-Inertia-Partial-Except']));

        $props = $only ? Arr::only($props, $only) : $props;

        if ($except) {
            Arr::forget($props, $except);
        }
        if (C('INERTIA_PROPS_HTML_DECODE', null, true)) {
            return $this->htmlDecode($props);
        }
        return $props;
    }

    protected function htmlDecode($data)
    {
        if (is_array($data)) {
            foreach ($data as &$v) {
                $v = $this->htmlDecode($v);
            }
            return $data;
        } elseif (is_string($data)) {
            return html_entity_decode($data);
        }
        return $data;
    }

    private function _render($component, $props, $rootView = '')
    {
        if (!$rootView) {
            $rootView = C('INERTIA.root_view');
        }

        $version = self::version();
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

    private function _share($key, $value)
    {
        if (is_array($key)) {
            $this->sharedProps = array_merge($this->sharedProps, $key);
        } elseif ($key instanceof Arrayable) {
            $this->sharedProps = array_merge($this->sharedProps, $key->toArray());
        } else {
            Arr::set($this->sharedProps, $key, $value);
        }
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return call_user_func_array([self::getInstance(), '_' . $name], $arguments);
    }
}