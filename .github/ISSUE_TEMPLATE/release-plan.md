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
- [ ] Ensure all dependencies and `composer.lock` are up to date (execute `composer update`)
- [ ] Ensure all translation templates are up to date (execute `php -dmemory_limit=2G bin/console translation:extract zikula en`)
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
  - [ ] Create release
  - [ ] Copy assets (note: it now downloads assets from the chosen GitHub Actions build, since Jenkins has been removed)
  - [ ] Update core version (this step is currently unused)
  - [ ] Close milestone
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
