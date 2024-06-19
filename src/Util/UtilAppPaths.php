<?php

namespace Topdata\TopdataDevelopmentHelperSW6\Util;

/**
 * all directory names are without trailing slash!
 *
 * 05/2024 created
 */
class UtilAppPaths
{


    /**
     * 03/2020
     *
     * @param string $path
     * @return string|string[]|null
     */
    private static function normalizePath(string $path): string
    {
        $r = [
            '~/{2,}~'                  => '/',
            '~/(\./)+~'                => '/',
            '~([^/\.]+/(?R)*\.{2,}/)~' => '',
            '~\.\./~'                  => '',
            '~/[^/\.]+/\.\.$~'         => '', // 04/2020 added: /a/b/c/.. --> a/b
        ];
        return preg_replace(array_keys($r), array_values($r), $path);
    }



    public static function getPluginConfigDumpDir(): string
    {
        return self::normalizePath(__DIR__ . '/../../plugin-config-dumps');
    }
}