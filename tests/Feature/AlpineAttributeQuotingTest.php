<?php

// A raw " inside an x-data/x-init attribute ends the attribute in the HTML parser,
// so Alpine gets a truncated expression and the whole component silently dies.
// This bit the TinyMCE editors via 'Segoe UI' in content_style.
it('has no raw double quotes inside Alpine expression attributes', function () {
    $offenders = [];

    $views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));

    foreach (new RegexIterator($views, '/\.blade\.php$/') as $file) {
        $html = file_get_contents($file);

        preg_match_all('/\b(x-data|x-init|x-effect|x-show|x-text)="(.*?)"/s', $html, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[2] as $i => [$expression, $offset]) {
            // A multi-line expression whose single quotes do not pair up was cut short
            // by a raw " earlier in the attribute.
            if (str_contains($expression, "\n") && substr_count($expression, "'") % 2 !== 0) {
                $line = substr_count(substr($html, 0, $offset), "\n") + 1;
                $offenders[] = basename($file) . ':' . $line . ' (' . $matches[1][$i][0] . ')';
            }
        }
    }

    expect($offenders)->toBe([]);
});
