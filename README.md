# PROpeg - Advanced Plant Prime Editing Guide RNA Design & Structure Visualization Tool

## Overview

**PROpeg** designs prime editing guide RNAs for plant genome editing. You provide one wild-type sequence and the desired edited sequence; PROpeg infers the edit, scans both strands for spacer–PAM candidates, and enumerates viable PBS and RT-template combinations — ranking them by a thermodynamic model and, where the sequence context allows, by a deep-learning efficiency prediction. It outputs ready-to-order sequences (including cloning primers), an optional secondary nicking sgRNA, and an interactive secondary-structure diagram of the final guide.

One target per run. The design engine is pure Python; the ML and linker tools run as separate services.

## Key Features

### 🧬 Prime editing architectures
- **pegRNA** — spacer, 86-nt scaffold, RT template and PBS for the standard design.
- **g-pegRNA** — substitutes the **terminal three nucleotides of the 86-nt scaffold** (and their pairing partners in the scaffold stem) with bases complementary to the RT template, extending the scaffold–template duplex by 3 bp. The scaffold length stays fixed at 86 nt: the bases are *replaced*, not appended.
- **epegRNA** — appends a 3′ structured motif (**tevopreQ₁**) to the RT template through a computationally optimised linker, on the standard unmodified scaffold, to slow exonucleolytic degradation.
- **g-epegRNA** — combines both: the g-pegRNA scaffold substitution *and* the 3′ tevopreQ₁ motif with its optimised linker.
- **PE3 / PE3b nicking** *(optional)* — designs a secondary nicking sgRNA on the non-edited strand within a chosen nick-to-nick distance range, preferring an edit-specific **PE3b** nick when one exists.

### 🤖 Integrated prediction and optimisation
- **PRIDICT2.0 scoring** — reports the deep-learning `deep_HEK` editing-efficiency score per candidate. Requires **≥99 bp of flanking sequence on both sides** of the edit; shorter targets are designed without scores.
- **pegLIT linker optimisation** — runs simulated annealing over RNA folds to pick a linker that keeps the tevopreQ₁ motif from base-pairing with the rest of the guide. Served by a local Flask/gunicorn process.

### 🔬 Secondary-structure visualisation
- **Per-architecture diagrams** — React-rendered schematics for pegRNA, g-pegRNA, epegRNA and g-epegRNA, drawn from the actual designed sequence rather than a template.
- **Annotated regions** — spacer–PAM, scaffold (with the g-modified bases highlighted), PBS, RT template, linker and motif are colour-coded, with the sense/antisense target strands aligned to the guide.
- **Exportable** — diagrams and result tables can be downloaded (SVG/PDF/CSV/JSON).

### 🌱 Plant-specific defaults
- **Cloning primer presets** for common monocot expression systems — `pOsU3`, `pTaU3`, `pTaU6` and `pH-nCas9-PPE-V2` — or fully custom primers.
- **Tm target defaulted to 30 °C** for PBS selection, reflecting typical plant growth temperatures.

## Project Structure

The platform is built on a lightweight PHP frontend with a Python core engine and PyTorch-backed ML toolings.

```text
propeg/
├── index.php                        # Landing page
├── design.php                       # Sequence input and parameter configuration
├── results.php                      # Results tables, PRIDICT2 scores, and visualizations
├── submit_design.php                # Async entry point: queues a job, returns a job id
├── process_pegrna.php               # Design orchestrator; also the CLI job worker
├── job_status.php                   # Poll endpoint for async job status
├── tutorial.php                     # User documentation
├── peg_core.py                      # Core design engine (PAM scanning, PBS/RT, primers, PE3/PE3b nicks)
├── propeg.py                        # CLI entry point for the design engine
├── globe-data.php                   # Visitor-globe data feed (needs umami_config.php)
├── umami_config.example.php         # Template for the analytics credentials
├── .htaccess                        # Apache hardening, HTTPS redirect, caching
├── robots.txt / sitemap.xml         # Search-engine directives
├── query/                           # Runtime job artifacts (auto-created, gitignored)
├── ext_tools/                       # Third-party ML integrations
│   ├── PRIDICT2/                    # PRIDICT2.0 deep-learning efficiency predictor
│   └── peglit/                      # pegLIT linker optimization (simulated annealing)
├── api/                             # Internal micro-service endpoints
│   └── generate_linker.php          # Linker computation API (calls the pegLIT server)
├── js/                              # Client-side logic and visualization bundles
│   ├── script.js                    # Main UI logic (async submit + poll)
│   ├── pegrna-visualization.min.js  # React 18 IIFE – pegRNA structure schematic
│   ├── gpegrna-visualization.min.js # React 18 IIFE – g-pegRNA structure schematic
│   └── gepegrna-visualization.min.js# React 18 IIFE – g-epegRNA / epegRNA structure schematic
├── visualization-react/             # React source for the visualization bundles
│   └── src/                         # JSX components compiled via Vite
├── css/                             # Responsive styling and animations
├── deploy/                          # Server deployment scripts and service files
└── img/                             # UI assets and institutional logos
```

### How a design runs

Long jobs never hold an HTTP connection open (any proxy/CDN would time them out):

1. The browser POSTs to `submit_design.php`, which queues the request and returns a **job id** immediately.
2. A **detached CLI worker** (`php process_pegrna.php <job_id>`) runs `propeg.py` and, when the edit has ≥99 bp flanks on both sides, PRIDICT2.0. A file-lock semaphore bounds concurrent heavy jobs.
3. The browser polls `job_status.php?job=<id>` every few seconds and loads `results.php?job=<id>` once the status is `done`.

## Setup & Deployment

### Server Requirements
- **Web Server**: Apache with PHP 8.0+ (mod_proxy_fcgi → PHP-FPM is the tested setup).
- **PHP CLI**: required — the async worker is spawned as `php process_pegrna.php <job_id>`. `setsid` (util-linux) is used to detach it on Linux.
- **Backend Environment**: Python 3.9+ for the ML tooling.
- **OS**: Linux (the tested deployment is CentOS Stream / Apache 2.4). Windows + XAMPP works for development.
- **HTTPS strongly recommended**: some networks transparently proxy plain HTTP (port 80) with a short read timeout, which can cut off the synchronous linker call. Serving over HTTPS avoids this entirely.

> The Python virtual environments are **not** committed — they are platform-specific and large. Build them from the `requirements.txt` files below. The PRIDICT2 **trained model weights (`trained_models/`) are included** in the repository, since they are not installable via pip.

### External Tools Initialization
PROpeg relies on two specialized Python tools under `ext_tools/`.

**1. pegLIT** (linker optimization)
```bash
cd ext_tools/peglit
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt          # includes gunicorn for production
```

**2. PRIDICT2.0** (efficiency prediction)
```bash
cd ext_tools/PRIDICT2
python3 -m venv venv
source venv/bin/activate

# Core dependencies
pip install tensorflow==2.13.1 biopython==1.81 joblib==1.3.1 matplotlib==3.7.2 \
            pandas==2.0.3 prettytable==3.8.0 primer3-py==2.3.0 scikit-learn==1.3.0 \
            scipy==1.11.1 seaborn==0.12.2 tqdm==4.65.0

# PyTorch — CPU build (use --index-url to force the CPU wheel; the CUDA wheel
# from PyPI is ~10x larger and unnecessary on a web server)
pip install torch==2.0.1 --index-url https://download.pytorch.org/whl/cpu
```
`requirements.txt` lists the same pinned set and can be used instead (`pip install -r requirements.txt`), but it relies on `--extra-index-url`, which does not *guarantee* pip picks the CPU wheel. The two-step command above is the verified install.

### Running the pegLIT linker server
The linker API is a separate Flask service that **must be running** — `api/generate_linker.php` POSTs to it on `127.0.0.1:5001`. If it is down, linker computation fails.

**Production (Linux, systemd + gunicorn):** edit `deploy/propeg-linker.service` to match your install path, then:
```bash
sudo cp deploy/propeg-linker.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now propeg-linker
sudo systemctl status propeg-linker        # expect: 1 master + N workers
```
Sizing: `--workers` ≈ CPU cores (each linker job pins one core for minutes) and `--timeout 600` is **mandatory** — gunicorn's 30 s default would kill the job.

**Development (Windows):** run `deploy/run_linker_server.bat` (visible console) or `deploy/run_linker_server_background.vbs` (hidden). These use the Flask dev server — gunicorn is Unix-only.

If you change the port, update **both** the server bind and `$flask_url` in `api/generate_linker.php`.

### Analytics (optional)
The footer visitor globe reads credentials from `umami_config.php`, which is gitignored:
```bash
cp umami_config.example.php umami_config.php   # then fill in real values
```
If you skip this, the globe simply fails to load; nothing else is affected.

### Permissions
The web server user (e.g. `www-data` / `apache`) needs read/write on:
- `query/` — **required**; async job artifacts live here (auto-created)
- `ext_tools/PRIDICT2/predictions/` — **required**; PRIDICT writes its CSV output here
- `ext_tools/PRIDICT2/log/`

PRIDICT2 (PyTorch) may also need a writable `$HOME`/cache when run as the web user; if predictions fail with cache-permission errors, set `HOME` to a writable path for the PHP-FPM process.

### Timeouts
The design flow is async and needs no special timeouts. The **linker call is still synchronous** and can run for minutes, so if you serve it over a path with a proxy, raise `Timeout` / `ProxyTimeout` (Apache), `request_terminate_timeout` (PHP-FPM) and any reverse-proxy read timeout to comfortably exceed the longest linker run.

## Support and Documentation

- **Tutorial**: Step-by-step guides for sequence input, parameter configuration, and result interpretation are available natively via `tutorial.php`.
- **Source Code**: [github.com/asifalivk007/propeg](https://github.com/asifalivk007/propeg)

## Copyright and Attribution

Copyright &copy; 2026 ICAR-Central Rice Research Institute (CRRI), Cuttack and ICAR-Indian Agricultural Statistics Research Institute (IASRI), New Delhi — see [copyright & attribution](LICENSE).

**Algorithmic basis:**
The pegRNA design procedure implements the approach described in Lin, Q., Jin, S., Zong, Y. *et al.* **High-efficiency prime editing with optimized extended pegRNAs in rice and wheat.** *Nature Biotechnology* **39**, 923–927 (2021). https://doi.org/10.1038/s41587-021-00868-w

**Third-Party Tooling** (retains its own original license):
- [pegLIT](ext_tools/peglit/LICENSE) (BSD 3-Clause) — *Broad Institute & Harvard* — Nelson et al., *Nat. Biotechnol.* **40**, 402–410 (2022)
- [PRIDICT2.0](ext_tools/PRIDICT2/LICENSE) (MIT) — *Krauthammer Lab & Schwank Lab* — Mathis et al., *Nat. Biotechnol.* (2024)
- [pegFinder](https://github.com/rdchow/pegfinder) (MIT) — *Chow et al., Nat. Biomed. Eng.* **5**, 190–194 (2021) — its PE3/PE3b nick-finding logic is ported into `peg_core.py`, where the upstream MIT notice is retained inline.

---

*PROpeg is developed for the scientific community to advance plant genome editing research. We encourage responsible use and adherence to appropriate biosafety guidelines.*