<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Vite
{
    protected static $buildDirectory = 'build';

    public static function tags($entrypoints)
    {
        $entrypoints = is_array($entrypoints) ? $entrypoints : func_get_args();

        if (static::isRunningHot()) {
            return new HtmlString(static::hotTags($entrypoints));
        }

        return new HtmlString(static::productionTags($entrypoints));
    }

    protected static function isRunningHot()
    {
        return file_exists(public_path('hot'));
    }

    protected static function hotTags(array $entrypoints)
    {
        $url = rtrim(file_get_contents(public_path('hot')), '/');
        $html = '<script type="module" src="'.$url.'/@vite/client"></script>';

        foreach ($entrypoints as $entrypoint) {
            if (static::isStyleEntrypoint($entrypoint)) {
                $html .= '<link rel="stylesheet" href="'.$url.'/'.$entrypoint.'">';
            } else {
                $html .= '<script type="module" src="'.$url.'/'.$entrypoint.'"></script>';
            }
        }

        return $html;
    }

    protected static function productionTags(array $entrypoints)
    {
        $manifestPath = public_path(static::$buildDirectory.'/manifest.json');

        if (! file_exists($manifestPath)) {
            return '';
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $styles = [];
        $scripts = [];

        foreach ($entrypoints as $entrypoint) {
            if (! isset($manifest[$entrypoint])) {
                continue;
            }

            $chunk = $manifest[$entrypoint];

            if (isset($chunk['css'])) {
                foreach ($chunk['css'] as $css) {
                    $styles[$css] = static::$buildDirectory.'/'.$css;
                }
            }

            if (static::isStyleEntrypoint($chunk['file'] ?? $entrypoint)) {
                $styles[$chunk['file']] = static::$buildDirectory.'/'.$chunk['file'];
            } else {
                $scripts[] = static::$buildDirectory.'/'.$chunk['file'];
            }
        }

        $html = '';

        foreach ($styles as $href) {
            $html .= '<link rel="stylesheet" href="'.asset($href).'">';
        }

        foreach ($scripts as $src) {
            $html .= '<script type="module" src="'.asset($src).'"></script>';
        }

        return $html;
    }

    protected static function isStyleEntrypoint($entrypoint)
    {
        return Str::endsWith($entrypoint, '.css');
    }
}
