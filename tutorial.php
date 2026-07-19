<?php include 'header.php'; ?>

<!-- Help Section -->
<section id="help" class="section active">
    <div class="container">
        <h2><i class="fas fa-book-open"></i> PROpeg Comprehensive Tutorial</h2>
        <p style="font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 2rem;">
            PROpeg is a state-of-the-art computational platform tailored for designing and analyzing
            prime editing guide RNAs (pegRNAs) in plant systems.
        </p>

        <!-- Video Tutorial -->
        <div class="video-container"
            style="max-width: 900px; margin: 0 auto 3rem auto; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); background-color: #000;">
            <video width="100%" controls style="display: block;">
                <source src="img/tutorial/propeg_1080p.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div style="padding: 12px; text-align: center; color: #ddd; font-size: 0.95rem; font-weight: 500; letter-spacing: 0.5px; border-top: 1px solid #333;">
                <i class="fas fa-play-circle" style="color: var(--primary-color, #3b82f6); margin-right: 5px;"></i> PROpeg Quick Start Video Tutorial
            </div>
        </div>

        <!-- Step-by-Step Guide -->
        <div class="help-grid">
            <div class="help-card" style="grid-column: 1 / -1;">
                <h3><i class="fas fa-chart-bar"></i> Step 1: Inputting Your Sequences</h3>
                <p>Begin by providing the DNA sequences in the <strong>Guide Design</strong> tab.</p>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li><strong>Wildtype/Reference Sequence:</strong> The original, unmodified DNA sequence.</li>
                    <li><strong>Edited/Desired Sequence:</strong> The DNA sequence containing your intended mutation.
                    </li>
                    <li><strong>One sequence per run:</strong> PROpeg designs a single target at a time. Provide one
                        wildtype and one edited sequence; uploaded files must contain a single sequence. Batch /
                        multi-FASTA input is not supported.</li>
                    <li><strong>Load Example 1:</strong> Auto-fills sequences where machine-learning efficiency
                        prediction is <strong>OFF</strong> due to insufficient flanking context (less than 99 bp).</li>
                    <li><strong>Load Example 2:</strong> Auto-fills sequences where prediction is <strong>ON</strong>
                        because it satisfies the 99 bp upstream and downstream requirement.</li>
                </ul>
                <div class="info-notice"
                    style="background-color: #e6f7ff; border-left: 4px solid var(--primary-color); padding: 10px 15px; margin-top: 15px;">
                    <strong><i class="fas fa-lightbulb"></i>Tip:</strong> For the predictions along with pegRNAs, ensure
                    your target edit has at least <strong> 99 base pairs</strong> upstream and downstream of the edit.
                </div>
            </div>

            <div class="help-card" style="grid-column: 1 / -1;">
                <h3><i class="fas fa-chart-bar"></i> Step 2: Configuring Parameters</h3>
                <p>Under the <strong>Parameters</strong> tab, you can fine-tune your prime editing system:</p>
                <ul
                    style="margin-top: 10px; margin-left: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 0 40px;">
                    <li><strong>PAM Sequence:</strong> Choose from NGG, NG, or a Custom user-defined sequence.</li>
                    <li><strong>Cut distance to PAM:</strong> Define the cleavage position offset (default -3).</li>
                    <li><strong>Spacer length:</strong> (Range 1-40) Adjust the standard spacer length.</li>
                    <li><strong>Spacer GC content (%):</strong> Constrain GC pairs in the spacer sequence (0-100%).</li>
                    <li><strong>Prime editing window:</strong> Focus the edit inclusion bounds (1-15).</li>
                    <li><strong>PBS length:</strong> Set exact lengths (7-16) for the PBS.</li>
                    <li><strong>PBS GC content (%):</strong> Define GC boundaries (0-100%) for the PBS.</li>
                    <li><strong>Recommended Tm of PBS sequence:</strong> Directly control the melting temperature
                        (default 30°C).</li>
                    <li><strong>Homologous RT template length:</strong> (Range 7-16) Adjust the RT template size.</li>
                    <li style="grid-column: 1 / -1; margin-top: 6px;"><strong>Toggle Options:</strong> Enable optional
                        design models:
                        <ul class="toggle-suboptions" style="margin-top: 6px; margin-left: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 0 40px;">
                            <li><strong>Tm-directed PBS length model</strong> &mdash; size the PBS by melting
                                temperature rather than fixed length.</li>
                            <li><strong>Dual-pegRNA model</strong> &mdash; process a paired-pegRNA strategy where
                                supported.</li>
                            <li><strong>Exclude first C in RT template</strong> &mdash; avoid a 5' C at the start of
                                the RT template.</li>
                            <li><strong>PE3 / PE3b secondary nicking</strong> &mdash; add a nicking sgRNA on the
                                non-edited strand within your chosen nick-to-nick distance range, preferring an
                                edit-specific PE3b nick when available to boost efficiency.</li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="help-card" style="grid-column: 1 / -1;">
                <h3><i class="fas fa-chart-bar"></i> Step 3: Primer Design</h3>
                <p>Under the <strong>Primer</strong> tab, configure the primers required for the assembly of your pegRNA
                    expression vectors:</p>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li><strong>Pre-configured Primer Types:</strong> Instantly select standard plant editing system
                        architectures like <strong>pOsU3</strong>, <strong>pTaU3</strong>, <strong>pTaU6</strong>, or
                        <strong>pH-nCas9-PPE-V2</strong> to auto-load the necessary primers.</li>
                    <li><strong>Custom Primers:</strong> Alternatively, choose <em>Custom</em> to explicitly define the
                        left and right parts of your Forward and Reverse primers manually.</li>
                </ul>
            </div>

            <div class="help-card" style="grid-column: 1 / -1;">
                <h3><i class="fas fa-chart-bar"></i> Step 4: Interpreting Results & Structure Analysis</h3>
                <p>Depending on your application, click one of the four design buttons: <em>Design pegRNA</em>,
                    <em>Design g-pegRNA</em>, <em>Design epegRNA</em>, or <em>Design g-epegRNA</em>. The algorithm then
                    calculates permutations against thermodynamic boundaries and scoring models.</p>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li><strong>Program & Recommendation Rows:</strong> Results are systematically grouped into programs
                        (highlighted in green). Within each program grouping, the most thermodynamically and
                        functionally optimal PBS and RT template parameters for a design are designated as
                        <strong>Recommended!</strong> (highlighted in red).</li>
                    <li><strong>Column Features:</strong> Each row explicitly delineates the designed sequences for the
                        <strong>Spacer-PAM</strong>, <strong>Linker</strong>, <strong>PBS</strong>, and <strong>RT
                            Template</strong>, as well as indicating the target sequence <strong>Strand</strong>
                        orientation (Sense/Antisense).</li>
                    <li><strong>Efficiency Score:</strong> Predicted editing efficiency, utilizing a varaiant of
                        deep-learning tool algorithm (PRIDICT2.0), adopting the baseline (HEK293T) score.</li>
                    <li><strong>Secondary Structure Visualization:</strong> The results table will feature a specific
                        visualization button matching your chosen design (pegRNA, g-pegRNA, epegRNA, or g-epegRNA).
                        <ul style="margin-top: 5px; margin-left: 20px; list-style-type: circle;">
                            <li><strong>pegRNA:</strong> Visualizes the standard folded RNA string, showcasing spacer,
                                scaffold, RT, and PBS sections correctly aligned.</li>
                            <li><strong>g-pegRNA:</strong> Highlights the specific modification of the last three
                                nucleotides of the 86-nucleotide scaffold sequence, demonstrating exact dot-bracket base
                                pairing.</li>
                            <li><strong>epegRNA:</strong> Engineered pegRNA &mdash; attaches a 3' protective structured
                                motif (tevopreQ<sub>1</sub> sequence) to the RT template through an optimal <strong>linker
                                    sequence</strong>, on the standard (unmodified) scaffold, to resist exonuclease
                                degradation.</li>
                            <li><strong>g-epegRNA:</strong> Combines the g-pegRNA scaffold modification with the epegRNA 3'
                                motif &mdash; an optimal <strong>linker sequence</strong> attaches the tevopreQ<sub>1</sub> motif on
                                top of the modified scaffold, enhancing both editing purity and transcript stability in
                                vivo.</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="faq-section" style="margin-top: 4rem;">
            <h3><i class="fas fa-question-circle"></i> Frequently Asked Questions</h3>
            <div class="faq-item">
                <button class="faq-question" onclick="showFaqAnswer(1)">What is the difference between pegRNA and
                    g-pegRNA?</button>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="showFaqAnswer(2)">What is the difference between g-pegRNA and
                    g-epegRNA?</button>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="showFaqAnswer(5)">What is an epegRNA, and how does it differ from
                    g-epegRNA?</button>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="showFaqAnswer(3)">Why must I provide 99bp flanking
                    sequences?</button>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="showFaqAnswer(4)">How does PROpeg tool streamline the pegRNA
                    design process?</button>
            </div>
        </div>
    </div>
</section>

<script>
    function showFaqAnswer(id) {
        var title = '';
        var content = '';

        if (id === 1) {
            title = 'pegRNA vs g-pegRNA';
            content = '<p>A standard <strong>pegRNA</strong> utilizes the default scaffold sequence. In contrast, a <strong>g-pegRNA</strong> involves a specific modification of the last three nucleotides of the 86-nucleotide scaffold sequence to perfectly complement the reverse transcribed region. This targeted modification enhances structural stability and prevents mismatch interference during reverse transcription, thereby reducing the generation of unintended mutations (indels) and significantly improving the purity of the editing results.</p>';
        } else if (id === 2) {
            title = 'g-pegRNA vs g-epegRNA';
            content = '<p>While a <strong>g-pegRNA</strong> modifies the last three nucleotides of the scaffold for stability, a <strong>g-epegRNA</strong> (genomic engineered pegRNA) combines that scaffold modification with the engineered 3\' motif used by an <strong>epegRNA</strong>: it generates an optimal <strong>linker sequence</strong> to append a 3\' protective structured motif, such as the tevopreQ<sub>1</sub> sequence, to the very end of the layout. The linker prevents structural interference so the transcript folds correctly, while the motif protects it from intracellular exonuclease degradation &mdash; so a g-epegRNA gains both the editing-purity benefit of the modified scaffold and the stability benefit of the 3\' motif.</p>';
        } else if (id === 3) {
            title = 'Why provide 99bp flanking sequences?';
            content = '<p>To accurately predict your pegRNA\'s in-vivo efficiency, PROpeg utilizes a variant of the PRIDICT2.0 deep learning model. This model requires a substantial sequence context comprising 99 base pairs on each side of the mutation site to assess the determinants effectively.</p>';
        } else if (id === 5) {
            title = 'epegRNA vs g-epegRNA';
            content = '<p>An <strong>epegRNA</strong> (engineered pegRNA) keeps the <em>standard</em> scaffold but appends a 3\' protective structured motif (such as the tevopreQ<sub>1</sub> sequence) to the RT template through a computationally optimized <strong>linker sequence</strong>, shielding the 3\' extension from exonuclease degradation. A <strong>g-epegRNA</strong> applies that same engineered 3\' motif on top of the g-pegRNA scaffold modification &mdash; so an epegRNA is essentially the g-epegRNA design without the scaffold change, useful when you want the 3\' stability benefit without modifying the scaffold.</p>';
        } else if (id === 4) {
            title = 'How does PROpeg streamline the design process?';
            content = '<p>PROpeg provides a comprehensive all-in-one platform for prime editing workflows. Instead of relying on multiple disparate computational tools, it seamlessly integrates optimal pegRNA design, Tm-based optimization, machine-learning efficiency scoring, structural visualizations, and optimal linker identification into a single interface. This unifies the workflow, saving valuable time and ensuring high accuracy.</p>';
        }

        if (typeof layui !== 'undefined') {
            layui.use('layer', function () {
                var layer = layui.layer;
                layer.open({
                    type: 1,
                    title: '<strong>' + title + '</strong>',
                    area: ['500px', 'auto'],
                    content: '<div style="padding: 20px; font-size: 15px; line-height: 1.6; color: #333;">' + content + '</div>',
                    btn: ['Close'],
                    shadeClose: true
                });
            });
        } else {
            // Fallback if LayUI isn't ready
            alert(title + '\n\n' + content.replace(/<\/?[^>]+(>|$)/g, ""));
        }
    }
</script>

<?php include 'footer.php'; ?>