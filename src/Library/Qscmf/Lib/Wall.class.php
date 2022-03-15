<?php

namespace Qscmf\Lib;

class Wall
{
    public function file_get_contents (string $filename, bool $use_include_path = false, ?string $context = null, int $offset = 0, ?int $length = null): string|false{
        if (is_null($length)){
            return file_get_contents($filename,$use_include_path,$context,$offset);
        }else{
            return file_get_contents($filename,$use_include_path,$context,$offset,$length);
        }
    }

}