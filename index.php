<?php include 'header.php'; ?>

<!-- Home Section -->
<section id="home" class="section active">
    <div class="hero">

        <!-- Left column: title + buttons + descriptive paragraph stacked -->
        <div class="hero-content">
            <h1>PROpeg</h1>
            <h2>Advanced Plant Prime Editing Guide RNA Design Tool</h2>
            <p>Comprehensive tool for designing genomic pegRNAs with structure visualization for plant prime editing
                applications.</p>
            <div class="hero-buttons">
                <button class="btn primary" onclick="window.location.href='design.php'">Start Designing</button>
                <button class="btn secondary" onclick="window.location.href='Tutorial.php'">Learn More</button>
            </div>

            <div class="feature-card hero-feature-card"
                style="text-align: left; padding: 28px 32px; background: rgba(255, 255, 255, 0.95); box-shadow: 0 15px 35px rgba(0,0,0,0.1); border-radius: 12px; border-top: 5px solid var(--primary-color);">
                <p style="margin-bottom: 0; font-size: 0.95rem; text-align: justify; color: #444; line-height: 1.6;">
                     The pegRNA design landscape is fragmented — researchers must navigate multiple separate tools to access thermodynamic optimization, algorithmic scoring, PE3/PE3b nicking sgRNA selection, linker identification, and secondary structure visualization. <strong>PROpeg</strong> (Precision-optimized pegRNA) unifies these into a single, streamlined platform built specifically for plant prime editing, and introduces g-pegRNA (genomic pegRNA) — a design unique to PROpeg that fundamentally improves editing precision.</br>
                     Unlike standard pegRNAs that rely on a default scaffolding string, g-pegRNA modifies the last three nucleotides of the 86-nt scaffold to perfectly complement the reverse transcribed region of the genomic target. This prevents reverse transcription mismatches, reducing scaffold derived mutations and maximizing editing purity.
                </p>
            </div>
        </div>

        <!-- Right column: large g-epegRNA SVG showcase, right side trimmed via object-fit -->
        <div class="hero-showcase">
            <div class="hero-showcase-frame">
                <span class="hero-showcase-glow" aria-hidden="true"></span>
                <img src="img/gepegrna-structure.svg" alt="g-epegRNA secondary-structure schematic"
                     class="hero-showcase-svg">
            </div>
            <p class="hero-showcase-caption">
                <strong>g-epegRNA</strong> &mdash; the engineered design template
            </p>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>