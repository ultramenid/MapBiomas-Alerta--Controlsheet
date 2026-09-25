<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

class Html
{
    /** Strip scripts/event handlers from TinyMCE-authored HTML before it is rendered raw. */
    public static function clean(?string $html): string
    {
        static $purifier;

        if ($purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('Cache.SerializerPath', storage_path('framework/cache'));
            // Inline images: data: is inert inside <img>, and older notes still embed base64.
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'data' => true]);
            $purifier = new HTMLPurifier($config);
        }

        return $purifier->purify($html ?? '');
    }
}
