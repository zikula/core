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
- [ ] Ensure all dependencies and `composer.lock` are up to date (execute `composer update`)
- [ ] Ensure all translation templates are up to date (see `Check translations` workflow)
  - [ ] first execute `php -dmemory_limit=2G bin/console translation:extract zikula en`
  - [ ] second execute `php -dmemory_limit=2G bin/console zikula:translation:keytovalue`
- [ ] Review whether there are changes required for install and/or upgrade docs

## Preparation

- [ ] Ensure there exists a milestone named like the version to be released (required in a later stage)
- [ ] Add release date to changelog and commit it
- [ ] Wait until the [build job](https://github.com/zikula/core/actions?query=workflow%3A%22Build+archives%22) is completed
- [ ] Ensure the created build artifacts works
  - [ ] Download and unpack the archives
  - [ ] Test CLI installer randomly
  - [ ] Test web installer randomly

## Create the release

- [ ] Start release process in core manager at <https://ziku.la>
  - [ ] Create QA ticket
  - [ ] Create tag for core project
  - [ ] Create tag for all slave repositories
- [ ] Wait a bit for subtree split to complete and packagist to update (12+ hours?)
- [ ] Update version number in distribution's `composer.json` and update `composer.lock`
  - [ ] Build distribution (final artifacts) **GitHub action workflow for this has been added**
- [ ] Continue with release process in core manager
  - [ ] Create tag for distribution project
  - [ ] Let core manager download the distribution artifacts
    - it currently downloads assets from the chosen GitHub Actions build from core project **to be changed**
  - [ ] Create core release (using the previously created tag)
    - [ ] Push the distribution build artifacts as assets to the core release
  - [ ] Create distribution release (using the previously created tag)
    - [ ] Push the distribution build artifacts as assets to the core release
  - [ ] Update core version (this step is currently unused)
  - [ ] Close core milestone
- [ ] Review the release page at GitHub
- [ ] Review release assets, try to download and unpack them

## Spread the word

- [ ] Provide news article at <https://ziku.la>
  - [ ] Let core manager load announcement from GitHub
  - [ ] Publish it
  - [ ] Repair the German news translation (minor core manager bug: the `mainText` field of the German translation is currently filled with the English content); simply correct it manually until this issue is fixed.
- [ ] Verify the article is automatically forwarded
  - [ ] To Slack (`#general` and `#german` channels)
  - [ ] To Facebook
  - [ ] To Twitter
- [ ] Add new version to [Wikipedia](https://de.wikipedia.org/wiki/Zikula)

## Start next iteration

- [ ] Changes in major version branch (e.g. `2.0`)
  - [ ] Increment version in Kernel class
  - [ ] Add new section to changelog
- [ ] Merge to `master` branch
