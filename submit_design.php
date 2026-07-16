<?php
/**
 * submit_design.php
 * Async entry point for pegRNA design. Enqueues the job, spawns a detached
 * CLI worker (process_pegrna.php <job_id>), and returns the job id immediately
 * so the browser can poll job_status.php instead of holding the connection
 * open for minutes (which any proxy/CDN would time out).
 */

header('Content-Type: application/json');

$inputSequence = $_POST['inputSequence'] ?? '';
if (trim($inputSequence) === '') {
    echo json_encode(['success' => false, 'error' => 'Input sequence is required']);
    exit;
}

$query_dir = __DIR__ . '/query';
if (!is_dir($query_dir)) {
    mkdir($query_dir, 0777, true);
}

// Opportunistic cleanup: drop job artifacts older than 24h.
foreach (glob($query_dir . '/*') as $old) {
    if (is_file($old) && (time() - filemtime($old)) > 86400) {
        @unlink($old);
    }
}

$job_id = date('Y-m-d_H-i-s') . '_' . bin2hex(random_bytes(4));

// Persist the exact POST params for the worker, and the initial status.
if (file_put_contents("$query_dir/$job_id.request.json", json_encode($_POST)) === false) {
    echo json_encode(['success' => false, 'error' => 'Could not queue the job (write failed).']);
    exit;
}
file_put_contents("$query_dir/$job_id.json", json_encode(['status' => 'queued']));

// Spawn the detached worker.
$php = locatePhpCli();
$worker = escapeshellarg($php) . ' '
        . escapeshellarg(__DIR__ . '/process_pegrna.php') . ' '
        . escapeshellarg($job_id);
spawnDetached($worker, "$query_dir/$job_id.log");

echo json_encode(['success' => true, 'job_id' => $job_id, 'status' => 'queued']);

/** Locate a PHP CLI binary usable for the background worker. */
function locatePhpCli() {
    $cands = [];
    if (defined('PHP_BINDIR') && PHP_BINDIR) {
        $cands[] = PHP_BINDIR . (DIRECTORY_SEPARATOR === '\\' ? '\\php.exe' : '/php');
    }
    if (DIRECTORY_SEPARATOR === '\\') {
        $cands[] = 'C:\\xampp\\php\\php.exe';
    } else {
        $cands[] = '/usr/bin/php';
        $cands[] = '/usr/local/bin/php';
    }
    foreach ($cands as $c) {
        if (@is_file($c)) { return $c; }
    }
    return 'php'; // fall back to PATH
}

/** Launch a command fully detached so the web request can return at once. */
function spawnDetached($cmd, $logfile) {
    if (DIRECTORY_SEPARATOR === '\\') {
        // Windows (dev): start in a detached, hidden process.
        pclose(popen('start /B "" ' . $cmd . ' > ' . escapeshellarg($logfile) . ' 2>&1', 'r'));
    } else {
        // Linux (prod): setsid so the child survives after PHP-FPM returns.
        exec('setsid ' . $cmd . ' > ' . escapeshellarg($logfile) . ' 2>&1 &');
    }
}
