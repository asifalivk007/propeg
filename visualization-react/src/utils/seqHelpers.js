/**
 * Generic sequence-string helpers used by the secondary-structure SVG
 * components. Kept tiny and pure so the figure code stays declarative.
 */

/** Reverse a sequence string. */
export const reverseSequence = (seq) => [...seq].reverse().join("");

/** Render a DNA sequence as RNA for display: T→U / t→u. */
export const rnaify = (seq) => seq.replace(/T/g, "U").replace(/t/g, "u");

/** Split a sequence at the given offset into [head, tail]. */
export const sliceAt = (seq, offset) => [seq.substring(0, offset), seq.substring(offset)];

/**
 * Long-RT layout policy, shared by every secondary-structure component so the
 * cadence stays in sync. As the RT template lengthens, the straight homology
 * arms grow and the RT font shrinks — both step every 12 nt from 30:
 *   arm:  5 (<30), then +1 at 30 / 42 / 54 / ...
 *   font: 14px (<30), then -1.5px at 30 / 42 / 54 / ... (floored at 9px)
 * Returns the arm length, the font scale, a ready-to-spread font ``style`` object,
 * and the per-nt loop-geometry step (11px scaled by the font) so the loop stays
 * proportional. Shrinking the font (not a transform) keeps the arms anchored.
 */
export function rtArmAndFont(rtLen) {
    const arm = rtLen < 30 ? 5 : 6 + Math.floor((rtLen - 30) / 12);
    const step = rtLen < 30 ? 0 : Math.floor((rtLen - 30) / 12) + 1;
    const fontPx = Math.max(9, 14 - 1.5 * step);
    const fontScale = fontPx / 14;
    const fontStyle = { fontSize: fontPx + "px", letterSpacing: (2.6 * fontScale).toFixed(2) + "px" };
    const loopStep = 11 * fontScale;
    return { arm, fontPx, fontScale, fontStyle, loopStep };
}

/**
 * Carve an RT template into the five regions used by the figure: the leading
 * straight arm (``armLen`` nt), the loop's first half, a central "flip" segment,
 * the loop's second half, and the trailing straight arm (``armLen`` nt). The
 * flip is 1 nt when the inner region length is even, 2 nt when odd, so the loop
 * arms balance about the central axis. ``armLen`` defaults to 5; callers grow it
 * for long RT templates so the loop stays compact.
 */
export function partitionRtTemplate(rtTemplate, armLen = 5) {
    const head = rtTemplate.substring(0, armLen);
    const tail = rtTemplate.substring(rtTemplate.length - armLen);
    const middle = rtTemplate.substring(armLen, rtTemplate.length - armLen);

    const halfLen = Math.floor(middle.length / 2);
    const flipLen = middle.length % 2 === 0 ? 1 : 2;

    const loopfirst = middle.substring(0, halfLen);
    const flip = middle.substring(halfLen, halfLen + flipLen);
    const looplast = middle.substring(halfLen + flipLen);

    return [head, loopfirst, flip, looplast, tail];
}
