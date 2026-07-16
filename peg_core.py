"""Core algorithm for prime editing guide RNA design.

Performs PAM site discovery, primer-binding-site (PBS) and reverse-transcription
(RT) template construction, scaffold-modification computation (gpegRNA/gepegRNA),
and PCR primer design for a wildtype-vs-edited target encoded as
``left(ref/alt)right``.

Designed to be invoked by ``propeg.py``. The HTML it emits is consumed by
the PHP layer's regex parser, so the visible markup is intentionally stable.
"""

from __future__ import annotations

import io
import math
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple


LEFT_FLANK_MIN = 20
RIGHT_FLANK_MIN = 10

DEFAULT_PEGRNA_SCAFFOLD = (
    "GTTTAAGAGCTATGCTGGAAACAGCATAGCAAGTTTAAATAAGGCT"
    "AGTCCGTTATCAACTTGAAAAAGTGGCACCGAGTCGGTGC"
)

VALID_DNA = set("atcgATGC")
VALID_PAM = set("atcgATCGrymkswhbvdnRYMKSWHBVDN")

_IUPAC = {
    "a": frozenset("a"), "t": frozenset("t"),
    "c": frozenset("c"), "g": frozenset("g"),
    "r": frozenset("ag"), "y": frozenset("ct"),
    "m": frozenset("ac"), "k": frozenset("gt"),
    "s": frozenset("gc"), "w": frozenset("at"),
    "h": frozenset("atc"), "b": frozenset("gtc"),
    "v": frozenset("gac"), "d": frozenset("gat"),
    "n": frozenset("atcg"),
}

_RC_PAIRS = {
    "A": "T", "T": "A", "C": "G", "G": "C",
    "a": "t", "t": "a", "c": "g", "g": "c",
    "(": ")", ")": "(", "/": "/",
}
_RC_TABLE = str.maketrans(_RC_PAIRS)


class DesignError(ValueError):
    """Raised when input fails validation; the message is surfaced to the user."""


# ----------------------------------------------------------------------------
# Sequence utilities
# ----------------------------------------------------------------------------

def reverse_complement(seq: str) -> str:
    """Return the reverse complement, preserving case and edit-notation chars."""
    for ch in seq:
        if ch not in _RC_PAIRS:
            raise DesignError(f"Cannot find reverse complement of {ch}")
    return seq.translate(_RC_TABLE)[::-1]


def gc_fraction(seq: str) -> float:
    """GC content as a fraction in [0, 1], truncated to 3 decimals.

    Returns 0.0 (with no exception) on non-ACGT input, matching the legacy
    behaviour expected by the rest of the pipeline.
    """
    s = seq.lower()
    gc = sum(1 for c in s if c in "cg")
    at = sum(1 for c in s if c in "at")
    if (gc + at) == 0:
        return 0.0
    if gc + at != len(s):
        return 0.0
    return math.trunc(gc / (gc + at) * 1000) / 1000


def tm_wallace(seq: str) -> int:
    """Wallace-rule melting temperature: 2 * (A+T) + 4 * (G+C)."""
    s = seq.lower()
    total = 0
    for c in s:
        if c in "at":
            total += 2
        elif c in "cg":
            total += 4
        else:
            return 0
    return total


def _iupac_match(pattern_base: str, target_base: str) -> bool:
    p = pattern_base.lower()
    t = target_base.lower()
    return t in _IUPAC.get(p, frozenset())


# Complement for IUPAC codes, used to reverse-complement a (possibly ambiguous)
# PAM so the opposite strand can be scanned for nicking-sgRNA sites.
_IUPAC_COMPLEMENT = {
    "A": "T", "T": "A", "C": "G", "G": "C",
    "R": "Y", "Y": "R", "M": "K", "K": "M",
    "S": "S", "W": "W", "B": "V", "V": "B",
    "D": "H", "H": "D", "N": "N",
}


def reverse_complement_pam(pam: str) -> str:
    """Reverse-complement an IUPAC PAM pattern (e.g. ``NGG`` -> ``CCN``)."""
    return "".join(_IUPAC_COMPLEMENT.get(ch.upper(), "N") for ch in reversed(pam))


# ----------------------------------------------------------------------------
# Input parsing & validation
# ----------------------------------------------------------------------------

@dataclass
class ParsedTarget:
    ref_full: str
    alt_full: str
    length_left: int
    length_right: int
    length_ref: int
    length_alt: int


def validate_pam(pam: str) -> None:
    if pam == "User_Defined":
        raise DesignError("Empty pam sequence!")
    for ch in pam:
        if ch not in VALID_PAM:
            raise DesignError(f"Wrong pam sequence of {pam}!")


def validate_input_sequence(seq: str) -> None:
    if seq.count("(") != 1 or seq.count(")") != 1:
        raise DesignError("format should be aaa(a/t)ggg!")
    o = seq.index("(")
    c = seq.index(")")
    if o > c:
        raise DesignError("format should be aaa(a/t)ggg!")

    left, target, right = seq[:o], seq[o + 1:c], seq[c + 1:]

    for ch in left:
        if ch not in VALID_DNA:
            raise DesignError(f"{left} contains {ch}!")
    for ch in right:
        if ch not in VALID_DNA:
            raise DesignError(f"{right} contains {ch}!")
    if len(left) < LEFT_FLANK_MIN:
        raise DesignError(f"Left flanking sequence is too short! (<{LEFT_FLANK_MIN})")
    if len(right) < RIGHT_FLANK_MIN:
        raise DesignError(f"Right flanking sequence is too short! (<{RIGHT_FLANK_MIN})")
    if "/" not in target:
        raise DesignError("format should be aaa(a/t)ggg!")
    ref, alt = target.split("/", 1)
    for ch in ref:
        if ch not in VALID_DNA:
            raise DesignError(f"{ref} contains {ch}!")
    for ch in alt:
        if ch not in VALID_DNA:
            raise DesignError(f"{alt} contains {ch}!")
    if ref == alt:
        raise DesignError(f"{ref} eq {alt}")


def parse_target(seq: str) -> ParsedTarget:
    """Decompose ``left(ref/alt)right`` into RefSeq/AltSeq with case lock-in.

    Flanks become lowercase, target alleles uppercase — this preserves how the
    legacy pipeline reasoned about edited bases visually and avoids case drift
    in downstream substring math.
    """
    o = seq.index("(")
    c = seq.index(")")
    left = seq[:o].lower()
    right = seq[c + 1:].lower()
    target = seq[o + 1:c].upper()
    ref, alt = target.split("/", 1)
    return ParsedTarget(
        ref_full=left + ref + right,
        alt_full=left + alt + right,
        length_left=len(left),
        length_right=len(right),
        length_ref=len(ref),
        length_alt=len(alt),
    )


def parse_target_reverse(rc_seq: str) -> ParsedTarget:
    """Parse a reverse-complemented input with ref/alt roles swapped.

    On the antisense strand the algorithm scans for PAMs and designs PBS
    against what is biologically the *alt* allele template, while the RT body
    is templated off the *ref* allele. Swapping ref/alt up front lets the
    rest of the pipeline stay strand-agnostic.
    """
    fwd = parse_target(rc_seq)
    return ParsedTarget(
        ref_full=fwd.alt_full,
        alt_full=fwd.ref_full,
        length_left=fwd.length_left,
        length_right=fwd.length_right,
        length_ref=fwd.length_alt,
        length_alt=fwd.length_ref,
    )


# ----------------------------------------------------------------------------
# PAM site discovery & filtering
# ----------------------------------------------------------------------------

@dataclass
class PamCandidate:
    spacer: str
    pam: str
    cut_position: int
    left_distance_to_cut: Optional[int] = None  # set by PE-window filter


def find_pam_candidates(
    ref_seq: str, pam_pattern: str,
    on_target_length: int, cut_to_pam: int,
) -> List[PamCandidate]:
    """Scan the reference strand for spacer+PAM matches.

    Replicates the legacy half-open scan so unit-test parity with the original
    implementation is preserved.
    """
    n = len(ref_seq)
    pam_len = len(pam_pattern)
    out: List[PamCandidate] = []
    # NB: original uses strict `<` here, which excludes the very last legal
    # window. We reproduce that boundary so designs match the previous tool.
    for i in range(0, n - on_target_length - pam_len):
        ok = True
        for j in range(pam_len):
            if not _iupac_match(pam_pattern[j], ref_seq[i + on_target_length + j]):
                ok = False
                break
        if not ok:
            continue
        spacer = ref_seq[i:i + on_target_length]
        pam = ref_seq[i + on_target_length:i + on_target_length + pam_len]
        cut_pos = i + on_target_length + cut_to_pam
        out.append(PamCandidate(spacer=spacer, pam=pam, cut_position=cut_pos))
    return out


def filter_pam_by_spacer_gc(
    candidates: List[PamCandidate],
    gc_min: float, gc_max: float,
) -> List[PamCandidate]:
    keep: List[PamCandidate] = []
    for c in candidates:
        gc = gc_fraction(c.spacer) * 100.0
        if gc_min <= gc <= gc_max:
            keep.append(c)
    return keep


def filter_pam_by_pe_window(
    candidates: List[PamCandidate],
    pe_left: int, pe_right: int, length_left: int,
) -> List[PamCandidate]:
    """Keep only sites whose PE window straddles the edit.

    Window spans positions `cut + pe_left - 1` (inclusive lower) to
    `cut + pe_right - 1` (inclusive upper); the edit at `length_left` must
    fall strictly inside this band on the right-hand side.
    """
    keep: List[PamCandidate] = []
    for c in candidates:
        lo = c.cut_position + pe_left - 1
        hi = c.cut_position + pe_right - 1
        if lo <= length_left and hi > length_left:
            c.left_distance_to_cut = length_left - c.cut_position
            keep.append(c)
    return keep


PE_WINDOW_UI_MAX = 50   # upper bound of the PE-window slider in the UI


def suggest_pe_window_max(
    parsed_targets: List[ParsedTarget], params: "DesignParams",
    window_cap: int = PE_WINDOW_UI_MAX,
) -> Optional[int]:
    """Smallest PE-window upper bound that would surface at least one spacer-PAM.

    When a run yields no usable sites, the edit simply falls outside the current
    prime-editing window for every candidate. Keeping the user's ``pe_left``,
    this returns the minimum ``pe_right`` at which some spacer-PAM (already
    passing PAM discovery and spacer-GC filtering, on either supplied strand
    parse) would place the edit inside the window.

    Returns ``None`` when widening the upper bound cannot help — either every
    candidate sits left of ``pe_left`` (needing a smaller lower bound instead),
    or the smallest workable ``pe_right`` exceeds ``window_cap`` (the UI slider
    maximum), which would make the suggestion unreachable from the interface.

    Mirrors the boundary logic of :func:`filter_pam_by_pe_window`: a candidate
    with cut ``c`` and edit at ``length_left`` is kept iff
    ``pe_left <= e`` and ``pe_right >= e + 1`` where ``e = length_left - c + 1``.
    """
    best: Optional[int] = None
    for parsed in parsed_targets:
        sites = find_pam_candidates(
            parsed.ref_full, params.pam,
            params.on_target_length, params.cut_to_pam,
        )
        sites = filter_pam_by_spacer_gc(
            sites, params.on_target_gc_min, params.on_target_gc_max,
        )
        for c in sites:
            edit_offset = parsed.length_left - c.cut_position + 1  # 1-based, rel. to cut
            if edit_offset < params.pe_left:
                continue  # only a smaller pe_left could capture this one
            required = edit_offset + 1
            if best is None or required < best:
                best = required
    if best is not None and best > window_cap:
        return None   # unreachable from the slider — don't suggest it
    return best


# ----------------------------------------------------------------------------
# PBS / RT / scaffold-ext / primer construction
# ----------------------------------------------------------------------------

@dataclass
class PbsEntry:
    sequence_rc: str       # reverse complement (what gets embedded in pegRNA)
    length: int
    tm: int
    gc_pct: float
    is_recommended: bool


@dataclass
class RtEntry:
    sequence_rc: str
    length: int
    scaffold_mod: str       # 3-nt RC of next bases on the alt template; substituted into
                            # the gpegRNA/gepegRNA scaffold's terminal 3 nt (replaces, not
                            # extends, the fixed 86-nt scaffold length)
    is_recommended: bool


@dataclass
class NickEntry:
    """A PE3/PE3b secondary nicking sgRNA on the strand opposite the pegRNA nick.

    Logic ported from pegFinder (rdchow, MIT) — see ``build_nick_entries`` for
    the full attribution/citation note.
    """
    kind: str            # "PE3" or "PE3b"
    spacer: str          # nicking-sgRNA spacer, 5'->3'
    pam: str             # the sgRNA's own PAM (NGG-form, same strand/orientation as spacer)
    distance: int        # signed nt from the pegRNA nick (opposite strand)
    gc_pct: float
    edit_specific: bool  # True for PE3b (recognises only the edited allele)


@dataclass
class ProgramResult:
    """One PAM site fully expanded into PBS, RT, scaffold-ext and primers."""
    strand: str
    on_target_seq: str
    pam_seq: str
    spacer_gc_pct: float
    cut_position: int
    left_distance_to_cut: int
    pbs_entries: List[PbsEntry] = field(default_factory=list)
    rt_entries: List[RtEntry] = field(default_factory=list)
    upstream_primer: str = ""
    downstream_primers: List[str] = field(default_factory=list)
    best_pbs: List[str] = field(default_factory=list)
    best_rt: List[str] = field(default_factory=list)
    linker: str = "NNNNNNNN"
    pe3_nick: Optional[NickEntry] = None
    pe3b_nick: Optional[NickEntry] = None


def build_pbs_entries(
    ref_seq: str, cut_position: int,
    pbs_min: int, pbs_max: int,
    pbs_gc_min: float, pbs_gc_max: float,
    target_tm: float, recommend_tm: bool,
) -> Tuple[List[PbsEntry], List[str]]:
    """Generate PBS candidates for a given cut, flagging the closest-to-Tm one."""
    raw: List[Tuple[str, int, int, float]] = []
    best_tm_value = 0
    for j in range(pbs_min, pbs_max + 1):
        seq = ref_seq[cut_position - j: cut_position]
        if len(seq) != j:
            continue
        tm = tm_wallace(seq)
        gc_pct = gc_fraction(seq) * 100.0
        if gc_pct > pbs_gc_max or gc_pct < pbs_gc_min:
            continue
        if abs(tm - target_tm) <= abs(best_tm_value - target_tm):
            best_tm_value = tm
        raw.append((seq, j, tm, gc_pct))

    entries: List[PbsEntry] = []
    best_pbs: List[str] = []
    for seq, length, tm, gc_pct in raw:
        is_best = recommend_tm and tm == best_tm_value
        if is_best:
            best_pbs.append(seq)
        entries.append(PbsEntry(
            sequence_rc=reverse_complement(seq),
            length=length,
            tm=tm,
            gc_pct=gc_pct,
            is_recommended=is_best,
        ))
    return entries, best_pbs


def build_rt_entries(
    alt_seq: str, cut_position: int,
    length_left: int, length_alt: int,
    rt_min: int, rt_max: int,
    exclude_last_g: bool,
) -> Tuple[List[RtEntry], List[str]]:
    """Generate RT-template candidates; the median-length passing one is flagged."""
    candidates: List[Tuple[str, int]] = []
    rt_left = alt_seq[cut_position: length_left]
    for j in range(rt_min, rt_max + 1):
        rt_right = alt_seq[length_left: length_left + length_alt + j]
        rt_seq = rt_left + rt_right
        if exclude_last_g and rt_seq and rt_seq[-1] in "gG":
            continue
        candidates.append((rt_seq, j))

    entries: List[RtEntry] = []
    best_rt: List[str] = []
    median_index = (len(candidates) + 1) // 2  # 1-based, matches legacy
    for idx, (rt_seq, j) in enumerate(candidates, start=1):
        is_best = (idx == median_index)
        if is_best:
            best_rt.append(rt_seq)
        seq_len = len(rt_seq)
        scaffold_mod = reverse_complement(
            alt_seq[cut_position + seq_len: cut_position + seq_len + 3]
        ) if cut_position + seq_len + 3 <= len(alt_seq) else ""
        entries.append(RtEntry(
            sequence_rc=reverse_complement(rt_seq),
            length=seq_len,
            scaffold_mod=scaffold_mod,
            is_recommended=is_best,
        ))
    return entries, best_rt


# ----------------------------------------------------------------------------
# PE3 / PE3b secondary nicking-sgRNA design
#
# Ported to native Python from pegFinder's Perl routines
# ``find_choose_nick_sgRNA_general`` and ``find_pe3b_sgRNA_general``
# (pegFinder lib/sub-sgRNA-finder.general.pl, https://github.com/rdchow/pegfinder).
# cite: Chow, R.D., Chen, S. et al., Nat. Biomed. Eng. (2021).
#
# As this is a derivative work, the upstream MIT copyright and permission notice
# is retained here in full (pegFinder's LICENSE):
#
#     MIT License
#
#     Copyright (c) 2020 rdchow
#
#     Permission is hereby granted, free of charge, to any person obtaining a copy
#     of this software and associated documentation files (the "Software"), to deal
#     in the Software without restriction, including without limitation the rights
#     to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
#     copies of the Software, and to permit persons to whom the Software is
#     furnished to do so, subject to the following conditions:
#
#     The above copyright notice and this permission notice shall be included in all
#     copies or substantial portions of the Software.
#
#     THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
#     IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
#     FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
#     AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
#     LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
#     OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
#     SOFTWARE.
#
# Only the off-target-free "general" logic is ported: candidate nicks are
# filtered by position, poly-T (Pol III terminator) avoidance, and — for PE3b —
# edit specificity. No genome-wide off-target scoring is performed (matching
# pegFinder's no-upload mode).
# ----------------------------------------------------------------------------

PE3_TARGET_DISTANCE = 50   # Anzalone 2019: a complementary-strand nick ~50 nt away works best


def _is_pure_dna(seq: str) -> bool:
    return bool(seq) and all(c in "ACGT" for c in seq)


def _pam_window_matches(window: str, pam_pattern: str) -> bool:
    if len(window) != len(pam_pattern):
        return False
    return all(_iupac_match(pam_pattern[k], window[k]) for k in range(len(pam_pattern)))


def _seed_pam_absent(ref: str, rc_pam: str, seed: str) -> bool:
    """True when the ``rc_pam``+``seed`` block occurs nowhere in ``ref``.

    Used by PE3b: the nicking spacer's seed+PAM must exist only in the edited
    allele, so the nick lands only after the edit is installed.
    """
    plen = len(rc_pam)
    slen = len(seed)
    for p in range(0, len(ref) - plen - slen + 1):
        if _pam_window_matches(ref[p:p + plen], rc_pam) and ref[p + plen:p + plen + slen] == seed:
            return False
    return True


def _scan_opposite_strand_nicks(
    seq: str, pam_pattern: str, on_target_length: int, cut_to_pam: int,
):
    """Yield ``(spacer5to3, pam_window, cut_pos, protospacer_start)`` for sites on
    the strand opposite the pegRNA.

    The pegRNA nicks the displayed strand, so a PE3 nick must fall on the other
    strand — which appears here as a reverse-complemented PAM (e.g. ``CCN`` for
    ``NGG``) immediately 5' of the protospacer. The nick position mirrors the
    sense-strand convention used by ``find_pam_candidates`` so distances are
    comparable to the pegRNA's ``cut_position``.
    """
    seq = seq.upper()
    rc_pam = reverse_complement_pam(pam_pattern)
    plen = len(rc_pam)
    L = on_target_length
    for p in range(0, len(seq) - plen - L + 1):
        if not _pam_window_matches(seq[p:p + plen], rc_pam):
            continue
        protospacer = seq[p + plen:p + plen + L]
        if not _is_pure_dna(protospacer):
            continue
        spacer = reverse_complement(protospacer)        # 5'->3' sgRNA spacer
        cut_pos = (p + plen) - cut_to_pam               # mirror of the sense cut
        yield spacer, seq[p:p + plen], cut_pos, p


def build_nick_entries(
    ref_seq: str, alt_seq: str,
    primary_cut: int, length_left: int, length_ref: int, length_alt: int,
    pam_pattern: str, on_target_length: int, cut_to_pam: int,
    nick_min: int, nick_max: int, nick_model: str,
) -> Tuple[Optional[NickEntry], Optional[NickEntry]]:
    """Design the best PE3 and/or PE3b nicking sgRNA for one pegRNA program.

    PE3  — scans the wildtype strand; keeps nicks ``nick_min``..``nick_max`` nt
           from the pegRNA nick and picks the one closest to ~50 nt.
    PE3b — scans the edited strand; keeps only nicks whose seed+PAM is absent in
           the wildtype (edit-specific) and picks the one closest to the nick.
    """
    model = (nick_model or "Off").lower()
    want_pe3 = model in ("pe3", "both")
    want_pe3b = model in ("pe3b", "both")
    ref_u = ref_seq.upper()
    alt_u = alt_seq.upper()

    pe3: Optional[NickEntry] = None
    pe3b: Optional[NickEntry] = None

    if want_pe3:
        best_score = None
        for spacer, pam, cut_pos, _ in _scan_opposite_strand_nicks(
            ref_u, pam_pattern, on_target_length, cut_to_pam
        ):
            if "TTTTT" in spacer:
                continue
            dist = cut_pos - primary_cut
            if not (nick_min <= abs(dist) <= nick_max):
                continue
            score = abs(abs(dist) - PE3_TARGET_DISTANCE)
            if best_score is None or score < best_score:
                best_score = score
                pe3 = NickEntry(
                    kind="PE3", spacer=spacer, pam=reverse_complement(pam), distance=dist,
                    gc_pct=gc_fraction(spacer) * 100.0, edit_specific=False,
                )

    if want_pe3b:
        rc_pam = reverse_complement_pam(pam_pattern)
        indel = length_alt - length_ref   # 0 for substitutions
        best_abs = None
        for spacer, pam, cut_pos, pstart in _scan_opposite_strand_nicks(
            alt_u, pam_pattern, on_target_length, cut_to_pam
        ):
            if "TTTTT" in spacer:
                continue
            seed = alt_u[pstart + len(pam): pstart + len(pam) + 10]   # PAM-proximal 10 nt
            if len(seed) < 10:
                continue
            if not _seed_pam_absent(ref_u, rc_pam, seed):
                continue   # also present in wildtype -> not edit-specific
            # map the edited-frame cut back to the wildtype frame for a comparable distance
            cut_ref = cut_pos - indel if cut_pos > length_left else cut_pos
            dist = cut_ref - primary_cut
            if abs(dist) > nick_max:
                continue
            if best_abs is None or abs(dist) < best_abs:
                best_abs = abs(dist)
                pe3b = NickEntry(
                    kind="PE3b", spacer=spacer, pam=reverse_complement(pam), distance=dist,
                    gc_pct=gc_fraction(spacer) * 100.0, edit_specific=True,
                )

    return pe3, pe3b


def build_primers(
    on_target_seq: str,
    upstream5: str, upstream3: str,
    downstream5: str, downstream3: str,
    best_pbs: List[str], best_rt: List[str],
) -> Tuple[str, List[str]]:
    spacer = on_target_seq[1:] if on_target_seq.startswith(("g", "G")) else on_target_seq
    upstream = upstream5 + spacer + upstream3
    downstreams = [
        downstream5 + pbs + rt + downstream3
        for pbs in best_pbs for rt in best_rt
    ]
    return upstream, downstreams


# ----------------------------------------------------------------------------
# Per-strand pipeline
# ----------------------------------------------------------------------------

@dataclass
class DesignParams:
    pam: str
    cut_to_pam: int
    on_target_length: int
    on_target_gc_min: float
    on_target_gc_max: float
    pe_left: int
    pe_right: int
    pbs_min: int
    pbs_max: int
    pbs_gc_min: float
    pbs_gc_max: float
    target_tm: float
    tm_model: bool
    rt_min: int
    rt_max: int
    exclude_last_g: bool
    ccnngg_model: bool
    upstream5: str
    upstream3: str
    downstream5: str
    downstream3: str
    # PE3/PE3b secondary nicking sgRNA (default Off keeps legacy behaviour).
    nick_model: str = "Off"
    nick_min: int = 40
    nick_max: int = 100

    @staticmethod
    def from_param_dict(d: Dict[str, str]) -> "DesignParams":
        def rng(key: str) -> Tuple[int, int]:
            lo, hi = d[key].split("-")
            return int(lo), int(hi)

        on_target_gc = rng("OnTarget_CG_Content")
        pe = rng("PE_Window")
        pbs_len = rng("PBS_Length")
        pbs_gc = rng("PBS_CG_Content")
        rt_len = rng("RT_Length")

        nick_model = d.get("Nick_Model", "Off")
        nick_min, nick_max = 40, 100
        nick_range = d.get("Nick_Distance", "")
        if "-" in nick_range:
            lo, hi = nick_range.split("-", 1)
            try:
                nick_min, nick_max = int(lo), int(hi)
            except ValueError:
                nick_min, nick_max = 40, 100

        return DesignParams(
            pam=d["PAM"],
            cut_to_pam=int(d["CutToPAM"]),
            on_target_length=int(d["OnTargetLength"]),
            on_target_gc_min=float(on_target_gc[0]),
            on_target_gc_max=float(on_target_gc[1]),
            pe_left=pe[0], pe_right=pe[1],
            pbs_min=pbs_len[0], pbs_max=pbs_len[1],
            pbs_gc_min=float(pbs_gc[0]), pbs_gc_max=float(pbs_gc[1]),
            target_tm=float(d["TM_Best"]),
            tm_model=(d.get("Tm_model", "True") == "True"),
            rt_min=rt_len[0], rt_max=rt_len[1],
            exclude_last_g=(d.get("Exclude_LastG_in_RT", "True") == "True"),
            ccnngg_model=(d.get("CCNNGG_model", "True") == "True"),
            upstream5=d.get("UpstreamPrimer5", ""),
            upstream3=d.get("UpstreamPrimer3", ""),
            downstream5=d.get("DownstreamPrimer5", ""),
            downstream3=d.get("DownstreamPrimer3", ""),
            nick_model=nick_model,
            nick_min=nick_min,
            nick_max=nick_max,
        )


@dataclass
class StrandRun:
    strand_label: str
    pam_count: int
    programs: List[ProgramResult]


def run_one_strand(
    parsed: ParsedTarget, params: DesignParams,
    label: str,
) -> StrandRun:
    """Run the full design pipeline for one strand orientation."""
    sites = find_pam_candidates(
        parsed.ref_full, params.pam,
        params.on_target_length, params.cut_to_pam,
    )
    sites = filter_pam_by_spacer_gc(sites, params.on_target_gc_min, params.on_target_gc_max)
    sites = filter_pam_by_pe_window(sites, params.pe_left, params.pe_right, parsed.length_left)

    programs: List[ProgramResult] = []
    for s in sites:
        pbs_entries, best_pbs = build_pbs_entries(
            parsed.ref_full, s.cut_position,
            params.pbs_min, params.pbs_max,
            params.pbs_gc_min, params.pbs_gc_max,
            params.target_tm, params.tm_model,
        )
        rt_entries, best_rt = build_rt_entries(
            parsed.alt_full, s.cut_position,
            parsed.length_left, parsed.length_alt,
            params.rt_min, params.rt_max,
            params.exclude_last_g,
        )
        upstream, downstreams = build_primers(
            s.spacer,
            params.upstream5, params.upstream3,
            params.downstream5, params.downstream3,
            best_pbs, best_rt,
        )
        pe3_nick = pe3b_nick = None
        if (params.nick_model or "Off").lower() != "off":
            pe3_nick, pe3b_nick = build_nick_entries(
                parsed.ref_full, parsed.alt_full,
                s.cut_position, parsed.length_left,
                parsed.length_ref, parsed.length_alt,
                params.pam, params.on_target_length, params.cut_to_pam,
                params.nick_min, params.nick_max, params.nick_model,
            )
        prog = ProgramResult(
            strand=label,
            on_target_seq=s.spacer,
            pam_seq=s.pam,
            spacer_gc_pct=gc_fraction(s.spacer) * 100.0,
            cut_position=s.cut_position,
            left_distance_to_cut=s.left_distance_to_cut or 0,
            pbs_entries=pbs_entries,
            rt_entries=rt_entries,
            upstream_primer=upstream,
            downstream_primers=downstreams,
            best_pbs=best_pbs,
            best_rt=best_rt,
            pe3_nick=pe3_nick,
            pe3b_nick=pe3b_nick,
        )
        programs.append(prog)
    return StrandRun(strand_label=label, pam_count=len(programs), programs=programs)


# ----------------------------------------------------------------------------
# HTML rendering
#
# The output HTML is read back by a regex-based parser in process_pegrna.php,
# so this section deliberately preserves spacing, attribute order, and the
# unclosed-<span> quirks of the legacy markup.
# ----------------------------------------------------------------------------

_HEADER_SINGLE = """<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Search</title>
<style>
#header {
    background-color:green;
    color:white;
    text-align:center;
    padding:5px;
}
#nav {
    line-height:30px;
    background-color:transparent;
    height:300px;
    width:600px;
    float:left;
    padding:5px;
}
#section {
	margin: 0 auto;
    width:400px;
    display: table;
    padding:10px;

}
#section input
{
	background-color:rgba(0,100,0,0.1);
	color: blue;
	font-size: 15px;
	display: table-cell;
	 width: 20%;
	text-align: center;
}
#section input.larger {
        transform: scale(2);
        margin: 10px;
      }
#footer {
    background-color:rgba(0,100,0,0.1);
    color:blue;
    clear:both;
    text-align:center;
   padding:5px;
}
</style>
</head>

<body>
<div id="header">
<h1>PROpeg</h1>

</div>

<!-- <div id="nav">

</div>
-->
<div id="section">
"""

_FOOTER = """</div>

<div id="footer">
Welcome to our website
<br/>
<!--Welcome To 804 Group
<form action="./index.CRISPR.php" method="post">
<input type="hidden" name="login_out" value="yes"/>
<input type="submit" value="Login OUT"/>
</form>
-->
</div>
</body>
</html>
"""


def _format_gc_pct(value: float) -> str:
    """Match the legacy formatting: trim trailing zeros / decimal point."""
    s = f"{value:g}"
    return s


def render_program_table(
    program: ProgramResult,
    program_number: int,
    program_label: str,
    strand_colspan: int = 5,
) -> str:
    """Render one PAM-site program as an HTML table fragment."""
    buf = io.StringIO()
    buf.write('<p></p>\n')
    buf.write('<table frame="hsides" width="1000">\n')

    # Program header row
    if program_label.startswith("Dual-"):
        buf.write("  <tr>\n")
        buf.write(f'    <th colspan="5" align="left"><span style="color:red">No. {program_number}: {program_label}</th>\n')
        buf.write("  </tr>\n")
    else:
        suffix = f": {program_label}" if program_label else ""
        buf.write("  <tr>\n")
        buf.write(f'    <th colspan="5" align="left"><span style="color:#7A09FA">No. {program_number}{suffix}</th>\n')
        buf.write("  </tr>\n")

    # Strand
    buf.write("  <tr>\n")
    buf.write(f'    <th colspan="{strand_colspan}" align="left">{program.strand}</th>\n')
    buf.write("  </tr>\n")

    # Spacer-PAM row
    buf.write("  <tr>\n")
    buf.write('    <th align="left">Spacer-PAM:</th>\n')
    buf.write(f'    <td>{program.on_target_seq}<span style="background-color:#8FBC8F">{program.pam_seq}</td>\n')
    buf.write(f'	<td>({_format_gc_pct(program.spacer_gc_pct)}% GC)</td>\n')
    buf.write('	<td></td>\n')
    buf.write('	<td></td>\n')
    buf.write("  </tr>\n")

    # PBS header + rows
    if program.pbs_entries:
        buf.write("  <tr>\n")
        buf.write('    <th align="left"><span style="color:blue">PBS:</th>\n')
        buf.write('    <td><span style="color:blue">Sequence</td>\n')
        buf.write('	<td><span style="color:blue">Length (nt)</td>\n')
        buf.write('	<td><span style="color:blue">Tm(&#176C)</td>\n')
        buf.write('	<td><span style="color:blue">GC(%)</td>\n')
        buf.write("  </tr>		\n")
        for pbs in program.pbs_entries:
            color = "red" if pbs.is_recommended else "black"
            label = "💡 Suggested" if pbs.is_recommended else ""
            buf.write("  <tr>\n")
            buf.write(f'    <td><span style="color:{color}">{label}</td>\n')
            buf.write(f'	<td><span style="color:{color}">{pbs.sequence_rc}</td>\n')
            buf.write(f'	<td><span style="color:{color}">{pbs.length}</td>\n')
            buf.write(f'	<td><span style="color:{color}">{pbs.tm}</td>\n')
            buf.write(f'	<td><span style="color:{color}">{_format_gc_pct(pbs.gc_pct)}</td>\n')
            buf.write("  </tr>	\n")

    # RT header + rows
    if program.rt_entries:
        buf.write("  <tr>\n")
        buf.write('    <th align="left"><span style="color:blue">RT template:</th>\n')
        buf.write('	<td><span style="color:blue">Sequence</td>	\n')
        buf.write('	<td><span style="color:blue">Length (nt)</td>	\n')
        buf.write("  </tr>\n")
        for rt in program.rt_entries:
            color = "red" if rt.is_recommended else "black"
            label = "💡 Suggested" if rt.is_recommended else ""
            buf.write("  <tr>\n")
            buf.write(f'    <td><span style="color:{color}">{label}</td>\n')
            buf.write(f'	<td><span style="color:{color}" data-scaffold-mod="{rt.scaffold_mod}">{rt.sequence_rc}</td>\n')
            buf.write(f'	<td><span style="color:{color}">{rt.length}</td>\n')
            buf.write('	<td></td>\n')
            buf.write('	<td></td>\n')
            buf.write("  </tr>\n")

    # PE3 / PE3b nicking sgRNA (opposite strand). Leading <th> + non-numeric cells
    # keep these rows clear of the PBS/RT regex parser in process_pegrna.php.
    for nick in (program.pe3_nick, program.pe3b_nick):
        if nick is None:
            continue
        sign = "+" if nick.distance >= 0 else ""
        note = " (edit-specific)" if nick.edit_specific else ""
        buf.write("  <tr>\n")
        buf.write(f'    <th align="left"><span style="color:#006400">{nick.kind} nicking sgRNA:</th>\n')
        buf.write(f'    <td><span style="color:#006400">{nick.spacer}<span style="background-color:#FFD9A0">{nick.pam}</span></td>\n')
        buf.write(f'	<td><span style="color:#006400">{sign}{nick.distance} nt from nick{note}</td>\n')
        buf.write(f'	<td><span style="color:#006400">{_format_gc_pct(nick.gc_pct)}% GC</td>\n')
        buf.write('	<td></td>\n')
        buf.write("  </tr>\n")

    # Primers
    buf.write("  <tr>\n")
    buf.write('    <th align="left"><span style="color:blue">Primers (Recommended):</th>\n')
    buf.write("  </tr>\n")
    buf.write("  <tr>\n")
    buf.write('    <td colspan="1"><span style="color:#7A09FA">Forward primer (5\'-3\')</td>\n')
    buf.write(f'	<td colspan="4"><span style="color:black">{program.upstream_primer}</td>\n')
    buf.write("  </tr>\n")
    for dp in program.downstream_primers:
        buf.write("  <tr>\n")
        buf.write('	<td colspan="1"><span style="color:#7A09FA">Reverse primer (5\'-3\')</td>\n')
        buf.write(f'	<td colspan="4"><span style="color:black">{dp}</td>\n')
        buf.write("  </tr>\n")

    buf.write("</table>\n")
    return buf.getvalue()


def _label_for(program_index: int, strand: str, dual_ok: bool, used: Dict[str, bool]) -> str:
    """Pick the recommended-program annotation matching the legacy phrasing."""
    if dual_ok:
        if strand == "Forward Strand":
            if not used["fwd"]:
                used["fwd"] = True
                return "Dual-pegRNA compatible with NGG support"
            return ""
        else:
            if not used["rev"]:
                used["rev"] = True
                return "Dual-pegRNA compatible with CCN support"
            return ""
    else:
        return ""


def render_single(
    programs: List[ProgramResult], dual_ok: bool, params: DesignParams,
    fwd_pam_count: int, rev_pam_count: int,
    pe_suggestion: Optional[int] = None,
) -> str:
    buf = io.StringIO()
    buf.write(_HEADER_SINGLE)

    if params.ccnngg_model:
        if dual_ok:
            buf.write(f'<p><span style="color:#c51b8a">Dual-pegRNA Supported: {fwd_pam_count} PAM(s) on the forward strand, {rev_pam_count} on the reverse.</p>\n')
        else:
            buf.write('<p><span style="color:#c51b8a">Dual-pegRNA Unsupported: Cannot be used for this sequence.</p>\n')

    if not programs:
        buf.write('<p><span style="color:#c51b8a">No PAM available.</p>\n')
        if pe_suggestion is not None:
            buf.write(
                '<p style="margin-top:18px"><span style="color:#2b8a3e; font-weight:bold">'
                'Suggestion: no spacer&ndash;PAM places the edit '
                f'inside the current prime-editing window ({params.pe_left}&ndash;{params.pe_right}). '
                f'Increase the prime-editing window to {params.pe_left}&ndash;{pe_suggestion} in the '
                'Parameters tab to obtain a design.</span></p>\n'
            )
    else:
        used = {"fwd": False, "rev": False, "any": False}
        for idx, prog in enumerate(programs, start=1):
            label = _label_for(idx, prog.strand, dual_ok, used)
            buf.write(render_program_table(prog, idx, label, strand_colspan=5))

    buf.write(_FOOTER)
    return buf.getvalue()


# ----------------------------------------------------------------------------
# I/O helpers
# ----------------------------------------------------------------------------

def read_param_file(path: str) -> Dict[str, str]:
    """Parse the tab-delimited key/value parameter file."""
    out: Dict[str, str] = {}
    with open(path, "r", encoding="utf-8", errors="replace") as fh:
        for raw in fh:
            line = raw.split("\r", 1)[0].rstrip("\n")
            if "\t" not in line:
                continue
            key, value = line.split("\t", 1)
            out[key] = value
    return out


def write_error_html(path: str, message: str) -> None:
    """Emit a minimal result file for error states.

    Uses the legacy ``Wrong:\\t...`` prefix so existing log scrapers still
    recognise these as validation failures.
    """
    with open(path, "w", encoding="utf-8") as fh:
        fh.write(f"Wrong:\t{message}\n")
