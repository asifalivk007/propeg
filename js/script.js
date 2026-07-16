// Global variables
let currentDesign = null;
let structureViewer = null;

// Genetic code lookup
const geneticCode = {
    'TTT': 'F', 'TTC': 'F', 'TTA': 'L', 'TTG': 'L',
    'TCT': 'S', 'TCC': 'S', 'TCA': 'S', 'TCG': 'S',
    'TAT': 'Y', 'TAC': 'Y', 'TAA': '*', 'TAG': '*',
    'TGT': 'C', 'TGC': 'C', 'TGA': '*', 'TGG': 'W',
    'CTT': 'L', 'CTC': 'L', 'CTA': 'L', 'CTG': 'L',
    'CCT': 'P', 'CCC': 'P', 'CCA': 'P', 'CCG': 'P',
    'CAT': 'H', 'CAC': 'H', 'CAA': 'Q', 'CAG': 'Q',
    'CGT': 'R', 'CGC': 'R', 'CGA': 'R', 'CGG': 'R',
    'ATT': 'I', 'ATC': 'I', 'ATA': 'I', 'ATG': 'M',
    'ACT': 'T', 'ACC': 'T', 'ACA': 'T', 'ACG': 'T',
    'AAT': 'N', 'AAC': 'N', 'AAA': 'K', 'AAG': 'K',
    'AGT': 'S', 'AGC': 'S', 'AGA': 'R', 'AGG': 'R',
    'GTT': 'V', 'GTC': 'V', 'GTA': 'V', 'GTG': 'V',
    'GCT': 'A', 'GCC': 'A', 'GCA': 'A', 'GCG': 'A',
    'GAT': 'D', 'GAC': 'D', 'GAA': 'E', 'GAG': 'E',
    'GGT': 'G', 'GGC': 'G', 'GGA': 'G', 'GGG': 'G'
};

// Plant species codon usage data (simplified)
const codonUsage = {
    'arabidopsis': {
        'TTT': 0.45, 'TTC': 0.55, 'TTA': 0.15, 'TTG': 0.25,
        'ATG': 1.00, 'GAA': 0.60, 'GAG': 0.40
    },
    'rice': {
        'TTT': 0.40, 'TTC': 0.60, 'TTA': 0.10, 'TTG': 0.30,
        'ATG': 1.00, 'GAA': 0.55, 'GAG': 0.45
    }
};

// Initialize the application
document.addEventListener('DOMContentLoaded', function () {
    initializeNavigation();

    // Initialize page-specific features based on current page
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';

    if (currentPage === 'design.php') {
        initializeDesignInterface();
    }
});

// Navigation functions
function initializeNavigation() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    // Set active nav link based on current page
    setActiveNavLink();

    // Mobile menu toggle
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function () {
            navMenu.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking nav links
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            if (navMenu) {
                navMenu.classList.remove('active');
            }
        });
    });
}

function setActiveNavLink() {
    const navLinks = document.querySelectorAll('.nav-link');
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';

    navLinks.forEach(link => {
        link.classList.remove('active');
        const linkHref = link.getAttribute('href');
        if (linkHref === currentPage ||
            (currentPage === '' && linkHref === 'index.php') ||
            (currentPage === 'index.php' && linkHref === 'index.php')) {
            link.classList.add('active');
        }
    });
}

// Removed showSection function - no longer needed for multi-page setup

// Design interface functions
function initializeDesignInterface() {
    initializeTabs();
    initializeRangeSliders();
    initializeDualRangeSliders();
    initializeDesignActions();
    initializeLoadExample();
    initializePrimerSelection();
    initializeParameterReset();
    initializeFileUpload();
}

// Initialize Load Example buttons
function initializeLoadExample() {
    const loadExample1Btn = document.getElementById('load-example-1');
    const loadExample2Btn = document.getElementById('load-example-2');
    const wildtypeSequence = document.getElementById('wildtype-sequence');
    const editedSequence = document.getElementById('edited-sequence');

    if (loadExample1Btn && wildtypeSequence && editedSequence) {
        loadExample1Btn.addEventListener('click', function () {
            // Example sequences from pegfinder - ACTB gene with G>A substitution
            wildtypeSequence.value = 'GCAACTGGGATGATATGGAGAAGATCTGGCATCACACCTTCTACAACGAGCTCCGTGTGGCCCCGGAGGAGCACCCCGTCCTCCTCACCGAGGCTCCTCTCAACCCCAAGGCCAATCGTGAGAAGATGACCCAGATCATGTTTGAGACCTT';
            editedSequence.value = 'GCAACTGGGATGATATGGAGAAGATCTGGCATCACACCTTCTACAACGAGCTCCGTGTGGCCCCGAAGGAGCACCCCGTCCTCCTCACCGAGGCTCCTCTCAACCCCAAGGCCAATCGTGAGAAGATGACCCAGATCATGTTTGAGACCTT';

            if (typeof showSuccess === 'function') showSuccess('Example 1 sequences loaded successfully!');
        });
    }

    if (loadExample2Btn && wildtypeSequence && editedSequence) {
        loadExample2Btn.addEventListener('click', function () {
            // PRIDICT prediction sequences meeting 99bp threshold
            wildtypeSequence.value = 'GCCTGGAGGTGTCTGGGTCCCTCCCCCACCCGACTACTTCACTCTCTGTCCTCTCTGCCCAGGAGCCCAGGATGTGCGAGTTCAAGTGGCTACGGCCGAGGTGCGAGGCCAGCTCGGGGGCACCGTGGAGCTGCCGTGCCACCTGCTGCCACCTGTTCCTGGACTGTACATCTCCCTGGTGACCTGGCAGCGCCCAGATGCACCTGCGAACCACCAGAATGTGGCCGC';
            editedSequence.value = 'GCCTGGAGGTGTCTGGGTCCCTCCCCCACCCGACTACTTCACTCTCTGTCCTCTCTGCCCAGGAGCCCAGGATGTGCGAGTTCAAGTGGCTACGGCCGACGTGCGAGGCCAGCTCGGGGGCACCGTGGAGCTGCCGTGCCACCTGCTGCCACCTGTTCCTGGACTGTACATCTCCCTGGTGACCTGGCAGCGCCCAGATGCACCTGCGAACCACCAGAATGTGGCCGC';

            if (typeof showSuccess === 'function') showSuccess('Example 2 sequences loaded successfully!');
        });
    }
}

function initializeTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetTab = this.getAttribute('data-tab');

            // Update active tab button
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Update active tab content
            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === `${targetTab}-tab`) {
                    content.classList.add('active');
                }
            });
        });
    });
}

function initializeRangeSliders() {
    const rangeSliders = document.querySelectorAll('input[type="range"]');

    rangeSliders.forEach(slider => {
        // Create tooltip element
        const tooltip = document.createElement('div');
        tooltip.className = 'range-tooltip';
        document.body.appendChild(tooltip);

        const updateValue = () => {
            const valueSpan = slider.parentNode.querySelector('.range-value');
            if (valueSpan) {
                const unit = slider.id.includes('length') ? ' nt' :
                    slider.id.includes('gc-content') ? '%' :
                        slider.id.includes('weight') ? '%' :
                            slider.id.includes('TM_Best') ? '°C' : '';
                valueSpan.textContent = slider.value + unit;
            }

            // Update progress fill for single-handle sliders
            if (!slider.classList.contains('dual-range-min') && !slider.classList.contains('dual-range-max')) {
                const progress = ((slider.value - slider.min) / (slider.max - slider.min)) * 100;
                slider.style.setProperty('--progress', progress + '%');
            }

            // Update labels for parameters tab sliders
            updateParameterLabel(slider);
        };

        const showTooltip = (e) => {
            const unit = slider.id.includes('length') ? ' nt' :
                slider.id.includes('gc-content') ? '%' :
                    slider.id.includes('weight') ? '%' :
                        slider.id.includes('TM_Best') ? '°C' : '';

            tooltip.textContent = slider.value + unit;
            tooltip.classList.add('visible');

            const rect = slider.getBoundingClientRect();
            const thumbPosition = ((slider.value - slider.min) / (slider.max - slider.min)) * rect.width;

            tooltip.style.left = (rect.left + thumbPosition) + 'px';
            tooltip.style.top = (rect.top - 35) + 'px';
        };

        const hideTooltip = () => {
            tooltip.classList.remove('visible');
        };

        const updateTooltipPosition = (e) => {
            if (tooltip.classList.contains('visible')) {
                showTooltip(e);
            }
        };

        // Event listeners
        slider.addEventListener('input', updateValue);
        slider.addEventListener('mouseover', showTooltip);
        slider.addEventListener('mouseout', hideTooltip);
        slider.addEventListener('input', updateTooltipPosition);
        slider.addEventListener('mousedown', showTooltip);
        slider.addEventListener('mouseup', hideTooltip);

        updateValue(); // Initialize
    });
}

function updateParameterLabel(slider) {
    const id = slider.id;
    const value = slider.value;

    // Update specific parameter labels
    switch (id) {
        case 'OnTargetLength':
            const slLabel = document.getElementById('sl');
            if (slLabel) slLabel.textContent = `Spacer length: ${value}`;
            break;
        case 'OnTarget_CG_Content_min':
            updateGCContentLabel();
            break;
        case 'OnTarget_CG_Content_max':
            updateGCContentLabel();
            break;
        case 'PE_window_min':
            updatePEWindowLabel();
            break;
        case 'PE_window_max':
            updatePEWindowLabel();
            break;
        case 'PBS_Length_min':
            updatePBSLengthLabel();
            break;
        case 'PBS_Length_max':
            updatePBSLengthLabel();
            break;
        case 'PBS_CG_Content_min':
            updatePBSGCLabel();
            break;
        case 'PBS_CG_Content_max':
            updatePBSGCLabel();
            break;
        case 'TM_Best':
            const tmLabel = document.getElementById('tmbest');
            if (tmLabel) tmLabel.textContent = `Recommended Tm of PBS sequence (°C): ${value}`;
            break;
        case 'RT_Length_min':
            updateRTLengthLabel();
            break;
        case 'RT_Length_max':
            updateRTLengthLabel();
            break;
    }
}

function updateGCContentLabel() {
    const minSlider = document.getElementById('OnTarget_CG_Content_min');
    const maxSlider = document.getElementById('OnTarget_CG_Content_max');
    const label = document.getElementById('sgcc');

    if (minSlider && maxSlider && label) {
        label.textContent = `Spacer GC content (%): ${minSlider.value}-${maxSlider.value}`;
    }
}

function updatePEWindowLabel() {
    const minSlider = document.getElementById('PE_window_min');
    const maxSlider = document.getElementById('PE_window_max');
    const label = document.getElementById('pew');

    if (minSlider && maxSlider && label) {
        label.textContent = `Prime editing window (the default values are recommended): ${minSlider.value}-${maxSlider.value}`;
    }
}

function updatePBSLengthLabel() {
    const minSlider = document.getElementById('PBS_Length_min');
    const maxSlider = document.getElementById('PBS_Length_max');
    const label = document.getElementById('pbs');

    if (minSlider && maxSlider && label) {
        label.textContent = `PBS length: ${minSlider.value}-${maxSlider.value}`;
    }
}

function updatePBSGCLabel() {
    const minSlider = document.getElementById('PBS_CG_Content_min');
    const maxSlider = document.getElementById('PBS_CG_Content_max');
    const label = document.getElementById('pbscg');

    if (minSlider && maxSlider && label) {
        label.textContent = `PBS GC content (%): ${minSlider.value}-${maxSlider.value}`;
    }
}

function updateRTLengthLabel() {
    const minSlider = document.getElementById('RT_Length_min');
    const maxSlider = document.getElementById('RT_Length_max');
    const label = document.getElementById('rt');

    if (minSlider && maxSlider && label) {
        label.textContent = `Homologous RT template length (the default values are recommended): ${minSlider.value}-${maxSlider.value}`;
    }
}

function initializeDesignActions() {
    const designGpegBtn = document.getElementById('design-gpegRNA');
    const designBtn = document.getElementById('design-pegRNA');
    const designGepegBtn = document.getElementById('design-gepegRNA');
    const designEpegBtn = document.getElementById('design-epegRNA');
    const clearBtn = document.getElementById('clear-design');
    const exportBtn = document.getElementById('export-results');

    if (designGpegBtn) designGpegBtn.addEventListener('click', () => designPegRNA('gpegrna'));
    if (designBtn) designBtn.addEventListener('click', () => designPegRNA('pegrna'));
    if (designGepegBtn) designGepegBtn.addEventListener('click', () => designPegRNA('gepegrna'));
    if (designEpegBtn) designEpegBtn.addEventListener('click', () => designPegRNA('epegrna'));
    if (clearBtn) clearBtn.addEventListener('click', clearDesign);
    if (exportBtn) exportBtn.addEventListener('click', exportResults);
}

function initializeFileUpload() {
    // Helper function to handle file reading
    function handleFile(file, targetTextarea) {
        if (!file) return;
        
        // Check file extension/type if needed
        const validExtensions = ['.txt', '.fasta'];
        const fileName = file.name.toLowerCase();
        const isValid = validExtensions.some(ext => fileName.endsWith(ext));
        
        if (!isValid && !file.type.match('text.*')) {
            showError('Invalid file type. Please upload a .txt or .fasta file.');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const raw = e.target.result;
            // Enforce single-sequence input: a file carrying more than one
            // sequence is batch input, which PROpeg does not support.
            if (detectMultipleSequences(raw)) {
                if (typeof showError === 'function') {
                    showError('This file contains multiple sequences. PROpeg designs one target per run — please upload a file with a single sequence.');
                } else {
                    alert('This file contains multiple sequences. Please upload a single-sequence file.');
                }
                return;
            }
            // Single record: drop a lone FASTA header, strip whitespace, uppercase.
            targetTextarea.value = normalizeSingleSequence(raw);
        };
        reader.readAsText(file);
    }

    // Process file upload for a specific zone
    function setupFileUpload(fileInputId, textareaId) {
        const fileInput = document.getElementById(fileInputId);
        const textarea = document.getElementById(textareaId);

        if (!fileInput || !textarea) return;

        fileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Check file size limit (10 KB)
            if (file.size > 10 * 1024) {
                if (typeof showError === 'function') {
                    showError('File size exceeds the 10 KB limit. Please upload a smaller file.');
                } else {
                    alert('File size exceeds the 10 KB limit.');
                }
                this.value = '';
                return;
            }

            handleFile(file, textarea);
            // Reset input so the same file could be uploaded again if needed
            this.value = ''; 
        });
    }

    // Setup for both wildtype and edited sequences
    setupFileUpload('wildtype-file', 'wildtype-sequence');
    setupFileUpload('edited-file', 'edited-sequence');
}

function initializePrimerSelection() {
    const primerRadios = document.querySelectorAll('input[name="Primer"]');
    const customSection = document.getElementById('custom-primer-section');

    if (!customSection) return;

    primerRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'Others') {
                customSection.style.display = 'block';
            } else {
                customSection.style.display = 'none';
            }
        });
    });

    // Initialize based on current selection
    const selectedPrimer = document.querySelector('input[name="Primer"]:checked');
    if (selectedPrimer && selectedPrimer.value === 'Others') {
        customSection.style.display = 'block';
    } else {
        customSection.style.display = 'none';
    }
}

function initializeDualRangeSliders() {
    // Initialize LayUI sliders
    if (typeof layui !== 'undefined') {
        layui.use('slider', function () {
            var slider = layui.slider;

            // Spacer GC content slider
            slider.render({
                elem: '#OnTarget_CG_Content_slider',
                range: true,
                value: [0, 100],
                theme: "#034078",
                change: function (value) {
                    document.getElementById('sgcc').textContent = 'Spacer GC content (%): ' + value[0] + '-' + value[1];
                    document.getElementById('OnTarget_CG_Content_min').value = value[0];
                    document.getElementById('OnTarget_CG_Content_max').value = value[1];
                }
            });

            // PE window slider
            slider.render({
                elem: '#PE_window_slider',
                range: true,
                min: 1,
                max: 50,
                value: [1, 15],
                theme: "#034078",
                change: function (value) {
                    document.getElementById('pew').textContent = 'Prime editing window (the default values are recommended): ' + value[0] + '-' + value[1];
                    document.getElementById('PE_window_min').value = value[0];
                    document.getElementById('PE_window_max').value = value[1];
                }
            });

            // PBS Length slider
            slider.render({
                elem: '#PBS_Length_slider',
                range: true,
                min: 5,
                max: 30,
                value: [7, 16],
                theme: "#034078",
                change: function (value) {
                    document.getElementById('pbs').textContent = 'PBS length: ' + value[0] + '-' + value[1];
                    document.getElementById('PBS_Length_min').value = value[0];
                    document.getElementById('PBS_Length_max').value = value[1];
                }
            });

            // PBS GC content slider
            slider.render({
                elem: '#PBS_CG_Content_slider',
                range: true,
                value: [0, 100],
                theme: "#034078",
                change: function (value) {
                    document.getElementById('pbscg').textContent = 'PBS GC content (%): ' + value[0] + '-' + value[1];
                    document.getElementById('PBS_CG_Content_min').value = value[0];
                    document.getElementById('PBS_CG_Content_max').value = value[1];
                }
            });

            // RT Length slider
            slider.render({
                elem: '#RT_Length_slider',
                range: true,
                min: 5,
                max: 80,
                value: [7, 16],
                theme: "#034078",
                change: function (value) {
                    document.getElementById('rt').textContent = 'Homologous RT template length (the default values are recommended): ' + value[0] + '-' + value[1];
                    document.getElementById('RT_Length_min').value = value[0];
                    document.getElementById('RT_Length_max').value = value[1];
                }
            });
        });
    }
}

function initializeParameterReset() {
    const resetBtn = document.getElementById('reset-parameters');
    if (resetBtn) {
        resetBtn.addEventListener('click', resetParametersToDefaults);
    }
}

function resetParametersToDefaults(silent = false) {
    // Reset PAM radio buttons
    const pamRadio = document.querySelector('input[name="PAM"][value="NGG"]');
    if (pamRadio) pamRadio.checked = true;

    // Reset custom PAM input
    const customPAM = document.querySelector('input[name="User_PAM"]');
    if (customPAM) customPAM.value = '';

    // Reset Cut distance to PAM
    const cutToPAM = document.querySelector('input[name="CutToPAM"]');
    if (cutToPAM) cutToPAM.value = '-3';

    // Reset single-range sliders
    const singleRangeSliders = [
        { id: 'OnTargetLength', value: 20 },
        { id: 'TM_Best', value: 30 }
    ];

    singleRangeSliders.forEach(item => {
        const element = document.getElementById(item.id);
        if (element) {
            element.value = item.value;
            element.dispatchEvent(new Event('input'));
        }
    });

    // Reset LayUI dual-range sliders
    if (typeof layui !== 'undefined') {
        layui.use('slider', function () {
            var slider = layui.slider;

            // Reset Spacer GC content slider
            slider.render({
                elem: '#OnTarget_CG_Content_slider',
                range: true,
                value: [0, 100],
                theme: "#034078",
                change: function (value) {
                    document.getElementById('sgcc').textContent = 'Spacer GC content (%): ' + value[0] + '-' + value[1];
                    document.getElementById('OnTarget_CG_Content_min').value = value[0];
                    document.getElementById('OnTarget_CG_Content_max').value = value[1];
                }
            });

            // Reset PE window slider
            slider.render({
                elem: '#PE_window_slider',
                range: true,
                min: 1,
                max: 50,
                value: [1, 15],
                theme: "#034078",
                change: function (value) {
                    document.getElementById('pew').textContent = 'Prime editing window (the default values are recommended): ' + value[0] + '-' + value[1];
                    document.getElementById('PE_window_min').value = value[0];
                    document.getElementById('PE_window_max').value = value[1];
                }
            });

            // Reset PBS Length slider
            slider.render({
                elem: '#PBS_Length_slider',
                range: true,
                min: 5,
                max: 30,
                value: [7, 16],
                theme: "#034078",
                change: function (value) {
                    document.getElementById('pbs').textContent = 'PBS length: ' + value[0] + '-' + value[1];
                    document.getElementById('PBS_Length_min').value = value[0];
                    document.getElementById('PBS_Length_max').value = value[1];
                }
            });

            // Reset PBS GC content slider
            slider.render({
                elem: '#PBS_CG_Content_slider',
                range: true,
                value: [0, 100],
                theme: "#034078",
                change: function (value) {
                    document.getElementById('pbscg').textContent = 'PBS GC content (%): ' + value[0] + '-' + value[1];
                    document.getElementById('PBS_CG_Content_min').value = value[0];
                    document.getElementById('PBS_CG_Content_max').value = value[1];
                }
            });

            // Reset RT Length slider
            slider.render({
                elem: '#RT_Length_slider',
                range: true,
                min: 5,
                max: 80,
                value: [7, 16],
                theme: "#034078",
                change: function (value) {
                    document.getElementById('rt').textContent = 'Homologous RT template length (the default values are recommended): ' + value[0] + '-' + value[1];
                    document.getElementById('RT_Length_min').value = value[0];
                    document.getElementById('RT_Length_max').value = value[1];
                }
            });
        });
    }

    // Reset checkboxes to checked state
    const checkboxes = [
        { id: 'tm-model', checked: true },
        { id: 'exclude-c', checked: true },
        { id: 'dual-pegrna', checked: true },
        { id: 'nick-model', checked: false }
    ];

    checkboxes.forEach(item => {
        const element = document.getElementById(item.id);
        if (element) {
            element.checked = item.checked;
        }
    });

    // Reset PE3/PE3b nick controls: default distance and hide the distance field
    const nickWrap = document.getElementById('nick-distance-wrap');
    if (nickWrap) nickWrap.style.display = 'none';
    const nickMin = document.getElementById('Nick_Distance_min');
    if (nickMin) nickMin.value = '40';
    const nickMax = document.getElementById('Nick_Distance_max');
    if (nickMax) nickMax.value = '100';

    // Show success message
    if (silent !== true && typeof showSuccess === 'function') {
        showSuccess('Parameters reset to default values');
    }
}

// Core pegRNA design function.
// Submits form and redirects to results.php
// mode: 'gpegrna', 'pegrna', or 'gepegrna'
async function designPegRNA(mode) {
    mode = mode || 'pegrna';

    // Map mode to button ID and label
    const modeConfig = {
        'gpegrna':  { btnId: 'design-gpegRNA',  label: 'Design g-pegRNA' },
        'pegrna':   { btnId: 'design-pegRNA',   label: 'Design pegRNA' },
        'gepegrna': { btnId: 'design-gepegRNA', label: 'Design g-epegRNA' },
        'epegrna':  { btnId: 'design-epegRNA',  label: 'Design epegRNA' }
    };
    const config = modeConfig[mode] || modeConfig['pegrna'];
    const designBtn = document.getElementById(config.btnId);

    // Show loading state
    designBtn.innerHTML = '<div class="loading"></div> Designing...';
    designBtn.disabled = true;

    try {
        // Read raw box values; a single FASTA header is allowed and stripped
        // during normalization. Multi-sequence (batch) input is rejected in validation.
        const wtRaw = document.getElementById('wildtype-sequence').value;
        const edRaw = document.getElementById('edited-sequence').value;

        // Validate inputs
        if (!validateSequenceInputs(wtRaw, edRaw)) {
            designBtn.innerHTML = '<i class="fas fa-cogs"></i> ' + config.label;
            designBtn.disabled = false;
            return;
        }

        const wildtype = normalizeSingleSequence(wtRaw);
        const edited = normalizeSingleSequence(edRaw);

        // Detect mutation and encode in the design tool's input notation
        const mutation = detectMutation(wildtype, edited);
        const inputSequence = encodeEditNotation(mutation);

        // Collect all parameters
        const params = collectDesignParameters(inputSequence);

        // Add design mode
        params.design_mode = mode;

        // Show loading UI (PRIDICT runs when both flanks are >= 99 bp)
        const posOpen = inputSequence.indexOf('(');
        const posClose = inputSequence.indexOf(')');
        let isPridict = false;
        if (posOpen !== -1 && posClose !== -1) {
            const leftFlank = posOpen;
            const rightFlank = inputSequence.length - posClose - 1;
            if (leftFlank >= 99 && rightFlank >= 99) {
                isPridict = true;
            }
        }
        if (isPridict) {
            const overlay = document.getElementById('pridict-loading-overlay');
            if (overlay) overlay.style.display = 'flex';
        }

        // Submit asynchronously: enqueue the job, then poll for completion so no
        // single HTTP request is held open for minutes (proxy/CDN timeout-safe).
        const body = new URLSearchParams();
        for (const [key, value] of Object.entries(params)) {
            body.append(key, value);
        }
        const submitRes = await fetch('submit_design.php', { method: 'POST', body });
        const submitData = await submitRes.json();
        if (!submitData.success || !submitData.job_id) {
            throw new Error(submitData.error || 'Could not start the design job.');
        }
        pollDesignJob(submitData.job_id, config, designBtn);

    } catch (error) {
        stopDesignLoading(config, designBtn);
        showError('Design failed: ' + error.message);
    }
}

// Poll an async design job until it finishes, then load its results page.
// Each request is sub-second, so no proxy/CDN can time the long design out.
function pollDesignJob(jobId, config, designBtn) {
    const intervalMs = 3000;
    const maxMs = 20 * 60 * 1000; // give up after 20 min
    const started = Date.now();

    const tick = async () => {
        try {
            const res = await fetch('job_status.php?job=' + encodeURIComponent(jobId), { cache: 'no-store' });
            const data = await res.json();
            if (data.status === 'done') {
                window.location.href = 'results.php?job=' + encodeURIComponent(jobId);
                return;
            }
            if (data.status === 'error') {
                stopDesignLoading(config, designBtn);
                showError('Design failed: ' + (data.error || 'unknown error'));
                return;
            }
        } catch (e) {
            // Transient poll failure — keep retrying until the ceiling below.
        }
        if (Date.now() - started > maxMs) {
            stopDesignLoading(config, designBtn);
            showError('Design timed out. Please try again.');
            return;
        }
        setTimeout(tick, intervalMs);
    };
    setTimeout(tick, intervalMs);
}

// Reset the design button and hide the loading overlay.
function stopDesignLoading(config, designBtn) {
    const overlay = document.getElementById('pridict-loading-overlay');
    if (overlay) overlay.style.display = 'none';
    if (designBtn) {
        designBtn.innerHTML = '<i class="fas fa-cogs"></i> ' + config.label;
        designBtn.disabled = false;
    }
}

/**
 * Detect mutation between wildtype and edited sequences
 */
function detectMutation(wildtype, edited) {
    // Find first position where sequences differ
    let diffStart = 0;
    while (diffStart < wildtype.length && diffStart < edited.length &&
        wildtype[diffStart] === edited[diffStart]) {
        diffStart++;
    }

    // Find last matching position from end
    let wtEnd = wildtype.length - 1;
    let edEnd = edited.length - 1;
    while (wtEnd >= diffStart && edEnd >= diffStart &&
        wildtype[wtEnd] === edited[edEnd]) {
        wtEnd--;
        edEnd--;
    }

    // Extract mutation details
    const refSeq = wildtype.substring(diffStart, wtEnd + 1);
    const altSeq = edited.substring(diffStart, edEnd + 1);
    const leftSeq = wildtype.substring(0, diffStart);
    const rightSeq = wildtype.substring(wtEnd + 1);

    // Determine type
    let type = 'substitution';
    if (refSeq === '') type = 'insertion';
    else if (altSeq === '') type = 'deletion';

    return { leftSeq, refSeq, altSeq, rightSeq, type, position: diffStart };
}

/**
 * Encode a detected mutation in the design tool's input notation: left(ref/alt)right.
 */
function encodeEditNotation(mutation) {
    return `${mutation.leftSeq}(${mutation.refSeq}/${mutation.altSeq})${mutation.rightSeq}`;
}

/**
 * Collect all design parameters from the form
 */
function collectDesignParameters(inputSequence) {
    // Get PAM value
    let pam = 'NGG';
    const pamRadios = document.querySelectorAll('input[name="PAM"]');
    pamRadios.forEach(radio => {
        if (radio.checked) {
            pam = radio.value;
        }
    });

    const userPam = document.querySelector('input[name="User_PAM"]');

    // Get Primer value
    let primer = 'OsU3';
    const primerRadios = document.querySelectorAll('input[name="Primer"]');
    primerRadios.forEach(radio => {
        if (radio.checked) {
            primer = radio.value;
        }
    });

    return {
        inputSequence: inputSequence,
        PAM: pam,
        User_PAM: userPam ? userPam.value : '',
        CutToPAM: document.querySelector('input[name="CutToPAM"]')?.value || '-3',
        OnTargetLength: document.getElementById('OnTargetLength')?.value || '20',
        OnTarget_CG_Content_min: document.getElementById('OnTarget_CG_Content_min')?.value || '0',
        OnTarget_CG_Content_max: document.getElementById('OnTarget_CG_Content_max')?.value || '100',
        PE_window_min: document.getElementById('PE_window_min')?.value || '1',
        PE_window_max: document.getElementById('PE_window_max')?.value || '15',
        PBS_Length_min: document.getElementById('PBS_Length_min')?.value || '7',
        PBS_Length_max: document.getElementById('PBS_Length_max')?.value || '16',
        PBS_CG_Content_min: document.getElementById('PBS_CG_Content_min')?.value || '0',
        PBS_CG_Content_max: document.getElementById('PBS_CG_Content_max')?.value || '100',
        TM_Best: document.getElementById('TM_Best')?.value || '30',
        RT_Length_min: document.getElementById('RT_Length_min')?.value || '7',
        RT_Length_max: document.getElementById('RT_Length_max')?.value || '16',
        Tm_model: document.getElementById('tm-model')?.checked ? 'True' : 'False',
        Exclude_LastG_in_RT: document.getElementById('exclude-c')?.checked ? 'True' : 'False',
        CCNNGG_model: document.getElementById('dual-pegrna')?.checked ? 'True' : 'False',
        Nick_Model: document.getElementById('nick-model')?.checked ? 'Both' : 'Off',
        Nick_Distance_min: document.getElementById('Nick_Distance_min')?.value || '40',
        Nick_Distance_max: document.getElementById('Nick_Distance_max')?.value || '100',
        Primer: primer,
        Forward_Primer_left: document.querySelector('input[name="Forward_Primer_left"]')?.value || '',
        Forward_Primer_right: document.querySelector('input[name="Forward_Primer_right"]')?.value || '',
        Reverse_Primer_left: document.querySelector('input[name="Reverse_Primer_left"]')?.value || '',
        Reverse_Primer_right: document.querySelector('input[name="Reverse_Primer_right"]')?.value || ''
    };
}

/**
 * Detect whether a block of text carries more than one sequence.
 * PROpeg designs one target per run, so multi-record FASTA (two or more
 * ">" headers) or multiple blank-line-separated blocks are treated as batch
 * input and rejected upstream.
 */
function detectMultipleSequences(text) {
    if (!text) return false;
    const headerCount = (text.match(/^\s*>/gm) || []).length;
    if (headerCount >= 2) return true;
    if (headerCount === 0) {
        const blocks = text.split(/\n\s*\n/).map(b => b.trim()).filter(Boolean);
        if (blocks.length >= 2) return true;
    }
    return false;
}

/**
 * Reduce a single-record input to a clean uppercase DNA string: drop one FASTA
 * header line if present, then strip all whitespace. Callers must first reject
 * multi-sequence input via detectMultipleSequences().
 */
function normalizeSingleSequence(raw) {
    if (!raw) return '';
    let s = raw;
    if (s.includes('>')) {
        s = s.split('\n').filter(line => !line.trim().startsWith('>')).join('');
    }
    return s.replace(/\s/g, '').toUpperCase();
}

/**
 * Validate wildtype and edited sequence inputs. Accepts raw box text: a single
 * FASTA header is allowed (stripped), but multiple sequences are rejected.
 */
function validateSequenceInputs(wildtypeRaw, editedRaw) {
    if (detectMultipleSequences(wildtypeRaw)) {
        showError('The wildtype/reference box contains more than one sequence. PROpeg designs one target per run — enter a single sequence.');
        return false;
    }
    if (detectMultipleSequences(editedRaw)) {
        showError('The edited/desired box contains more than one sequence. PROpeg designs one target per run — enter a single sequence.');
        return false;
    }

    const wildtype = normalizeSingleSequence(wildtypeRaw);
    const edited = normalizeSingleSequence(editedRaw);

    if (!wildtype || wildtype.length < 20) {
        showError('Wildtype sequence must be at least 20 bases long');
        return false;
    }
    
    if (wildtype.length > 2000) {
        showError('Sequence exceeds the maximum limit of 2,000 bases.');
        return false;
    }

    if (!edited || edited.length < 20) {
        showError('Edited sequence must be at least 20 bases long');
        return false;
    }
    
    if (edited.length > 2000) {
        showError('Sequence exceeds the maximum limit of 2,000 bases.');
        return false;
    }

    // Validate DNA sequences
    const validBases = /^[ATCG]+$/i;
    if (!validBases.test(wildtype)) {
        showError('Wildtype sequence contains invalid characters. Use only A, T, C, G');
        return false;
    }
    if (!validBases.test(edited)) {
        showError('Edited sequence contains invalid characters. Use only A, T, C, G');
        return false;
    }

    // Check that sequences are different
    if (wildtype === edited) {
        showError('Wildtype and edited sequences are identical. Please provide different sequences.');
        return false;
    }

    return true;
}

/**
 * Submit parameters to backend for pegRNA design
 */
async function submitToPegDesigner(params) {
    const formData = new FormData();
    for (const [key, value] of Object.entries(params)) {
        formData.append(key, value);
    }

    const response = await fetch('process_pegrna.php', {
        method: 'POST',
        body: formData
    });

    if (!response.ok) {
        throw new Error('Network response was not ok');
    }

    return await response.json();
}

/**
 * Display pegRNA design results
 */
function displayPegRNAResults(data) {
    const resultsContainer = document.getElementById('design-results');
    const resultsGrid = resultsContainer.querySelector('.results-grid');

    // Clear previous results
    resultsGrid.innerHTML = '';

    // Check if we have HTML results to display
    if (data.html) {
        // Create results display
        const resultCard = document.createElement('div');
        resultCard.className = 'result-card full-width';

        // Parse and clean the HTML
        let cleanHtml = data.html;
        // Remove the head and body tags, keep only content
        cleanHtml = cleanHtml.replace(/<head>[\s\S]*?<\/head>/gi, '');
        cleanHtml = cleanHtml.replace(/<\/?html[^>]*>/gi, '');
        cleanHtml = cleanHtml.replace(/<\/?body[^>]*>/gi, '');
        cleanHtml = cleanHtml.replace(/<\/?!DOCTYPE[^>]*>/gi, '');

        // Inject PRIDICT2 scores into the HTML tables if available
        if (data.results && data.results.programs && Object.keys(data.pridictDict || {}).length > 0) {
            let tempDiv = document.createElement('div');
            tempDiv.innerHTML = cleanHtml;
            if (window.injectPridictScoresDOM) {
                window.injectPridictScoresDOM(tempDiv, data.results.programs, data.pridictDict);
            }
            cleanHtml = tempDiv.innerHTML;
        }

        resultCard.innerHTML = `
            <h4><i class="fas fa-dna"></i> pegRNA Design Results</h4>
            <div class="pegrna-results-content">
                ${cleanHtml}
            </div>
        `;

        resultsGrid.appendChild(resultCard);
    }

    // Add mutation info card
    if (data.inputSequence) {
        const mutationCard = document.createElement('div');
        mutationCard.className = 'result-card';
        mutationCard.innerHTML = `
            <h4><i class="fas fa-exchange-alt"></i> Mutation Detected</h4>
            <div class="mutation-info">
                <p><strong>Input Format:</strong></p>
                <code class="sequence-display">${escapeHtml(data.inputSequence)}</code>
            </div>
        `;
        resultsGrid.insertBefore(mutationCard, resultsGrid.firstChild);
    }

    // Add dual-pegRNA status if available
    if (data.results && data.results.dualPegRNAMessage) {
        const dualCard = document.createElement('div');
        dualCard.className = 'result-card';
        dualCard.innerHTML = `
            <h4><i class="fas fa-code-branch"></i> Dual-pegRNA Model</h4>
            <p class="${data.results.dualPegRNA ? 'success-text' : 'info-text'}">
                ${escapeHtml(data.results.dualPegRNAMessage)}
            </p>
        `;
    resultsGrid.appendChild(dualCard);
    }
}

/* --- Navigation functions --- */
function navigateToSetup() {
    window.location.hash = 'setup';
    showTab('setup-tab');
    document.getElementById('inputSequence').focus();
}

/**
 * Global function to show custom tooltips (matches design.php style)
 */
window.showCustomTooltip = function(element, text) {
    document.querySelectorAll('.custom-click-tooltip').forEach(t => t.remove());
    
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-click-tooltip';
    tooltip.innerText = text;
    
    tooltip.style.position = 'absolute';
    tooltip.style.backgroundColor = '#034078';
    tooltip.style.color = '#fff';
    tooltip.style.padding = '8px 12px';
    tooltip.style.borderRadius = '4px';
    tooltip.style.fontSize = '12px';
    tooltip.style.whiteSpace = 'nowrap';
    tooltip.style.zIndex = '99999';
    tooltip.style.top = '-35px';
    tooltip.style.left = '50%';
    tooltip.style.transform = 'translateX(-50%)';
    tooltip.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
    
    const arrow = document.createElement('div');
    arrow.style.position = 'absolute';
    arrow.style.bottom = '-5px';
    arrow.style.left = '50%';
    arrow.style.transform = 'translateX(-50%)';
    arrow.style.borderWidth = '5px 5px 0';
    arrow.style.borderStyle = 'solid';
    arrow.style.borderColor = '#034078 transparent transparent transparent';
    tooltip.appendChild(arrow);
    
    element.parentElement.appendChild(tooltip);
    
    setTimeout(() => {
        tooltip.style.transition = 'opacity 0.5s ease';
        tooltip.style.opacity = '0';
        setTimeout(() => tooltip.remove(), 500);
    }, 3500);
};

/**
 * Global function to inject PRIDICT scores into HTML elements
 */
window.injectPridictScoresDOM = function(containerElement, programs, pridictDict) {
    if (!programs || !pridictDict || Object.keys(pridictDict).length === 0) return;
    
    // Helper: fuzzy match a PROpeg spacer against PRIDICT keys to handle 1-char offset
    function findExactScore(pegSpacer, pegPbs, pegRt) {
        if (!pegSpacer || !pegPbs || !pegRt) return undefined;
        let suffix = pegSpacer.substring(1).toUpperCase();
        let prefix = pegSpacer.substring(0, pegSpacer.length - 1).toUpperCase();
        let targetSuffix = "_" + pegPbs.toUpperCase() + "_" + pegRt.toUpperCase();
        
        // Exact match first
        let exactKey = pegSpacer.toUpperCase() + targetSuffix;
        if (pridictDict[exactKey] !== undefined) return pridictDict[exactKey];
        
        // Fuzzy match spacer
        for (let key in pridictDict) {
            if (key.endsWith(targetSuffix)) {
                let dictSpacer = key.split("_")[0];
                if (dictSpacer.substring(1) === suffix || 
                    dictSpacer.substring(0, dictSpacer.length - 1) === prefix) {
                    return pridictDict[key];
                }
            }
        }
        return undefined;
    }
    
    let tables = containerElement.querySelectorAll('table');
    
    tables.forEach((table, index) => {
        let prog = programs[index];
        if (!prog || !prog.spacer) return;
        
        // Find recommended PBS sequence for this program
        let recPbs = prog.pbs ? prog.pbs.find(p => p.recommended) : null;
        let pbsSeq = recPbs ? recPbs.sequence : "";
        
        let isRTSection = false;
        let rows = table.querySelectorAll('tr');
        
        rows.forEach(row => {
            let text = row.textContent.trim();
            
            // Detect the RT template section header
            if (text.includes('RT template')) {
                isRTSection = true;
                // Add a new <td> header for PRIDICT2 score
                let newTd = document.createElement('td');
                newTd.innerHTML = '<span style="color:blue"><strong>Efficiency Score</strong></span>' +
                    '<span style="position: relative; display: inline-block; margin-left: 5px;">' +
                    '<i class="fas fa-question-circle" onclick="window.showCustomTooltip(this, \'Predicted using PRIDICT2.0 (HEK293T baseline)\')" style="color: var(--primary-color, #034078); cursor: pointer; font-size: 0.9em;"></i>' +
                    '</span>';
                row.appendChild(newTd);
                return;
            }
            
            // Stop at Primers section
            if (text.includes('Primer')) {
                isRTSection = false;
                return;
            }
            
            // For data rows in the RT section
            if (isRTSection) {
                let cells = row.querySelectorAll('td');
                if (cells.length < 4) return;
                
                let seqText = cells[1] ? cells[1].textContent.trim() : "";
                if (!seqText || !/^[ACGTUacgtu]+$/i.test(seqText)) return;
                
                let rtSeq = seqText;
                let score = findExactScore(prog.spacer, pbsSeq, rtSeq);
                
                let firstCellText = cells[0] ? cells[0].textContent.trim() : "";
                let isRec = firstCellText.includes('Recommended');
                
                // Put score in cells[3] (first empty column after Length). Marked with its
                // own class so the recommendation-row red highlight (applied to every <td>
                // in that row) doesn't bleed into the efficiency score.
                if (cells[3]) {
                    cells[3].classList.add('pridict-score-cell');
                    if (score !== undefined) {
                        if (isRec) {
                            cells[3].innerHTML = `<strong style="color:var(--primary-color); font-size:1.1em;">${score}%</strong>`;
                        } else {
                            cells[3].innerHTML = `${score}%`;
                        }
                    } else {
                        cells[3].innerHTML = '-';
                    }
                }
            }
        });
    });
};

/* --- Event Listeners --- */
/**
 * Clear all design inputs and results
 */
function clearDesign() {
    // Clear sequence inputs
    const wildtypeSeq = document.getElementById('wildtype-sequence');
    const editedSeq = document.getElementById('edited-sequence');
    if (wildtypeSeq) wildtypeSeq.value = '';
    if (editedSeq) editedSeq.value = '';

    // Hide results
    const resultsSection = document.getElementById('design-results');
    if (resultsSection) {
        resultsSection.style.display = 'none';
        const resultsGrid = resultsSection.querySelector('.results-grid');
        if (resultsGrid) resultsGrid.innerHTML = '';
    }

    showSuccess('Design cleared');
}

/**
 * Export results - placeholder for future implementation
 */
function exportResults() {
    // Get the results HTML
    const resultsContent = document.querySelector('.pegrna-results-content');
    if (!resultsContent) {
        showError('No results to export');
        return;
    }

    // Create downloadable HTML file
    const htmlContent = `
<!DOCTYPE html>
<html>
<head>
    <title>pegRNA Design Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        .recommended { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>pegRNA Design Results</h1>
    ${resultsContent.innerHTML}
</body>
</html>`;

    const blob = new Blob([htmlContent], { type: 'text/html' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'pegRNA_results_' + new Date().toISOString().slice(0, 10) + '.html';
    a.click();
    URL.revokeObjectURL(url);

    showSuccess('Results exported successfully');
}

/**
 * Helper function to escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function generatePegRNADesigns(params) {
    // Simulate design process with realistic timing
    await delay(2000);

    const targetSeq = params.targetSequence.toUpperCase();
    const editPos = params.editPosition - 1; // Convert to 0-based index

    // Find potential gRNA sites around edit position
    const gRNASites = findGRNASites(targetSeq, editPos);

    // Generate pegRNA designs for each site
    const designs = [];
    for (let site of gRNASites.slice(0, 5)) { // Limit to top 5
        const design = generateSinglePegRNA(site, params);
        if (design) {
            designs.push(design);
        }
    }

    // Sort by overall score
    designs.sort((a, b) => b.overallScore - a.overallScore);

    return designs;
}

function findGRNASites(sequence, editPosition) {
    const sites = [];
    const pamSequence = 'NGG';

    // Search for PAM sites within reasonable distance of edit
    for (let i = Math.max(0, editPosition - 100); i < Math.min(sequence.length - 20, editPosition + 100); i++) {
        const potential = sequence.substring(i, i + 23);
        if (potential.length === 23 && isPAMSite(potential.substring(20))) {
            const distance = Math.abs(i + 10 - editPosition); // Distance from nick site to edit
            if (distance >= 1 && distance <= 50) { // Optimal range
                sites.push({
                    sequence: potential,
                    position: i,
                    distance: distance,
                    strand: '+'
                });
            }
        }
    }

    // Search reverse strand
    const revComp = reverseComplement(sequence);
    for (let i = Math.max(0, editPosition - 100); i < Math.min(revComp.length - 20, editPosition + 100); i++) {
        const potential = revComp.substring(i, i + 23);
        if (potential.length === 23 && isPAMSite(potential.substring(20))) {
            const distance = Math.abs(sequence.length - i - 13 - editPosition);
            if (distance >= 1 && distance <= 50) {
                sites.push({
                    sequence: reverseComplement(potential),
                    position: sequence.length - i - 23,
                    distance: distance,
                    strand: '-'
                });
            }
        }
    }

    // Sort by distance to edit
    return sites.sort((a, b) => a.distance - b.distance);
}

function generateSinglePegRNA(site, params) {
    try {
        // Generate spacer sequence (first 20 nt of gRNA)
        const spacer = site.sequence.substring(0, 20);

        // Generate PBS (primer binding site)
        const pbsStart = site.strand === '+' ? site.position + 13 : site.position + 6;
        const pbsSeq = params.targetSequence.substring(pbsStart, pbsStart + params.pbsLength);
        const pbs = site.strand === '+' ? reverseComplement(pbsSeq) : pbsSeq;

        // Generate RT template
        const rtTemplate = generateRTTemplate(params, site);

        // Construct full pegRNA
        // Optimized F+E SpCas9 Scaffold
        const pegRNA = spacer + 'GTTTAAGAGCTATGCTGGAAACAGCATAGCAAGTTTAAATAAGGCTAGTCCGTTATCAACTTGAAAAAGTGGCACCGAGTCGGTGC' + pbs + rtTemplate;

        // Calculate scores
        const efficiencyScore = calculateEfficiencyScore(spacer, pbs, rtTemplate, params);
        const specificityScore = calculateSpecificityScore(spacer, params.targetSequence);
        const structureScore = calculateStructureScore(pegRNA);

        const overallScore = (
            efficiencyScore * params.efficiencyWeight +
            specificityScore * params.specificityWeight +
            structureScore * params.structureWeight
        ) / 100;

        return {
            spacer: spacer,
            pbs: pbs,
            rtTemplate: rtTemplate,
            fullSequence: pegRNA,
            efficiencyScore: efficiencyScore,
            specificityScore: specificityScore,
            structureScore: structureScore,
            overallScore: overallScore,
            site: site,
            params: params
        };

    } catch (error) {
        console.error('Error generating pegRNA:', error);
        return null;
    }
}

function generateRTTemplate(params, site) {
    let template = '';
    const editPos = params.editPosition - 1;
    const sitePos = site.position;

    // Determine template based on edit type
    switch (params.editType) {
        case 'substitution':
            // Include the substitution in the template
            const beforeEdit = Math.max(0, params.rtTemplateLength - params.desiredEdit.length - 5);
            const afterEdit = params.rtTemplateLength - beforeEdit - params.desiredEdit.length;

            const startPos = editPos - beforeEdit;
            const endPos = editPos + afterEdit;

            template = params.targetSequence.substring(startPos, editPos) +
                params.desiredEdit +
                params.targetSequence.substring(editPos + 1, endPos + 1);
            break;

        case 'insertion':
            const insertPos = editPos;
            const beforeInsert = Math.floor(params.rtTemplateLength / 2);
            const afterInsert = params.rtTemplateLength - beforeInsert - params.desiredEdit.length;

            template = params.targetSequence.substring(insertPos - beforeInsert, insertPos) +
                params.desiredEdit +
                params.targetSequence.substring(insertPos, insertPos + afterInsert);
            break;

        case 'deletion':
            const delLength = parseInt(params.desiredEdit) || 1;
            const beforeDel = Math.floor(params.rtTemplateLength / 2);
            const afterDel = params.rtTemplateLength - beforeDel;

            template = params.targetSequence.substring(editPos - beforeDel, editPos) +
                params.targetSequence.substring(editPos + delLength, editPos + delLength + afterDel);
            break;

        default:
            template = params.targetSequence.substring(editPos, editPos + params.rtTemplateLength);
    }

    return site.strand === '+' ? template : reverseComplement(template);
}

function calculateEfficiencyScore(spacer, pbs, rtTemplate, params) {
    let score = 70; // Base score

    // GC content of spacer
    const spacerGC = calculateGCContent(spacer);
    if (spacerGC >= 40 && spacerGC <= 60) score += 10;
    else score -= Math.abs(spacerGC - 50) / 2;

    // PBS length and GC content
    const pbsGC = calculateGCContent(pbs);
    if (params.pbsLength >= 10 && params.pbsLength <= 15) score += 5;
    if (pbsGC >= 40 && pbsGC <= 60) score += 5;

    // RT template length
    if (params.rtTemplateLength >= 10 && params.rtTemplateLength <= 20) score += 5;

    // PE system bonus
    if (params.peSystem === 'enpPE2') score += 15;
    else if (params.peSystem === 'pe7') score += 10;
    else if (params.peSystem === 'pemax') score += 12;

    // epegRNA bonus
    if (params.pegRNAType.includes('epegRNA')) score += 8;
    if (params.pegRNAType.includes('tevopreQ1')) score += 15;

    // Add some randomness to simulate biological variability
    score += (Math.random() - 0.5) * 20;

    return Math.max(0, Math.min(100, score));
}

function calculateSpecificityScore(spacer, targetSequence) {
    let score = 85; // Base high specificity

    // Check for repeat sequences
    const kmerCounts = {};
    for (let i = 0; i <= spacer.length - 4; i++) {
        const kmer = spacer.substring(i, i + 4);
        kmerCounts[kmer] = (kmerCounts[kmer] || 0) + 1;
    }

    // Penalize high-frequency kmers
    for (let count of Object.values(kmerCounts)) {
        if (count > 2) score -= (count - 2) * 5;
    }

    // Penalize poly-T stretches (transcription termination)
    const polyT = spacer.match(/T{4,}/g);
    if (polyT) score -= polyT.length * 10;

    // Add randomness
    score += (Math.random() - 0.5) * 15;

    return Math.max(0, Math.min(100, score));
}

function calculateStructureScore(pegRNA) {
    let score = 75; // Base score

    // Simple structure prediction based on base pairing potential
    const gcContent = calculateGCContent(pegRNA);
    if (gcContent >= 45 && gcContent <= 55) score += 10;

    // Check for stable secondary structures in critical regions
    const spacerRegion = pegRNA.substring(0, 20);
    const pbsRegion = pegRNA.substring(pegRNA.length - 25);

    // Avoid very high GC in spacer (can form hairpins)
    if (calculateGCContent(spacerRegion) > 70) score -= 15;

    // PBS should have moderate stability
    const pbsGC = calculateGCContent(pbsRegion);
    if (pbsGC >= 40 && pbsGC <= 60) score += 5;

    // Add randomness
    score += (Math.random() - 0.5) * 20;

    return Math.max(0, Math.min(100, score));
}

function displayDesignResults(designs) {
    if (designs.length === 0) {
        showError('No suitable pegRNA designs found. Try adjusting parameters.');
        return;
    }

    currentDesign = designs[0]; // Store best design

    // Display primary design
    const pegRNASeq = document.getElementById('pegRNA-sequence');
    const efficiencyScore = document.getElementById('efficiency-score');
    const specificityScore = document.getElementById('specificity-score');

    pegRNASeq.innerHTML = formatSequenceDisplay(designs[0].fullSequence);

    // Animate score bars
    setTimeout(() => {
        efficiencyScore.style.width = designs[0].efficiencyScore + '%';
        specificityScore.style.width = designs[0].specificityScore + '%';
    }, 500);

    // Display alternative designs
    const altDesigns = document.getElementById('alternative-designs');
    altDesigns.innerHTML = '';

    for (let i = 1; i < Math.min(designs.length, 4); i++) {
        const design = designs[i];
        const altDiv = document.createElement('div');
        altDiv.className = 'alternative-design';
        altDiv.innerHTML = `
            <div class="alt-header">
                <strong>Design ${i + 1}</strong>
                <span class="score">Score: ${design.overallScore.toFixed(1)}</span>
            </div>
            <div class="alt-sequence">${formatSequenceDisplay(design.spacer + '...' + design.pbs)}</div>
            <div class="alt-metrics">
                <span>Efficiency: ${design.efficiencyScore.toFixed(0)}%</span>
                <span>Specificity: ${design.specificityScore.toFixed(0)}%</span>
            </div>
        `;
        altDiv.addEventListener('click', () => selectAlternativeDesign(design));
        altDesigns.appendChild(altDiv);
    }

    // Generate optimization suggestions
    generateOptimizationSuggestions(designs[0]);
}

function selectAlternativeDesign(design) {
    currentDesign = design;
    displayDesignResults([design, ...arguments[1] || []]);
}

function generateOptimizationSuggestions(design) {
    const suggestions = document.getElementById('optimization-suggestions');
    if (!suggestions) return;

    const tips = [];

    if (design.efficiencyScore < 70) {
        tips.push('Consider using enpPE2 or PE7 systems for higher efficiency');
        tips.push('Try epegRNA-tevopreQ1 for enhanced stability');
    }

    if (design.specificityScore < 80) {
        tips.push('Run off-target analysis to identify potential issues');
        tips.push('Consider adjusting spacer sequence if possible');
    }

    if (design.structureScore < 70) {
        tips.push('Optimize PBS length and GC content');
        tips.push('Add protective structural motifs');
    }

    tips.push('Test multiple PBS lengths experimentally (±2 nt)');
    tips.push('Consider temperature optimization for your experimental conditions');

    suggestions.innerHTML = tips.map(tip =>
        `<div class="suggestion-item">
            <i class="fas fa-lightbulb"></i> ${tip}
        </div>`
    ).join('');
}

function formatSequenceDisplay(sequence) {
    if (!sequence) return '';

    // Break sequence into chunks for readability
    const chunks = [];
    for (let i = 0; i < sequence.length; i += 10) {
        chunks.push(sequence.substring(i, i + 10));
    }
    return chunks.join(' ');
}

function clearDesign() {
    const wildtypeSeq = document.getElementById('wildtype-sequence');
    if (wildtypeSeq) wildtypeSeq.value = '';
    const editedSeq = document.getElementById('edited-sequence');
    if (editedSeq) editedSeq.value = '';
    
    // Also clear file inputs so the same file could be uploaded again
    const wildtypeFile = document.getElementById('wildtype-file');
    if (wildtypeFile) wildtypeFile.value = '';
    const editedFile = document.getElementById('edited-file');
    if (editedFile) editedFile.value = '';

    const targetSeq = document.getElementById('target-sequence');
    if (targetSeq) targetSeq.value = '';
    const editPos = document.getElementById('edit-position');
    if (editPos) editPos.value = '';
    const desiredEdit = document.getElementById('desired-edit');
    if (desiredEdit) desiredEdit.value = '';
    
    const designResults = document.getElementById('design-results');
    if (designResults) designResults.style.display = 'none';

    // Reset sliders to defaults
    const pbsLength = document.getElementById('pbs-length');
    if (pbsLength) pbsLength.value = 13;
    const rtTemplateLength = document.getElementById('rt-template-length');
    if (rtTemplateLength) rtTemplateLength.value = 13;
    const gcContent = document.getElementById('gc-content');
    if (gcContent) gcContent.value = 50;

    // Update range displays
    if (typeof initializeRangeSliders === 'function') {
        initializeRangeSliders();
    }
    
    // Use the existing comprehensive parameter reset if possible
    if (typeof resetParametersToDefaults === 'function') {
        resetParametersToDefaults(true); // Pass true to silence the default message
    }

    currentDesign = null;
    
    if (typeof showSuccess === 'function') {
        showSuccess('Sequence fields reset to none');
    }
}

function exportResults() {
    if (!currentDesign) {
        showError('No design results to export');
        return;
    }

    const data = {
        design: currentDesign,
        timestamp: new Date().toISOString(),
        parameters: currentDesign.params
    };

    const blob = new Blob([JSON.stringify(data, null, 2)], {
        type: 'application/json'
    });

    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'PROpeg_design_results.json';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// Utility functions
function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function reverseComplement(sequence) {
    const complement = {
        'A': 'T', 'T': 'A', 'C': 'G', 'G': 'C',
        'a': 't', 't': 'a', 'c': 'g', 'g': 'c'
    };

    return sequence
        .split('')
        .reverse()
        .map(base => complement[base] || base)
        .join('');
}

function calculateGCContent(sequence) {
    if (!sequence) return 0;
    const gcCount = (sequence.match(/[GC]/gi) || []).length;
    return (gcCount / sequence.length) * 100;
}

function isPAMSite(sequence) {
    // NGG PAM site
    return /[ATCG]GG/i.test(sequence);
}

function showError(message) {
    showNotification(message, 'error');
}

function showInfo(message) {
    showNotification(message, 'info');
}

function showSuccess(message) {
    showNotification(message, 'success');
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'error' ? 'exclamation-triangle' :
            type === 'success' ? 'check-circle' :
                'info-circle'}"></i>
            <span>${message}</span>
            <button class="notification-close">×</button>
        </div>
    `;

    // Add styles if not already present
    if (!document.getElementById('notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            .notification {
                position: fixed;
                top: 90px;
                right: 20px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                z-index: 10000;
                max-width: 400px;
                animation: slideIn 0.3s ease;
            }
            
            .notification.error {
                border-left: 4px solid var(--error-color);
            }
            
            .notification.success {
                border-left: 4px solid var(--success-color);
            }
            
            .notification.info {
                border-left: 4px solid var(--primary-color);
            }
            
            .notification-content {
                padding: 1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .notification-close {
                margin-left: auto;
                background: none;
                border: none;
                font-size: 1.2rem;
                cursor: pointer;
                color: #666;
            }
            
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Add to DOM
    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);

    // Manual close
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.remove();
    });
}

// Export functions for use in HTML
window.designPegRNA = designPegRNA;
window.clearDesign = clearDesign;
window.exportResults = exportResults;