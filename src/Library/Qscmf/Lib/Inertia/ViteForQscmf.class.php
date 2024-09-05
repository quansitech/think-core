<?php

namespace Qscmf\Lib\Inertia;

use Illuminate\Foundation\Vite;

class ViteForQscmf extends Vite
{
    protected $publicPath = WWW_DIR;

    public function __construct()
    {
        if (!$this->hotFile) {
            $this->hotFile = C('INERTIA.hot_file');
        }

    }

    protected function manifestPath($buildDirectory)
    {
        return C('INERTIA.build_path') . '/' . $this->manifestFilename;
    }

    protected function assetPath($path, $secure = null)
    {
        return asset(C('INERTIA.base_path') . $path);
    }

}