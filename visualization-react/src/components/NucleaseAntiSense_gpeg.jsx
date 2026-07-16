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
// The 86-nt scaffold sequence (RNA) — gpegRNA antisense variant
// ========================================================================
export const SCAFFOLD_SEQ = "GUUUAAGAGCUAUGCUGGAAACAGCAUAGCAAGUUUAAAUAAGGCUAGUCCGUUAUCAACUUGAAAAAGUGGCACCGAGUCGGUGC";

// Font style — matches the rest of the figure
const ntStyle = { fontSize: '10px', fill: '#ff10dbff', fontWeight: 'bold', fontFamily: "'Courier New', monospace" };
const ntClass = "st12";

// ========================================================================
// LAYOUT CONSTANTS
// ========================================================================
const HALF_SEP = 12;    // half inter-strand gap; total = 48px (dash fits cleanly)
const V_STEP = 9;       // vertical spacing between consecutive paired nt in a stem
const H_STEP = 9;       // horizontal spacing for the right-angle turn section
const CENTER_X = 325;   // horizontal center of scaffold
const L = CENTER_X - HALF_SEP;   // left strand x
const R = CENTER_X + HALF_SEP;   // right strand x

const pos = new Array(86);

// ========================================================================
// LOWER STEM  (7 pairs)
// pos[0–6]  = left strand, going UP (decreasing y)
// pos[39–33] = right strand, same y as paired left, x = R
// pos[0] = scaffold entry at spacer/guide junction
// ========================================================================
const LOWER_STEM_ENTRY_Y = 242;

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
//  SEGMENT 1 — Straight DOWN, 13 nt: pos[40–52] = AAGGCUAGUCCGU
//  SEGMENT 2 — Right-angle HORIZONTAL turn, 5 nt: pos[53–57] = UAUCA
//  NEXUS STEM LEFT  — 4 nt going UP: pos[58–61] = ACUU
//  HAIRPIN 2        — GAAA loop: pos[62–65]
//  NEXUS STEM RIGHT — 4 nt going DOWN (antiparallel): pos[66–69] = AAGU
//  ISOLATED G       — 1 unpaired nt: pos[70]
// ========================================================================

// --- SEGMENT 1: straight down from below pos[39] ---
const SEG1_X = R;
const SEG1_START_Y = LOWER_STEM_ENTRY_Y + V_STEP;

for (let i = 0; i < 13; i++) {
    pos[40 + i] = { x: SEG1_X, y: SEG1_START_Y + i * V_STEP };
}

// --- SEGMENT 2: right-angle turn going RIGHT (horizontal), 5 nt ---
const SEG2_Y = SEG1_START_Y + 12 * V_STEP;
const SEG2_START_X = SEG1_X + H_STEP;

for (let i = 0; i < 5; i++) {
    pos[53 + i] = { x: SEG2_START_X + i * H_STEP, y: SEG2_Y };
}

// --- NEXUS STEM LEFT: 4 nt going UP from pos[57] ---
const NEXUS_STEM_X_AT57 = SEG2_START_X + 6.5 * H_STEP;
const NEXUS_STEM_L = NEXUS_STEM_X_AT57 - HALF_SEP;
const NEXUS_STEM_R = NEXUS_STEM_X_AT57 + HALF_SEP;
const NEXUS_STEM_BASE_Y = SEG2_Y;

pos[58] = { x: NEXUS_STEM_L, y: NEXUS_STEM_BASE_Y };
pos[59] = { x: NEXUS_STEM_L, y: NEXUS_STEM_BASE_Y - 1 * V_STEP };
pos[60] = { x: NEXUS_STEM_L, y: NEXUS_STEM_BASE_Y - 2 * V_STEP };
pos[61] = { x: NEXUS_STEM_L, y: NEXUS_STEM_BASE_Y - 3 * V_STEP };

// --- HAIRPIN 2: GAAA loop at top of nexus stem ---
const LOOP2_TOP_Y = NEXUS_STEM_BASE_Y - 3 * V_STEP;
const LOOP2_R = 18;
const LOOP2_CX = NEXUS_STEM_X_AT57;

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
pos[69] = { x: NEXUS_STEM_R, y: NEXUS_STEM_BASE_Y };             // pairs with pos[58]
pos[68] = { x: NEXUS_STEM_R, y: NEXUS_STEM_BASE_Y - 1 * V_STEP };  // pairs with pos[59]
pos[67] = { x: NEXUS_STEM_R, y: NEXUS_STEM_BASE_Y - 2 * V_STEP };  // pairs with pos[60]
pos[66] = { x: NEXUS_STEM_R, y: NEXUS_STEM_BASE_Y - 3 * V_STEP };  // pairs with pos[61]

// --- ISOLATED G: pos[70], single unpaired nt below nexus stem right ---
pos[70] = { x: NEXUS_STEM_R + 0.8 * H_STEP, y: NEXUS_STEM_BASE_Y };

// ========================================================================
// 3' STEM  (6 pairs)
// ========================================================================
const PRIME3_STEM_L_X = pos[70].x + 0.8 * HALF_SEP;
const PRIME3_STEM_R_X = pos[70].x + 2.8 * HALF_SEP;
const PRIME3_BASE_Y = NEXUS_STEM_BASE_Y;

pos[71] = { x: PRIME3_STEM_L_X, y: PRIME3_BASE_Y };
pos[72] = { x: PRIME3_STEM_L_X, y: PRIME3_BASE_Y - 1 * V_STEP };
pos[73] = { x: PRIME3_STEM_L_X, y: PRIME3_BASE_Y - 2 * V_STEP };
pos[74] = { x: PRIME3_STEM_L_X, y: PRIME3_BASE_Y - 3 * V_STEP };
pos[75] = { x: PRIME3_STEM_L_X, y: PRIME3_BASE_Y - 4 * V_STEP };
pos[76] = { x: PRIME3_STEM_L_X, y: PRIME3_BASE_Y - 5 * V_STEP };

pos[85] = { x: PRIME3_STEM_R_X, y: PRIME3_BASE_Y };               // pairs with pos[71]
pos[84] = { x: PRIME3_STEM_R_X, y: PRIME3_BASE_Y - 1 * V_STEP };    // pairs with pos[72]
pos[83] = { x: PRIME3_STEM_R_X, y: PRIME3_BASE_Y - 2 * V_STEP };    // pairs with pos[73]
pos[82] = { x: PRIME3_STEM_R_X, y: PRIME3_BASE_Y - 3 * V_STEP };    // pairs with pos[74]
pos[81] = { x: PRIME3_STEM_R_X, y: PRIME3_BASE_Y - 4 * V_STEP };    // pairs with pos[75]
pos[80] = { x: PRIME3_STEM_R_X, y: PRIME3_BASE_Y - 5 * V_STEP };    // pairs with pos[76]

// ========================================================================
// 3' LOOP — AGU triloop  (pos[77–79])
// ========================================================================
const PRIME3_STEM_CX = (PRIME3_STEM_L_X + PRIME3_STEM_R_X) / 2;
const LOOP3_TOP_Y = PRIME3_BASE_Y - 5 * V_STEP;
const LOOP3_R = 14;

pos[77] = {
    x: Math.round(PRIME3_STEM_CX + LOOP3_R * Math.cos(210 * Math.PI / 180)),
    y: Math.round(LOOP3_TOP_Y + LOOP3_R * Math.sin(210 * Math.PI / 180))
};
pos[78] = {
    x: PRIME3_STEM_CX,
    y: LOOP3_TOP_Y - LOOP3_R
};
pos[79] = {
    x: Math.round(PRIME3_STEM_CX + LOOP3_R * Math.cos(330 * Math.PI / 180)),
    y: Math.round(LOOP3_TOP_Y + LOOP3_R * Math.sin(330 * Math.PI / 180))
};

// ========================================================================
// NOTE: No horizontal mirror for the antisense version.
// Apply rotation at 140° around pos[0] as anchor.
// ========================================================================
// ========================================================================
for (let i = 0; i < 86; i++) {
    if (pos[i]) {
        // pos[i].x remains unchanged
        pos[i].y = 600 - pos[i].y;  // Horizontal mirror: flip y across height/2
    }
}

const cx = L;                    // x of pos[0]
const cy = LOWER_STEM_ENTRY_Y;   // y of pos[0]

const angleDeg = 140;
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
    [10, 27],  // U-A
    [11, 26],  // A-U
    [12, 25],  // U-A
    [13, 24],  // G-C
    [14, 23],  // C-G
    [15, 22],  // U-A
    [16, 21],  // G-C

    // Nexus stem — 4 pairs
    [58, 69],  // A-U
    [59, 68],  // C-G
    [60, 67],  // U-A
    [61, 66],  // U-A

    // 3' stem — 6 pairs
    [71, 85],  // G-C
    [72, 84],  // C-G
    [73, 83],  // A-U
    [74, 82],  // C-G
    [75, 81],  // C-G
    [76, 80],  // G-C
];

export default function NucleaseAntiSense_gpeg({ spacer = '', pbs = '', rtTemplate = '', scaffoldMod = '' }) {
    rtTemplate = rtTemplate.toUpperCase().replaceAll('T', 'U');
    pbs = pbs.replaceAll('T', 'U');
    // Long-RT layout (arms grow + font shrinks every 12 nt from 30) — shared policy.
    const { arm: rtArm, fontScale: rtFontScale, fontStyle: rtFontStyle, loopStep: LOOP_STEP } = rtArmAndFont(rtTemplate.length);
    const [first5, loopfirst, flip, looplast, last5] = partitionRtTemplate(rtTemplate, rtArm);
    const pbsLength = pbs.length;
    const spacerLength = spacer.length;
    const rtLen = rtTemplate.length;
    // Antisense loop overflows the BOTTOM; extend the viewBox height to fit the apex.
    const apexBottom = 402 + (loopfirst.length - 3) * LOOP_STEP;
    const rtViewH = rtLen <= 25 ? 450 : Math.max(450, Math.round(apexBottom + 12));

    let seq = SCAFFOLD_SEQ;
    if (scaffoldMod && scaffoldMod.length === 3) {
        scaffoldMod = scaffoldMod.toUpperCase().replaceAll('T', 'U');
        const compExt = reverseComplementRNA(scaffoldMod);
        let seqArr = seq.split('');
        seqArr[83] = scaffoldMod[0];
        seqArr[84] = scaffoldMod[1];
        seqArr[85] = scaffoldMod[2];
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
        viewBox={`0 0 710 ${rtViewH}`}
        xmlSpace="preserve"
    >
        <path className="st0" d="M304.5,388.5c8.4,6.8,17,0.3,24.8,6.9c6.3,5.4,7.2,16.2,5.4,23.5c-5.6,22.8-41.9,31.6-61.3,31.1
		c-5.5-0.1-9.7-1-13.3-2.3c-9.2-3.2-17.1-9.5-22.6-18.1c-6.9-10.8-9.6-24-10.1-36.7c-0.6-17.1,0-18.2-1.4-31.6
		c-3.2-31.8-15.5-40-9.9-49c5.3-8.5,17.4-3.1,57.1-7.4c24.4-2.7,28.7-5,48-5.4c14.9-0.3,30.1,1.7,42.1,11.2
		c7.1,5.6,11.6,13.3,11.7,21.6c0.1,5.1-1.6,9-2.4,11c-0.3,0.7-5.7,12.6-15.7,16c-13.8,4.7-21.5-11.5-38.6-8.8
		c-7.8,1.2-17.9,6.4-20.5,15.8C295.5,374.5,298.6,383.7,304.5,388.5z"/>
        <g className="st1">
            <path d="M361.1,355.2c-13.8,4.7-21.5-11.5-38.6-8.8c-6,0.9-13.3,4.2-17.6,9.9c4.1-3.1,9.1-5,13.4-5.7c17.1-2.7,24.8,13.5,38.6,8.8
		c4.4-1.5,7.9-4.6,10.5-7.8C365.6,353.1,363.5,354.4,361.1,355.2z"/>
            <path d="M226.1,361.3c1.4,13.4,0.8,14.5,1.4,31.6c0.4,12.7,3.2,25.9,10.1,36.7c5.5,8.6,13.3,15,22.6,18.1
		c3.6,1.2,7.8,2.1,13.3,2.3c15.8,0.4,43.1-5.5,55.5-20.1c-13.6,11.6-37.1,16.3-51.3,16c-5.5-0.1-9.7-1-13.3-2.3
		c-9.2-3.2-17.1-9.5-22.6-18.1c-6.9-10.8-9.6-24-10.1-36.7c-0.6-17.1,0-18.2-1.4-31.6c-3.1-30.9-14.8-39.5-10.3-48.3
		c-1.6,0.8-2.8,1.9-3.7,3.4C210.6,321.3,222.9,329.6,226.1,361.3z"/>
        </g>
        <g>
            <path className="st2" d="M464.3,190.6c-34.7-15.5-59.5-11.6-91-17.2c-25.2-4.4-68.7-12.1-92-43.5c-22.3-30.1-7.8-57.4-38.1-88.1
		c-6.5-6.6-17.9-17.7-34.7-19.6c-24.6-2.7-44.3,16.1-52.6,24.2c-15.7,15.2-22.1,31.6-26.2,42.1c-8.5,21.7-9.3,40.4-9.7,51
		c-0.5,13.6,0.7,24.7,1.8,34.5c1.8,16.6,4.6,28.9,5.4,32.3c2.3,9.8,4.7,20.4,10.1,33.1c7.5,17.8,16.7,30.1,20.1,34.5
		c20.6,26.4,45.5,38.5,58.9,44.9c5.2,2.5,25.3,11.7,53.6,16.2c25.2,4,44.5,2.1,67.2,0c19.1-1.8,32.4-4.5,54.6-9
		c27.6-5.6,41.4-8.8,52.1-13.9c9.1-4.3,52.4-24.9,56.8-62.7c0.4-3.8,2-20.7-8.1-36.6C484.8,200.8,473.6,194.8,464.3,190.6z"/>
            <path className="st1" d="M121.8,174.2c1.8,16.6,4.6,28.9,5.4,32.3c2.3,9.8,4.7,20.4,10.1,33.1c7.5,17.8,16.7,30.1,20.1,34.5
		c20.6,26.4,45.5,38.5,58.9,44.9c5.2,2.5,25.3,11.7,53.6,16.2c25.2,4,44.5,2.1,67.2,0c19.1-1.8,32.4-4.5,54.6-9
		c27.6-5.6,41.4-8.8,52.1-13.9c5.8-2.8,25.4-12.1,40-28.2c-13.5,12.3-28.8,19.6-33.8,22c-10.7,5.1-24.5,8.3-52.1,13.9
		c-22.3,4.5-35.5,7.2-54.6,9c-22.7,2.1-42,4-67.2,0c-28.3-4.4-48.4-13.7-53.6-16.2c-13.4-6.4-38.3-18.6-58.9-44.9
		c-3.5-4.4-12.6-16.7-20.1-34.5c-5.3-12.7-7.8-23.2-10.1-33.1c-0.8-3.5-3.6-15.7-5.4-32.3c-1.1-9.8-2.3-20.9-1.8-34.5
		c0.4-10.6,1.2-29.4,9.7-51c3.8-9.6,9.5-24.1,22.5-38.2c-0.9,0.9-1.7,1.7-2.5,2.4c-15.7,15.2-22.1,31.6-26.2,42.1
		c-8.5,21.7-9.3,40.4-9.7,51C119.5,153.3,120.7,164.4,121.8,174.2z"/>
        </g>

        {/* === SCAFFOLD STRUCTURE (86-nt gpegRNA antisense) === */}
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

        {/* === GUIDE STRAND, PBS, RT TEMPLATE === */}
        <text transform="matrix(1 0 0 1 253.9385 198.8576)" className="st5 st7">{reverseSequence(dnaComplement(spacer).compSeq)}</text>
        <path id="Line_2_" className="st9" d="M478.1,198.3" />
        <path className="st9" d="M478.1,237.9" />
        <g>
            <path d="M77.8,256.3" />
            <path d="M77.8,180.3" />
            <text transform="matrix(1 0 0 1 69.1657 189.8603)" className="st5 st6">5&apos;</text>
            <text transform="matrix(1 0 0 1 70.3651 261.527)" className="st5 st6">3&apos;</text>
            <line className="st8" x1="56.8" y1="241.3" x2="248.8" y2="240.3" />
            <path id="Line_1_" className="st9" d="M56.8,240.6" />
            <path className="st9" d="M56.8,201" />
            <line className="st8" x1="55.8" y1="201.8" x2="247.8" y2="200.8" />
            <g>
                {/* Dynamic bars based on spacer length */}
                {Array.from({ length: spacerLength }, (_, i) => {
                    const startX = 72.8;
                    const endX = 245.9;
                    const spacing = (endX - startX) / (spacerLength - 1);
                    const x = startX + (i * spacing);
                    return <line key={i} className="st8" x1={x} y1="207.8" x2={x} y2="231.8" />;
                })}
            </g>
        </g>
        <path id="Line_10_" d="M287.7,237.5" />
        <path id="Line_9_" d="M479.7,237.5" />
        <path d="M287.7,277.1" />
        <path d="M479.7,277.1" />
        <path id="Line_8_" d="M186.3,237.6" />
        <text transform="matrix(1 0 0 1 252.3311 156.298)" className="st14 st12 st7">{reverseSequence(rnaify(spacer))}</text>
        <text transform="matrix(0.866 0.5 -0.5 0.866 215.4339 331.7481)" className="st13 st12 st7" style={rtFontStyle}>{first5}</text>
        <text transform="matrix(0.866 0.5 -0.5 0.866 253.182 247.7062)" className="st5 st12 st7">{reverseSequence(rnaify(spacer.substring(spacerLength - 3, spacerLength)))}</text>
        <g>
            <path d="M617,255.3" />
            <path d="M617,179.3" />
            <text transform="matrix(1 0 0 1 615.4731 188.9085)" className="st5 st6">3&apos;</text>
            <text transform="matrix(1 0 0 1 615.4731 260.5741)" className="st5 st6">5&apos;</text>
            <g>
                <line className="st8" x1="478" y1="240.3" x2="670" y2="239.3" />
                <path id="Line_4_" className="st9" d="M670.5,239.7" />
                <path id="Line_3_" className="st9" d="M478.6,239.7" />
                <path className="st9" d="M670.5,200.1" />
                <path className="st9" d="M478.6,200.1" />
                <line className="st8" x1="477.6" y1="200.8" x2="669.6" y2="199.8" />
            </g>
            <g>
                {/* Dynamic bars for right strand based on spacer length */}
                {Array.from({ length: spacerLength }, (_, i) => {
                    const startX = 483;
                    const endX = 656.2;
                    const spacing = (endX - startX) / (spacerLength - 1);
                    const x = startX + (i * spacing);
                    return <line key={i} className="st8" x1={x} y1="207.8" x2={x} y2="231.8" />;
                })}
            </g>
        </g>
        <g>
            <text transform="matrix(0.866 -0.5 0.5 0.866 321.33 332.5125)" className="st5 st12 st7">{reverseSequence(rnaify(spacer.substring(0, spacerLength - 3)))}</text>
            <text transform="matrix(0.866 -0.5 0.5 0.866 345.5946 368.1297)">
                <tspan x="0" y="0" className="st11 st12 st7">
                    {pbs}
                </tspan>
                <tspan x={pbsLength * 10 + 9} y="-5" className="st11" style={{ fontSize: '13px', fontFamily: 'sans-serif' }}>
                    3&apos;
                </tspan>
            </text>
            <g>
                {pbsLength > 16 && <line className="st8" x1="477.9" y1="248.9" x2="489.9" y2="269.7" />}
                {pbsLength > 15 && <line className="st8" x1="469.6" y1="253.7" x2="481.6" y2="274.4" />}
                {pbsLength > 14 && <line className="st8" x1="461" y1="258.6" x2="473" y2="279.4" />}
                {pbsLength > 13 && <line className="st8" x1="451.3" y1="264.2" x2="463.3" y2="285" />}
                {pbsLength > 12 && <line className="st8" x1="442.6" y1="269.3" x2="454.6" y2="290.1" />}
                {pbsLength > 11 && <line className="st8" x1="433.5" y1="274.5" x2="445.5" y2="295.3" />}
                {pbsLength > 10 && <line className="st8" x1="424.8" y1="279.5" x2="436.8" y2="300.3" />}
                {pbsLength > 9 && <line className="st8" x1="415" y1="285.2" x2="427" y2="306" />}
                {pbsLength > 8 && <line className="st8" x1="407" y1="289.8" x2="419" y2="310.6" />}
                {pbsLength > 7 && <line className="st8" x1="398.2" y1="294.9" x2="410.2" y2="315.7" />}
                {pbsLength > 6 && <line className="st8" x1="389.1" y1="300.1" x2="401.1" y2="320.9" />}
                {pbsLength > 5 && <line className="st8" x1="379.4" y1="305.7" x2="391.4" y2="326.5" />}
                {pbsLength > 4 && <line className="st8" x1="370.6" y1="310.8" x2="382.6" y2="331.6" />}
                {pbsLength > 3 && <line className="st8" x1="361.6" y1="316" x2="373.6" y2="336.8" />}
                {pbsLength > 2 && <line className="st8" x1="353.8" y1="320.5" x2="365.8" y2="341.3" />}
                {pbsLength > 1 && <line className="st8" x1="344.7" y1="325.8" x2="356.7" y2="346.5" />}
                {pbsLength > 0 && <line className="st8" x1="334.9" y1="331.4" x2="346.9" y2="352.2" />}
            </g>
        </g>
        <text transform="matrix(1 0 0 1 285.1099 365.0117)">
            <tspan x="0" y="0" className="st13 st12 st7" style={rtFontStyle}>{last5}</tspan>
        </text>
        <text transform="matrix(1 0 0 1 259.437 419.9679)">
            <tspan x={(-(loopfirst.length - 5) * 3 + 5 * (flip.length - 2)) * rtFontScale} y={-(5 - loopfirst.length) * LOOP_STEP} className="st13 st12 st7" style={rtFontStyle}>
                {flip}
            </tspan>
        </text>
        <text transform="matrix(0.2588 -0.9659 0.9659 0.2588 276.8433 418.2174)">
            <tspan x={(6 - loopfirst.length) * LOOP_STEP} y="0" className="st13 st12 st7" style={rtFontStyle}>{looplast}</tspan>
        </text>
        <text transform="matrix(-0.2588 0.9659 -0.9659 -0.2588 262.5258 357.9525)">
            <tspan x="0" y="0" className="st13 st12 st7" style={rtFontStyle}>{loopfirst}</tspan>
        </text>
        <g>
            {/* Dynamic bars for top spacer strand */}
            {Array.from({ length: spacerLength }, (_, i) => {
                const startX = 256.6;
                const endX = 467.1;
                const spacing = (endX - startX) / (spacerLength - 1);
                const x = startX + (i * spacing);
                return <line key={i} className="st8" x1={x} y1="188.6" x2={x} y2="164.6" />;
            })}
        </g>
        <text transform="matrix(1 0 0 1 471 151)" className="st14" style={{ fontSize: '13px', fontFamily: 'sans-serif' }}>5&apos;</text>
    </svg>
}
