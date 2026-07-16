<?php
/**
 * results.php
 * Page to display pegRNA design results from PROpeg
 */

// Start session to retrieve results
session_start();

/**
 * Parse mutation info from input sequence format: left(ref/alt)right
 */
function parseMutationInfo($seq)
{
    $info = [
        'type' => 'Unknown',
        'ref' => '',
        'alt' => '',
        'position' => 0
    ];

    // Match the (ref/alt) pattern
    if (preg_match('/^(.*)\\(([^\\/]*)\\/([^\\)]*)\\)(.*)$/', $seq, $matches)) {
        $leftSeq = $matches[1];
        $ref = $matches[2];
        $alt = $matches[3];

        $info['ref'] = $ref ?: '-';
        $info['alt'] = $alt ?: '-';
        $info['position'] = strlen($leftSeq) + 1;

        // Determine mutation type
        if (empty($ref) && !empty($alt)) {
            $info['type'] = 'Insertion';
        } elseif (!empty($ref) && empty($alt)) {
            $info['type'] = 'Deletion';
        } else {
            $info['type'] = 'Substitution';
        }
    }

    return $info;
}

/**
 * Highlight the mutation in the sequence for display
 */
function highlightMutation($seq)
{
    // Replace (ref/alt) with highlighted version
    $highlighted = preg_replace(
        '/\\(([^\\/]*)\\/([^\\)]*)\\)/',
        '<span class="mutation-highlight">(<span class="ref">$1</span>/<span class="alt">$2</span>)</span>',
        htmlspecialchars($seq)
    );
    return $highlighted;
}

// Results are keyed by job id (query/<job_id>.json), so concurrent designs
// each read their own file. A legacy session slot is kept as a fallback.
$results = null;
$inputSequence = '';
$error = null;

if (isset($_GET['job'])) {
    // Sanitize to a bare job id (defends against path traversal).
    $job_id = preg_replace('/[^A-Za-z0-9_\-]/', '', $_GET['job']);
    $job_file = __DIR__ . "/query/$job_id.json";
    if ($job_id !== '' && is_file($job_file)) {
        $data = json_decode(file_get_contents($job_file), true);
        if (is_array($data) && !empty($data['success'])) {
            $results = $data;
            $inputSequence = $data['inputSequence'] ?? '';
            $designMode = $data['design_mode'] ?? 'pegrna';
        } else {
            $error = (is_array($data) && !empty($data['error']))
                ? $data['error']
                : 'No results to display. Please go back and design a pegRNA first.';
        }
    } else {
        $error = 'No results to display. Please go back and design a pegRNA first.';
    }
} elseif (isset($_SESSION['pegrna_results'])) {
    // Legacy fallback (single-slot session hand-off).
    $results = $_SESSION['pegrna_results'];
    $inputSequence = $_SESSION['pegrna_input'] ?? '';
    $designMode = $_SESSION['pegrna_design_mode'] ?? 'pegrna';
    unset($_SESSION['pegrna_results']);
    unset($_SESSION['pegrna_input']);
    unset($_SESSION['pegrna_design_mode']);
} else {
    $error = 'No results to display. Please go back and design a pegRNA first.';
}

// Map design mode to display titles
$modeTitles = ['gpegrna' => 'g-pegRNA', 'pegrna' => 'pegRNA', 'gepegrna' => 'g-epegRNA', 'epegrna' => 'epegRNA'];
$designModeTitle = $modeTitles[$designMode ?? 'pegrna'] ?? 'pegRNA';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($designModeTitle); ?> Design Results - PROpeg</title>
    <!-- Favicon — rounded-corner PNG (corners baked transparent), browser scales as needed -->
    <link rel="icon" type="image/png" href="img/propeg-favicon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/propeg-favicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/propeg-favicon.png">
    <link rel="apple-touch-icon" href="img/propeg-favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="img/propeg-favicon.png">
    <link rel="shortcut icon" href="img/propeg-favicon.png">
    <link rel="stylesheet" href="css/styles.css?v=2.1.18">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>window.process = { env: { NODE_ENV: 'production' } };</script>
    <script>window.designMode = '<?php echo htmlspecialchars($designMode ?? "pegrna"); ?>';</script>
    <?php
    // Cache-bust by file mtime so a rebuilt bundle is always re-fetched
    // (no more manual ?v bumps). Falls back to a constant if stat fails.
    $vizVer = function (string $f): string {
        $p = __DIR__ . '/' . $f;
        return is_file($p) ? (string) filemtime($p) : '1';
    };
    ?>
    <script src="js/pegrna-visualization.min.js?v=<?php echo $vizVer('js/pegrna-visualization.min.js'); ?>"></script>
    <script src="js/gpegrna-visualization.min.js?v=<?php echo $vizVer('js/gpegrna-visualization.min.js'); ?>"></script>
    <script src="js/gepegrna-visualization.min.js?v=<?php echo $vizVer('js/gepegrna-visualization.min.js'); ?>"></script>
    <style>
        .results-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .results-header h1 {
            color: var(--primary-color);
            margin: 0;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .mutation-card {
            background: var(--bg-secondary);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .mutation-card h3 {
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .mutation-details {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
        }

        .mutation-type-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .mutation-type-badge.substitution {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }

        .mutation-type-badge.deletion {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            color: white;
        }

        .mutation-type-badge.insertion {
            background: linear-gradient(135deg, #22c55e, #15803d);
            color: white;
        }

        .mutation-type-badge.unknown {
            background: #6b7280;
            color: white;
        }

        .mutation-summary {
            flex: 1;
        }

        .mutation-summary p {
            margin: 0;
            font-size: 1.1rem;
            color: var(--text-primary);
        }

        .mutation-summary strong {
            color: var(--primary-color);
            font-family: 'Courier New', monospace;
            background: #e0e7ff;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .mutation-sequence {
            font-family: 'Courier New', monospace;
            background: white;
            padding: 1rem;
            border-radius: 6px;
            word-break: break-all;
            font-size: 0.85rem;
            border-left: 4px solid var(--primary-color);
            line-height: 1.6;
        }

        .mutation-sequence .mutation-highlight {
            background: #fef3c7;
            padding: 2px 4px;
            border-radius: 4px;
            font-weight: 600;
        }

        .mutation-sequence .mutation-highlight .ref {
            color: #dc2626;
            text-decoration: line-through;
        }

        .mutation-sequence .mutation-highlight .alt {
            color: #16a34a;
            font-weight: bold;
        }

        .pegrna-results-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .pegrna-results-content {
            padding: 2rem;
            max-height: none;
            overflow: visible;
        }

        .pegrna-results-content #header,
        .pegrna-results-content #footer {
            display: none;
        }

        .pegrna-results-content #section {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 0;
        }

        .pegrna-results-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        .pegrna-results-content table th,
        .pegrna-results-content table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .pegrna-results-content table th {
            background: var(--bg-secondary);
        }

        .pegrna-results-content span[style*="color:red"] {
            color: #dc2626 !important;
            font-weight: 600;
        }

        .pegrna-results-content span[style*="background-color:#8FBC8F"] {
            background-color: var(--success-color) !important;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
        }

        /* Green styling for Program rows */
        .pegrna-results-content .program-row {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0) !important;
        }

        .pegrna-results-content .program-row th,
        .pegrna-results-content .program-row td {
            color: #166534 !important;
            font-weight: 600;
            background: transparent !important;
        }

        .pegrna-results-content .program-row th:first-child,
        .pegrna-results-content .program-row td:first-child {
            color: #15803d !important;
            font-weight: bold;
        }

        /* Red styling for recommendation rows */
        .pegrna-results-content .recommendation-row {
            background: linear-gradient(135deg, #fee2e2, #fecaca) !important;
        }

        .pegrna-results-content .recommendation-row td {
            color: #dc2626 !important;
            font-weight: 600;
        }

        /* Efficiency-score column keeps its own color even on a recommended row —
           red here would misread as a poor/error score rather than the top pick. */
        .pegrna-results-content .recommendation-row td.pridict-score-cell {
            color: var(--primary-color, #034078) !important;
        }

        .pegrna-results-content .recommendation-row td:first-child {
            color: #b91c1c !important;
            font-weight: bold;
        }

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
        }

        .actions-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            padding: 2rem;
            background: var(--bg-secondary);
            border-top: 1px solid var(--border-color);
        }

        @media (max-width: 768px) {
            .actions-bar {
                padding: 1.25rem 1rem;
                gap: 0.75rem;
            }

            .actions-bar .btn {
                flex: 1 1 calc(50% - 0.75rem);
                min-width: 140px;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .actions-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .actions-bar .btn {
                width: 100%;
                flex: 0 0 auto;
            }
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #printable-results,
            #printable-results * {
                visibility: visible;
            }

            #printable-results {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .results-header,
            .mutation-card,
            .actions-bar,
            .back-btn,
            header,
            footer,
            nav {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="results-page">
        <div class="results-header">
            <h1><i class="fas fa-dna"></i> <?php echo htmlspecialchars($designModeTitle); ?> Design Results</h1>
            <a href="design.php" class="btn secondary back-btn">
                <i class="fas fa-arrow-left"></i> Back to Design
            </a>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p><?php echo htmlspecialchars($error); ?></p>
                <a href="design.php" class="btn primary" style="margin-top: 1rem;">
                    <i class="fas fa-arrow-left"></i> Go to Design Page
                </a>
            </div>
        <?php else: ?>
            <?php if ($inputSequence): ?>
                <?php
                // Parse the input sequence to extract mutation details
                $mutationInfo = parseMutationInfo($inputSequence);
                ?>
                <div class="mutation-card">
                    <h3><i class="fas fa-exchange-alt"></i> Mutation Detected</h3>

                    <div class="mutation-details">
                        <div class="mutation-type-badge <?php echo strtolower($mutationInfo['type']); ?>">
                            <?php echo htmlspecialchars($mutationInfo['type']); ?>
                        </div>

                        <div class="mutation-summary">
                            <?php if ($mutationInfo['type'] === 'Substitution'): ?>
                                <p><strong><?php echo htmlspecialchars($mutationInfo['ref']); ?></strong> →
                                    <strong><?php echo htmlspecialchars($mutationInfo['alt']); ?></strong> at position
                                    <strong><?php echo $mutationInfo['position']; ?></strong>
                                </p>
                            <?php elseif ($mutationInfo['type'] === 'Deletion'): ?>
                                <p>Deletion of <strong><?php echo htmlspecialchars($mutationInfo['ref']); ?></strong>
                                    (<?php echo strlen($mutationInfo['ref']); ?> bp) at position
                                    <strong><?php echo $mutationInfo['position']; ?></strong>
                                </p>
                            <?php elseif ($mutationInfo['type'] === 'Insertion'): ?>
                                <p>Insertion of <strong><?php echo htmlspecialchars($mutationInfo['alt']); ?></strong>
                                    (<?php echo strlen($mutationInfo['alt']); ?> bp) at position
                                    <strong><?php echo $mutationInfo['position']; ?></strong>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mutation-sequence">
                        <?php
                        // Highlight the mutation in the sequence
                        $highlightedSeq = highlightMutation($inputSequence);
                        echo $highlightedSeq;
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="pegrna-results-wrapper">
                <div class="pegrna-results-content" id="printable-results">
                    <?php
                    if ($results && isset($results['html'])) {
                        // Clean the HTML
                        $html = $results['html'];
                        $html = preg_replace('/<head>[\s\S]*?<\/head>/i', '', $html);
                        $html = preg_replace('/<\/?html[^>]*>/i', '', $html);
                        $html = preg_replace('/<\/?body[^>]*>/i', '', $html);
                        $html = preg_replace('/<\/?!DOCTYPE[^>]*>/i', '', $html);
                        echo $html;

                        // If PRIDICT model ran successfully, inject scores into the HTML
                        if (isset($results['pridictDict']) && !empty($results['pridictDict']) && isset($results['results']['programs'])) {
                            $progJson = json_encode($results['results']['programs']);
                            $dictJson = json_encode($results['pridictDict']);
                            echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    setTimeout(function() {
                                        if (typeof window.injectPridictScoresDOM === 'function') {
                                            var container = document.getElementById('printable-results');
                                            var programs = {$progJson};
                                            var pridictDict = {$dictJson};
                                            window.injectPridictScoresDOM(container, programs, pridictDict);
                                        }
                                    }, 200);
                                });
                            </script>";
                        }
                    } else {
                        echo '<p>No results available.</p>';
                    }
                    ?>
                </div>

                <div class="actions-bar">
                    <button class="btn secondary" onclick="printResults()">
                        <i class="fas fa-print"></i> Print Results
                    </button>
                    <button class="btn secondary" onclick="exportCSV()">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </button>
                    <a href="design.php" class="btn secondary">
                        <i class="fas fa-redo"></i> New Design
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        // Apply styling to recommended titles and recommendation rows on page load
        // Also add "View pegRNA" buttons to program tables
        document.addEventListener('DOMContentLoaded', function () {
            const content = document.querySelector('#printable-results');
            if (!content) return;

            // Track current program data for structure visualization
            let programTables = [];

            // Find and style rows
            const tables = content.querySelectorAll('table');
            tables.forEach((table, tableIndex) => {
                // Check entire table text for strand orientation first
                const tableText = table.innerText.toLowerCase();
                const isReverseStrand = tableText.includes('reverse strand') || tableText.includes('reverse orientation');

                let currentProgramData = {
                    spacer: '',
                    pbs: '',
                    rtTemplate: '',
                    scaffoldMod: '',
                    strand: isReverseStrand ? 'antisense' : 'sense'
                };
                let hasProgramHeader = false;

                const rows = table.querySelectorAll('tr');
                rows.forEach(row => {
                    // Check for program header rows (th elements with "No." pattern)
                    const headerCell = row.querySelector('th');
                    if (headerCell) {
                        const headerText = headerCell.textContent.trim();
                        // Green styling for "No. X program" or "No. X recommended program" rows
                        if (headerText.match(/^No\.\s*\d+/i)) {
                            row.classList.add('program-row');
                            hasProgramHeader = true;

                            // Save the cell to inject buttons later, after parsing all rows
                            currentProgramData.headerLastCell = row.querySelector('th:last-child, td:last-child');
                        }
                    }

                    // Extract data from table rows
                    const cells = row.querySelectorAll('th, td');
                    if (cells.length >= 2) {
                        const label = cells[0].textContent.trim().toLowerCase();
                        const value = cells[1].textContent.trim();

                        // Extract Spacer-PAM
                        if (label.includes('spacer-pam') || label === 'spacer') {
                            const spacerPam = value.replace(/\s/g, '');
                            // PAM is usually last 3 chars (NGG/NG)
                            currentProgramData.spacer = spacerPam.length >= 20 ? spacerPam.substring(0, 20) : spacerPam;
                        }

                        // Extract Linker
                        if (label === 'linker' || label.includes('linker')) {
                            currentProgramData.linker = value.split(',')[0].trim();
                        }

                        // Extract Strand - check both label and value for reverse indicators
                        if (label.includes('strand') || label.includes('orientation')) {
                            currentProgramData.strand = value.toLowerCase().includes('reverse') ? 'antisense' : 'sense';
                        }
                        // Also check if the label itself indicates reverse strand
                        if (label.includes('reverse strand') || label.includes('reverse orientation')) {
                            currentProgramData.strand = 'antisense';
                        }
                        if (label.includes('forward strand') || label.includes('forward orientation')) {
                            currentProgramData.strand = 'sense';
                        }
                    }

                    // Check for Recommended! rows (td elements) - these contain PBS and RT
                    const firstDataCell = row.querySelector('td');
                    if (firstDataCell) {
                        const cellText = firstDataCell.textContent.trim();
                        // Red styling for Recommended! rows
                        if (cellText.startsWith('💡 Suggested') || cellText === '💡 Suggested') {
                            row.classList.add('recommendation-row');

                            // Extract the recommended value
                            const valueCell = firstDataCell.nextElementSibling;
                            if (valueCell) {
                                const recValue = valueCell.textContent.trim().split(',')[0];

                                // Look at previous rows to determine if this is PBS or RT
                                let prevRow = row.previousElementSibling;
                                while (prevRow) {
                                    const prevLabel = prevRow.querySelector('th, td');
                                    if (prevLabel) {
                                        const prevText = prevLabel.textContent.trim().toLowerCase();
                                        if (prevText.includes('pbs') && !currentProgramData.pbs) {
                                            currentProgramData.pbs = recValue.toUpperCase();
                                            break;
                                        }
                                        if (prevText.includes('rt') && !currentProgramData.rtTemplate) {
                                            currentProgramData.rtTemplate = recValue.toUpperCase();
                                            const scaffoldMod = valueCell.querySelector('span')?.getAttribute('data-scaffold-mod');
                                            if (scaffoldMod) currentProgramData.scaffoldMod = scaffoldMod.toUpperCase();
                                            break;
                                        }
                                        if (prevText.match(/^no\./)) break; // Stop at program header
                                    }
                                    prevRow = prevRow.previousElementSibling;
                                }
                            }
                        }
                    }
                });

                // Now that all rows in this table are parsed, inject the buttons if it's a program table
                if (hasProgramHeader && currentProgramData.headerLastCell) {
                    const lastCell = currentProgramData.headerLastCell;
                    const mode = window.designMode || 'pegrna';
                    if (!lastCell.querySelector('.view-structure-btn')) {
                        // Conditionally show only the View button matching the design mode
                        if (mode === 'pegrna') {
                            // View pegRNA Button
                            const btnPeg = document.createElement('button');
                            btnPeg.className = 'view-structure-btn';
                            btnPeg.innerHTML = 'View pegRNA';
                            btnPeg.style.marginLeft = '10px';
                            btnPeg.dataset.tableIndex = tableIndex;
                            btnPeg.onclick = function (e) {
                                e.preventDefault();
                                showStructureForTable(tableIndex);
                            };
                            lastCell.appendChild(btnPeg);
                        }

                        if (mode === 'gpegrna') {
                            // View g-pegRNA Button
                            const btnGpeg = document.createElement('button');
                            btnGpeg.className = 'view-structure-btn';
                            btnGpeg.innerHTML = 'View g-pegRNA';
                            btnGpeg.style.marginLeft = '10px';
                            btnGpeg.dataset.tableIndex = tableIndex;
                            btnGpeg.onclick = function (e) {
                                e.preventDefault();
                                showGpegRNAStructureForTable(tableIndex);
                            };
                            lastCell.appendChild(btnGpeg);
                        }

                        if (mode === 'gepegrna') {
                            // View g-epegRNA Button
                            // Will compute linker on-demand if it is missing or NNNNNNNN
                            const btnGepeg = document.createElement('button');
                            btnGepeg.className = 'view-structure-btn';
                            btnGepeg.innerHTML = 'View g-epegRNA';
                            btnGepeg.style.marginLeft = '10px';
                            btnGepeg.dataset.tableIndex = tableIndex;
                            btnGepeg.onclick = function (e) {
                                e.preventDefault();
                                showGepegRNAStructureForTable(tableIndex);
                            };
                            lastCell.appendChild(btnGepeg);
                        }

                        if (mode === 'epegrna') {
                            // View epegRNA Button — same motif+linker structure as g-epegRNA but
                            // with the standard (unmodified) scaffold. Linker computed on-demand.
                            const btnEpeg = document.createElement('button');
                            btnEpeg.className = 'view-structure-btn';
                            btnEpeg.innerHTML = 'View epegRNA';
                            btnEpeg.style.marginLeft = '10px';
                            btnEpeg.dataset.tableIndex = tableIndex;
                            btnEpeg.onclick = function (e) {
                                e.preventDefault();
                                showEpegRNAStructureForTable(tableIndex);
                            };
                            lastCell.appendChild(btnEpeg);
                        }
                    }
                    delete currentProgramData.headerLastCell;
                }

                if (hasProgramHeader) {
                    programTables.push({
                        tableIndex: tableIndex,
                        data: currentProgramData
                    });
                }
            });

            // Store program data globally for access by button click
            window.pegrnaProgramData = programTables;
        });

        // Helper function to reverse complement DNA
        function reverseComplement(seq) {
            const complement = { 'A': 'T', 'T': 'A', 'C': 'G', 'G': 'C', 'a': 't', 't': 'a', 'c': 'g', 'g': 'c' };
            return seq.toUpperCase()
                .split('')
                .reverse()
                .map(c => complement[c] || c)
                .join('');
        }

        function getModifiedScaffoldDNA(scaffoldModStr) {
            const defaultScaffoldDNA = "GTTTAAGAGCTATGCTGGAAACAGCATAGCAAGTTTAAATAAGGCTAGTCCGTTATCAACTTGAAAAAGTGGCACCGAGTCGGTGC";
            if (!scaffoldModStr || scaffoldModStr.length !== 3) {
                return defaultScaffoldDNA;
            }
            let sc = defaultScaffoldDNA.split('');
            sc[83] = scaffoldModStr[0];
            sc[84] = scaffoldModStr[1];
            sc[85] = scaffoldModStr[2];
            const comp = { 'A': 'T', 'T': 'A', 'C': 'G', 'G': 'C', 'a': 't', 't': 'a', 'c': 'g', 'g': 'c' };
            sc[73] = comp[scaffoldModStr[0]] || sc[73];
            sc[72] = comp[scaffoldModStr[1]] || sc[72];
            sc[71] = comp[scaffoldModStr[2]] || sc[71];
            return sc.join('');
        }

        // Show structure modal for a specific program table
        function showStructureForTable(tableIndex) {
            try {
                console.log('showStructureForTable called with index:', tableIndex);
                if (!window.pegrnaProgramData) {
                    console.error('No pegrnaProgramData found');
                    alert('Error: No program data available. Please reload the page.');
                    return;
                }

                const programInfo = window.pegrnaProgramData.find(p => p.tableIndex === tableIndex);
                console.log('Program info found:', programInfo);

                if (programInfo && programInfo.data) {
                    // Fallback: if PBS/RT not found in data, try to extract from the table again
                    if (!programInfo.data.pbs || !programInfo.data.rtTemplate) {
                        const tables = document.querySelectorAll('#printable-results table');
                        const table = tables[tableIndex];
                        if (table) {
                            const extracted = extractPegRNAFromTable(table);
                            if (extracted.pbs) programInfo.data.pbs = extracted.pbs;
                            if (extracted.rtTemplate) {
                                programInfo.data.rtTemplate = extracted.rtTemplate;
                                if (extracted.scaffoldMod) programInfo.data.scaffoldMod = extracted.scaffoldMod;
                            }
                        }
                    }

                    showPegRNAModal(programInfo.data);
                } else {
                    console.error('No program data found for table index:', tableIndex);
                    alert('Error: Could not find program data for this result.');
                }
            } catch (err) {
                console.error('Error in showStructureForTable:', err);
                alert('Error showing structure: ' + err.message);
            }
        }

        // Show pegRNA structure modal using React visualization
        function showPegRNAModal(data) {
            try {
                console.log('showPegRNAModal called with data:', data);

                // Create modal if it doesn't exist
                let modal = document.getElementById('pegrna-viz-modal');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = 'pegrna-viz-modal';
                    modal.className = 'pegrna-modal';
                    // Force fixed positioning with inline styles - start HIDDEN
                    modal.style.cssText = 'display:none;visibility:hidden;opacity:0;position:fixed;top:0;left:0;right:0;bottom:0;width:100%;height:100%;z-index:99999;background:rgba(0,0,0,0.7);align-items:center;justify-content:center;padding:2rem;overflow:auto;';
                    modal.innerHTML = `
                        <div class="pegrna-modal-content">
                            <div class="pegrna-modal-header">
                                <h3><i class="fas fa-dna"></i> pegRNA Structure Visualization</h3>
                                <button class="pegrna-modal-close" onclick="closePegRNAModal()">&times;</button>
                            </div>
                            <div class="pegrna-modal-body">
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item"><span class="label">Strand:</span><span class="value" id="pegrna-strand">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">Spacer:</span><span class="value" id="pegrna-spacer">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">RT Template:</span><span class="value" id="pegrna-rt">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">PBS:</span><span class="value" id="pegrna-pbs">-</span></div>
                                </div>
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item" style="flex: 100%;"><span class="label">Scaffold (default):</span><span class="value" id="pegrna-scaffold" style="word-break: break-all;">-</span></div>
                                </div>
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item" style="flex: 100%;"><span class="label">Full Sequence:</span><span class="value" id="pegrna-fullseq" style="word-break: break-all;">-</span></div>
                                </div>
                                <div id="pegrna-viz-container" style="min-height:400px;background:#f8f9fa;border-radius:8px;padding:1rem;"></div>
                            </div>
                            <div class="pegrna-modal-footer">
                                <button class="btn secondary" onclick="downloadPegRNASVG()"><i class="fas fa-download"></i> Download SVG</button>
                                <button class="btn secondary" onclick="closePegRNAModal()">Close</button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }

                // Update info bar
                document.getElementById('pegrna-spacer').textContent = data.spacer || '-';
                document.getElementById('pegrna-pbs').textContent = data.pbs || '-';
                document.getElementById('pegrna-rt').textContent = data.rtTemplate || '-';
                document.getElementById('pegrna-strand').textContent = data.strand === 'antisense' ? 'Antisense (-)' : 'Sense (+)';
                var pegScaffold = 'GTTTAAGAGCTATGCTGGAAACAGCATAGCAAGTTTAAATAAGGCTAGTCCGTTATCAACTTGAAAAAGTGGCACCGAGTCGGTGC';
                document.getElementById('pegrna-scaffold').textContent = pegScaffold;
                // Full pegRNA: spacer + scaffold + RT template + PBS
                document.getElementById('pegrna-fullseq').textContent =
                    ((data.spacer || '') + pegScaffold + (data.rtTemplate || '') + (data.pbs || '')).toUpperCase() || '-';

                // Render visualization using React component
                const vizContainer = document.getElementById('pegrna-viz-container');
                if (window.PegRNAVisualization && window.PegRNAVisualization.render) {
                    console.log('Rendering with PegRNAVisualization');
                    try {
                        window.PegRNAVisualization.render('#pegrna-viz-container', {
                            spacer: data.spacer,
                            pbs: data.pbs,
                            rtTemplate: data.rtTemplate,
                            scaffoldMod: data.scaffoldMod || '',
                            strand: data.strand
                        });
                    } catch (renderErr) {
                        console.error('React render error:', renderErr);
                        vizContainer.innerHTML = '<div style="padding:2rem;text-align:center;color:#666;">Visualization component failed to render. Please check browser console for errors.</div>';
                    }
                } else {
                    console.error('PegRNAVisualization not available');
                    vizContainer.innerHTML = '<div style="padding:2rem;text-align:center;color:#666;">Visualization component not loaded. Please refresh the page.</div>';
                }

                // Show modal by adding 'active' class (CSS uses !important)
                setTimeout(function () {
                    modal.classList.add('active');
                    console.log('Modal active class added');
                }, 50);
                document.body.style.overflow = 'hidden';
            } catch (err) {
                console.error('Error in showPegRNAModal:', err);
                alert('Error showing modal: ' + err.message);
            }
        }

        function closePegRNAModal() {
            const modal = document.getElementById('pegrna-viz-modal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function downloadPegRNASVG() {
            if (window.PegRNAVisualization) {
                window.PegRNAVisualization.exportSVG('#pegrna-viz-container', 'pegrna-structure.svg');
            }
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closePegRNAModal();
                closeGpegRNAModal();
                closeGepegRNAModal();
                closeEpegRNAModal();
            }
        });

        // --- g-pegRNA Visualization Functions ---

        function showGpegRNAStructureForTable(tableIndex) {
            try {
                if (!window.pegrnaProgramData) return;
                const programInfo = window.pegrnaProgramData.find(p => p.tableIndex === tableIndex);

                if (programInfo && programInfo.data) {
                    if (!programInfo.data.pbs || !programInfo.data.rtTemplate) {
                        const tables = document.querySelectorAll('#printable-results table');
                        const table = tables[tableIndex];
                        if (table) {
                            const extracted = extractPegRNAFromTable(table);
                            if (extracted.pbs) programInfo.data.pbs = extracted.pbs;
                            if (extracted.rtTemplate) {
                                programInfo.data.rtTemplate = extracted.rtTemplate;
                                if (extracted.scaffoldMod) programInfo.data.scaffoldMod = extracted.scaffoldMod;
                            }
                        }
                    }
                    showGpegRNAModal(programInfo.data);
                }
            } catch (err) {
                console.error('Error in showGpegRNAStructureForTable:', err);
            }
        }

        function showGpegRNAModal(data) {
            try {
                let modal = document.getElementById('gpegrna-viz-modal');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = 'gpegrna-viz-modal';
                    modal.className = 'pegrna-modal';
                    modal.style.cssText = 'display:none;visibility:hidden;opacity:0;position:fixed;top:0;left:0;right:0;bottom:0;width:100%;height:100%;z-index:99999;background:rgba(0,0,0,0.7);align-items:center;justify-content:center;padding:2rem;overflow:auto;';
                    modal.innerHTML = `
                        <div class="pegrna-modal-content">
                            <div class="pegrna-modal-header">
                                <h3><i class="fas fa-dna"></i> g-pegRNA Structure Visualization</h3>
                                <button class="pegrna-modal-close" onclick="closeGpegRNAModal()">&times;</button>
                            </div>
                            <div class="pegrna-modal-body">
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item"><span class="label">Strand:</span><span class="value" id="gpegrna-strand">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">Spacer:</span><span class="value" id="gpegrna-spacer">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">RT Template:</span><span class="value" id="gpegrna-rt">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">PBS:</span><span class="value" id="gpegrna-pbs">-</span></div>
                                </div>
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item" style="flex: 100%;"><span class="label">Scaffold (modified):</span><span class="value" id="gpegrna-scaffold" style="word-break: break-all;">-</span></div>
                                </div>
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item" style="flex: 100%;"><span class="label">Full Sequence:</span><span class="value" id="gpegrna-fullseq" style="word-break: break-all;">-</span></div>
                                </div>
                                <div id="gpegrna-viz-container" style="min-height:400px;background:#f8f9fa;border-radius:8px;padding:1rem;"></div>
                            </div>
                            <div class="pegrna-modal-footer">
                                <button class="btn secondary" onclick="downloadGpegRNASVG()"><i class="fas fa-download"></i> Download SVG</button>
                                <button class="btn secondary" onclick="closeGpegRNAModal()">Close</button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }

                document.getElementById('gpegrna-spacer').textContent = data.spacer || '-';
                document.getElementById('gpegrna-pbs').textContent = data.pbs || '-';
                document.getElementById('gpegrna-rt').textContent = data.rtTemplate || '-';
                document.getElementById('gpegrna-strand').textContent = data.strand === 'antisense' ? 'Antisense (-)' : 'Sense (+)';

                // Calculate modified scaffold string
                const modifiedScaffoldDNA = getModifiedScaffoldDNA(data.scaffoldMod);
                document.getElementById('gpegrna-scaffold').textContent = modifiedScaffoldDNA;
                // Full g-pegRNA: spacer + modified scaffold + RT template + PBS
                document.getElementById('gpegrna-fullseq').textContent =
                    ((data.spacer || '') + modifiedScaffoldDNA + (data.rtTemplate || '') + (data.pbs || '')).toUpperCase() || '-';

                const vizContainer = document.getElementById('gpegrna-viz-container');
                if (window.GpegRNAVisualization && window.GpegRNAVisualization.render) {
                    window.GpegRNAVisualization.render('#gpegrna-viz-container', {
                        spacer: data.spacer,
                        pbs: data.pbs,
                        rtTemplate: data.rtTemplate,
                        scaffoldMod: data.scaffoldMod || '',
                        strand: data.strand
                    });
                } else {
                    vizContainer.innerHTML = '<div style="padding:2rem;text-align:center;color:#666;">Visualization component not loaded.</div>';
                }

                setTimeout(() => modal.classList.add('active'), 50);
                document.body.style.overflow = 'hidden';
            } catch (err) {
                console.error('Error in showGpegRNAModal:', err);
            }
        }

        function closeGpegRNAModal() {
            const modal = document.getElementById('gpegrna-viz-modal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function downloadGpegRNASVG() {
            if (window.GpegRNAVisualization) {
                window.GpegRNAVisualization.exportSVG('#gpegrna-viz-container', 'gpegrna-structure.svg');
            }
        }

        // --- g-epegRNA Visualization Functions ---

        function showGepegRNAStructureForTable(tableIndex) {
            try {
                if (!window.pegrnaProgramData) return;
                const programInfo = window.pegrnaProgramData.find(p => p.tableIndex === tableIndex);

                if (programInfo && programInfo.data) {
                    if (!programInfo.data.pbs || !programInfo.data.rtTemplate) {
                        const tables = document.querySelectorAll('#printable-results table');
                        const table = tables[tableIndex];
                        if (table) {
                            const extracted = extractPegRNAFromTable(table);
                            if (extracted.pbs) programInfo.data.pbs = extracted.pbs;
                            if (extracted.rtTemplate) {
                                programInfo.data.rtTemplate = extracted.rtTemplate;
                                if (extracted.scaffoldMod) programInfo.data.scaffoldMod = extracted.scaffoldMod;
                            }
                            if (extracted.linker) programInfo.data.linker = extracted.linker;
                        }
                    }

                    if (!programInfo.data.linker || programInfo.data.linker === 'NNNNNNNN') {
                        computeLinkerOnDemand(programInfo.data, () => {
                            showGepegRNAModal(programInfo.data);
                        });
                    } else {
                        showGepegRNAModal(programInfo.data);
                    }
                }
            } catch (err) {
                console.error('Error in showGepegRNAStructureForTable:', err);
            }
        }

        function showGepegRNAModal(data) {
            try {
                let modal = document.getElementById('gepegrna-viz-modal');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = 'gepegrna-viz-modal';
                    modal.className = 'pegrna-modal';
                    modal.style.cssText = 'display:none;visibility:hidden;opacity:0;position:fixed;top:0;left:0;right:0;bottom:0;width:100%;height:100%;z-index:99999;background:rgba(0,0,0,0.7);align-items:center;justify-content:center;padding:2rem;overflow:auto;';
                    modal.innerHTML = `
                        <div class="pegrna-modal-content">
                            <div class="pegrna-modal-header">
                                <h3><i class="fas fa-dna"></i> g-epegRNA Structure Visualization</h3>
                                <button class="pegrna-modal-close" onclick="closeGepegRNAModal()">&times;</button>
                            </div>
                            <div class="pegrna-modal-body">
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item"><span class="label">Strand:</span><span class="value" id="gepegrna-strand">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">Spacer:</span><span class="value" id="gepegrna-spacer">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">RT Template:</span><span class="value" id="gepegrna-rt">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">PBS:</span><span class="value" id="gepegrna-pbs">-</span></div>
                                </div>
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item" style="flex: 100%;"><span class="label">Scaffold (modified):</span><span class="value" id="gepegrna-scaffold" style="word-break: break-all;">-</span></div>
                                </div>
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item"><span class="label">Linker:</span><span class="value" id="gepegrna-linker">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">Motif:</span><span class="value" id="gepegrna-motif">-</span></div>
                                </div>
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item" style="flex: 100%;"><span class="label">Full Sequence:</span><span class="value" id="gepegrna-fullseq" style="word-break: break-all;">-</span></div>
                                </div>
                                <div id="gepegrna-viz-container" style="min-height:400px;background:#f8f9fa;border-radius:8px;padding:1rem;"></div>
                            </div>
                            <div class="pegrna-modal-footer">
                                <button class="btn secondary" onclick="downloadGepegRNASVG()"><i class="fas fa-download"></i> Download SVG</button>
                                <button class="btn secondary" onclick="closeGepegRNAModal()">Close</button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }

                document.getElementById('gepegrna-spacer').textContent = data.spacer || '-';
                document.getElementById('gepegrna-pbs').textContent = data.pbs || '-';
                document.getElementById('gepegrna-rt').textContent = data.rtTemplate || '-';
                document.getElementById('gepegrna-linker').textContent = data.linker || '-';
                document.getElementById('gepegrna-strand').textContent = data.strand === 'antisense' ? 'Antisense (-)' : 'Sense (+)';
                document.getElementById('gepegrna-motif').textContent = 'CGCGGTTCTATCTAGTTACGCGTTAAACCAACTAGAA';

                // Calculate modified scaffold string
                const modifiedScaffoldDNA = getModifiedScaffoldDNA(data.scaffoldMod);
                document.getElementById('gepegrna-scaffold').textContent = modifiedScaffoldDNA;
                // Full g-epegRNA: spacer + modified scaffold + RT template + PBS + linker + tevopreQ1 motif
                document.getElementById('gepegrna-fullseq').textContent =
                    ((data.spacer || '') + modifiedScaffoldDNA + (data.rtTemplate || '') + (data.pbs || '')
                        + (data.linker || '') + 'CGCGGTTCTATCTAGTTACGCGTTAAACCAACTAGAA').toUpperCase() || '-';

                const vizContainer = document.getElementById('gepegrna-viz-container');
                if (window.GepegRNAVisualization && window.GepegRNAVisualization.render) {
                    window.GepegRNAVisualization.render('#gepegrna-viz-container', {
                        spacer: data.spacer,
                        pbs: data.pbs,
                        rtTemplate: data.rtTemplate,
                        scaffoldMod: data.scaffoldMod || '',
                        linker: data.linker || '',
                        strand: data.strand
                    });
                } else {
                    vizContainer.innerHTML = '<div style="padding:2rem;text-align:center;color:#666;">Visualization component not loaded.</div>';
                }

                setTimeout(() => modal.classList.add('active'), 50);
                document.body.style.overflow = 'hidden';
            } catch (err) {
                console.error('Error in showGepegRNAModal:', err);
            }
        }

        function computeLinkerOnDemand(data, callback) {
            // Create loading overlay
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:100000;display:flex;flex-direction:column;align-items:center;justify-content:center;color:white;font-family:Arial,sans-serif;';
            overlay.innerHTML = `
                <i class="fas fa-spinner fa-spin" style="font-size:3rem;margin-bottom:1rem;color:#10b981;"></i>
                <h3 style="margin-bottom:0.5rem;font-size:1.5rem;">Computing Optimal Linker...</h3>
                <p style="color:#d1d5db;">This optimization typically takes about 2 minutes.</p>
            `;
            document.body.appendChild(overlay);

            fetch('api/generate_linker.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    spacer: data.spacer,
                    rt: data.rtTemplate,
                    pbs: data.pbs,
                    modified_scaffold: getModifiedScaffoldDNA(data.scaffoldMod)
                })
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(result => {
                    document.body.removeChild(overlay);
                    if (result.success && result.linker && result.linker !== 'NNNNNNNN') {
                        data.linker = result.linker; // Cache it for future clicks
                        // Update table UI if possible
                        updateTableLinkerValue(data);
                        callback();
                    } else {
                        alert('Could not compute optimal linker. Please try again or check the documentation.');
                    }
                })
                .catch(error => {
                    console.error('Linker compute error:', error);
                    document.body.removeChild(overlay);
                    alert('An error occurred while computing the linker sequence.');
                });
        }

        function updateTableLinkerValue(data) {
            try {
                const tables = document.querySelectorAll('#printable-results table');
                const tableInfo = window.pegrnaProgramData.find(p => p.data === data);
                if (tableInfo && tables[tableInfo.tableIndex]) {
                    const rows = tables[tableInfo.tableIndex].querySelectorAll('tr');
                    rows.forEach(row => {
                        const cells = row.querySelectorAll('th, td');
                        if (cells.length >= 2) {
                            const label = cells[0].textContent.trim().toLowerCase();
                            if (label === 'linker' || label.includes('linker')) {
                                cells[1].textContent = data.linker;
                            }
                        }
                    });
                }
            } catch (e) {
                console.error("Failed to update table linker text", e);
            }
        }

        function closeGepegRNAModal() {
            const modal = document.getElementById('gepegrna-viz-modal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function downloadGepegRNASVG() {
            if (window.GepegRNAVisualization) {
                window.GepegRNAVisualization.exportSVG('#gepegrna-viz-container', 'gepegrna-structure.svg');
            }
        }

        // --- epegRNA Visualization Functions ---
        // epegRNA = the g-epegRNA motif+linker structure WITHOUT the genomic (scaffold)
        // modification. We reuse the g-epegRNA visualization but force scaffoldMod='' so the
        // standard scaffold is drawn, and compute the linker against that standard scaffold.

        function showEpegRNAStructureForTable(tableIndex) {
            try {
                if (!window.pegrnaProgramData) return;
                const programInfo = window.pegrnaProgramData.find(p => p.tableIndex === tableIndex);

                if (programInfo && programInfo.data) {
                    if (!programInfo.data.pbs || !programInfo.data.rtTemplate) {
                        const tables = document.querySelectorAll('#printable-results table');
                        const table = tables[tableIndex];
                        if (table) {
                            const extracted = extractPegRNAFromTable(table);
                            if (extracted.pbs) programInfo.data.pbs = extracted.pbs;
                            if (extracted.rtTemplate) programInfo.data.rtTemplate = extracted.rtTemplate;
                        }
                    }

                    // epegRNA ignores the scaffold modification: work on a clone with no scaffoldMod.
                    // The epegRNA linker differs from the g-epegRNA one (different scaffold), so it is
                    // cached separately on the original record to avoid recomputing on re-open.
                    const epegData = Object.assign({}, programInfo.data, { scaffoldMod: '' });
                    if (programInfo.data.epegLinker) {
                        epegData.linker = programInfo.data.epegLinker;
                        showEpegRNAModal(epegData);
                    } else {
                        epegData.linker = 'NNNNNNNN'; // force on-demand compute against standard scaffold
                        computeLinkerOnDemand(epegData, () => {
                            programInfo.data.epegLinker = epegData.linker;
                            showEpegRNAModal(epegData);
                        });
                    }
                }
            } catch (err) {
                console.error('Error in showEpegRNAStructureForTable:', err);
            }
        }

        function showEpegRNAModal(data) {
            try {
                let modal = document.getElementById('epegrna-viz-modal');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = 'epegrna-viz-modal';
                    modal.className = 'pegrna-modal';
                    modal.style.cssText = 'display:none;visibility:hidden;opacity:0;position:fixed;top:0;left:0;right:0;bottom:0;width:100%;height:100%;z-index:99999;background:rgba(0,0,0,0.7);align-items:center;justify-content:center;padding:2rem;overflow:auto;';
                    modal.innerHTML = `
                        <div class="pegrna-modal-content">
                            <div class="pegrna-modal-header">
                                <h3><i class="fas fa-dna"></i> epegRNA Structure Visualization</h3>
                                <button class="pegrna-modal-close" onclick="closeEpegRNAModal()">&times;</button>
                            </div>
                            <div class="pegrna-modal-body">
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item"><span class="label">Strand:</span><span class="value" id="epegrna-strand">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">Spacer:</span><span class="value" id="epegrna-spacer">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">RT Template:</span><span class="value" id="epegrna-rt">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">PBS:</span><span class="value" id="epegrna-pbs">-</span></div>
                                </div>
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item" style="flex: 100%;"><span class="label">Scaffold:</span><span class="value" id="epegrna-scaffold" style="word-break: break-all;">-</span></div>
                                </div>
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item"><span class="label">Linker:</span><span class="value" id="epegrna-linker">-</span></div>
                                    <div class="pegrna-info-item"><span class="label">Motif:</span><span class="value" id="epegrna-motif">-</span></div>
                                </div>
                                <div class="pegrna-info-bar">
                                    <div class="pegrna-info-item" style="flex: 100%;"><span class="label">Full Sequence:</span><span class="value" id="epegrna-fullseq" style="word-break: break-all;">-</span></div>
                                </div>
                                <div id="epegrna-viz-container" style="min-height:400px;background:#f8f9fa;border-radius:8px;padding:1rem;"></div>
                            </div>
                            <div class="pegrna-modal-footer">
                                <button class="btn secondary" onclick="downloadEpegRNASVG()"><i class="fas fa-download"></i> Download SVG</button>
                                <button class="btn secondary" onclick="closeEpegRNAModal()">Close</button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }

                document.getElementById('epegrna-spacer').textContent = data.spacer || '-';
                document.getElementById('epegrna-pbs').textContent = data.pbs || '-';
                document.getElementById('epegrna-rt').textContent = data.rtTemplate || '-';
                document.getElementById('epegrna-linker').textContent = data.linker || '-';
                document.getElementById('epegrna-strand').textContent = data.strand === 'antisense' ? 'Antisense (-)' : 'Sense (+)';
                document.getElementById('epegrna-motif').textContent = 'CGCGGTTCTATCTAGTTACGCGTTAAACCAACTAGAA';

                // Standard (unmodified) scaffold — no genomic modification for epegRNA.
                const epegScaffold = getModifiedScaffoldDNA('');
                document.getElementById('epegrna-scaffold').textContent = epegScaffold;
                // Full epegRNA: spacer + scaffold + RT template + PBS + linker + tevopreQ1 motif
                document.getElementById('epegrna-fullseq').textContent =
                    ((data.spacer || '') + epegScaffold + (data.rtTemplate || '') + (data.pbs || '')
                        + (data.linker || '') + 'CGCGGTTCTATCTAGTTACGCGTTAAACCAACTAGAA').toUpperCase() || '-';

                const vizContainer = document.getElementById('epegrna-viz-container');
                if (window.GepegRNAVisualization && window.GepegRNAVisualization.render) {
                    window.GepegRNAVisualization.render('#epegrna-viz-container', {
                        spacer: data.spacer,
                        pbs: data.pbs,
                        rtTemplate: data.rtTemplate,
                        scaffoldMod: '',
                        linker: data.linker || '',
                        strand: data.strand
                    });
                } else {
                    vizContainer.innerHTML = '<div style="padding:2rem;text-align:center;color:#666;">Visualization component not loaded.</div>';
                }

                setTimeout(() => modal.classList.add('active'), 50);
                document.body.style.overflow = 'hidden';
            } catch (err) {
                console.error('Error in showEpegRNAModal:', err);
            }
        }

        function closeEpegRNAModal() {
            const modal = document.getElementById('epegrna-viz-modal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function downloadEpegRNASVG() {
            if (window.GepegRNAVisualization) {
                window.GepegRNAVisualization.exportSVG('#epegrna-viz-container', 'epegrna-structure.svg');
            }
        }

        // More thorough extraction from table
        function extractPegRNAFromTable(table) {
            const data = { spacer: '', pbs: '', rtTemplate: '', scaffoldMod: '', strand: 'sense' };
            const rows = table.querySelectorAll('tr');

            rows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                if (cells.length >= 2) {
                    const label = cells[0].textContent.trim().toLowerCase();
                    const value = cells[1].textContent.trim();

                    if (label.includes('spacer-pam') || label === 'spacer') {
                        const spacerPam = value.replace(/\s/g, '');
                        data.spacer = spacerPam.length >= 20 ? spacerPam.substring(0, 20) : spacerPam;
                    }
                    if (label.includes('strand')) {
                        data.strand = value.toLowerCase().includes('reverse') ? 'antisense' : 'sense';
                    }
                }

                // Look for Recommended! rows
                const firstCell = row.querySelector('td');
                if (firstCell && firstCell.textContent.includes('💡 Suggested')) {
                    const valueCell = firstCell.nextElementSibling;
                    if (valueCell) {
                        const recValue = valueCell.textContent.trim().split(',')[0];
                        // Determine type by looking at row context
                        let sibling = row.previousElementSibling;
                        while (sibling) {
                            const sibLabel = sibling.querySelector('th, td');
                            if (sibLabel) {
                                const sibText = sibLabel.textContent.toLowerCase();
                                if (sibText.includes('pbs') && recValue.length <= 17) {
                                    data.pbs = recValue.toUpperCase();
                                    break;
                                }
                                if (sibText.includes('rt') && recValue.length > 17) {
                                    data.rtTemplate = recValue.toUpperCase();
                                    const scaffoldMod = valueCell.querySelector('span')?.getAttribute('data-scaffold-mod');
                                    if (scaffoldMod) data.scaffoldMod = scaffoldMod.toUpperCase();
                                    break;
                                }
                            }
                            sibling = sibling.previousElementSibling;
                        }
                    }
                }
            });

            return data;
        }

        function printResults() {
            const content = document.querySelector('#printable-results');
            if (!content) {
                alert('No results to print');
                return;
            }

            // Create a new tab for printing with complete content
            const printWindow = window.open('', '_blank');

            // Generate timestamp for header
            const now = new Date();
            const timestamp = now.toLocaleString();

            // Build the print content with proper styling
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>pegRNA Design Results - PROpeg</title>
                    <style>
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        body {
                            font-family: Arial, sans-serif;
                            font-size: 12px;
                            line-height: 1.4;
                            padding: 20px;
                            color: #333;
                        }
                        .print-header {
                            text-align: center;
                            margin-bottom: 20px;
                            padding-bottom: 15px;
                            border-bottom: 2px solid #16a34a;
                        }
                        .print-header h1 {
                            color: #16a34a;
                            font-size: 24px;
                            margin-bottom: 5px;
                        }
                        .print-header p {
                            color: #666;
                            font-size: 11px;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 15px 0;
                            page-break-inside: auto;
                        }
                        tr {
                            page-break-inside: avoid;
                            page-break-after: auto;
                        }
                        th, td {
                            padding: 6px 10px;
                            text-align: left;
                            border-bottom: 1px solid #ddd;
                            font-size: 11px;
                        }
                        th {
                            background-color: #f5f5f5;
                        }
                        /* Green styling for Program header rows */
                        tr.program-row {
                            background: #dcfce7 !important;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }
                        tr.program-row th,
                        tr.program-row td {
                            color: #15803d !important;
                            font-weight: bold;
                            background: transparent !important;
                        }
                        /* Red styling for Recommended rows */
                        tr.recommendation-row {
                            background: #fee2e2 !important;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }
                        tr.recommendation-row td {
                            color: #dc2626 !important;
                            font-weight: 600;
                        }
                        tr.recommendation-row td.pridict-score-cell {
                            color: #034078 !important;
                        }
                        span[style*="color:red"] {
                            color: #dc2626 !important;
                            font-weight: 600;
                        }
                        span[style*="background-color:#8FBC8F"] {
                            background-color: #22c55e !important;
                            color: white !important;
                            padding: 1px 4px;
                            border-radius: 2px;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }
                        @media print {
                            body { 
                                padding: 10px;
                                -webkit-print-color-adjust: exact;
                                print-color-adjust: exact;
                            }
                            .print-header {
                                page-break-after: avoid;
                            }
                            table {
                                page-break-inside: auto;
                            }
                            tr {
                                page-break-inside: avoid;
                            }
                        }
                        @page {
                            margin: 1cm;
                            size: A4;
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h1>pegRNA Design Results</h1>
                        <p>Generated by PROpeg on ${timestamp}</p>
                    </div>
                    <div id="content"></div>
                </body>
                </html>
            `);

            // Clone and insert content
            const contentClone = content.cloneNode(true);
            printWindow.document.getElementById('content').appendChild(contentClone);

            // Apply styling classes to the cloned content
            const tables = printWindow.document.querySelectorAll('table');
            tables.forEach(table => {
                const rows = table.querySelectorAll('tr');
                rows.forEach(row => {
                    // Check for program header rows
                    const headerCell = row.querySelector('th');
                    if (headerCell) {
                        const headerText = headerCell.textContent.trim();
                        if (headerText.match(/^No\\.\\s*\\d+/i)) {
                            row.classList.add('program-row');
                        }
                    }

                    // Check for Recommended! rows
                    const firstDataCell = row.querySelector('td');
                    if (firstDataCell) {
                        const cellText = firstDataCell.textContent.trim();
                        if (cellText.startsWith('💡 Suggested') || cellText === '💡 Suggested') {
                            row.classList.add('recommendation-row');
                        }
                    }
                });
            });

            // Close document and trigger print once
            printWindow.document.close();

            // Single delayed print call - wait for content to render
            setTimeout(() => {
                printWindow.focus();
                printWindow.print();
            }, 500);
        }

        function exportCSV() {
            const content = document.querySelector('#printable-results');
            if (!content) {
                alert('No results to export');
                return;
            }

            let csvContent = 'pegRNA Design Results - PROpeg\n';
            csvContent += 'Generated on,' + new Date().toLocaleString() + '\n\n';

            // Extract the dual-pegRNA message
            const dualPegMessage = content.querySelector('p span');
            if (dualPegMessage) {
                csvContent += dualPegMessage.textContent.trim() + '\n\n';
            }

            // Extract tables
            const tables = content.querySelectorAll('table');
            tables.forEach((table, tableIndex) => {
                const rows = table.querySelectorAll('tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('th, td');
                    const rowData = [];
                    cells.forEach(cell => {
                        let cellText = cell.textContent.trim().replace(/,/g, ';').replace(/\n/g, ' ');
                        rowData.push('"' + cellText + '"');
                    });
                    if (rowData.length > 0) {
                        csvContent += rowData.join(',') + '\n';
                    }
                });
                csvContent += '\n';
            });

            // Generate timestamp for filename (YYYYMMDD_HHMMSS format)
            const now = new Date();
            const timestamp = now.getFullYear() +
                ('0' + (now.getMonth() + 1)).slice(-2) +
                ('0' + now.getDate()).slice(-2) + '_' +
                ('0' + now.getHours()).slice(-2) +
                ('0' + now.getMinutes()).slice(-2) +
                ('0' + now.getSeconds()).slice(-2);

            // Create and download CSV file
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'pegRNA_results_' + timestamp + '.csv';
            a.click();
            URL.revokeObjectURL(url);
        }
    </script>
</body>

</html>