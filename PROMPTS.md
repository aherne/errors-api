# Prompt Templates

## Build Lucinda Tests

```text
Read `src`, compare against `tests_old` if present, and implement the generated empty tests in `tests` using `lucinda/unit-testing` validators only. Each test method must return a `Result` or `Result[]`. Add fixtures/mocks if needed under `tests/`, run `php test.php`, and fix failures or small blocking source issues.
```

## Review Existing Lucinda Tests

```text
Review the tests in `tests/` against the current `src/` API. Identify incorrect assumptions, direct boolean comparisons that should become validators, stale legacy behavior copied from `tests_old`, and missing coverage for current public methods.
```

## Migrate Legacy Tests

```text
Use `tests_old/` only as behavioral intent. Rewrite the generated tests in `tests/` so they match the current `src/` classes and use `Lucinda\UnitTest\Validator\...` assertions instead of direct `new Result($a == $b)` style checks.
```

## Rebuild README From Source

```text
Read the current `README.md`, then inspect the actual code in `src/` and the active tests/fixtures so the documentation matches the real API instead of historical behavior.

Before rewriting, suggest a better README structure for this repository type. Then rewrite `README.md` around responsibilities and integration flow, not around outdated class dumps.

Requirements:
- document only what exists now in `src/`
- prefer grouping by concepts such as package purpose, runtime flow, configuration, main contracts, responses, services, exceptions, and testing
- include valid examples only if they match the current implementation
- remove stale sections, stale classes, and stale method descriptions
- keep it practical for developers integrating the package
- if useful, mention constraints enforced by the code or tests

After editing, sanity-check the README against the source and run the relevant test command if available.
```
