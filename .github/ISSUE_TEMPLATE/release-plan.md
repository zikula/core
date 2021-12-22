---
name: Release Plan
about: internal issue template used for preparing releases
title: Release Zikula ZKVERSION
labels: 
assignees: 'Guite, craigh'

---

## Prerequisites

- [ ] Check if there are no open issues labeled as `blocker`
- [ ] Check if there are no open issues which are not labeled as `blocker` but seem critical
- [ ] Ensure all VA modules and own components have been released to ensure all changes are correctly pulled.
     - [ ] ensure core and distribution composer.json files are set to appropriate version strings for VA (not `dev-main`)
- [ ] Ensure all dependencies and `composer.lock` are up to date (execute `composer update`)
- [ ] Ensure all translation templates are up to date (see `Check translations` workflow)
  - [ ] If not update translation templates:
     - [ ] first execute `php -dmemory_limit=2G bin/console translation:extract zikula en`
     - [ ] second execute `php -dmemory_limit=2G bin/console zikula:translation:keytovalue`
- [ ] Review whether there are changes required for install and/or upgrade docs
- [ ] Update version in Kernel class and `/system` extensions: `bin/console rt:up 3.0 3.0.2-dev 3.0.2` (creates a pull request)
- [ ] Update dependency versions for all `/system` and `/Zikula` subpackages to version of current release
     - `bin/console rt:set-deps 3.1 dev-3.1 3.1.0` (`dev-3.1` is current value, `3.1.0` is desired value)
- [ ] Create/update vendor changelog
  - [ ] Ensure the `CHANGELOG-VENDORS-<branch>.md` file contains a `###PLACEHOLDER_FOR_VENDOR_UPDATES###` line below the `- Vendor updates:` line
  - [ ] Run `bin/console rt:vendor 3.0.1 3.0` whereby `3.0.1` is the last release tag and `3.0` the desired target branch (creates a pull request).
- [ ] Add release date to changelog (only for final release)

## Create tags and artifacts

- [ ] Only for final release:
  - [ ] Split the monorepo using `bin/console rt:split 3.0 --tag=3.0.6` to push the release tag to all component repositories.
  - [ ] Wait a bit for packagist to update
  - [ ] Update distribution
    - [ ] Update dependency versions in distribution's `composer.json` (e.g. `dev-main` -> `^3.1` or `^3.1` -> `3.2`)
    - [ ] Run `symfony composer update` to update `composer.lock` (take care for using the correct branch)
    - [ ] Commit the changes (this triggers the distribution build for creating the final artifacts)
    - [ ] Wait until the [build job](https://github.com/zikula/distribution/actions?query=workflow%3A%22Build+archives%22) is completed
    - [ ] Create tag for distribution project (same as for the core!)
- [ ] Ensure the created build artifacts work
  - [ ] Download and unpack the archives (from core for pre releases; from distribution for final release)
  - [ ] Test CLI installer randomly
  - [ ] Test web installer randomly

## Create the release

- [ ] Start release process in core manager at <https://ziku.la>
- [ ] Core manager will do the following steps:
  - [ ] Creates QA ticket at core project (only for pre releases)
  - [ ] Downloads artifacts from last build (from core for pre releases; from distribution for final release)
  - [ ] Creates core release (using the previously created tag)
    - [ ] Pushes build artifacts as assets to the core release
  - [ ] Only for final release:
    - [ ] Creates distribution release (using the previously created tag)
       - [ ] Pushes build artifacts as assets to the distribution release
    - [ ] Closes core milestone (if exists)
- [ ] Review the release page at GitHub
- [ ] Review release assets, try to download and unpack them

## Spread the word

- [ ] Provide news article at <https://ziku.la>
  - [ ] Let core manager load announcement from GitHub
  - [ ] Publish it
  - [ ] Repair the German news translation (minor core manager bug: the `mainText` field of the German translation is currently filled with the English content); simply correct it manually until this issue is fixed.
- [ ] Update supported releases in CMM frontend releases list page
- [ ] Verify the article is automatically forwarded
  - [ ] To Slack (`#general` and `#german` channels)
  - [ ] To Facebook
  - [ ] To Twitter
- [ ] Add new version to [Wikipedia](https://de.wikipedia.org/wiki/Zikula) (only for final release)

## Start next iteration

- [ ] Changes in major version branch (e.g. `3.0`)
  - [ ] increment version in Kernel class and `/system` extensions: `bin/console rt:up 3.0 3.0.2 3.0.3-dev` (creates a pull request)
  - [ ] increment version in VA extensions
  - [ ] Add new section to both changelogs (normal + vendor)
  - [ ] Reset zikula dependencies to `dev-x.y`
    - `bin/console rt:set-deps main 3.1.0 dev-3.1` (`3.1.0` is current value, `dev-3.1` is desired value)
- [ ] Merge to `main` branch
