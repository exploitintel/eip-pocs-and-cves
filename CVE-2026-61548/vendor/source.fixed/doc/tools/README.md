This directory contains tools primarily of use for maintainers
or people who want to build the doc (on a schedule).

Useful checks:

- `./doc/tools/check-html-links.py`
  Deterministically checks generated HTML in `doc/build` for invalid local
  links. Returns non-zero on failures. Optional flags:
  - `--check-anchors` to validate `#fragment` targets.
  - `--check-root-absolute` to validate links beginning with `/`.
