<?php

namespace App\Services;

class Letterhead
{
    public const MODES = [
        'auto'          => 'Auto (logo + company details)',
        'header_footer' => 'Header & footer images',
        'full'          => 'Full-page letterhead (A4 background)',
    ];

    public static function config(): array
    {
        $app = require BASE_PATH . '/app/Config/app.php';
        $mode = setting('letterhead_mode', 'auto');
        $header = setting('letterhead_header');
        $footer = setting('letterhead_footer');
        $full = setting('letterhead_full');

        if ($mode === 'full' && !$full) {
            $mode = 'auto';
        }
        if ($mode === 'header_footer' && !$header && !$footer) {
            $mode = 'auto';
        }

        return [
            'mode'      => $mode,
            'header'    => $header ? asset($header) : null,
            'footer'    => $footer ? asset($footer) : null,
            'full'      => $full ? asset($full) : null,
            'top_mm'    => max(0, min(120, (int) setting('letterhead_top_mm', 38))),
            'bottom_mm' => max(0, min(80, (int) setting('letterhead_bottom_mm', 22))),
            'side_mm'   => max(5, min(30, (int) setting('letterhead_side_mm', 12))),
            'logo'      => asset($app['brand']['logo']),
            'company'   => setting('company_name', $app['company']),
            'tagline'   => setting('company_tagline', $app['tagline']),
            'address'   => setting('company_address', ''),
            'email'     => setting('company_email', ''),
            'phone'     => setting('company_phone', ''),
            'website'   => setting('company_website', ''),
        ];
    }
}
