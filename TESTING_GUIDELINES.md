# Lucinda Unit Testing Workflow

Use this guide when implementing tests in repositories that use `lucinda/unit-testing`.

## Goal

Fill generated test skeletons in `tests/` for the current `src/` API using:

- `Lucinda\UnitTest\Result`
- `Lucinda\UnitTest\Validator\...` assertions

Each public test method must return either:

- one `Result`
- an array of `Result`

Do not return booleans, strings, or ad-hoc payloads.

## Expected Workflow

1. Read the current `src/` classes first.
2. Read `tests_old/` if it exists.
3. Treat `tests_old/` as intent only, not as source of truth.
4. Update tests to match the current API in `src/`.
5. Prefer validator assertions over direct comparisons like `==`, `===`, or manual `new Result(...)`.
6. Add small fixtures, stubs, or helpers under `tests/` when needed.
7. Run `php test.php`.
8. Fix test failures and source-level blockers that prevent the suite from running.

## Assertion Style

Prefer these validators:

- `Validator\Strings`
- `Validator\Integers`
- `Validator\Floats`
- `Validator\Booleans`
- `Validator\Arrays`
- `Validator\Objects`
- `Validator\Files`

Examples:

```php
return (new \Lucinda\UnitTest\Validator\Strings($value))->assertEquals("expected");
```

```php
return [
    (new \Lucinda\UnitTest\Validator\Arrays($items))->assertNotEmpty(),
    (new \Lucinda\UnitTest\Validator\Arrays($items))->assertContainsKey("id")
];
```

Avoid:

```php
return new Result($value == "expected");
```

## Test Design Rules

- One test method may return multiple `Result` objects when validating multiple expectations.
- Keep setup local to the test unless reuse clearly helps.
- Use fixtures for XML/filesystem-heavy behavior.
- Use support classes under `tests/Support` for stubs, fake controllers, fake listeners, fake resolvers, and fake facets.
- For exit-producing code, run it in a subprocess instead of invoking it inline when needed.
- For private state that cannot be asserted otherwise, reflection is acceptable, but prefer observable behavior first.

## Source of Truth

When `tests_old/` conflicts with `src/`, trust `src/`.

Typical migration situations:

- constructor signatures changed
- classes were renamed or moved
- return types became stricter
- XML structure changed
- old tests used shortcuts that should now become validator-based assertions

## Allowed Small Fixes

While implementing tests, it is acceptable to make small source fixes if they are blocking the suite, for example:

- parse errors
- invalid typed properties
- obvious incorrect variable usage
- obvious child-tag parsing bugs

Keep such fixes minimal and directly tied to making the code testable/runnable.

## Reusable Prompt

Use this prompt for future repositories:

```text
Read `src`, compare against `tests_old` if present, and implement the generated empty tests in `tests` using `lucinda/unit-testing` validators only. Each test method must return a `Result` or `Result[]`. Add fixtures/mocks if needed, run `php test.php`, and fix failures or small blocking source issues.
```

## Repo Conventions Used Here

- Fixtures live in `tests/fixtures`
- Support doubles live in `tests/Support`
- Generated tests in `tests/` should be filled, not replaced with another framework
- `php test.php` is the final verification command
