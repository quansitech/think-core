<?php

namespace Faker\Provider\zh_CN;

use Faker\Provider\Base;

class Image extends Base
{
    protected static $categories = [
        'animals', 'arch', 'nature', 'people', 'tech',
    ];

    protected static $filter = [
        'grayscale', 'sepia',
    ];

    public static function imageUrl($width = 640, $height = 480, $category = null, $randomize = true, $filter = null)
    {
        $baseUrl = 'http://placeimg.com/';
        $url = "{$width}/{$height}/";

        if ($category) {
            if (!in_array($category, static::$categories)) {
                throw new \InvalidArgumentException(sprintf('Unknown image category "%s"', $category));
            }
            $url .= "{$category}/";
        }

        if ($filter) {
            if (!in_array($filter, static::$filter)) {
                throw new \InvalidArgumentException(sprintf('Unknown image filter "%s"', $filter));
            }
            $url .= "{$filter}/";
        }

        if ($randomize) {
            $url = trim($url, '/').'?'.static::randomNumber(5, true);
        }

        return $baseUrl.$url;
    }

    /**
     * Download a remote random image to disk and return its location.
     *
     * Requires curl, or allow_url_fopen to be on in php.ini.
     *
     * @example '/path/to/dir/13b73edae8443990be1aa8f1a483bc27.jpg'
     */
    public static function image($dir = null, $width = 640, $height = 480, $category = null, $fullPath = true, $randomize = true, $filter = null)
    {
        $dir = is_null($dir) ? sys_get_temp_dir() : $dir; // GNU/Linux / OS X / Windows compatible
        // Validate directory path
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('Cannot write to directory "%s"', $dir));
        }

        // Generate a random filename. Use the server address so that a file
        // generated at the same time on a different server won't have a collision.
        $name = md5(uniqid(empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'], true));
        $filename = $name.'.jpg';
        $filepath = $dir.DIRECTORY_SEPARATOR.$filename;

        $url = static::imageUrl($width, $height, $category, $randomize, $filter);

        // save file
        if (function_exists('curl_exec')) {
            // use cURL
            $fp = fopen($filepath, 'w');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $success = curl_exec($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
            fclose($fp);
            curl_close($ch);

            if (!$success) {
                unlink($filepath);

                // could not contact the distant URL or HTTP error - fail silently.
                return false;
            }
        } elseif (ini_get('allow_url_fopen')) {
            // use remote fopen() via copy()
            $success = copy($url, $filepath);
        } else {
            return new \RuntimeException('The image formatter downloads an image from a remote HTTP server. Therefore, it requires that PHP can request remote hosts, either via cURL or fopen()');
        }

        return $fullPath ? $filepath : $filename;
    }
}
