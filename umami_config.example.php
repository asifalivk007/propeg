<?php
/**
 * umami_config.example.php
 *
 * Template for the analytics credentials used by globe-data.php (the footer
 * visitor globe). Copy this file to umami_config.php and fill in real values:
 *
 *     cp umami_config.example.php umami_config.php
 *
 * umami_config.php is gitignored and must NEVER be committed. It is also blocked
 * from web access by .htaccess. If the globe is not needed, you can omit the file
 * entirely — globe-data.php simply returns an auth error and the footer degrades.
 */
return [
    // Umami account with read access to the website's stats.
    'username'   => 'CHANGE_ME',
    'password'   => 'CHANGE_ME',
    // The website id from your Umami dashboard.
    'website_id' => 'CHANGE_ME',
    // Optional: override the Umami server URL (defaults inside globe-data.php).
    // 'umami_url' => 'https://analytics.example.org',
];
