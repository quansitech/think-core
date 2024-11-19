<?php

namespace Qscmf\Lib\Inertia;

use Illuminate\Foundation\Vite;
use Illuminate\Foundation\ViteException;
use Illuminate\Support\Str;

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

    public function __invoke($entrypoints, $buildDirectory = null)
    {
        try {
            return parent::__invoke($entrypoints, $buildDirectory);
        } catch (ViteException $exception) {
            if (Str::contains($exception->getMessage(), 'Vite manifest not found')) {
                throw new \Exception('请先执行 npm run build 或 npm run build:backend');
            }
            throw $exception;
        }
    }

}