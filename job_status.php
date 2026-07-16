<?php
/**
 * job_status.php?job=<id>
 * Lightweight poll endpoint. Returns the job's current status so the browser
 * can wait for an async design without holding a long HTTP connection.
 * Responses: {status: queued|running|done|error, error?}
 */

header('Content-Type: application/json');
header('Cache-Control: no-store');

$job_id = isset($_GET['job']) ? preg_replace('/[^A-Za-z0-9_\-]/', '', $_GET['job']) : '';
if ($job_id === '') {
    echo json_encode(['status' => 'unknown', 'error' => 'Missing job id']);
    exit;
}

$job_file = __DIR__ . "/query/$job_id.json";
if (!is_file($job_file)) {
    echo json_encode(['status' => 'unknown']);
    exit;
}

$data = json_decode(file_get_contents($job_file), true);
if (!is_array($data)) {
    echo json_encode(['status' => 'unknown']);
    exit;
}

// Derive status: an explicit 'status' wins; otherwise a legacy done record
// (written by the synchronous form path) has 'success' set.
$status = $data['status'] ?? (isset($data['success']) ? ($data['success'] ? 'done' : 'error') : 'running');

$resp = ['status' => $status];
if ($status === 'error' && !empty($data['error'])) {
    $resp['error'] = $data['error'];
}
echo json_encode($resp);
