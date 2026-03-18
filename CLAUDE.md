# CLAUDE.md — eip-pocs-and-cves

## What This Repo Is

Public collection of CVE proof-of-concept exploits and Docker lab environments, generated autonomously by [CVEForge](https://exploit-intel.com/blog/posts/cveforge-from-shannon-to-autonomous-poc/). Each entry is a fully self-contained package: vulnerable + patched Docker targets, standalone PoC scripts (Python 3, stdlib only), root cause analysis, and — when the fix looked narrow — a bypass attempt.

All entries are created by the CVEForge pipeline. Human/agent work in this repo is limited to:
- Publishing new CVEForge runs to the repo
- Fixing lab breakage (e.g. upstream image changes, broken Dockerfiles)
- Updating README tables and blog post lists
- Responding to responsible disclosure or public corrections

## Key Paths

| Path | Purpose |
|---|---|
| `CVE-YYYY-XXXXX/` | One directory per CVE — self-contained lab + PoC |
| `CVE-YYYY-XXXXX/README.md` | Full writeup: root cause, attack chain, fix analysis |
| `CVE-YYYY-XXXXX/poc/poc.py` | Standalone exploit — Python 3, no external deps |
| `CVE-YYYY-XXXXX/poc/control_test.py` | Control: proves auth is required without the bug |
| `CVE-YYYY-XXXXX/Dockerfile.vulnerable` | Target container (vulnerable version) |
| `CVE-YYYY-XXXXX/Dockerfile.patched` | Target container (patched version, when applicable) |
| `CVE-YYYY-XXXXX/docker-compose.yml` | Stands up the full lab with one command |
| `CVE-YYYY-XXXXX/intel_brief.md` | CVE intelligence pulled from EIP at forge time |
| `CVE-YYYY-XXXXX/vulnerability_analysis.md` | Root cause trace and fix assessment |
| `CVE-YYYY-XXXXX/poc_verification_report.md` | Test results: vulnerable vs patched |
| `CVE-YYYY-XXXXX/bypass_analysis.md` | Present when fix didn't hold |
| `README.md` | Root index — CVE table, quick start, how-to |

## Architecture

Each CVE directory is completely self-contained. There are no shared libraries or cross-CVE dependencies. The repo is a flat collection:

```
eip-pocs-and-cves/
├── README.md                    # Root index — CVE table, blog links, quick start
├── CVE-YYYY-XXXXX/
│   ├── README.md                # Writeup — root cause, attack chain, fix analysis
│   ├── poc/
│   │   ├── poc.py               # Standalone exploit — Python 3, stdlib only
│   │   └── control_test.py      # Proves auth required without exploit
│   ├── Dockerfile.vulnerable
│   ├── Dockerfile.patched       # When applicable
│   ├── docker-compose.yml
│   ├── intel_brief.md
│   ├── vulnerability_analysis.md
│   ├── poc_verification_report.md
│   └── bypass_analysis.md       # When fix didn't hold
└── CVE-YYYY-YYYYY/
    └── ...
```

CVEForge produces all files. The CODEX:RUNTIME_PORT block in each CVE README is auto-managed — do not edit it manually.

## Setup

No install step needed. Each lab is self-contained via Docker.

Requirements:
- Docker + Docker Compose
- Python 3 (for running PoC scripts)

```bash
cd CVE-YYYY-XXXXX

# Build and start the lab
docker compose build
docker compose up -d

# Run the exploit
python3 poc/poc.py <target> -p <port>

# Tear it down
docker compose down
```

Check the individual CVE README for target-specific ports, credentials, and expected output.

## Making Changes

All changes go through a branch + PR. Never push to main directly.

```bash
git checkout -b EXP-<issue-id>
# make changes
git add <files>
git commit -m "feat/fix: description (EXP-NNN)"
git push -u origin EXP-<issue-id>
# open PR → QAGuy reviews and merges
```

### Adding a New CVE Entry

CVEForge generates all files. To publish a forge run:
1. Copy the generated `CVE-YYYY-XXXXX/` directory into the repo root
2. Add an entry to the CVE table in `README.md` (maintain sort by CVSS descending within each score tier)
3. Update the summary line at the bottom of the table if bypass count changed
4. Add a blog post link to the Blog Posts section if applicable

### Updating the Root README Table

Keep entries sorted by CVSS score (descending). Format:
```markdown
| [CVE-YYYY-XXXXX](CVE-YYYY-XXXXX/) | Target | Vuln Class | **X.X** | No / **Yes** — brief bypass note |
```

## Deploy

This is a public GitHub repo. There is no build pipeline or server deployment. Publishing is just pushing to main via PR.

## Common Pitfalls

- **CODEX:RUNTIME_PORT blocks** — auto-generated markers in each CVE README. Do not edit them manually; they are overwritten by the forge pipeline on re-runs.
- **PoC scripts use stdlib only** — never add external dependencies to `poc/poc.py`. If a new CVE needs one, it's a forge-level decision.
- **Bypass entries** — the Bypass? column in README.md must match whether `bypass_analysis.md` exists in the directory. If you add a bypass manually, add the file too.
- **Image rot** — upstream Docker images change; Dockerfiles may break over time. When fixing, update the Dockerfile but do not alter the PoC logic or vulnerability mechanics.
