// ---------------------------------------------------------------------------
// Robust standalone-SVG export.
//
// The on-screen figure is styled with a <style> block of CSS classes that use
// currentColor and 8-digit hex (#RRGGBBAA) colours. Browsers resolve all of
// that fine, but PowerPoint, Word and (older) Inkscape largely ignore <style>
// / class selectors and cannot parse #RRGGBBAA, so elements fall back to solid
// black and the figure looks broken once downloaded.
//
// To make the downloaded file render identically everywhere, we walk the live
// (already-rendered) SVG, read each element's *computed* style, and write the
// relevant presentation properties back as inline styles on the clone. The
// browser hands us concrete rgb()/rgba() values with currentColor and class
// rules already resolved — exactly what every other SVG consumer understands.
// ---------------------------------------------------------------------------

// Presentation properties worth carrying over. Anything geometry-related
// (width/height/transform/d/etc.) stays on the element as an attribute and is
// preserved by cloneNode, so it is deliberately omitted here.
const SVG_STYLE_PROPS = [
    'fill', 'fill-opacity', 'fill-rule',
    'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin',
    'stroke-miterlimit', 'stroke-dasharray', 'stroke-dashoffset', 'stroke-opacity',
    'opacity',
    'font-family', 'font-size', 'font-weight', 'font-style',
    'letter-spacing', 'text-anchor', 'dominant-baseline',
];

function inlineComputedStyles(srcRoot, cloneRoot) {
    const srcNodes = [srcRoot, ...srcRoot.querySelectorAll('*')];
    const dstNodes = [cloneRoot, ...cloneRoot.querySelectorAll('*')];
    const n = Math.min(srcNodes.length, dstNodes.length);

    for (let i = 0; i < n; i++) {
        const src = srcNodes[i];
        const dst = dstNodes[i];
        if (!dst || dst.nodeType !== 1) continue;

        // The figure fakes extra-bold sequence text with a hairline
        // (stroke: currentColor; stroke-width: 0.2px). Browsers anti-alias
        // that to near-nothing, but Inkscape / PowerPoint / Word render it,
        // and on tightly letter-spaced rows the stroked letter-bottoms merge
        // into a faint line along each baseline. The text is already
        // font-weight:bold, so drop the stroke on text to kill the artifact.
        const tag = dst.tagName.toLowerCase();
        const isText = tag === 'text' || tag === 'tspan';

        const cs = window.getComputedStyle(src);
        const parts = [];
        for (const prop of SVG_STYLE_PROPS) {
            if (isText && prop.indexOf('stroke') === 0) continue;
            let val = cs.getPropertyValue(prop);
            if (!val) continue;
            val = val.trim();
            if (val === '' || val === 'normal') continue;
            // 'none' only carries meaning for fill / stroke.
            if (val === 'none' && prop !== 'fill' && prop !== 'stroke') continue;
            parts.push(`${prop}:${val}`);
        }
        if (parts.length) dst.setAttribute('style', parts.join(';'));
        // The class hook is no longer needed once styles are inlined, and
        // dropping it stops class-aware tools re-applying the broken rules.
        dst.removeAttribute('class');
    }
}

// Add a slight top & bottom margin to the downloaded figure by growing the
// viewBox vertically (content coordinates are untouched).
function addViewBoxMargin(svg, margin) {
    const vb = svg.getAttribute('viewBox');
    if (!vb) return;
    const p = vb.split(/[\s,]+/).map(Number);
    if (p.length !== 4 || p.some(Number.isNaN)) return;
    const [minX, minY, w, h] = p;
    svg.setAttribute('viewBox', `${minX} ${minY - margin} ${w} ${h + 2 * margin}`);
}

/**
 * Serialize a live SVG element into a self-contained, cross-app-compatible
 * .svg file and trigger a download.
 *
 * @param {SVGSVGElement} svg      The rendered <svg> element.
 * @param {string} filename        Download filename.
 * @param {number} [margin=30]     Vertical margin (user units) added top & bottom.
 */
export function exportSvgElement(svg, filename, margin = 30) {
    if (!svg) return;

    const clone = svg.cloneNode(true);
    inlineComputedStyles(svg, clone);
    addViewBoxMargin(clone, margin);

    if (!clone.getAttribute('xmlns')) {
        clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
    }
    clone.setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');

    const data = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>\n'
        + new XMLSerializer().serializeToString(clone);
    const blob = new Blob([data], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();

    URL.revokeObjectURL(url);
}
