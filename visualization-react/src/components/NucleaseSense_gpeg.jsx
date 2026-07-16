import React from "react";
import { dnaComplement } from "../utils/dnaUtils";
import { reverseSequence, partitionRtTemplate, rtArmAndFont, rnaify } from "../utils/seqHelpers";

const reverseComplementRNA = (seq) => {
    const complement = { 'A': 'U', 'U': 'A', 'C': 'G', 'G': 'C' };
    return seq.toUpperCase()
        .split('')
        .reverse()
        .map(c => complement[c] || c)
        .join('');
};

// ========================================================================
// The 86-nt scaffold sequence (RNA)
// Sequence: G U U U A A G A G C U A U G C U G G A A A C A G C A U A G C A A G U U U A A A U A A G G C U A G U C C G U U A U C A A C U U G A A A A A G U G G C A C C G A G U C G G U G C
// ========================================================================
const SCAFFOLD_SEQ = "GUUUAAGAGCUAUGCUGGAAACAGCAUAGCAAGUUUAAAUAAGGCUAGUCCGUUAUCAACUUGAAAAAGUGGCACCGAGUCGGUGC";
//                    0         1         2         3         4         5         6         7         8
//                    0123456789012345678901234567890123456789012345678901234567890123456789012345678901234 5

// Font style — matches the rest of the figure
const ntStyle = { fontSize: '10px', fill: '#ff10dbff', fontWeight: 'bold', fontFamily: "'Courier New', monospace" };
const ntClass = "st12";

// ========================================================================
// VERIFIED SECONDARY STRUCTURE  (all pairs Watson-Crick or G·U wobble)
// ========================================================================
//
//  LOWER STEM — 7 pairs
//    [0]G  · [39]U   G·U wobble ✓
//    [1]U  - [38]A   U-A ✓
//    [2]U  - [37]A   U-A ✓
//    [3]U  - [36]A   U-A ✓
//    [4]A  - [35]U   A-U ✓
//    [5]A  - [34]U   A-U ✓
//    [6]G  · [33]U   G·U wobble ✓
//
//  LEFT BULGE — 1 unpaired nt
//    [7] A
//
//  UPPER STEM — 9 pairs
//    [8]G  - [29]C   G-C ✓
//    [9]C  - [28]G   C-G ✓
//    [10]U - [27]A   U-A ✓
//    [11]A - [26]U   A-U ✓
//    [12]U - [25]A   U-A ✓
//    [13]G - [24]C   G-C ✓
//    [14]C - [23]G   C-G ✓
//    [15]U - [22]A   U-A ✓
//    [16]G - [21]C   G-C ✓
//
//  HAIRPIN 1 — GAAA tetraloop (top of upper stem)
//    [17]G  [18]A  [19]A  [20]A
//
//  RIGHT BULGE — 3 unpaired nt (asymmetric, opposite single left bulge)
//    [30]A  [31]A  [32]G
//
//  NEXUS REGION:
//    Straight down  — 13 nt single-stranded: pos[40–52] = AAGGCUAGUCCGU
//    Right-angle    —  5 nt horizontal turn: pos[53–57] = UAUCA
//    Nexus stem LEFT  (4 nt going up into hairpin): pos[58–61] = ACUU
//    Hairpin 2 — GAAA loop: pos[62–65] = GAAA
//    Nexus stem RIGHT (4 nt going back down, antiparallel): pos[66–69] = AAGU
//      Pairs: [58,69] A-U ✓  [59,68] C-G ✓  [60,67] U-A ✓  [61,66] U-A ✓
//
//  ISOLATED G — 1 unpaired nt between nexus and 3' stem
//    [70] G
//
//  3' STEM — 6 pairs
//    [71]G - [85]C   G-C ✓
//    [72]C - [84]G   C-G ✓
//    [73]A - [83]U   A-U ✓
//    [74]C - [82]G   C-G ✓
//    [75]C - [81]G   C-G ✓
//    [76]G - [80]C   G-C ✓
//
//  3' LOOP — AGU triloop
//    [77]A  [78]G  [79]U
//
// ========================================================================
// LAYOUT CONSTANTS
// ========================================================================

const HALF_SEP = 12;    // half inter-strand gap; total = 48px (dash fits cleanly)
const V_STEP = 9;    // vertical spacing between consecutive paired nt in a stem
const H_STEP = 9;    // horizontal spacing for the right-angle turn section
const CENTER_X = 476;   // horizontal center of scaffold
const L = CENTER_X - HALF_SEP;   // 556 — left strand x
const R = CENTER_X + HALF_SEP;   // 604 — right strand x

const pos = new Array(86);

// ========================================================================
// LOWER STEM  (7 pairs)
// pos[0–6]  = left strand, going UP (decreasing y)
// pos[39–33] = right strand, same y as paired left, x = R
// pos[0] = scaffold entry at spacer/guide junction
// ========================================================================
const LOWER_STEM_ENTRY_Y = 325;

pos[0] = { x: L, y: LOWER_STEM_ENTRY_Y };
pos[1] = { x: L, y: LOWER_STEM_ENTRY_Y - 1 * V_STEP };
pos[2] = { x: L, y: LOWER_STEM_ENTRY_Y - 2 * V_STEP };
pos[3] = { x: L, y: LOWER_STEM_ENTRY_Y - 3 * V_STEP };
pos[4] = { x: L, y: LOWER_STEM_ENTRY_Y - 4 * V_STEP };
pos[5] = { x: L, y: LOWER_STEM_ENTRY_Y - 5 * V_STEP };
pos[6] = { x: L, y: LOWER_STEM_ENTRY_Y - 6 * V_STEP };

pos[39] = { x: R, y: LOWER_STEM_ENTRY_Y };             // pairs with pos[0]
pos[38] = { x: R, y: LOWER_STEM_ENTRY_Y - 1 * V_STEP };  // pairs with pos[1]
pos[37] = { x: R, y: LOWER_STEM_ENTRY_Y - 2 * V_STEP };  // pairs with pos[2]
pos[36] = { x: R, y: LOWER_STEM_ENTRY_Y - 3 * V_STEP };  // pairs with pos[3]
pos[35] = { x: R, y: LOWER_STEM_ENTRY_Y - 4 * V_STEP };  // pairs with pos[4]
pos[34] = { x: R, y: LOWER_STEM_ENTRY_Y - 5 * V_STEP };  // pairs with pos[5]
pos[33] = { x: R, y: LOWER_STEM_ENTRY_Y - 6 * V_STEP };  // pairs with pos[6]

// ========================================================================
// BULGES
// Left bulge:  1 nt pos[7]=A,   protrudes LEFT  between lower and upper stem
// Right bulge: 3 nt pos[30-32], protrudes RIGHT asymmetrically opposite
// ========================================================================
const BULGE_Y = LOWER_STEM_ENTRY_Y - 6 * V_STEP;

pos[7] = { x: L - 12, y: BULGE_Y - 15 };   // A — single left bulge

pos[30] = { x: R + 10, y: BULGE_Y - 7 };   // A \
pos[31] = { x: R + 18, y: BULGE_Y - 15 };   // A  > right bulge (3 nt)
pos[32] = { x: R + 10, y: BULGE_Y - 22 };   // G /

// ========================================================================
// UPPER STEM  (9 pairs)
// pos[8–16]  = left strand, continuing upward above bulge
// pos[29–21] = right strand, same y as paired left
// ========================================================================
const UPPER_STEM_BASE_Y = BULGE_Y - 30;

pos[8] = { x: L, y: UPPER_STEM_BASE_Y };
pos[9] = { x: L, y: UPPER_STEM_BASE_Y - 1 * V_STEP };
pos[10] = { x: L, y: UPPER_STEM_BASE_Y - 2 * V_STEP };
pos[11] = { x: L, y: UPPER_STEM_BASE_Y - 3 * V_STEP };
pos[12] = { x: L, y: UPPER_STEM_BASE_Y - 4 * V_STEP };
pos[13] = { x: L, y: UPPER_STEM_BASE_Y - 5 * V_STEP };
pos[14] = { x: L, y: UPPER_STEM_BASE_Y - 6 * V_STEP };
pos[15] = { x: L, y: UPPER_STEM_BASE_Y - 7 * V_STEP };
pos[16] = { x: L, y: UPPER_STEM_BASE_Y - 8 * V_STEP };

pos[29] = { x: R, y: UPPER_STEM_BASE_Y };              // pairs with pos[8]
pos[28] = { x: R, y: UPPER_STEM_BASE_Y - 1 * V_STEP };   // pairs with pos[9]
pos[27] = { x: R, y: UPPER_STEM_BASE_Y - 2 * V_STEP };   // pairs with pos[10]
pos[26] = { x: R, y: UPPER_STEM_BASE_Y - 3 * V_STEP };   // pairs with pos[11]
pos[25] = { x: R, y: UPPER_STEM_BASE_Y - 4 * V_STEP };   // pairs with pos[12]
pos[24] = { x: R, y: UPPER_STEM_BASE_Y - 5 * V_STEP };   // pairs with pos[13]
pos[23] = { x: R, y: UPPER_STEM_BASE_Y - 6 * V_STEP };   // pairs with pos[14]
pos[22] = { x: R, y: UPPER_STEM_BASE_Y - 7 * V_STEP };   // pairs with pos[15]
pos[21] = { x: R, y: UPPER_STEM_BASE_Y - 8 * V_STEP };   // pairs with pos[16]

// ========================================================================
// HAIRPIN 1 — GAAA tetraloop  (top of upper stem)
// 4 nt arc above pos[16]/pos[21], evenly spaced 210°→330°
// ========================================================================
const LOOP1_TOP_Y = UPPER_STEM_BASE_Y - 8 * V_STEP;
const LOOP1_R = 20;

pos[17] = {
    x: Math.round(CENTER_X + LOOP1_R * Math.cos(210 * Math.PI / 180)),
    y: Math.round(LOOP1_TOP_Y + LOOP1_R * Math.sin(210 * Math.PI / 180))
};
pos[18] = {
    x: Math.round(CENTER_X + LOOP1_R * Math.cos(250 * Math.PI / 180)),
    y: Math.round(LOOP1_TOP_Y + LOOP1_R * Math.sin(250 * Math.PI / 180))
};
pos[19] = {
    x: Math.round(CENTER_X + LOOP1_R * Math.cos(290 * Math.PI / 180)),
    y: Math.round(LOOP1_TOP_Y + LOOP1_R * Math.sin(290 * Math.PI / 180))
};
pos[20] = {
    x: Math.round(CENTER_X + LOOP1_R * Math.cos(330 * Math.PI / 180)),
    y: Math.round(LOOP1_TOP_Y + LOOP1_R * Math.sin(330 * Math.PI / 180))
};

// ========================================================================
// NEXUS REGION
//
// The strand exits the lower stem at pos[39] (bottom-right of lower stem)
// and travels as follows (matching reference image):
//
//  SEGMENT 1 — Straight DOWN, 13 nt: pos[40–52] = AAGGCUAGUCCGU
//  SEGMENT 2 — Right-angle HORIZONTAL turn, 5 nt: pos[53–57] = UAUCA
//  NEXUS STEM LEFT  — 4 nt going UP: pos[58–61] = ACUU
//  HAIRPIN 2        — GAAA loop: pos[62–65]
//  NEXUS STEM RIGHT — 4 nt going DOWN (antiparallel): pos[66–69] = AAGU
//  ISOLATED G       — 1 unpaired nt: pos[70]
// ========================================================================

// --- SEGMENT 1: straight down from below pos[39] ---
const SEG1_X = R;                                  // same x as right strand of lower stem
const SEG1_START_Y = LOWER_STEM_ENTRY_Y + V_STEP;     // just below pos[39]

for (let i = 0; i < 13; i++) {
    pos[40 + i] = { x: SEG1_X, y: SEG1_START_Y + i * V_STEP };
}
// pos[52] is the last of segment 1, at the corner before the right-angle turn

// --- SEGMENT 2: right-angle turn going LEFT (horizontal), 5 nt ---
// The turn goes RIGHT (toward higher x) from the bottom of segment 1
const SEG2_Y = SEG1_START_Y + 12 * V_STEP;        // same y as pos[52]
const SEG2_START_X = SEG1_X + H_STEP;                 // first step RIGHT from pos[52]

for (let i = 0; i < 5; i++) {
    pos[53 + i] = { x: SEG2_START_X + i * H_STEP, y: SEG2_Y };
}
// pos[57] is the last of the turn, at the base of the nexus stem

// --- NEXUS STEM LEFT: 4 nt going UP from pos[57] ---
const NEXUS_STEM_CX = pos[57].x;  // will be set after pos[57] is defined above
// Actually compute directly:
const NEXUS_STEM_X_AT57 = SEG2_START_X + 6.5 * H_STEP; // x of pos[57], rightward end of turn
const NEXUS_STEM_L = NEXUS_STEM_X_AT57 - HALF_SEP;   // left strand of nexus stem
const NEXUS_STEM_R = NEXUS_STEM_X_AT57 + HALF_SEP;   // right strand of nexus stem
const NEXUS_STEM_BASE_Y = SEG2_Y;            // one step above the turn corner

pos[58] = { x: NEXUS_STEM_L, y: NEXUS_STEM_BASE_Y };
pos[59] = { x: NEXUS_STEM_L, y: NEXUS_STEM_BASE_Y - 1 * V_STEP };
pos[60] = { x: NEXUS_STEM_L, y: NEXUS_STEM_BASE_Y - 2 * V_STEP };
pos[61] = { x: NEXUS_STEM_L, y: NEXUS_STEM_BASE_Y - 3 * V_STEP };  // top of nexus stem left

// --- HAIRPIN 2: GAAA loop at top of nexus stem ---
const LOOP2_TOP_Y = NEXUS_STEM_BASE_Y - 3 * V_STEP;
const LOOP2_R = 18;
const LOOP2_CX = NEXUS_STEM_X_AT57;   // centered between the two nexus stem strands

pos[62] = {
    x: Math.round(LOOP2_CX + LOOP2_R * Math.cos(210 * Math.PI / 180)),
    y: Math.round(LOOP2_TOP_Y + LOOP2_R * Math.sin(210 * Math.PI / 180))
};
pos[63] = {
    x: Math.round(LOOP2_CX + LOOP2_R * Math.cos(250 * Math.PI / 180)),
    y: Math.round(LOOP2_TOP_Y + LOOP2_R * Math.sin(250 * Math.PI / 180))
};
pos[64] = {
    x: Math.round(LOOP2_CX + LOOP2_R * Math.cos(290 * Math.PI / 180)),
    y: Math.round(LOOP2_TOP_Y + LOOP2_R * Math.sin(290 * Math.PI / 180))
};
pos[65] = {
    x: Math.round(LOOP2_CX + LOOP2_R * Math.cos(330 * Math.PI / 180)),
    y: Math.round(LOOP2_TOP_Y + LOOP2_R * Math.sin(330 * Math.PI / 180))
};

// --- NEXUS STEM RIGHT: 4 nt going DOWN (antiparallel to pos[58–61]) ---
// Same y as paired left strand, x = NEXUS_STEM_R
pos[69] = { x: NEXUS_STEM_R, y: NEXUS_STEM_BASE_Y };             // pairs with pos[58]
pos[68] = { x: NEXUS_STEM_R, y: NEXUS_STEM_BASE_Y - 1 * V_STEP };  // pairs with pos[59]
pos[67] = { x: NEXUS_STEM_R, y: NEXUS_STEM_BASE_Y - 2 * V_STEP };  // pairs with pos[60]
pos[66] = { x: NEXUS_STEM_R, y: NEXUS_STEM_BASE_Y - 3 * V_STEP };  // pairs with pos[61]

// --- ISOLATED G: pos[70], single unpaired nt below nexus stem right ---
pos[70] = { x: NEXUS_STEM_R + 0.8 * H_STEP, y: NEXUS_STEM_BASE_Y };

// ========================================================================
// 3' STEM  (6 pairs)
// The backbone arrives from pos[70] (isolated G).
// pos[71] = BOTTOM of stem (largest y, just above pos[70])
// pos[76] = TOP  of stem left strand (smallest y, closest to loop)
// Left strand goes UP (decreasing y), mirroring the nexus stem left strand.
// Right strand same y as paired left, antiparallel.
// Loop arcs ABOVE the top (above pos[76] and pos[80]).
// ========================================================================
const PRIME3_STEM_L_X = pos[70].x + 0.8 * HALF_SEP;         // left strand, to the right of pos[70]
const PRIME3_STEM_R_X = pos[70].x + 2.8 * HALF_SEP;     // right strand, further right
const PRIME3_BASE_Y = NEXUS_STEM_BASE_Y;             // same y level as pos[70]

// Left strand — goes UP (decreasing y) from PRIME3_BASE_Y
pos[71] = { x: PRIME3_STEM_L_X, y: PRIME3_BASE_Y };              // bottom
pos[72] = { x: PRIME3_STEM_L_X, y: PRIME3_BASE_Y - 1 * V_STEP };
pos[73] = { x: PRIME3_STEM_L_X, y: PRIME3_BASE_Y - 2 * V_STEP };
pos[74] = { x: PRIME3_STEM_L_X, y: PRIME3_BASE_Y - 3 * V_STEP };
pos[75] = { x: PRIME3_STEM_L_X, y: PRIME3_BASE_Y - 4 * V_STEP };
pos[76] = { x: PRIME3_STEM_L_X, y: PRIME3_BASE_Y - 5 * V_STEP };   // top of left strand

// Right strand — same y as paired left (antiparallel)
pos[85] = { x: PRIME3_STEM_R_X, y: PRIME3_BASE_Y };               // pairs with pos[71]
pos[84] = { x: PRIME3_STEM_R_X, y: PRIME3_BASE_Y - 1 * V_STEP };    // pairs with pos[72]
pos[83] = { x: PRIME3_STEM_R_X, y: PRIME3_BASE_Y - 2 * V_STEP };    // pairs with pos[73]
pos[82] = { x: PRIME3_STEM_R_X, y: PRIME3_BASE_Y - 3 * V_STEP };    // pairs with pos[74]
pos[81] = { x: PRIME3_STEM_R_X, y: PRIME3_BASE_Y - 4 * V_STEP };    // pairs with pos[75]
pos[80] = { x: PRIME3_STEM_R_X, y: PRIME3_BASE_Y - 5 * V_STEP };    // pairs with pos[76], top of right strand

// ========================================================================
// 3' LOOP — AGU triloop  (pos[77–79])
// Arcs ABOVE the top of the 3' stem (above pos[76] and pos[80])
// Mirrors hairpin 1 and hairpin 2 — all loops arc upward
// ========================================================================
const PRIME3_STEM_CX = (PRIME3_STEM_L_X + PRIME3_STEM_R_X) / 2;
const LOOP3_TOP_Y = PRIME3_BASE_Y - 5 * V_STEP;      // same y as pos[76]/pos[80]
const LOOP3_R = 14;

pos[77] = {
    x: Math.round(PRIME3_STEM_CX + LOOP3_R * Math.cos(210 * Math.PI / 180)),
    y: Math.round(LOOP3_TOP_Y + LOOP3_R * Math.sin(210 * Math.PI / 180))
};
pos[78] = {
    x: PRIME3_STEM_CX,
    y: LOOP3_TOP_Y - LOOP3_R
};               // apex arcs UPWARD
pos[79] = {
    x: Math.round(PRIME3_STEM_CX + LOOP3_R * Math.cos(330 * Math.PI / 180)),
    y: Math.round(LOOP3_TOP_Y + LOOP3_R * Math.sin(330 * Math.PI / 180))
};

// ========================================================================
for (let i = 0; i < 86; i++) {
    if (pos[i]) pos[i].x = 900 - pos[i].x;
}

const cx = L;                    // = CENTER_X - HALF_SEP = x of pos[0]
const cy = LOWER_STEM_ENTRY_Y;   // = y of pos[0]

const angleDeg = 140;  // ← change this to 10, 20, 30, any angle
const angleRad = angleDeg * Math.PI / 180;

for (let i = 0; i < 86; i++) {
    if (pos[i]) {
        const dx = pos[i].x - cx;
        const dy = pos[i].y - cy;
        pos[i].x = Math.round(cx + dx * Math.cos(angleRad) - dy * Math.sin(angleRad));
        pos[i].y = Math.round(cy + dx * Math.sin(angleRad) + dy * Math.cos(angleRad));
    }
}

// ========================================================================
// BASE PAIRS  (all verified Watson-Crick or G·U wobble)
// ========================================================================
const BASE_PAIRS = [
    // Lower stem — 7 pairs
    [0, 39],   // G·U
    [1, 38],   // U-A
    [2, 37],   // U-A
    [3, 36],   // U-A
    [4, 35],   // A-U
    [5, 34],   // A-U
    [6, 33],   // G·U

    // Upper stem — 9 pairs
    [8, 29],   // G-C
    [9, 28],   // C-G
    [10, 27],   // U-A
    [11, 26],   // A-U
    [12, 25],   // U-A
    [13, 24],   // G-C
    [14, 23],   // C-G
    [15, 22],   // U-A
    [16, 21],   // G-C

    // Nexus stem — 4 pairs
    [58, 69],   // A-U
    [59, 68],   // C-G
    [60, 67],   // U-A
    [61, 66],   // U-A

    // 3' stem — 6 pairs
    [71, 85],   // G-C
    [72, 84],   // C-G
    [73, 83],   // A-U
    [74, 82],   // C-G
    [75, 81],   // C-G
    [76, 80],   // G-C
];

export default ({ spacer = '', pbs = '', rtTemplate = '', scaffoldMod = '' }) => {
    rtTemplate = rtTemplate.toUpperCase().replaceAll('T', 'U');
    pbs = pbs.replaceAll('T', 'U')
    // Long-RT layout (arms grow + font shrinks every 12 nt from 30) — shared policy.
    const { arm: rtArm, fontScale: rtFontScale, fontStyle: rtFontStyle, loopStep: LOOP_STEP } = rtArmAndFont(rtTemplate.length);
    const [first5, loopfirst, flip, looplast, last5] = partitionRtTemplate(rtTemplate, rtArm);
    const pbsLength = pbs.length;
    const spacerLength = spacer.length;
    const rtLen = rtTemplate.length;
    const apexFull = 46 - (loopfirst.length - 3) * LOOP_STEP;
    const rtViewTop = rtLen <= 25 ? 0 : Math.min(0, Math.round(apexFull - 12));

    let seq = SCAFFOLD_SEQ;
    if (scaffoldMod && scaffoldMod.length === 3) {
        // scaffoldMod is the RNA reverse complement of the 3 bases after RT template
        scaffoldMod = scaffoldMod.toUpperCase().replaceAll('T', 'U');
        // We need the forward RNA complement to maintain the stem pairing at [71, 72, 73]
        const compExt = reverseComplementRNA(scaffoldMod);

        let seqArr = seq.split('');
        // Replace 3' flap ends
        seqArr[83] = scaffoldMod[0];
        seqArr[84] = scaffoldMod[1];
        seqArr[85] = scaffoldMod[2];
        // Replace matching 3' stem pairings (71↔85, 72↔84, 73↔83)
        seqArr[71] = compExt[0];
        seqArr[72] = compExt[1];
        seqArr[73] = compExt[2];
        seq = seqArr.join('');
    }

    return <svg
        xmlns="http://www.w3.org/2000/svg"
        x="0"
        y="0"
        enableBackground="new 0 0 900 450"
        version="1.1"
        viewBox={`0 ${rtViewTop} 710 ${452 - rtViewTop}`}
        xmlSpace="preserve"
    >
        <path
            d="M422.3 61.6c-8.4-6.8-17-.3-24.8-6.9-6.3-5.4-7.2-16.2-5.4-23.5C397.7 8.4 434-.4 453.4.1c5.5.1 9.7 1 13.3 2.3 9.2 3.2 17.1 9.5 22.6 18.1 6.9 10.8 9.6 24 10.1 36.7.6 17.1 0 18.2 1.4 31.6 3.2 31.8 15.5 40 9.9 49-5.3 8.5-17.4 3.1-57.1 7.4-24.4 2.7-28.7 5-48 5.4-14.9.3-30.1-1.7-42.1-11.2-7.1-5.6-11.6-13.3-11.7-21.6-.1-5.1 1.6-9 2.4-11 .3-.7 5.7-12.6 15.7-16 13.8-4.7 21.5 11.5 38.6 8.8 7.8-1.2 17.9-6.4 20.5-15.8 2.3-8.2-.8-17.4-6.7-22.2z"
            className="st0"
        />
        <g className="st1">
            <path d="M365.7 94.9c13.8-4.7 21.5 11.5 38.6 8.8 6-.9 13.3-4.2 17.6-9.9-4.1 3.1-9.1 5-13.4 5.7-17.1 2.7-24.8-13.5-38.6-8.8-4.4 1.5-7.9 4.6-10.5 7.8 1.8-1.5 3.9-2.8 6.3-3.6zM500.7 88.8c-1.4-13.4-.8-14.5-1.4-31.6-.4-12.7-3.2-25.9-10.1-36.7-5.5-8.6-13.3-15-22.6-18.1C463 1.2 458.8.3 453.3.1c-15.8-.4-43.1 5.5-55.5 20.1 13.6-11.6 37.1-16.3 51.3-16 5.5.1 9.7 1 13.3 2.3 9.2 3.2 17.1 9.5 22.6 18.1 6.9 10.8 9.6 24 10.1 36.7.6 17.1 0 18.2 1.4 31.6 3.1 30.9 14.8 39.5 10.3 48.3 1.6-.8 2.8-1.9 3.7-3.4 5.7-9-6.6-17.3-9.8-49z" />
        </g>
        <path
            d="M262.5 259.5c34.7 15.5 59.5 11.6 91 17.2 25.2 4.4 68.7 12.1 92 43.5 22.3 30.1 7.8 57.4 38.1 88.1 6.5 6.6 17.9 17.7 34.7 19.6 24.6 2.7 44.3-16.1 52.6-24.2 15.7-15.2 22.1-31.6 26.2-42.1 8.5-21.7 9.3-40.4 9.7-51 .5-13.6-.7-24.7-1.8-34.5-1.8-16.6-4.6-28.9-5.4-32.3-2.3-9.8-4.7-20.4-10.1-33.1-7.5-17.8-16.7-30.1-20.1-34.5-20.6-26.4-45.5-38.5-58.9-44.9-5.2-2.5-25.3-11.7-53.6-16.2-25.2-4-44.5-2.1-67.2 0-19.1 1.8-32.4 4.5-54.6 9-27.6 5.6-41.4 8.8-52.1 13.9-9.1 4.3-52.4 24.9-56.8 62.7-.4 3.8-2 20.7 8.1 36.6 7.7 12 18.9 18 28.2 22.2z"
            className="st2"
        />
        <path
            d="M605 275.9c-1.8-16.6-4.6-28.9-5.4-32.3-2.3-9.8-4.7-20.4-10.1-33.1-7.5-17.8-16.7-30.1-20.1-34.5-20.6-26.4-45.5-38.5-58.9-44.9-5.2-2.5-25.3-11.7-53.6-16.2-25.2-4-44.5-2.1-67.2 0-19.1 1.8-32.4 4.5-54.6 9-27.6 5.6-41.4 8.8-52.1 13.9-5.8 2.8-25.4 12.1-40 28.2 13.5-12.3 28.8-19.6 33.8-22 10.7-5.1 24.5-8.3 52.1-13.9 22.3-4.5 35.5-7.2 54.6-9 22.7-2.1 42-4 67.2 0 28.3 4.4 48.4 13.7 53.6 16.2 13.4 6.4 38.3 18.6 58.9 44.9 3.5 4.4 12.6 16.7 20.1 34.5 5.3 12.7 7.8 23.2 10.1 33.1.8 3.5 3.6 15.7 5.4 32.3 1.1 9.8 2.3 20.9 1.8 34.5-.4 10.6-1.2 29.4-9.7 51-3.8 9.6-9.5 24.1-22.5 38.2.9-.9 1.7-1.7 2.5-2.4 15.7-15.2 22.1-31.6 26.2-42.1 8.5-21.7 9.3-40.4 9.7-51 .5-13.5-.7-24.6-1.8-34.4z"
            className="st1"
        />

        {/* === SCAFFOLD STRUCTURE (86 nucleotides) === */}
        <g>
            {/* Base pair dash lines */}
            {BASE_PAIRS.map(([a, b]) => {
                const pa = pos[a];
                const pb = pos[b];
                if (!pa || !pb) return null;

                const aRad = angleDeg * Math.PI / 180;

                const rotatePoint = (x, y, anchorX, anchorY) => {
                    const dx = x - anchorX;
                    const dy = y - anchorY;
                    return {
                        x: anchorX + dx * Math.cos(aRad) - dy * Math.sin(aRad),
                        y: anchorY + dx * Math.sin(aRad) + dy * Math.cos(aRad)
                    };
                };

                const rawCenterA = { x: pa.x - 3, y: pa.y + 3 };
                const rawCenterB = { x: pb.x - 3, y: pb.y + 3 };

                const ca = rotatePoint(rawCenterA.x, rawCenterA.y, pa.x, pa.y);
                const cb = rotatePoint(rawCenterB.x, rawCenterB.y, pb.x, pb.y);

                const dx = cb.x - ca.x;
                const dy = cb.y - ca.y;
                const len = Math.sqrt(dx * dx + dy * dy);
                const ux = dx / len;
                const uy = dy / len;
                const trim = 9;

                return <line key={`bp-${a}-${b}`}
                    x1={ca.x + ux * trim} y1={ca.y + uy * trim}
                    x2={cb.x - ux * trim} y2={cb.y - uy * trim}
                    stroke="#1f77b4" strokeWidth="1.5" opacity="0.8" />;
            })}

            {/* Nucleotide letters — rotated to match structure tilt, plus 180° flip */}
            {seq.split('').map((nt, i) => {
                const p = pos[i];
                if (!p) return null;

                const isSpecialPos = [71, 72, 73, 83, 84, 85].includes(i);
                const currentStyle = isSpecialPos
                    ? { ...ntStyle, fill: '#0707b9ff' }
                    : ntStyle;

                return <text key={`nt-${i}`} x={p.x} y={p.y}
                    transform={`rotate(${angleDeg + 180}, ${p.x}, ${p.y})`}
                    className={ntClass} style={currentStyle}>{nt}</text>;
            })}
        </g>

        {/* === GUIDE, PBS, RT TEMPLATE (unchanged) === */}
        <g>
            <text className="st5 st6" transform="translate(69.166 201.023)">5&apos;</text>
            <text className="st5 st6" transform="translate(70.365 272.69)">3&apos;</text>
            <text className="st5 st7" x={(20 - spacerLength) * 10} transform="translate(256.166 259.223)">
                {dnaComplement(spacer).compSeq}
            </text>
            <path d="M56.8 252.5L248.8 251.5" className="st8" />
            <path d="M55.8 213L247.8 212" className="st8" />
            <path d="M72.8 219L72.8 243" className="st8" />
            <path d="M237.8 219L237.8 243" className="st8" />
            <path d="M211.3 219L211.3 243" className="st8" />
            <path d="M89.3 219L89.3 243" className="st8" />
            <path d="M105.8 219L105.8 243" className="st8" />
            <path d="M124.3 219L124.3 243" className="st8" />
            <path d="M184.3 219L184.3 243" className="st8" />
            <path d="M140.8 219L140.8 243" className="st8" />
            <path d="M167.8 219L167.8 243" className="st8" />
            <path d="M80.4 219L80.4 243" className="st8" />
            <path d="M158.9 219L158.9 243" className="st8" />
            <path d="M245.9 219L245.9 243" className="st8" />
            <path d="M229.4 219L229.4 243" className="st8" />
            <path d="M219.9 219L219.9 243" className="st8" />
            <path d="M96.9 219L96.9 243" className="st8" />
            <path d="M115.4 219L115.4 243" className="st8" />
            <path d="M202.4 219L202.4 243" className="st8" />
            <path d="M131.9 219L131.9 243" className="st8" />
            <path d="M193.9 219L193.9 243" className="st8" />
            <path d="M149.4 219L149.4 243" className="st8" />
            <path d="M176.4 219L176.4 243" className="st8" />
            <text className="st5 st6" transform="translate(615.473 200.071)">3&apos;</text>
            <text className="st5 st6" transform="translate(615.473 271.737)">5&apos;</text>
            <g>
                <path d="M478 251.5L670 250.5" className="st8" />
                <path d="M477.6 212L669.6 211" className="st8" />
            </g>
            <text className="st14 st12 st7" x={(20 - spacerLength) * 10} transform="translate(256.166 301.005)">
                {rnaify(spacer)}
            </text>
            <text className="st13 st12 st7" style={rtFontStyle} transform="rotate(30 48.05 910.285)">
                {reverseSequence(first5)}
            </text>
            <text className="st5 st12 st7" transform="rotate(30 -139.884 921.731)">
                {rnaify(spacer.substring(spacerLength - 3, spacerLength))}
            </text>
            <g>
                <path d="M483 219L483 243" className="st8" />
                <path d="M648 219L648 243" className="st8" />
                <path d="M621.5 219L621.5 243" className="st8" />
                <path d="M499.5 219L499.5 243" className="st8" />
                <path d="M516 219L516 243" className="st8" />
                <path d="M534.5 219L534.5 243" className="st8" />
                <path d="M594.5 219L594.5 243" className="st8" />
                <path d="M551 219L551 243" className="st8" />
                <path d="M578 219L578 243" className="st8" />
                <path d="M490.7 219L490.7 243" className="st8" />
                <path d="M569.2 219L569.2 243" className="st8" />
                <path d="M656.2 219L656.2 243" className="st8" />
                <path d="M639.7 219L639.7 243" className="st8" />
                <path d="M630.2 219L630.2 243" className="st8" />
                <path d="M507.2 219L507.2 243" className="st8" />
                <path d="M525.7 219L525.7 243" className="st8" />
                <path d="M612.7 219L612.7 243" className="st8" />
                <path d="M542.2 219L542.2 243" className="st8" />
                <path d="M604.2 219L604.2 243" className="st8" />
                <path d="M559.7 219L559.7 243" className="st8" />
                <path d="M586.7 219L586.7 243" className="st8" />
            </g>
            <g>
                <text className="st5 st12 st7" x={(20 - spacerLength) * 10} transform="rotate(-30 526.818 -360.396)">
                    {rnaify(spacer.substring(0, spacerLength - 3))}
                </text>
                <text transform="rotate(-30 434.94 -420.564)">
                    <tspan x={(13 - pbsLength) * 10 - 12} y="-5" className="st11" style={{ fontSize: '13px', fontFamily: 'sans-serif' }}>
                        3&apos;
                    </tspan>
                    <tspan x={(13 - pbsLength) * 10} y="0" className="st11 st12 st7">
                        {reverseSequence(pbs)}
                    </tspan>
                </text>
                {pbsLength > 0 && <path d="M385 97.9L397 118.7" className="st8" />}
                {pbsLength > 1 && <path d="M371.6 102.7L383.6 123.4" className="st8" />}
                {pbsLength > 2 && <path d="M363 107.6L375 128.4" className="st8" />}
                {pbsLength > 3 && <path d="M353.3 113.2L365.3 134" className="st8" />}
                {pbsLength > 4 && <path d="M344.6 118.3L356.6 139.1" className="st8" />}
                {pbsLength > 5 && <path d="M335.5 123.5L347.5 144.3" className="st8" />}
                {pbsLength > 6 && <path d="M326.8 128.5L338.8 149.3" className="st8" />}
                {pbsLength > 7 && <path d="M317 134.2L329 155" className="st8" />}
                {pbsLength > 8 && <path d="M309 138.8L321 159.6" className="st8" />}
                {pbsLength > 9 && <path d="M300.2 143.9L312.2 164.7" className="st8" />}
                {pbsLength > 10 && <path d="M291.1 149.1L303.1 169.9" className="st8" />}
                {pbsLength > 11 && <path d="M281.4 154.7L293.4 175.5" className="st8" />}
                {pbsLength > 12 && <path d="M272.6 159.8L284.6 180.6" className="st8" />}
                {pbsLength > 13 && <path d="M263.6 165L275.6 185.8" className="st8" />}
                {pbsLength > 14 && <path d="M255.8 169.5L267.8 190.3" className="st8" />}
                {pbsLength > 15 && <path d="M246.7 174.8L258.7 195.5" className="st8" />}
                {pbsLength > 16 && <path d="M236.9 180.4L248.9 201.2" className="st8" />}
            </g>
            <text transform="translate(386.513 92.29)">
                <tspan x="5" y="-5" className="st13 st12 st7" style={rtFontStyle}>{reverseSequence(last5)}</tspan>
            </text>
            <text transform="translate(458.702 35.889)">
                <tspan x={((loopfirst.length - 5) * 3 - 5 * (flip.length - 1)) * rtFontScale} y={(5 - loopfirst.length) * LOOP_STEP} className="st13 st12 st7" style={rtFontStyle}>{reverseSequence(flip)}</tspan>
            </text>
            <text transform="rotate(-75.001 277.498 -245.58)">
                <tspan x="10" y="0" className="st13 st12 st7" style={rtFontStyle}>{reverseSequence(looplast)}</tspan>
            </text>
            <text transform="scale(-1) rotate(-75.001 -260.38 288.099)">
                <tspan x={(5 - loopfirst.length) * LOOP_STEP} y="0" className="st13 st12 st7" style={rtFontStyle}>{reverseSequence(loopfirst)}</tspan>
            </text>
            <g>
                {spacer.split("").map((_, i) => <path key={i} d={"M" + (466.3 - i * 10.7) + " 261.5L" + (466.3 - i * 10.7) + " 285.5"} className="st8" />)}
            </g>
        </g>
        <text transform="matrix(1 0 0 1 247.1 292.7799)" className="st14" style={{ fontSize: '13px', fontFamily: 'sans-serif' }}>5&apos;</text>
    </svg>
}