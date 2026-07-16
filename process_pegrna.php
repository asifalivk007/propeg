<?php
/**
 * process_pegrna.php
 * Backend endpoint for pegRNA design. Three entry modes:
 *   - CLI worker:  `php process_pegrna.php <job_id>` — reads params from
 *                  query/<job_id>.request.json and writes status+results to
 *                  query/<job_id>.json. Used by the async submit_design.php flow.
 *   - AJAX (XHR):  returns JSON directly.
 *   - Form submit: writes a per-job result file and redirects to results.php.
 */

$query_dir = __DIR__ . '/query';
if (!is_dir($query_dir)) {
    mkdir($query_dir, 0777, true);
}

$isWorker = (PHP_SAPI === 'cli');
$job_id = '';

if ($isWorker) {
    // Async worker: consume the queued request file for this job id.
    $job_id = isset($argv[1]) ? preg_replace('/[^A-Za-z0-9_\-]/', '', $argv[1]) : '';
    if ($job_id === '') {
        fwrite(STDERR, "usage: php process_pegrna.php <job_id>\n");
        exit(1);
    }
    $reqRaw = @file_get_contents("$query_dir/$job_id.request.json");
    $_POST = $reqRaw ? (json_decode($reqRaw, true) ?: []) : [];
    $isAjax = false;
    // Flip queued -> running so the poller can reflect it.
    @file_put_contents("$query_dir/$job_id.json", json_encode(['status' => 'running']));
} else {
    session_start();
    // Check if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    if ($isAjax) {
        header('Content-Type: application/json');
    }
}

// Error handler
function customError($errno, $errstr) {
    global $isAjax, $isWorker, $job_id, $query_dir;
    if ($isWorker) {
        @file_put_contents("$query_dir/$job_id.json", json_encode([
            'status'  => 'error',
            'success' => false,
            'error'   => "Error [$errno]: $errstr",
        ]));
    } else if ($isAjax) {
        echo json_encode(['success' => false, 'error' => "Error [$errno]: $errstr"]);
    } else if (!empty($job_id) && !empty($query_dir)) {
        // Persist the error to this job's file so concurrent designs don't clash.
        @file_put_contents("$query_dir/$job_id.json", json_encode([
            'success' => false,
            'error'   => "Error [$errno]: $errstr",
        ]));
        header('Location: results.php?job=' . urlencode($job_id));
    } else {
        // Error before a job id was assigned — fall back to the session.
        $_SESSION['pegrna_error'] = "Error [$errno]: $errstr";
        header('Location: results.php');
    }
    exit();
}
set_error_handler("customError");

// Terminate with an error, routing it to the right sink per mode:
// worker -> status:error in the job file; ajax/legacy -> JSON to the caller.
function finishError($error, $debug = null) {
    global $isWorker, $job_id, $query_dir;
    $payload = ['success' => false, 'error' => $error];
    if ($debug !== null) { $payload['debug'] = $debug; }
    if ($isWorker) {
        $payload['status'] = 'error';
        @file_put_contents("$query_dir/$job_id.json", json_encode($payload));
    } else {
        echo json_encode($payload);
    }
    exit();
}

// Get POST data
$inputSequence = $_POST['inputSequence'] ?? '';
$PAM = $_POST['PAM'] ?? 'NGG';
$User_PAM = $_POST['User_PAM'] ?? '';
$CutToPAM = $_POST['CutToPAM'] ?? '-3';
$OnTargetLength = $_POST['OnTargetLength'] ?? '20';
$PE_window_min = $_POST['PE_window_min'] ?? '1';
$PE_window_max = $_POST['PE_window_max'] ?? '15';
$PBS_Length_min = $_POST['PBS_Length_min'] ?? '7';
$PBS_Length_max = $_POST['PBS_Length_max'] ?? '16';
$PBS_CG_Content_min = $_POST['PBS_CG_Content_min'] ?? '0';
$PBS_CG_Content_max = $_POST['PBS_CG_Content_max'] ?? '100';
$TM_Best = $_POST['TM_Best'] ?? '30';
$RT_Length_min = $_POST['RT_Length_min'] ?? '7';
$RT_Length_max = $_POST['RT_Length_max'] ?? '16';
$OnTarget_CG_Content_min = $_POST['OnTarget_CG_Content_min'] ?? '0';
$OnTarget_CG_Content_max = $_POST['OnTarget_CG_Content_max'] ?? '100';

// Checkboxes
$Tm_model = isset($_POST['Tm_model']) ? $_POST['Tm_model'] : 'True';
$Exclude_LastG_in_RT = isset($_POST['Exclude_LastG_in_RT']) ? $_POST['Exclude_LastG_in_RT'] : 'True';
$CCNNGG_model = isset($_POST['CCNNGG_model']) ? $_POST['CCNNGG_model'] : 'True';

// PE3/PE3b secondary nicking sgRNA (Off keeps legacy behaviour)
$Nick_Model = $_POST['Nick_Model'] ?? 'Off';
$Nick_Distance_min = $_POST['Nick_Distance_min'] ?? '40';
$Nick_Distance_max = $_POST['Nick_Distance_max'] ?? '100';

// Primer settings
$Primer = $_POST['Primer'] ?? 'OsU3';
$Forward_Primer_left = $_POST['Forward_Primer_left'] ?? '';
$Forward_Primer_right = $_POST['Forward_Primer_right'] ?? '';
$Reverse_Primer_left = $_POST['Reverse_Primer_left'] ?? '';
$Reverse_Primer_right = $_POST['Reverse_Primer_right'] ?? '';

// Set primer sequences based on selection
if ($Primer == 'OsU3') {
    $Forward_Primer_left = 'TTGTGCAGATGATCCGTGGCG';
    $Forward_Primer_right = 'GTTTAAGAGCTATGCTGG';
    $Reverse_Primer_left = 'CTATGACCATGATTACGCCAAGCTTAAAAAAA';
    $Reverse_Primer_right = 'GCACCGACTCGGTGCCAC';
} elseif ($Primer == 'TaU3') {
    $Forward_Primer_left = 'AGGCGCGGCACCAAGAAGCG';
    $Forward_Primer_right = 'GTTTAAGAGCTATGCTGG';
    $Reverse_Primer_left = 'ATTATGGAGAAACTCGAGCCATGGAAAAAAA';
    $Reverse_Primer_right = 'GCACCGACTCGGTGCCACTT';
} elseif ($Primer == 'TaU6') {
    $Forward_Primer_left = 'CTTGCTGCATCAGACTTG';
    $Forward_Primer_right = 'GTTTAAGAGCTATGCTGGAA';
    $Reverse_Primer_left = 'TGGCCGATTCATTAATGCAGGGTACCAAAAAAA';
    $Reverse_Primer_right = 'GCACCGACTCGGTGCCACTT';
} elseif ($Primer == 'pHn-Cas9-V2') {
    $Forward_Primer_left = 'TTGTGCAGATGATCCGTGGCG';
    $Forward_Primer_right = 'GTTTAAGAGCTATGCTGG';
    $Reverse_Primer_left = 'ACGCTGCACTGCAGGCATGCAAGCTTAAAAAAA';
    $Reverse_Primer_right = 'GCACCGACTCGGTGCCAC';
}

// Handle custom PAM
if ($PAM == 'User_Defined' && !empty($User_PAM)) {
    $PAM = $User_PAM;
}

// Validate input
if (empty($inputSequence)) {
    finishError('Input sequence is required');
}

// Clean the sequence
$inputSequence = preg_replace("/\s+/", "", $inputSequence);

// $query_dir was resolved at the top. Assign a fresh job id in web mode
// (the CLI worker already received one via argv).
if ($job_id === '') {
    // Unique job id shared by this design's input/result/response files, so
    // concurrent submissions never collide (results were previously handed
    // off through a single global session slot the second design clobbered).
    $job_id = date("Y-m-d_H-i-s") . "_" . bin2hex(random_bytes(4));
}
$filename = $query_dir . "/" . $job_id . ".fa";
$result_file = $query_dir . "/" . $job_id . ".result";

// Web form path: release the session lock now (results are handed off by job
// id), so the long PRIDICT run no longer blocks a concurrent submission whose
// session_start() would otherwise wait for this request to finish.
if (!$isWorker) {
    session_write_close();
}

// Opportunistic cleanup: drop job artifacts older than 24h so query/ doesn't grow.
foreach (glob($query_dir . "/*") as $old) {
    if (is_file($old) && (time() - filemtime($old)) > 86400) {
        @unlink($old);
    }
}

// Create parameter file content
$param_content = "Input_Sequence\t$inputSequence\n";
$param_content .= "PAM\t$PAM\n";
$param_content .= "CutToPAM\t$CutToPAM\n";
$param_content .= "OnTargetLength\t$OnTargetLength\n";
$param_content .= "PE_Window\t$PE_window_min-$PE_window_max\n";
$param_content .= "PBS_Length\t$PBS_Length_min-$PBS_Length_max\n";
$param_content .= "PBS_CG_Content\t$PBS_CG_Content_min-$PBS_CG_Content_max\n";
$param_content .= "TM_Best\t$TM_Best\n";
$param_content .= "RT_Length\t$RT_Length_min-$RT_Length_max\n";
$param_content .= "Tm_model\t$Tm_model\n";
$param_content .= "Exclude_LastG_in_RT\t$Exclude_LastG_in_RT\n";
$param_content .= "OnTarget_CG_Content\t$OnTarget_CG_Content_min-$OnTarget_CG_Content_max\n";
$param_content .= "CCNNGG_model\t$CCNNGG_model\n";
$param_content .= "Nick_Model\t$Nick_Model\n";
$param_content .= "Nick_Distance\t$Nick_Distance_min-$Nick_Distance_max\n";
$param_content .= "UpstreamPrimer5\t$Forward_Primer_left\n";
$param_content .= "UpstreamPrimer3\t$Forward_Primer_right\n";
$param_content .= "DownstreamPrimer5\t$Reverse_Primer_left\n";
$param_content .= "DownstreamPrimer3\t$Reverse_Primer_right\n";

// Write parameter file
if (file_put_contents($filename, $param_content) === false) {
    finishError('Could not create parameter file');
}

// Execute the Python design script
$design_script = __DIR__ . DIRECTORY_SEPARATOR . "propeg.py";
$python_exe = locatePythonExe();
if ($python_exe === null) {
    finishError('Python 3 interpreter not found. Install Python 3 or ensure one of the bundled venvs is present.');
}
$design_command = escapeshellarg($python_exe) . ' ' . escapeshellarg($design_script)
    . ' ' . escapeshellarg($filename) . ' ' . escapeshellarg($result_file) . ' 2>&1';

// Bounded concurrency (worker mode only): grab one of N flock slots before the
// heavy design + PRIDICT run so a small box isn't overwhelmed. The lock is held
// via the open handle and released automatically when this process exits.
$sem_handle = null;
if ($isWorker) {
    $slots = 2; // ~= CPU cores; keep in step with the box
    $waited = 0;
    while ($sem_handle === null) {
        for ($i = 0; $i < $slots; $i++) {
            $fh = fopen("$query_dir/.worker_slot_$i.lock", 'c');
            if ($fh && flock($fh, LOCK_EX | LOCK_NB)) { $sem_handle = $fh; break; }
            if ($fh) { fclose($fh); }
        }
        if ($sem_handle === null) {
            if ($waited >= 1800) { break; } // don't wait forever for a slot
            sleep(2); $waited += 2;
        }
    }
}

$output = [];
$return_code = 0;
exec($design_command, $output, $return_code);

// Check if result file was created
if (!file_exists($result_file)) {
    finishError('Design script failed to generate results', [
        'command' => $design_command,
        'return_code' => $return_code,
        'output' => implode("\n", $output),
    ]);
}

// Read and parse the result HTML
$result_html = file_get_contents($result_file);

// Parse the HTML to extract structured data
$results = parseResultHTML($result_html);

// Clean up temporary files (optional - keep for debugging)
// unlink($filename);
// unlink($result_file);

// Run PRIDICT2.0 if valid (>= 100bp on both sides of the edit)
$posOpen = strpos($inputSequence, '(');
$posClose = strpos($inputSequence, ')');
$pridictDict = [];

error_log("PRIDICT DEBUG: posOpen=$posOpen, posClose=$posClose");

if ($posOpen !== false && $posClose !== false) {
    $leftFlankLen = $posOpen;
    $rightFlankLen = strlen($inputSequence) - $posClose - 1;
    
    error_log("PRIDICT DEBUG: leftFlank=$leftFlankLen, rightFlank=$rightFlankLen");
    
    if ($leftFlankLen >= 99 && $rightFlankLen >= 99) {
        $seqName = "seq_" . time() . "_" . rand(100, 999);
        $pythonExe = PHP_OS_FAMILY === 'Windows'
            ? __DIR__ . '/ext_tools/PRIDICT2/venv/Scripts/python.exe'
            : __DIR__ . '/ext_tools/PRIDICT2/venv/bin/python3';
        $pridictScript = __DIR__ . '/ext_tools/PRIDICT2/pridict2_pegRNA_design.py';
        $pridictDir = __DIR__ . '/ext_tools/PRIDICT2';
        
        error_log("PRIDICT DEBUG: pythonExe exists=" . (file_exists($pythonExe) ? 'YES' : 'NO'));
        error_log("PRIDICT DEBUG: script exists=" . (file_exists($pridictScript) ? 'YES' : 'NO'));
        
        $cmd = "cd " . escapeshellarg($pridictDir) . " && " . escapeshellarg($pythonExe) . " " . escapeshellarg($pridictScript) . " single --sequence-name " . escapeshellarg($seqName) . " --sequence " . escapeshellarg($inputSequence) . " 2>&1";
        
        error_log("PRIDICT DEBUG: cmd=$cmd");
        
        $pridictOutput = [];
        $pridictRet = 0;
        
        // Increase time limit for ML execution
        set_time_limit(600);
        exec($cmd, $pridictOutput, $pridictRet);
        
        error_log("PRIDICT DEBUG: exec returned code=$pridictRet");
        error_log("PRIDICT DEBUG: exec output=" . implode("\n", array_slice($pridictOutput, -5)));
        
        $csvFile = $pridictDir . "/predictions/" . $seqName . "_pegRNA_Pridict_full.csv";
        error_log("PRIDICT DEBUG: csvFile=$csvFile, exists=" . (file_exists($csvFile) ? 'YES' : 'NO'));
        
        if (file_exists($csvFile)) {
            if (($handle = fopen($csvFile, "r")) !== FALSE) {
                $header = fgetcsv($handle, 10000, ",");
                $colSpacer = array_search('Spacer-Sequence', $header);
                $colPBS = array_search('PBSrevcomp', $header);
                $colRT = array_search('RTrevcomp', $header);
                $colHEK = array_search('PRIDICT2_0_editing_Score_deep_HEK', $header);
                
                while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                    $spacer = strtoupper(trim($data[$colSpacer]));
                    $pbs = strtoupper(trim($data[$colPBS]));
                    $rt = strtoupper(trim($data[$colRT]));
                    $efficiency = round((float)$data[$colHEK], 1);
                    
                    $key = $spacer . "_" . $pbs . "_" . $rt;
                    
                    if (!isset($pridictDict[$key]) || $efficiency > $pridictDict[$key]) {
                        $pridictDict[$key] = $efficiency;
                    }
                }
                fclose($handle);
            }
        }
    } else {
        error_log("PRIDICT DEBUG: SKIPPED - flanking too short");
    }
} else {
    error_log("PRIDICT DEBUG: SKIPPED - no parentheses found in sequence");
}

// Prepare response data
$responseData = [
    'success' => true,
    'html' => $result_html,
    'results' => $results,
    'pridictDict' => $pridictDict,
    'inputSequence' => $inputSequence
];

$responseData['design_mode'] = $_POST['design_mode'] ?? 'pegrna';

if ($isWorker) {
    // Async worker: mark the job done; the browser is polling job_status.php
    // and will then load results.php?job=<id>.
    $responseData['status'] = 'done';
    file_put_contents("$query_dir/$job_id.json", json_encode($responseData));
    exit(0);
} else if ($isAjax) {
    // Return JSON for AJAX requests
    echo json_encode($responseData);
} else {
    // Legacy synchronous form submit: persist per-job and redirect by job id.
    file_put_contents("$query_dir/$job_id.json", json_encode($responseData));
    header('Location: results.php?job=' . urlencode($job_id));
    exit();
}

/**
 * Resolve a usable Python 3 executable.
 * Falls back through: PATH lookup, then the bundled peglit venv, then PRIDICT2 venv.
 * Returns the executable path or null when none can be invoked.
 */
function locatePythonExe() {
    $candidates = [];
    if (PHP_OS_FAMILY === 'Windows') {
        $candidates[] = 'python';
        $candidates[] = 'python3';
        $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . 'ext_tools' . DIRECTORY_SEPARATOR . 'peglit' . DIRECTORY_SEPARATOR . 'venv'
                      . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe';
        $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . 'ext_tools' . DIRECTORY_SEPARATOR . 'PRIDICT2' . DIRECTORY_SEPARATOR . 'venv'
                      . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe';
    } else {
        $candidates[] = 'python3';
        $candidates[] = 'python';
        $candidates[] = __DIR__ . '/ext_tools/peglit/venv/bin/python3';
        $candidates[] = __DIR__ . '/ext_tools/PRIDICT2/venv/bin/python3';
    }
    foreach ($candidates as $cand) {
        $check = [];
        $code = 0;
        @exec(escapeshellarg($cand) . ' --version 2>&1', $check, $code);
        if ($code === 0) {
            return $cand;
        }
    }
    return null;
}

/**
 * Parse the result HTML to extract structured pegRNA data
 */
function parseResultHTML($html) {
    $results = [
        'programs' => [],
        'dualPegRNA' => false,
        'dualPegRNAMessage' => ''
    ];
    
    // Check for dual-pegRNA model message
    if (preg_match('/dual-pegRNA model could be used/i', $html)) {
        $results['dualPegRNA'] = true;
        $results['dualPegRNAMessage'] = 'Dual-pegRNA model can be used!';
    } elseif (preg_match('/dual-pegRNA model could not be used/i', $html)) {
        $results['dualPegRNAMessage'] = 'Dual-pegRNA model cannot be used.';
    }
    
    // Split by table to get individual programs
    preg_match_all('/<table[^>]*>(.*?)<\/table>/s', $html, $tables);
    
    $currentProgram = null;
    
    foreach ($tables[1] as $tableContent) {
        // Extract program number and recommendation
        if (preg_match('/No\.\s*(\d+)([^<]*)/i', $tableContent, $noMatch)) {
            $currentProgram = [
                'number' => $noMatch[1],
                'recommended' => strpos($noMatch[2], 'recommended') !== false,
                'strand' => '',
                'spacerPAM' => '',
                'spacerGC' => '',
                'pbs' => [],
                'rt' => [],
                'forwardPrimer' => '',
                'reversePrimer' => ''
            ];
        }
        
        // Extract strand
        if (preg_match('/Forward Strand|Reverse Strand/i', $tableContent, $strandMatch)) {
            if ($currentProgram) {
                $currentProgram['strand'] = $strandMatch[0];
            }
        }
        
        // Extract Spacer-PAM
        if (preg_match('/Spacer-PAM:.*?<td>([^<]+)<span[^>]*>([^<]+)(?:<\/span>)?<\/td>\s*\n?\s*<td>\(([0-9.]+)% GC\)/s', $tableContent, $pamMatch)) {
            if ($currentProgram) {
                $currentProgram['spacerPAM'] = $pamMatch[1] . $pamMatch[2];
                $currentProgram['spacer'] = $pamMatch[1];
                $currentProgram['pam'] = $pamMatch[2];
                $currentProgram['spacerGC'] = $pamMatch[3];
            }
        }
        
        // Extract PBS entries
        preg_match_all('/<tr>\s*<td><span[^>]*>([^<]*)(?:<\/span>)?<\/td>\s*\n?\s*<td><span[^>]*>([A-Za-z]+)(?:<\/span>)?<\/td>\s*\n?\s*<td><span[^>]*>(\d+)(?:<\/span>)?<\/td>\s*\n?\s*<td><span[^>]*>(\d+)(?:<\/span>)?<\/td>\s*\n?\s*<td><span[^>]*>([0-9.]+)(?:<\/span>)?<\/td>/s', $tableContent, $pbsMatches, PREG_SET_ORDER);
        
        foreach ($pbsMatches as $pbs) {
            if ($currentProgram) {
                $currentProgram['pbs'][] = [
                    'recommended' => !empty(trim($pbs[1])),
                    'sequence' => $pbs[2],
                    'length' => $pbs[3],
                    'tm' => $pbs[4],
                    'gc' => $pbs[5]
                ];
            }
        }
        
        // Extract RT entries
        preg_match_all('/<tr>\s*<td><span[^>]*>([^<]*)(?:<\/span>)?<\/td>\s*\n?\s*<td><span[^>]*(?:\s+data-scaffold-mod="[^"]*")?[^>]*>([A-Za-z]+)(?:<\/span>)?<\/td>\s*\n?\s*<td><span[^>]*>(\d+)(?:<\/span>)?<\/td>\s*\n?\s*<td>(?:<\/td>)?\s*\n?\s*<td>(?:<\/td>)?/s', $tableContent, $rtMatches, PREG_SET_ORDER);
        
        foreach ($rtMatches as $rt) {
            if ($currentProgram) {
                $currentProgram['rt'][] = [
                    'recommended' => !empty(trim($rt[1])),
                    'sequence' => $rt[2],
                    'length' => $rt[3]
                ];
            }
        }
        
        // Extract primers
        if (preg_match('/Forward primer.*?<td[^>]*>([^<]+)<\/td>/s', $tableContent, $fwdMatch)) {
            if ($currentProgram) {
                $currentProgram['forwardPrimer'] = trim($fwdMatch[1]);
            }
        }
        if (preg_match('/Reverse primer.*?<td[^>]*>([^<]+)<\/td>/s', $tableContent, $revMatch)) {
            if ($currentProgram) {
                $currentProgram['reversePrimer'] = trim($revMatch[1]);
            }
        }
        
        if ($currentProgram && !empty($currentProgram['spacerPAM'])) {
            $results['programs'][] = $currentProgram;
            $currentProgram = null;
        }
    }
    
    return $results;
}
?>
