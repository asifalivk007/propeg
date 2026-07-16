import React from 'react';
import { createRoot } from 'react-dom/client';
import NucleaseSense from './components/NucleaseSense_gepeg';
import NucleaseAntiSense from './components/NucleaseAntiSense_gepeg';
import { exportSvgElement } from './utils/svgExport';

// CSS Styles for the SVG - adjusted for proper alignment
const styles = `
.st0 { fill: #d5f3acff; opacity: 0.5; }
.st1 { opacity: 0.6; fill: #f0f181ff; }
.st2 { fill: #fdf4e1ff; opacity: 1; }
.st3 { fill: none; stroke: #055aaaff; stroke-width: 3; stroke-linecap: round; stroke-miterlimit: 10; }
.st4 { fill: none; stroke: #055aaaff; stroke-width: 2; stroke-miterlimit: 10; }
.st5 { fill: #ff8000ff; font-family: 'Courier New', monospace; font-size: 13px; font-weight: bold !important; stroke: currentColor; stroke-width: 0.2px; }
.st6 { font-size: 13px; font-weight: bold !important; }
.st7 { font-family: 'Courier New', monospace; font-size: 14px; letter-spacing: 2.6px; font-weight: bold !important; stroke: currentColor; stroke-width: 0.2px; }
.st8 { fill: none; stroke: #282828ff; stroke-width: 2; stroke-miterlimit: 10; }
.st9 { fill: none; }
/* .st10 { fill: none; stroke: #E74C3C; stroke-width: 2; stroke-linecap: round; stroke-dasharray: 4,2; } - commented out for future use */
.st11 { fill: #008800ff; }
.st12 { font-weight: bold !important; stroke: currentColor; stroke-width: 0.2px; }
.st13 { fill: #d32f2fff; } /* New color for RT Template */
.st14 { fill: #055aaaff; } /* New color for Spacer/Guide */
`;

// Wrapper component that applies styles
const PegRNAVisualization = ({ spacer, pbs, rtTemplate, strand, scaffoldMod, linker, motifX, motifY, motifAngle }) => {
    const VisualizationComponent = strand === 'antisense' ? NucleaseAntiSense : NucleaseSense;

    return (
        <div className="pegrna-viz-container">
            <style>{styles}</style>
            <VisualizationComponent
                spacer={spacer}
                pbs={pbs}
                rtTemplate={rtTemplate}
                scaffoldMod={scaffoldMod}
                linker={linker}
                motifX={motifX}
                motifY={motifY}
                motifAngle={motifAngle}
            />
        </div>
    );
};

// Store for active roots
const roots = new Map();

/**
 * Render pegRNA visualization into a container
 * @param {HTMLElement|string} container - DOM element or selector
 * @param {Object} options - Visualization options
 * @param {string} options.spacer - Spacer sequence (20bp DNA)
 * @param {string} options.pbs - PBS sequence (DNA)
 * @param {string} options.rtTemplate - RT Template sequence (DNA)
 * @param {string} options.strand - 'sense' or 'antisense'
 * @param {string} [options.scaffoldMod] - 3-nt replacement for the scaffold's terminal
 *   nucleotides (gpegRNA scaffold modification), not an added/extended sequence
 * @param {string} [options.linker] - Linker sequence
 */
function render(container, options = {}) {
    const element = typeof container === 'string'
        ? document.querySelector(container)
        : container;

    if (!element) {
        console.error('PegRNAVisualization: Container not found');
        return null;
    }

    // Clean up existing root if present
    if (roots.has(element)) {
        roots.get(element).unmount();
    }

    // Create new root and render
    const root = createRoot(element);
    roots.set(element, root);

    root.render(
        <PegRNAVisualization
            spacer={options.spacer || ''}
            pbs={options.pbs || ''}
            rtTemplate={options.rtTemplate || ''}
            strand={options.strand || 'sense'}
            scaffoldMod={options.scaffoldMod || ''}
            linker={options.linker || ''}
            motifX={options.motifX}
            motifY={options.motifY}
            motifAngle={options.motifAngle}
        />
    );

    return root;
}

/**
 * Unmount visualization from container
 * @param {HTMLElement|string} container - DOM element or selector
 */
function unmount(container) {
    const element = typeof container === 'string'
        ? document.querySelector(container)
        : container;

    if (element && roots.has(element)) {
        roots.get(element).unmount();
        roots.delete(element);
    }
}

/**
 * Export SVG from container
 * @param {HTMLElement|string} container - DOM element or selector
 * @param {string} filename - Download filename
 */
function exportSVG(container, filename = 'pegrna-structure.svg') {
    const element = typeof container === 'string'
        ? document.querySelector(container)
        : container;

    if (!element) return;

    const svg = element.querySelector('svg');
    if (!svg) return;

    // Inline computed styles so the file renders correctly outside the browser
    // (PowerPoint, Word, Inkscape) instead of falling back to black.
    exportSvgElement(svg, filename);
}

// Export the API
const PegRNAVisualizationAPI = {
    render,
    unmount,
    exportSVG,
    version: '1.2.0'
};

// Expose to global window object for browser usage
if (typeof window !== 'undefined') {
    window.GepegRNAVisualization = PegRNAVisualizationAPI;
}

export default PegRNAVisualizationAPI;
export { render, unmount, exportSVG };
