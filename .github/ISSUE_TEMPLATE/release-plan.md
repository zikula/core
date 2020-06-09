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
  - [ ] If not update translation templates
     - [ ] first execute `php -dmemory_limit=2G bin/console translation:extract zikula en`
     - [ ] second execute `php -dmemory_limit=2G bin/console zikula:translation:keytovalue`
- [ ] Review whether there are changes required for install and/or upgrade docs
- [ ] Add release date to changelog (only for final release) and commit it

## Create tags and artifacts

- [ ] Create tag for core project
- [ ] Only for final release:
  - [ ] Execute `zsplit` to push the tag to all slave repositories
  - [ ] Wait a bit for packagist to update
  - [ ] Update version number in distribution's `composer.json` and update `composer.lock`
    - [ ] Commit triggers the distribution build (final artifacts)
    - [ ] Wait until the [build job](https://github.com/zikula/distribution/actions?query=workflow%3A%22Build+archives%22) is completed
- [ ] Ensure the created build artifacts work
  - [ ] Download and unpack the archives
  - [ ] Test CLI installer randomly
  - [ ] Test web installer randomly

## Create the release

- [ ] Start release process in core manager at <https://ziku.la>
- [ ] Core manager will do the following steps:
  - [ ] Creates QA ticket at core project (only for pre releases)
  - [ ] Creates tag for distribution project (only for final release)
  - [ ] Downloads artifacts from last build (from core for pre releases; from distribution for final release)
  - [ ] Creates core release (using the previously created tag)
    - [ ] Pushes build artifacts as assets to the core release
  - [ ] Only for final release:
    - [ ] Creates distribution release (using the previously created tag)
       - [ ] Pushes build artifacts as assets to the distribution release
    - [ ] Updates core version (this step is currently unused)
    - [ ] Closes core milestone (if exists)
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
