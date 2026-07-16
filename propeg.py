#!/usr/bin/env python3
"""PROpeg — single-sequence pegRNA design CLI.

Usage:
    python propeg.py <input_param_file> <output_html_file>

The input file is the tab-delimited parameter file written by the PHP layer;
the output is an HTML report consumed by ``process_pegrna.php``.
"""

from __future__ import annotations

import sys

import peg_core as pc


def main(argv: list[str]) -> int:
    if len(argv) >= 3:
        input_file, output_file = argv[1], argv[2]
    else:
        input_file, output_file = "Input.txt", "Output.html"

    try:
        params_dict = pc.read_param_file(input_file)
    except OSError as exc:
        pc.write_error_html(output_file, f"Cannot open input: {exc}")
        return 1

    seq = params_dict.get("Input_Sequence", "")
    pam = params_dict.get("PAM", "")

    try:
        pc.validate_input_sequence(seq)
        pc.validate_pam(pam)
    except pc.DesignError as exc:
        pc.write_error_html(output_file, str(exc))
        return 1

    try:
        params = pc.DesignParams.from_param_dict(params_dict)
    except (KeyError, ValueError) as exc:
        pc.write_error_html(output_file, f"Bad parameter: {exc}")
        return 1

    try:
        # Compute per-strand PAM counts so the dual-pegRNA banner can report them.
        fwd_parsed = pc.parse_target(seq)
        rev_parsed = pc.parse_target_reverse(pc.reverse_complement(seq))
        fwd_run = pc.run_one_strand(fwd_parsed, params, "Forward Strand")
        rev_run = pc.run_one_strand(rev_parsed, params, "Reverse Strand")

        dual_ok = (
            params.ccnngg_model
            and fwd_run.pam_count > 0
            and rev_run.pam_count > 0
        )

        programs = fwd_run.programs + rev_run.programs
        programs.sort(key=lambda p: (
            p.left_distance_to_cut,
            0 if p.strand == "Forward Strand" else 1,
        ))

        # When nothing surfaced, suggest the smallest PE-window upper bound that
        # would yield a design (inferred from the same candidates).
        pe_suggestion = None
        if not programs:
            pe_suggestion = pc.suggest_pe_window_max([fwd_parsed, rev_parsed], params)

        html = pc.render_single(
            programs, dual_ok, params,
            fwd_pam_count=fwd_run.pam_count,
            rev_pam_count=rev_run.pam_count,
            pe_suggestion=pe_suggestion,
        )
    except pc.DesignError as exc:
        pc.write_error_html(output_file, str(exc))
        return 1

    with open(output_file, "w", encoding="utf-8") as fh:
        fh.write(html)
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv))
