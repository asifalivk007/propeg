/**
 * DNA / RNA complementing utilities.
 *
 * Supports IUPAC ambiguity codes plus the gap character '-' and treats U as
 * A's complement so that hybrid DNA/RNA inputs round-trip cleanly. Original
 * case is preserved on the output, and unrecognised characters are quietly
 * filtered out so callers can pass user input without sanitising first.
 */

const COMPLEMENT_PAIRS = Object.freeze({
    A: "T", C: "G", G: "C", T: "A", U: "A",
    a: "t", c: "g", g: "c", t: "a", u: "a",
    R: "Y", Y: "R", M: "K", K: "M", S: "S", W: "W",
    r: "y", y: "r", m: "k", k: "m", s: "s", w: "w",
    B: "V", V: "B", D: "H", H: "D",
    b: "v", v: "b", d: "h", h: "d",
    N: "N", n: "n", X: "X", x: "x",
    "-": "-",
});

/**
 * Given a (possibly noisy) sequence, return the surviving recognised bases
 * alongside their per-position complement.
 *
 * @returns {{seq: string, compSeq: string}}
 */
export function dnaComplement(input) {
    if (!input) return { seq: "", compSeq: "" };

    const kept = [];
    const comp = [];
    for (const ch of input) {
        const c = COMPLEMENT_PAIRS[ch];
        if (c !== undefined) {
            kept.push(ch);
            comp.push(c);
        }
    }
    return { seq: kept.join(""), compSeq: comp.join("") };
}

/** Convenience: full reverse complement as a plain string. */
export function reverseComplement(input) {
    const { compSeq } = dnaComplement(input);
    return [...compSeq].reverse().join("");
}
