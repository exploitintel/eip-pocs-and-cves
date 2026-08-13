# Lab health audit

Builds every CVE lab in this repository from source, starts it, and checks that
it reports healthy. Used by `.github/workflows/lab-health.yml`, and runnable by
hand.

It publishes nothing and never modifies a lab.

## Run it

```bash
python3 .github/audit/audit.py --repo . --runs-dir /tmp/runs --run-id local
```

Useful flags:

| Flag | Purpose |
|---|---|
| `--only CVE-2026-58154` | Audit one lab (repeatable) |
| `--shards N --shard I` | Split the labs across parallel runners |
| `--skip-privileged` | Exclude labs needing privileged containers |
| `--build-workers N` | Parallel builds. Several labs run `make -j$(nproc)`, so a high value starves them into false timeouts |
| `--disk-floor-gb N` | Abort below this much free space |
| `--allow-dirty` | Audit uncommitted changes; the recorded SHA gains a `-dirty` suffix |

## What counts as healthy

Each lab is judged by the strongest signal it offers, and the record says which
was used:

- **T1** — every service declaring a healthcheck reaches `healthy`
- **T2** — no healthcheck, so every published port must accept a connection
- **T3** — no healthcheck and no ports, so containers must simply stay up

The timeout comes from each lab's own healthcheck declaration
(`start_period + interval × retries`) rather than a fixed value, because labs
legitimately differ: one asks for 900s.

## Outcomes that are not lab defects

- `FAIL_PREREQ` — the lab needs something the runner does not provide, such as
  the kind cluster `CVE-2026-44182` documents as a requirement
- `CRASH_EXPECTED` — the target aborts under a sanitizer on purpose, which is
  the demonstration; recorded only when the lab declares that intent *and* its
  output carries a sanitizer signature
- `INFRA_ERROR` — a registry or network problem, not the lab

## Scope

It answers "does the lab stand up", not "does the exploit still work". A lab can
come up perfectly with a dead PoC, and this will call it healthy.
