# Maintaining and releasing the codekj fork

This repository publishes `codekj/roach-core` with unprefixed `1.x` tags.
The older upstream `v3.x` tags and changelog entries are history, not the fork's
release numbering. Application deployments are separate operations.

## Local checks

The committed Composer development lockfile is tested on PHP 8.5. CI resolves
compatible dependency versions independently on PHP 8.2, 8.3, 8.4, and 8.5, using
both lowest and current allowed versions. PHPUnit 10 is permitted for PHP 8.2.
Use Node 22.12+ (CI uses Node 24).

All eight PHP/dependency combinations run the complete test suite and dependency
audits. Full-project Psalm runs with current dependencies on each PHP version.
Oldest permitted analyzer dependencies can fail during their own bootstrap on
newer PHP runtimes, so they are not used as the static-analysis toolchain. Runtime
compatibility coverage is retained in the lowest-version test jobs.

```bash
composer install --no-interaction --prefer-dist
npm ci
composer validate --strict
composer analyze
composer audit --locked --no-interaction
npm audit
bash .github/scripts/check-style.sh
bash .github/scripts/test.sh
git diff --check
```

`npm ci` installs the pinned Puppeteer package and its matching Chrome binaries.
Only that pinned package's install script is approved in `package.json`. With a
separately installed browser, set `PUPPETEER_EXECUTABLE_PATH` to its executable.
Chrome needs its normal Linux shared libraries and writable HOME/XDG directories.
The real-browser fixture disables its sandbox for container/CI execution; the
production middleware's sandbox defaults are unchanged.

The test script owns a local fixture server on port 8000, waits for readiness,
passes arguments through to PHPUnit, and stops the server on exit. Do not run it
alongside another process on that port. All tests, including the rendering test,
must pass for a release; an excluded or skipped test is not a pass.

The style check covers PHP source/tests changed since fork tag `1.1.0`. It fails
on drift without rewriting the branch or mass-formatting untouched upstream
history. Full-project Psalm remains required. Never suppress an advisory or an
analysis finding just to publish a release.

## Publication

1. Review the diff, verify the checks above, and obtain explicit publication
   authorization. Keep machine-local files, credentials, and IDE state out of
   commits. Use a conventional commit message.
2. Push the reviewed branch and inspect its GitHub checks. Merge/fast-forward the
   reviewed commit to the intended release branch only after required checks pass.
3. Confirm the new tag is unused, then dispatch **Release codekj/roach-core** on
   that exact branch/ref with an unprefixed stable tag, such as `1.1.1`:

   ```bash
   gh workflow run release.yml --repo citronkaste/roach-core --ref main -f tag=1.1.1
   ```

   Substitute the approved version. GitHub CLI must be authenticated; Git-over-SSH
   access alone does not provide API authentication.
4. The workflow reruns the test matrix/audits and style checks, then atomically
   creates the new tag at the verified workflow commit and publishes release
   notes starting from the previous fork tag. It never moves an existing tag.
   Inspect the completed workflow and the release target before reporting success.
5. Consumers can now update their Composer lockfile from the published tag. For
   Listory, update only `codekj/roach-core`, ensure `jakeasmith/http_build_url` is
   absent, then run its `make check` and `make audit` gates.

If tag creation succeeds but release creation fails, inspect the tag's commit and
successful checks before creating the missing GitHub release with `gh release
create --verify-tag`. Do not delete/move a public tag as a retry shortcut.
