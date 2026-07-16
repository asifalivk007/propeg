<?php
/**
 * api/generate_linker.php
 * Endpoint to compute gepegRNA linker asynchronously
 */

header('Content-Type: application/json');

// Prevent PHP from timing out during long linker computation
set_time_limit(0);

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

// Get input JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['spacer']) || !isset($input['modified_scaffold']) || !isset($input['rt']) || !isset($input['pbs'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: spacer, modified_scaffold, rt, pbs']);
    exit;
}

$spacer = strtoupper(preg_replace('/[^ACGTU]/i', '', $input['spacer']));
$mod_scaffold = strtoupper(preg_replace('/[^ACGTU]/i', '', $input['modified_scaffold']));
$rt = strtoupper(preg_replace('/[^ACGTU]/i', '', $input['rt']));
$pbs = strtoupper(preg_replace('/[^ACGTU]/i', '', $input['pbs']));

// Reverse complement helper
function revCom($seq) {
    return strtr(strrev($seq), 'ACGTUacgtu', 'TGCAAtgcaa');
}

// Prepare sequences for pegLIT.
// pegLIT expects the spacer in 5'->3' pegRNA orientation (RC of the on-target),
// and likewise wants RC'd RT and PBS subsequences.
$spacer_for_peglit = revCom($spacer);

// RT and PBS
$rt_for_peglit = revCom($rt);
$pbs_for_peglit = revCom($pbs);

// Prepare payload for Flask server
$flask_url = "http://127.0.0.1:5001/api/compute_linker";

$payload = json_encode([
    'spacer' => $spacer_for_peglit,
    'scaffold' => $mod_scaffold,
    'template' => $rt_for_peglit,
    'pbs' => $pbs_for_peglit,
    'linker_length' => 8
]);

// Use cURL to make a fast internal POST request to the local Flask server
$ch = curl_init($flask_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_TIMEOUT, 600); // Wait up to 10 minutes just in case

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

$linker = "NNNNNNNN";
$success = false;

if ($http_code === 200 && $response) {
    $result_data = json_decode($response, true);
    if (isset($result_data['linker']) && $result_data['success']) {
        $linker = $result_data['linker'];
        $success = true;
    }
}

echo json_encode([
    'linker' => $linker,
    'success' => $success,
    'debug_flask_response' => $response,
    'debug_curl_error' => $curl_error
]);
