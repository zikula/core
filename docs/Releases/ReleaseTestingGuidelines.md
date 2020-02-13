---
currentMenu: releases
---
# Release Testing Guidelines

This document is designed to help those wishing to participate in testing of releases. There are no hard and fast rules, but rather this document attempts to give suggestions of the kind of things to be tested. Ultimately, good quality testing comes best when there are multiple testers all testing in a variety of different ways.

By making the guidelines less rigid, more people will be able to test according to their free time.

## Packaging

Packages (what is made available for download) are packaged automatically, but we should not assume they are correct because many issues can affect the successful assembly of the packages.

- Test if all the archives unpack.
- Check the MD5 and SHA1 hashes against those published.
- Check the contents of the packages is as expected.  This is especially important for patch files.

## New Installation

When testing new installation, simply follow the instructions of how to make a new installation and see if it works as expected.  You can also things like form validation (put in invalid or wrong data into the forms) and see if errors are handled correctly or not.

## Upgrade

When testing upgrade you should follow the instructions on how to upgrade from one version to the next and see if the upgrade behaves as expected.

## General Use Testing

In this form of testing you should just use Zikula as you would normally to build a site and during your normal use of the product, see if you encounter any errors.

## Ongoing Testing

The Zikula Project releases regular preview releases, and beta releases.  We also generate build every time there is a push to the central repository.  You are encouraged to test preview and beta releases because it greatly speeds the time required to reach release candidate phase and improves the speed at which we can make a final release.

The best way to test during this phase is to to use the product in your development environment.
