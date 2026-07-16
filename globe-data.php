<?php
set_time_limit(0); // Allow long execution time for initial geocoding
ignore_user_abort(true); // Keep running even if the user refreshes
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

// 1. Securely load the credentials
// $config = require_once('assets/images/others/umami_config.php');
$config = require_once(__DIR__ . '/umami_config.php');

// --- CONFIGURATION ---
// We default to your main analytics server, but allow overriding in umami_config.php
$umami_url  = isset($config['umami_url']) ? $config['umami_url'] : 'https://asifalivk7analytics.duckdns.org';
$username   = $config['username'];
$password   = $config['password'];
$website_id = $config['website_id']; 

$cache_file = __DIR__ . '/globe_cache.json';
$cache_time = 60; // 1 minute

// Serve from cache if fresh
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
    echo file_get_contents($cache_file);
    exit;
}

// 2. Authenticate to get JWT token
$ch = curl_init("$umami_url/api/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => $username, 'password' => $password]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$auth_response = json_decode(curl_exec($ch), true);

if (!isset($auth_response['token'])) {
    die(json_encode(["error" => "Auth failed."]));
}
$token = $auth_response['token'];

// 3. Fetch traffic data (Country and City)
$end_at = time() * 1000;
$start_at = 0; 

$ch2 = curl_init("$umami_url/api/websites/$website_id/metrics?startAt=$start_at&endAt=$end_at&type=country");
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
$country_metrics = json_decode(curl_exec($ch2), true);
curl_close($ch2);

$ch3 = curl_init("$umami_url/api/websites/$website_id/metrics?startAt=$start_at&endAt=$end_at&type=city");
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch3, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
$city_metrics = json_decode(curl_exec($ch3), true);
curl_close($ch3);

// 4. Map ISO Codes and Country Names
$geo_map = [
    'AD' => ['lat' => 42.5, 'lng' => 1.5], 'AE' => ['lat' => 24, 'lng' => 54], 'AF' => ['lat' => 33, 'lng' => 65],
    'AG' => ['lat' => 17.05, 'lng' => -61.8], 'AI' => ['lat' => 18.25, 'lng' => -63.17], 'AL' => ['lat' => 41, 'lng' => 20],
    'AM' => ['lat' => 40, 'lng' => 45], 'AN' => ['lat' => 12.25, 'lng' => -68.75], 'AO' => ['lat' => -12.5, 'lng' => 18.5],
    'AP' => ['lat' => 35, 'lng' => 105], 'AQ' => ['lat' => -90, 'lng' => 0], 'AR' => ['lat' => -34, 'lng' => -64],
    'AS' => ['lat' => -14.33, 'lng' => -170], 'AT' => ['lat' => 47.33, 'lng' => 13.33], 'AU' => ['lat' => -27, 'lng' => 133],
    'AW' => ['lat' => 12.5, 'lng' => -69.97], 'AZ' => ['lat' => 40.5, 'lng' => 47.5], 'BA' => ['lat' => 44, 'lng' => 18],
    'BB' => ['lat' => 13.17, 'lng' => -59.53], 'BD' => ['lat' => 24, 'lng' => 90], 'BE' => ['lat' => 50.83, 'lng' => 4],
    'BF' => ['lat' => 13, 'lng' => -2], 'BG' => ['lat' => 43, 'lng' => 25], 'BH' => ['lat' => 26, 'lng' => 50.55],
    'BI' => ['lat' => -3.5, 'lng' => 30], 'BJ' => ['lat' => 9.5, 'lng' => 2.25], 'BM' => ['lat' => 32.33, 'lng' => -64.75],
    'BN' => ['lat' => 4.5, 'lng' => 114.67], 'BO' => ['lat' => -17, 'lng' => -65], 'BR' => ['lat' => -10, 'lng' => -55],
    'BS' => ['lat' => 24.25, 'lng' => -76], 'BT' => ['lat' => 27.5, 'lng' => 90.5], 'BV' => ['lat' => -54.43, 'lng' => 3.4],
    'BW' => ['lat' => -22, 'lng' => 24], 'BY' => ['lat' => 53, 'lng' => 28], 'BZ' => ['lat' => 17.25, 'lng' => -88.75],
    'CA' => ['lat' => 60, 'lng' => -95], 'CC' => ['lat' => -12.5, 'lng' => 96.83], 'CD' => ['lat' => 0, 'lng' => 25],
    'CF' => ['lat' => 7, 'lng' => 21], 'CG' => ['lat' => -1, 'lng' => 15], 'CH' => ['lat' => 47, 'lng' => 8],
    'CI' => ['lat' => 8, 'lng' => -5], 'CK' => ['lat' => -21.23, 'lng' => -159.77], 'CL' => ['lat' => -30, 'lng' => -71],
    'CM' => ['lat' => 6, 'lng' => 12], 'CN' => ['lat' => 35, 'lng' => 105], 'CO' => ['lat' => 4, 'lng' => -72],
    'CR' => ['lat' => 10, 'lng' => -84], 'CU' => ['lat' => 21.5, 'lng' => -80], 'CV' => ['lat' => 16, 'lng' => -24],
    'CX' => ['lat' => -10.5, 'lng' => 105.67], 'CY' => ['lat' => 35, 'lng' => 33], 'CZ' => ['lat' => 49.75, 'lng' => 15.5],
    'DE' => ['lat' => 51, 'lng' => 9], 'DJ' => ['lat' => 11.5, 'lng' => 43], 'DK' => ['lat' => 56, 'lng' => 10],
    'DM' => ['lat' => 15.42, 'lng' => -61.33], 'DO' => ['lat' => 19, 'lng' => -70.67], 'DZ' => ['lat' => 28, 'lng' => 3],
    'EC' => ['lat' => -2, 'lng' => -77.5], 'EE' => ['lat' => 59, 'lng' => 26], 'EG' => ['lat' => 27, 'lng' => 30],
    'EH' => ['lat' => 24.5, 'lng' => -13], 'ER' => ['lat' => 15, 'lng' => 39], 'ES' => ['lat' => 40, 'lng' => -4],
    'ET' => ['lat' => 8, 'lng' => 38], 'EU' => ['lat' => 47, 'lng' => 8], 'FI' => ['lat' => 64, 'lng' => 26],
    'FJ' => ['lat' => -18, 'lng' => 175], 'FK' => ['lat' => -51.75, 'lng' => -59], 'FM' => ['lat' => 6.92, 'lng' => 158.25],
    'FO' => ['lat' => 62, 'lng' => -7], 'FR' => ['lat' => 46, 'lng' => 2], 'GA' => ['lat' => -1, 'lng' => 11.75],
    'GB' => ['lat' => 54, 'lng' => -2], 'GD' => ['lat' => 12.12, 'lng' => -61.67], 'GE' => ['lat' => 42, 'lng' => 43.5],
    'GF' => ['lat' => 4, 'lng' => -53], 'GH' => ['lat' => 8, 'lng' => -2], 'GI' => ['lat' => 36.18, 'lng' => -5.37],
    'GL' => ['lat' => 72, 'lng' => -40], 'GM' => ['lat' => 13.47, 'lng' => -16.57], 'GN' => ['lat' => 11, 'lng' => -10],
    'GP' => ['lat' => 16.25, 'lng' => -61.58], 'GQ' => ['lat' => 2, 'lng' => 10], 'GR' => ['lat' => 39, 'lng' => 22],
    'GS' => ['lat' => -54.5, 'lng' => -37], 'GT' => ['lat' => 15.5, 'lng' => -90.25], 'GU' => ['lat' => 13.47, 'lng' => 144.78],
    'GW' => ['lat' => 12, 'lng' => -15], 'GY' => ['lat' => 5, 'lng' => -59], 'HK' => ['lat' => 22.25, 'lng' => 114.17],
    'HM' => ['lat' => -53.1, 'lng' => 72.52], 'HN' => ['lat' => 15, 'lng' => -86.5], 'HR' => ['lat' => 45.17, 'lng' => 15.5],
    'HT' => ['lat' => 19, 'lng' => -72.42], 'HU' => ['lat' => 47, 'lng' => 20], 'ID' => ['lat' => -5, 'lng' => 120],
    'IE' => ['lat' => 53, 'lng' => -8], 'IL' => ['lat' => 31.5, 'lng' => 34.75], 'IN' => ['lat' => 20, 'lng' => 77],
    'IO' => ['lat' => -6, 'lng' => 71.5], 'IQ' => ['lat' => 33, 'lng' => 44], 'IR' => ['lat' => 32, 'lng' => 53],
    'IS' => ['lat' => 65, 'lng' => -18], 'IT' => ['lat' => 42.83, 'lng' => 12.83], 'JM' => ['lat' => 18.25, 'lng' => -77.5],
    'JO' => ['lat' => 31, 'lng' => 36], 'JP' => ['lat' => 36, 'lng' => 138], 'KE' => ['lat' => 1, 'lng' => 38],
    'KG' => ['lat' => 41, 'lng' => 75], 'KH' => ['lat' => 13, 'lng' => 105], 'KI' => ['lat' => 1.42, 'lng' => 173],
    'KM' => ['lat' => -12.17, 'lng' => 44.25], 'KN' => ['lat' => 17.33, 'lng' => -62.75], 'KP' => ['lat' => 40, 'lng' => 127],
    'KR' => ['lat' => 37, 'lng' => 127.5], 'KW' => ['lat' => 29.34, 'lng' => 47.66], 'KY' => ['lat' => 19.5, 'lng' => -80.5],
    'KZ' => ['lat' => 48, 'lng' => 68], 'LA' => ['lat' => 18, 'lng' => 105], 'LB' => ['lat' => 33.83, 'lng' => 35.83],
    'LC' => ['lat' => 13.88, 'lng' => -61.13], 'LI' => ['lat' => 47.17, 'lng' => 9.53], 'LK' => ['lat' => 7, 'lng' => 81],
    'LR' => ['lat' => 6.5, 'lng' => -9.5], 'LS' => ['lat' => -29.5, 'lng' => 28.5], 'LT' => ['lat' => 56, 'lng' => 24],
    'LU' => ['lat' => 49.75, 'lng' => 6.17], 'LV' => ['lat' => 57, 'lng' => 25], 'LY' => ['lat' => 25, 'lng' => 17],
    'MA' => ['lat' => 32, 'lng' => -5], 'MC' => ['lat' => 43.73, 'lng' => 7.4], 'MD' => ['lat' => 47, 'lng' => 29],
    'ME' => ['lat' => 42, 'lng' => 19], 'MG' => ['lat' => -20, 'lng' => 47], 'MH' => ['lat' => 9, 'lng' => 168],
    'MK' => ['lat' => 41.83, 'lng' => 22], 'ML' => ['lat' => 17, 'lng' => -4], 'MM' => ['lat' => 22, 'lng' => 98],
    'MN' => ['lat' => 46, 'lng' => 105], 'MO' => ['lat' => 22.17, 'lng' => 113.55], 'MP' => ['lat' => 15.2, 'lng' => 145.75],
    'MQ' => ['lat' => 14.67, 'lng' => -61], 'MR' => ['lat' => 20, 'lng' => -12], 'MS' => ['lat' => 16.75, 'lng' => -62.2],
    'MT' => ['lat' => 35.83, 'lng' => 14.58], 'MU' => ['lat' => -20.28, 'lng' => 57.55], 'MV' => ['lat' => 3.25, 'lng' => 73],
    'MW' => ['lat' => -13.5, 'lng' => 34], 'MX' => ['lat' => 23, 'lng' => -102], 'MY' => ['lat' => 2.5, 'lng' => 112.5],
    'MZ' => ['lat' => -18.25, 'lng' => 35], 'NA' => ['lat' => -22, 'lng' => 17], 'NC' => ['lat' => -21.5, 'lng' => 165.5],
    'NE' => ['lat' => 16, 'lng' => 8], 'NF' => ['lat' => -29.03, 'lng' => 167.95], 'NG' => ['lat' => 10, 'lng' => 8],
    'NI' => ['lat' => 13, 'lng' => -85], 'NL' => ['lat' => 52.5, 'lng' => 5.75], 'NO' => ['lat' => 62, 'lng' => 10],
    'NP' => ['lat' => 28, 'lng' => 84], 'NR' => ['lat' => -0.53, 'lng' => 166.92], 'NU' => ['lat' => -19.03, 'lng' => -169.87],
    'NZ' => ['lat' => -41, 'lng' => 174], 'OM' => ['lat' => 21, 'lng' => 57], 'PA' => ['lat' => 9, 'lng' => -80],
    'PE' => ['lat' => -10, 'lng' => -76], 'PF' => ['lat' => -15, 'lng' => -140], 'PG' => ['lat' => -6, 'lng' => 147],
    'PH' => ['lat' => 13, 'lng' => 122], 'PK' => ['lat' => 30, 'lng' => 70], 'PL' => ['lat' => 52, 'lng' => 20],
    'PM' => ['lat' => 46.83, 'lng' => -56.33], 'PR' => ['lat' => 18.25, 'lng' => -66.5], 'PS' => ['lat' => 32, 'lng' => 35.25],
    'PT' => ['lat' => 39.5, 'lng' => -8], 'PW' => ['lat' => 7.5, 'lng' => 134.5], 'PY' => ['lat' => -23, 'lng' => -58],
    'QA' => ['lat' => 25.5, 'lng' => 51.25], 'RE' => ['lat' => -21.1, 'lng' => 55.6], 'RO' => ['lat' => 46, 'lng' => 25],
    'RS' => ['lat' => 44, 'lng' => 21], 'RU' => ['lat' => 60, 'lng' => 100], 'RW' => ['lat' => -2, 'lng' => 30],
    'SA' => ['lat' => 25, 'lng' => 45], 'SB' => ['lat' => -8, 'lng' => 159], 'SC' => ['lat' => -4.58, 'lng' => 55.67],
    'SD' => ['lat' => 15, 'lng' => 30], 'SE' => ['lat' => 62, 'lng' => 15], 'SG' => ['lat' => 1.37, 'lng' => 103.8],
    'SH' => ['lat' => -15.93, 'lng' => -5.7], 'SI' => ['lat' => 46, 'lng' => 15], 'SJ' => ['lat' => 78, 'lng' => 20],
    'SK' => ['lat' => 48.67, 'lng' => 19.5], 'SL' => ['lat' => 8.5, 'lng' => -11.5], 'SM' => ['lat' => 43.77, 'lng' => 12.42],
    'SN' => ['lat' => 14, 'lng' => -14], 'SO' => ['lat' => 10, 'lng' => 49], 'SR' => ['lat' => 4, 'lng' => -56],
    'ST' => ['lat' => 1, 'lng' => 7], 'SV' => ['lat' => 13.83, 'lng' => -88.92], 'SY' => ['lat' => 35, 'lng' => 38],
    'SZ' => ['lat' => -26.5, 'lng' => 31.5], 'TC' => ['lat' => 21.75, 'lng' => -71.58], 'TD' => ['lat' => 15, 'lng' => 19],
    'TF' => ['lat' => -43, 'lng' => 67], 'TG' => ['lat' => 8, 'lng' => 1.17], 'TH' => ['lat' => 15, 'lng' => 100],
    'TJ' => ['lat' => 39, 'lng' => 71], 'TK' => ['lat' => -9, 'lng' => -172], 'TM' => ['lat' => 40, 'lng' => 60],
    'TN' => ['lat' => 34, 'lng' => 9], 'TO' => ['lat' => -20, 'lng' => -175], 'TR' => ['lat' => 39, 'lng' => 35],
    'TT' => ['lat' => 11, 'lng' => -61], 'TV' => ['lat' => -8, 'lng' => 178], 'TW' => ['lat' => 23.5, 'lng' => 121],
    'TZ' => ['lat' => -6, 'lng' => 35], 'UA' => ['lat' => 49, 'lng' => 32], 'UG' => ['lat' => 1, 'lng' => 32],
    'UM' => ['lat' => 19.28, 'lng' => 166.6], 'US' => ['lat' => 38, 'lng' => -97], 'UY' => ['lat' => -33, 'lng' => -56],
    'UZ' => ['lat' => 41, 'lng' => 64], 'VA' => ['lat' => 41.9, 'lng' => 12.45], 'VC' => ['lat' => 13.25, 'lng' => -61.2],
    'VE' => ['lat' => 8, 'lng' => -66], 'VG' => ['lat' => 18.5, 'lng' => -64.5], 'VI' => ['lat' => 18.33, 'lng' => -64.83],
    'VN' => ['lat' => 16, 'lng' => 106], 'VU' => ['lat' => -16, 'lng' => 167], 'WF' => ['lat' => -13.3, 'lng' => -176.2],
    'WS' => ['lat' => -13.58, 'lng' => -172.33], 'YE' => ['lat' => 15, 'lng' => 48], 'YT' => ['lat' => -12.83, 'lng' => 45.17],
    'ZA' => ['lat' => -29, 'lng' => 24], 'ZM' => ['lat' => -15, 'lng' => 30], 'ZW' => ['lat' => -20, 'lng' => 30],
];

// Provide human readable names instead of abbreviations
$country_names = [
    'AD' => 'Andorra', 'AE' => 'United Arab Emirates', 'AF' => 'Afghanistan', 'AG' => 'Antigua and Barbuda', 'AI' => 'Anguilla',
    'AL' => 'Albania', 'AM' => 'Armenia', 'AN' => 'Netherlands Antilles', 'AO' => 'Angola', 'AP' => 'Asia/Pacific Region',
    'AQ' => 'Antarctica', 'AR' => 'Argentina', 'AS' => 'American Samoa', 'AT' => 'Austria', 'AU' => 'Australia',
    'AW' => 'Aruba', 'AZ' => 'Azerbaijan', 'BA' => 'Bosnia and Herzegovina', 'BB' => 'Barbados', 'BD' => 'Bangladesh',
    'BE' => 'Belgium', 'BF' => 'Burkina Faso', 'BG' => 'Bulgaria', 'BH' => 'Bahrain', 'BI' => 'Burundi', 'BJ' => 'Benin',
    'BM' => 'Bermuda', 'BN' => 'Brunei Darussalam', 'BO' => 'Bolivia', 'BR' => 'Brazil', 'BS' => 'Bahamas', 'BT' => 'Bhutan',
    'BV' => 'Bouvet Island', 'BW' => 'Botswana', 'BY' => 'Belarus', 'BZ' => 'Belize', 'CA' => 'Canada', 'CC' => 'Cocos Islands',
    'CD' => 'Democratic Republic of the Congo', 'CF' => 'Central African Republic', 'CG' => 'Congo', 'CH' => 'Switzerland',
    'CI' => 'Cote d\'Ivoire', 'CK' => 'Cook Islands', 'CL' => 'Chile', 'CM' => 'Cameroon', 'CN' => 'China', 'CO' => 'Colombia',
    'CR' => 'Costa Rica', 'CU' => 'Cuba', 'CV' => 'Cape Verde', 'CX' => 'Christmas Island', 'CY' => 'Cyprus',
    'CZ' => 'Czech Republic', 'DE' => 'Germany', 'DJ' => 'Djibouti', 'DK' => 'Denmark', 'DM' => 'Dominica',
    'DO' => 'Dominican Republic', 'DZ' => 'Algeria', 'EC' => 'Ecuador', 'EE' => 'Estonia', 'EG' => 'Egypt',
    'EH' => 'Western Sahara', 'ER' => 'Eritrea', 'ES' => 'Spain', 'ET' => 'Ethiopia', 'EU' => 'Europe', 'FI' => 'Finland',
    'FJ' => 'Fiji', 'FK' => 'Falkland Islands (Malvinas)', 'FM' => 'Micronesia', 'FO' => 'Faroe Islands', 'FR' => 'France',
    'GA' => 'Gabon', 'GB' => 'United Kingdom', 'GD' => 'Grenada', 'GE' => 'Georgia', 'GF' => 'French Guiana', 'GH' => 'Ghana',
    'GI' => 'Gibraltar', 'GL' => 'Greenland', 'GM' => 'Gambia', 'GN' => 'Guinea', 'GP' => 'Guadeloupe', 'GQ' => 'Equatorial Guinea',
    'GR' => 'Greece', 'GS' => 'South Georgia and the South Sandwich Islands', 'GT' => 'Guatemala', 'GU' => 'Guam',
    'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HK' => 'Hong Kong', 'HM' => 'Heard Island and McDonald Islands',
    'HN' => 'Honduras', 'HR' => 'Croatia', 'HT' => 'Haiti', 'HU' => 'Hungary', 'ID' => 'Indonesia', 'IE' => 'Ireland',
    'IL' => 'Israel', 'IN' => 'India', 'IO' => 'British Indian Ocean Territory', 'IQ' => 'Iraq', 'IR' => 'Iran',
    'IS' => 'Iceland', 'IT' => 'Italy', 'JM' => 'Jamaica', 'JO' => 'Jordan', 'JP' => 'Japan', 'KE' => 'Kenya',
    'KG' => 'Kyrgyzstan', 'KH' => 'Cambodia', 'KI' => 'Kiribati', 'KM' => 'Comoros', 'KN' => 'Saint Kitts and Nevis',
    'KP' => 'North Korea', 'KR' => 'South Korea', 'KW' => 'Kuwait', 'KY' => 'Cayman Islands', 'KZ' => 'Kazakhstan',
    'LA' => 'Lao People\'s Democratic Republic', 'LB' => 'Lebanon', 'LC' => 'Saint Lucia', 'LI' => 'Liechtenstein',
    'LK' => 'Sri Lanka', 'LR' => 'Liberia', 'LS' => 'Lesotho', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'LV' => 'Latvia',
    'LY' => 'Libya', 'MA' => 'Morocco', 'MC' => 'Monaco', 'MD' => 'Moldova', 'ME' => 'Montenegro', 'MG' => 'Madagascar',
    'MH' => 'Marshall Islands', 'MK' => 'Macedonia', 'ML' => 'Mali', 'MM' => 'Myanmar (Burma)', 'MN' => 'Mongolia',
    'MO' => 'Macao', 'MP' => 'Northern Mariana Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MS' => 'Montserrat',
    'MT' => 'Malta', 'MU' => 'Mauritius', 'MV' => 'Maldives', 'MW' => 'Malawi', 'MX' => 'Mexico', 'MY' => 'Malaysia',
    'MZ' => 'Mozambique', 'NA' => 'Namibia', 'NC' => 'New Caledonia', 'NE' => 'Niger', 'NF' => 'Norfolk Island',
    'NG' => 'Nigeria', 'NI' => 'Nicaragua', 'NL' => 'Netherlands', 'NO' => 'Norway', 'NP' => 'Nepal', 'NR' => 'Nauru',
    'NU' => 'Niue', 'NZ' => 'New Zealand', 'OM' => 'Oman', 'PA' => 'Panama', 'PE' => 'Peru', 'PF' => 'French Polynesia',
    'PG' => 'Papua New Guinea', 'PH' => 'Philippines', 'PK' => 'Pakistan', 'PL' => 'Poland', 'PM' => 'Saint Pierre and Miquelon',
    'PR' => 'Puerto Rico', 'PS' => 'Palestinian Territory', 'PT' => 'Portugal', 'PW' => 'Palau', 'PY' => 'Paraguay',
    'QA' => 'Qatar', 'RE' => 'Reunion', 'RO' => 'Romania', 'RS' => 'Serbia', 'RU' => 'Russia', 'RW' => 'Rwanda',
    'SA' => 'Saudi Arabia', 'SB' => 'Solomon Islands', 'SC' => 'Seychelles', 'SD' => 'Sudan', 'SE' => 'Sweden',
    'SG' => 'Singapore', 'SH' => 'Saint Helena', 'SI' => 'Slovenia', 'SJ' => 'Svalbard and Jan Mayen', 'SK' => 'Slovakia',
    'SL' => 'Sierra Leone', 'SM' => 'San Marino', 'SN' => 'Senegal', 'SO' => 'Somalia', 'SR' => 'Suriname',
    'ST' => 'Sao Tome and Principe', 'SV' => 'El Salvador', 'SY' => 'Syria', 'SZ' => 'Swaziland',
    'TC' => 'Turks and Caicos Islands', 'TD' => 'Chad', 'TF' => 'French Southern Territories', 'TG' => 'Togo',
    'TH' => 'Thailand', 'TJ' => 'Tajikistan', 'TK' => 'Tokelau', 'TM' => 'Turkmenistan', 'TN' => 'Tunisia',
    'TO' => 'Tonga', 'TR' => 'Turkey', 'TT' => 'Trinidad and Tobago', 'TV' => 'Tuvalu', 'TW' => 'Taiwan',
    'TZ' => 'Tanzania', 'UA' => 'Ukraine', 'UG' => 'Uganda', 'UM' => 'United States Minor Outlying Islands',
    'US' => 'United States', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VA' => 'Vatican City',
    'VC' => 'Saint Vincent and the Grenadines', 'VE' => 'Venezuela', 'VG' => 'British Virgin Islands',
    'VI' => 'U.S. Virgin Islands', 'VN' => 'Vietnam', 'VU' => 'Vanuatu', 'WF' => 'Wallis and Futuna',
    'WS' => 'Samoa', 'YE' => 'Yemen', 'YT' => 'Mayotte', 'ZA' => 'South Africa', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe',
];

$globe_data = [];
$country_totals = [];

if (is_array($country_metrics)) {
    foreach($country_metrics as $row) {
        if(isset($row['x'])) $country_totals[$row['x']] = $row['y'];
    }
}

$city_coords_file = __DIR__ . '/city_coords.json';
$city_coords = file_exists($city_coords_file) ? json_decode(file_get_contents($city_coords_file), true) : [];

// Generic User-Agent based on domain using this script
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$user_agent = "UmamiGlobeMap/1.0 (contact@$host)";

if (is_array($city_metrics)) {
    foreach($city_metrics as $city) {
        $city_name = isset($city['x']) ? $city['x'] : null;
        $country_code = isset($city['country']) ? $city['country'] : null;
        $visitors = isset($city['y']) ? $city['y'] : 0;
        
        if(!$city_name || !$country_code || $visitors == 0) continue;
        
        // Prevent Double-Plotting country dots
        if(isset($country_totals[$country_code])) {
            $country_totals[$country_code] -= $visitors;
            if($country_totals[$country_code] < 0) $country_totals[$country_code] = 0;
        }
        
        $cache_key = $city_name . ',' . $country_code;
        if(!isset($city_coords[$cache_key])) {
            // New City Found! Geocode using Nominatim
            $geocode_url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($city_name) . "&countrycodes=" . urlencode($country_code) . "&format=json&limit=1";
            $ch_geo = curl_init($geocode_url);
            curl_setopt($ch_geo, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_geo, CURLOPT_USERAGENT, $user_agent); // Required by Nominatim
            curl_setopt($ch_geo, CURLOPT_TIMEOUT, 5); 
            $geo_res = curl_exec($ch_geo);
            curl_close($ch_geo);
            
            $geo_data = json_decode($geo_res, true);
            if(is_array($geo_data) && count($geo_data) > 0) {
                $city_coords[$cache_key] = [
                    'lat' => (float)$geo_data[0]['lat'],
                    'lng' => (float)$geo_data[0]['lon']
                ];
                file_put_contents($city_coords_file, json_encode($city_coords, JSON_PRETTY_PRINT)); // Save instantly
            }
            // SLEEP to respect Nominatim's strict 1 request/second API limit.
            // Works perfectly for real, organic traffic. Mass DB injections require Python pre-caching.
            usleep(1500000); 
        }
        
        $final_lat = isset($city_coords[$cache_key]) ? $city_coords[$cache_key]['lat'] : (isset($geo_map[$country_code]) ? $geo_map[$country_code]['lat'] : 0);
        $final_lng = isset($city_coords[$cache_key]) ? $city_coords[$cache_key]['lng'] : (isset($geo_map[$country_code]) ? $geo_map[$country_code]['lng'] : 0);
        
        $full_country_name = isset($country_names[$country_code]) ? $country_names[$country_code] : $country_code;
        
        $globe_data[] = [
            'lat' => $final_lat,
            'lng' => $final_lng,
            'weight' => $visitors,
            'label' => $city_name . ', ' . $full_country_name
        ];
    }
}

// Plot remaining generic country dots for visitors without specific cities
foreach($country_totals as $country_code => $visitors) {
    if($visitors > 0 && isset($geo_map[$country_code])) {
        $label = isset($country_names[$country_code]) ? $country_names[$country_code] : $country_code;
        $globe_data[] = [
            'lat' => $geo_map[$country_code]['lat'],
            'lng' => $geo_map[$country_code]['lng'],
            'weight' => $visitors,
            'label' => $label
        ];
    }
}

$stats_url = "$umami_url/api/websites/$website_id/stats?startAt=$start_at&endAt=$end_at";
$ch_stats = curl_init($stats_url);
curl_setopt($ch_stats, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_stats, CURLOPT_HTTPHEADER, array("Authorization: Bearer $token"));
$stats_response = json_decode(curl_exec($ch_stats), true);
curl_close($ch_stats);

$total_visits = isset($stats_response['visits']) ? $stats_response['visits'] : 0;
$total_visitors = isset($stats_response['visitors']) ? $stats_response['visitors'] : 0;

$final_output = [
    'globe' => $globe_data,
    'total_visits' => $total_visits,
    'total_visitors' => $total_visitors
];

// 5. Save to cache and output
$json_output = json_encode($final_output);
file_put_contents($cache_file, $json_output);
echo $json_output;
?>
