<?php

namespace Qscmf\Lib\Inertia\TagLib;

use Qscmf\Lib\Inertia\ViteForQscmf;
use Think\Template\TagLib;

class Inertia extends TagLib
{
    protected $tags = array(
        'vite' => array('attr' => 'file'),
        'render' => array(),
        'head' => array(),
        'reactRefresh' => array(),
    );

    public function __construct()
    {

    }

    public function _vite($tag, $content)
    {
        $file = $tag['file'];
        $vite = new ViteForQscmf();

        return $vite($file);
    }

    public function _render($tag, $content)
    {
        $id = trim(trim($tag['id'] ?? ''), "\'\"") ?: 'app';

        $template = '<?php
            if ($ssr_data) {
                echo $ssr_data["body"];
            } else {
                ?><div id="' . $id . '" data-page="{: htmlentities(json_encode($page)) }"></div><?php
            }
        ?>';

        return implode(' ', array_map('trim', explode("\n", $template)));
    }

    public function _head($tag, $content)
    {
        $template = '<?php
            if ($ssr_data) {
                echo $ssr_data["head"];
            }
        ?>';

        return implode(' ', array_map('trim', explode("\n", $template)));
    }

    public function _reactRefresh($tag, $content)
    {
        $vite = new ViteForQscmf();
        return $vite->reactRefresh();
    }
}