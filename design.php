<?php include 'header.php'; ?>

<!-- Design Section -->
<section id="design" class="section active">
    <div class="container">
        <!-- <h2><i class="fas fa-edit"></i> Prime Editor Guide RNA Design</h2> -->

        <div class="design-tabs">
            <button class="tab-btn active" data-tab="sequence">Guide Design</button>
            <button class="tab-btn" data-tab="parameters">Parameters</button>
            <button class="tab-btn" data-tab="optimization">Primer</button>
        </div>

        <div class="design-content">
            <!-- Sequence Design Tab -->
            <div id="sequence-tab" class="tab-content active">
                <div class="input-grid">
                    <div class="input-group full-width">
                        <label for="wildtype-sequence">Wildtype/Reference Sequence</label>
                        <div class="sequence-input-container dropzone" id="wildtype-dropzone"
                            style="position: relative; border: 2px dashed #ccc; border-radius: 5px; padding: 10px; background-color: #f9f9f9; transition: all 0.3s ease;">
                            <textarea id="wildtype-sequence"
                                placeholder="Enter your wildtype/reference DNA sequence, or click Browse to upload a file (.txt, .fasta)"
                                style="width: 100%; border: none; background: transparent; resize: vertical; min-height: 100px; outline: none;"></textarea>
                            <div class="file-upload-actions" style="position: absolute; bottom: 10px; right: 10px;">
                                <input type="file" id="wildtype-file" class="file-input" accept=".txt,.fasta"
                                    style="display: none;">
                                <label for="wildtype-file" class="btn secondary"
                                    style="cursor: pointer; padding: 5px 10px; font-size: 0.9em; margin: 0;"><i
                                        class="fas fa-upload"></i> Browse</label>
                            </div>
                        </div>
                        <small>Paste a single wildtype/reference sequence (a FASTA header is optional), or upload a
                            single-sequence file (.txt, .fasta).</small>
                    </div>

                    <div class="input-group full-width">
                        <label for="edited-sequence">Edited/Desired Sequence</label>
                        <div class="sequence-input-container dropzone" id="edited-dropzone"
                            style="position: relative; border: 2px dashed #ccc; border-radius: 5px; padding: 10px; background-color: #f9f9f9; transition: all 0.3s ease;">
                            <textarea id="edited-sequence"
                                placeholder="Enter your edited/desired DNA sequence, or click Browse to upload a file (.txt, .fasta)"
                                style="width: 100%; border: none; background: transparent; resize: vertical; min-height: 100px; outline: none;"></textarea>
                            <div class="file-upload-actions" style="position: absolute; bottom: 10px; right: 10px;">
                                <input type="file" id="edited-file" class="file-input" accept=".txt,.fasta"
                                    style="display: none;">
                                <label for="edited-file" class="btn secondary"
                                    style="cursor: pointer; padding: 5px 10px; font-size: 0.9em; margin: 0;"><i
                                        class="fas fa-upload"></i> Browse</label>
                            </div>
                        </div>
                        <small>Paste a single edited/desired sequence with your intended mutation (a FASTA header is
                            optional), or upload a single-sequence file (.txt, .fasta).</small>
                    </div>

                    <div class="input-help"
                        style="margin-top: -10px; display: flex; gap: 12px; align-items: center; justify-content: flex-start; position: relative;">
                        <span style="display: inline-flex; align-items: center; gap: 3px;">
                            <button type="button" class="btn secondary load-example-btn" id="load-example-1"
                                style="padding: 4px 10px; font-size: 0.78em;">
                                <i class="fas fa-file-alt"></i> Load Example 1
                            </button>
                            <span style="position: relative; display: inline-block;">
                                <i class="fas fa-question-circle"
                                    onclick="showCustomTooltip(this, 'Prediction is OFF. Limited by minimum requirement of 99 bp upstream and downstream of the edit.')"
                                    style="color: var(--primary-color); cursor: pointer; font-size: 0.9em;"></i>
                            </span>
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 3px;">
                            <button type="button" class="btn secondary load-example-btn" id="load-example-2"
                                style="padding: 4px 10px; font-size: 0.78em;">
                                <i class="fas fa-file-alt"></i> Load Example 2
                            </button>
                            <span style="position: relative; display: inline-block;">
                                <i class="fas fa-question-circle"
                                    onclick="showCustomTooltip(this, 'Prediction is ON. Satisfied by over 99 bp upstream and downstream of the edit.')"
                                    style="color: var(--primary-color); cursor: pointer; font-size: 0.9em;"></i>
                            </span>
                        </span>
                    </div>

                </div>

                <script>
                    function showCustomTooltip(element, text) {
                        // Remove any existing custom tooltips
                        document.querySelectorAll('.custom-click-tooltip').forEach(t => t.remove());

                        // Create the tooltip element
                        const tooltip = document.createElement('div');
                        tooltip.className = 'custom-click-tooltip';
                        tooltip.innerText = text;

                        // Style it to look like a layui/layer tooltip
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

                        // Add a little triangle arrow
                        const arrow = document.createElement('div');
                        arrow.style.position = 'absolute';
                        arrow.style.bottom = '-5px';
                        arrow.style.left = '50%';
                        arrow.style.transform = 'translateX(-50%)';
                        arrow.style.borderWidth = '5px 5px 0';
                        arrow.style.borderStyle = 'solid';
                        arrow.style.borderColor = '#034078 transparent transparent transparent';
                        tooltip.appendChild(arrow);

                        // Append to the wrapper span
                        element.parentElement.appendChild(tooltip);

                        // Make it fade out after 4 seconds
                        setTimeout(() => {
                            tooltip.style.transition = 'opacity 0.5s ease';
                            tooltip.style.opacity = '0';
                            setTimeout(() => tooltip.remove(), 500);
                        }, 3500);
                    }
                </script>

                <div class="design-actions">
                    <button class="btn primary" id="design-pegRNA">
                        <i class="fas fa-cogs"></i> Design pegRNA
                    </button>
                    <button class="btn primary" id="design-gpegRNA">
                        <i class="fas fa-cogs"></i> Design g-pegRNA
                    </button>
                    <button class="btn primary" id="design-epegRNA">
                        <i class="fas fa-cogs"></i> Design epegRNA
                    </button>
                    <button class="btn primary" id="design-gepegRNA">
                        <i class="fas fa-cogs"></i> Design g-epegRNA
                    </button>
                    <button class="btn secondary" id="clear-design">
                        <i class="fas fa-trash"></i> Clear All
                    </button>
                </div>

                <div class="info-notice"
                    style="background-color: #e6f7ff; border-left: 4px solid var(--primary-color); padding: 10px 15px; border-radius: 4px; font-size: 0.85rem; color: #333; margin-bottom: 20px;">
                    <strong><i class="fas fa-info-circle" style="color: var(--primary-color);"></i> Design
                        Limits:</strong>
                    <ul style="margin-top: 5px; margin-bottom: 0; padding-left: 20px;">
                        <li>Minimum sequence length: <strong>30 bp</strong> total (at least 20 bp upstream, 10 bp
                            downstream of edit).</li>
                        <li>Prediction algorithm requires at least <strong>99 bp</strong> upstream and downstream of the
                            edit.</li>
                        <li>Maximum sequence length: <strong>2,000 bp</strong> (to ensure fast processing).</li>
                        <li>Maximum file upload size: <strong>10 KB</strong>.</li>
                    </ul>
                </div>
            </div>

            <!-- Parameters Tab -->
            <div id="parameters-tab" class="tab-content">
                <div class="param-group">

                    <div class="param-item param-item-inline">
                        <label>PAM sequence</label>
                        <div class="radio-group">
                            <input type="radio" class="larger" name="PAM" checked="checked" value="NGG" id="pam-ngg">
                            <label for="pam-ngg" style="color:#CC6600; margin-right:20px;">NGG</label>
                            <input type="radio" class="larger" name="PAM" value="NG" id="pam-ng">
                            <label for="pam-ng" style="color:#CC6600; margin-right:20px;">NG</label>
                            <input type="radio" class="larger" name="PAM" value="User_Defined" id="pam-custom">
                            <input type="text" name="User_PAM" autocomplete="off" class="layui-input"
                                placeholder="Custom PAM">
                        </div>
                    </div>

                    <div class="param-item param-item-inline">
                        <label for="CutToPAM">Cut distance to PAM</label>
                        <input type="number" name="CutToPAM" autocomplete="off" class="layui-input" value="-3">
                    </div>

                    <div class="param-item param-item-inline">
                        <label id="sl">Spacer length: 20</label>
                        <input type="range" id="OnTargetLength" name="OnTargetLength" min="1" max="40" value="20">
                    </div>

                    <div class="param-item param-item-inline">
                        <label id="sgcc">Spacer GC content (%): 0-100</label>
                        <div>
                            <div id="OnTarget_CG_Content_slider" class="demo-slider"></div>
                            <input type="hidden" id="OnTarget_CG_Content_min" name="OnTarget_CG_Content_min" value="0">
                            <input type="hidden" id="OnTarget_CG_Content_max" name="OnTarget_CG_Content_max"
                                value="100">
                        </div>
                    </div>

                    <div class="param-item param-item-inline">
                        <label id="pew">Prime editing window (the default values are recommended): 1-15</label>
                        <div>
                            <div id="PE_window_slider" class="demo-slider"></div>
                            <input type="hidden" id="PE_window_min" name="PE_window_min" value="1">
                            <input type="hidden" id="PE_window_max" name="PE_window_max" value="15">
                        </div>
                    </div>

                    <div class="param-item param-item-inline">
                        <label id="pbs">PBS length: 7-16</label>
                        <div>
                            <div id="PBS_Length_slider" class="demo-slider"></div>
                            <input type="hidden" id="PBS_Length_min" name="PBS_Length_min" value="7">
                            <input type="hidden" id="PBS_Length_max" name="PBS_Length_max" value="16">
                        </div>
                    </div>

                    <div class="param-item param-item-inline">
                        <label id="pbscg">PBS GC content (%): 0-100</label>
                        <div>
                            <div id="PBS_CG_Content_slider" class="demo-slider"></div>
                            <input type="hidden" id="PBS_CG_Content_min" name="PBS_CG_Content_min" value="0">
                            <input type="hidden" id="PBS_CG_Content_max" name="PBS_CG_Content_max" value="100">
                        </div>
                    </div>

                    <div class="param-item param-item-inline">
                        <label id="tmbest">Recommended Tm of PBS sequence (°C): 30</label>
                        <input type="range" id="TM_Best" name="TM_Best" min="20" max="50" value="30">
                    </div>

                    <div class="param-item param-item-inline">
                        <label id="rt">Homologous RT template length (the default values are recommended): 7-16</label>
                        <div>
                            <div id="RT_Length_slider" class="demo-slider"></div>
                            <input type="hidden" id="RT_Length_min" name="RT_Length_min" value="7">
                            <input type="hidden" id="RT_Length_max" name="RT_Length_max" value="16">
                        </div>
                    </div>

                    <div class="param-item param-item-inline">
                        <label for="tm-model">Tm-directed PBS length model</label>
                        <input type="checkbox" name="Tm_model" value="True" checked="checked" id="tm-model">
                    </div>

                    <div class="param-item param-item-inline">
                        <label for="exclude-c">Exclude first C in RT template</label>
                        <input type="checkbox" name="Exclude_LastG_in_RT" value="True" checked="checked" id="exclude-c">
                    </div>

                    <div class="param-item param-item-inline">
                        <label for="dual-pegrna">Dual-pegRNA model</label>
                        <input type="checkbox" name="CCNNGG_model" value="True" checked="checked" id="dual-pegrna">
                    </div>

                    <div class="param-item param-item-inline">
                        <label for="nick-model">PE3/PE3b nicking sgRNA</label>
                        <div style="display:flex; align-items:center; gap:18px;">
                            <input type="checkbox" name="Nick_Model" value="Both" id="nick-model">
                            <span id="nick-distance-wrap"
                                style="display:none; align-items:center; gap:8px; white-space:nowrap;">
                                <label for="Nick_Distance_min" style="margin:0;">Nick-to-nick distance (nt):</label>
                                <input type="number" name="Nick_Distance_min" id="Nick_Distance_min"
                                    class="layui-input" min="0" max="200" value="40" style="width:70px;">
                                <span>-</span>
                                <input type="number" name="Nick_Distance_max" id="Nick_Distance_max"
                                    class="layui-input" min="0" max="300" value="100" style="width:70px;">
                            </span>
                        </div>
                    </div>
                    <script>
                        (function () {
                            var cb = document.getElementById('nick-model');
                            var wrap = document.getElementById('nick-distance-wrap');
                            if (cb && wrap) {
                                var sync = function () {
                                    wrap.style.display = cb.checked ? 'inline-flex' : 'none';
                                };
                                cb.addEventListener('change', sync);
                                sync();
                            }
                        })();
                    </script>

                    <div class="param-actions">
                        <button type="button" class="btn secondary" id="reset-parameters">
                            <i class="fas fa-undo"></i> Reset to Defaults
                        </button>
                    </div>
                </div>
            </div>

            <!-- Optimization Tab -->
            <div id="optimization-tab" class="tab-content">
                <div class="param-group">
                    <h3>Primer Design</h3>

                    <div class="param-item">
                        <label>Primer Type</label>
                        <div class="radio-group">
                            <input type="radio" class="larger" name="Primer" checked="checked" value="OsU3"
                                id="primer-osu3">
                            <label for="primer-osu3" style="margin-right:20px;">pOsU3</label>
                            <input type="radio" class="larger" name="Primer" value="TaU3" id="primer-tau3">
                            <label for="primer-tau3" style="margin-right:20px;">pTaU3</label>
                            <input type="radio" class="larger" name="Primer" value="TaU6" id="primer-tau6">
                            <label for="primer-tau6" style="margin-right:20px;">pTaU6</label>
                            <input type="radio" class="larger" name="Primer" value="pHn-Cas9-V2" id="primer-hncas9">
                            <label for="primer-hncas9" style="margin-right:20px;">pH-nCas9-PPE-V2</label>
                            <input type="radio" class="larger" name="Primer" value="Others" id="primer-custom">
                            <label for="primer-custom">Custom</label>
                        </div>
                    </div>

                    <div class="custom-primer-section" id="custom-primer-section" style="display: none;">
                        <div class="param-item">
                            <label>Forward primer (5'-3'):</label>
                            <div class="primer-input-group">
                                <input type="text" name="Forward_Primer_left" autocomplete="off" class="layui-input"
                                    placeholder="Left part">
                                <span class="primer-connector">+ Spacer sequence +</span>
                                <input type="text" name="Forward_Primer_right" autocomplete="off" class="layui-input"
                                    placeholder="Right part">
                            </div>
                        </div>

                        <div class="param-item">
                            <label>Reverse primer (5'-3'):</label>
                            <div class="primer-input-group">
                                <input type="text" name="Reverse_Primer_left" autocomplete="off" class="layui-input"
                                    placeholder="Left part">
                                <span class="primer-connector">+ PBS and RT template sequence +</span>
                                <input type="text" name="Reverse_Primer_right" autocomplete="off" class="layui-input"
                                    placeholder="Right part">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="design-results" class="results-section" style="display: none;">
            <h3><i class="fas fa-dna"></i> pegRNA Design Results</h3>
            <div class="results-grid">
                <!-- Results will be dynamically inserted here -->
            </div>

            <div class="result-actions">
                <button class="btn secondary" id="export-results">
                    <i class="fas fa-download"></i> Export Results
                </button>
            </div>
        </div>
    </div>
</section>
<style>
    #pridict-loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.95);
        z-index: 9999;
        display: none;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .pridict-spinner {
        width: 80px;
        height: 80px;
        border: 8px solid #f3f3f3;
        border-top: 8px solid var(--primary-color, #034078);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 30px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    #pridict-loading-overlay h2 {
        color: var(--primary-color, #034078);
        margin-bottom: 10px;
        font-size: 24px;
    }

    #pridict-loading-overlay p {
        color: #555;
        font-size: 16px;
        max-width: 500px;
        text-align: center;
        line-height: 1.5;
    }
</style>

<div id="pridict-loading-overlay">
    <div class="pridict-spinner"></div>
    <h2>Running Prediciton Model</h2>
    <p>Sequence meets the flanking criteria.</p>
    <p>Efficiency scores are calculated for proposed pegRNAs.</p>
    <p>May take <strong>few minutes</strong>.</p>
</div>

<?php include 'footer.php'; ?>