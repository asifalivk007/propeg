<?php include 'header.php'; ?>

<!-- About Section -->
<section id="about" class="section active">
    <div class="container">

        <!-- Page header -->
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <img src="img/propeg.png" alt="PROpeg"
                style="height: 140px; width: auto; background: transparent; display: inline-block; border-radius: 7%;">
        </div>

        <!-- Mission / scope -->
        <div class="help-grid">
            <div class="help-card" style="grid-column: 1 / -1;">
                <h3><i class="fas fa-bullseye"></i> Scope &amp; Purpose</h3>
                <p>
                    Prime editing enables precise nucleotide-level genome modifications without inducing
                    double-strand breaks. The technology depends on a carefully engineered guide RNA &mdash;
                    the <strong>pegRNA</strong> &mdash; whose secondary structure, primer-binding site (PBS),
                    reverse-transcription (RT) template, and 3' protective elements all must be tuned for
                    the target locus. PROpeg removes that tuning burden:
                </p>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li>Generates plant-optimized pegRNA candidates from a wildtype-vs-edited sequence pair.</li>
                    <li>Ranks candidates against PAM, GC, T<sub>m</sub>, and PE-window constraints in one pass.</li>
                    <li>Predicts in-vivo editing efficiency.</li>
                    <li>Computes optimal nucleotide linkers for engineered designs.</li>
                    <li>Renders secondary-structure views for every variant in the browser.</li>
                </ul>
                <div class="info-notice"
                    style="background-color: #e6f7ff; border-left: 4px solid var(--primary-color); padding: 10px 15px; margin-top: 15px;">
                    <strong><i class="fas fa-lightbulb"></i> Designed for plants:</strong>
                    Default scaffold sequences, primer presets (<em>OsU3, TaU3, TaU6, pHn-Cas9-V2</em>), and
                    codon-usage tables target monocot and dicot crops. The same algorithms work on any
                    eukaryotic locus, but the defaults assume a plant-editing context.
                </div>
            </div>
        </div>

        <!-- Institutional affiliation -->
        <div class="help-grid" style="margin-top: 2rem; margin-bottom: 0rem;">
            <div class="help-card" style="grid-column: 1 / -1; text-align: center;">
                <h3 style="justify-content: center; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-university"></i> Developed By
                </h3>
                <p style="margin-bottom: 0.5rem;">
                    <a href="https://icar-crri.in/" target="_blank">ICAR &mdash; Central Rice Research Institute
                        (CRRI)</a>, Cuttack
                </p>
                <p style="margin-bottom: 0.5rem;">
                    <a href="https://iasri.res.in/" target="_blank">ICAR &mdash; Indian Agricultural Statistics
                        Research Institute (IASRI)</a>, New Delhi
                </p>
                <p style="font-size: 0.9rem; color: var(--text-secondary); margin-top: 1rem;">
                    &copy; 2026 ICAR-CRRI and ICAR-IASRI
                    (<a href="LICENSE" target="_blank">copyright &amp; attribution</a>).
                    Bundled third-party tools retain their original licenses
                    (<a href="ext_tools/peglit/LICENSE" target="_blank">pegLIT &mdash; BSD 3-Clause</a>,
                    <a href="ext_tools/PRIDICT2/LICENSE" target="_blank">PRIDICT2 &mdash; MIT</a>);
                    the PE3/PE3b nicking logic is derived from
                    <a href="https://github.com/rdchow/pegfinder" target="_blank">pegFinder &mdash; MIT</a>.
                    The pegRNA design approach implements the method of
                    <a href="https://doi.org/10.1038/s41587-021-00868-w" target="_blank">Lin
                        <em>et al.</em>, <em>Nature Biotechnology</em> <strong>39</strong>, 923&ndash;927 (2021)</a>.
                </p>
            </div>
        </div>

    </div>
</section>

<?php include 'footer.php'; ?>