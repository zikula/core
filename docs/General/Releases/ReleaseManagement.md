---
currentMenu: roadmap-releases
---
# Release Management

This document describes the release management process which describes the manual work-flow that is involved in promoting a build to various release stages, including final release. The purpose of a more formal release process is not only to provide high quality releases, but to allow for a more frequent release cycle.

## Continuous Integration

Builds are created automatically as part of continuous integration. Various time consuming tasks are automated including running various tests and gathering metrics which are stored and graphed. The continuous integration will ultimately package the final release packages.

## Preview Releases (PR)

Preview release are designed keep the wider community involved and connected to development process ensuring there is a feedback loop from end users to core development. Preview releases may be announced at any time and may also be released prior to a release candidate.  This process is essential to find the more obvious but non-blocking bugs, short-comings and general feedback about UI and so on. Previews give no sense of being finished or being bug free, their purpose is solely to gain wider testing and feedback and ultimately increase issue tracker reporting by involving the wider community.

## Release Candidates (RC)

**Major and Minor Releases require a Release Candidate phase.** Release candidates are builds that enter into the "release workflow": if they pass the QA process, they will become a "General Release" with no extra "packaging". The build will be chosen by the release manager and will enter a round of QA. 3 people must sign off of the QA. Testing must focus especially on installation and upgrade and verify the package archives and patch files. If a release blocker is found, the build will be failed by the release manager. Minor and even major bugs will mostly be placed in future milestones. Two "-1" votes on a major issue should cause the build to fail, however the release manager may take the issue to the developers for arbitration. If there is agreement, the release manager can override the vote-down. Votes cannot be carried from one RC to the next. If an RC fails, the testing must start fresh with the next RC version. The release manager may disqualify votes or fail a build at their discretion.

Please understand that no build will ever be perfect and there will always be bugs. It is normal to find more bugs during QA but the question is if those issues really warrant failing the build or not. Release QA is specifically looking for 'blocking issues'. Failing a build is a costly process because it resets the entire QA which will have to be repeated again from scratch on the next QA build. Votes may not be carried over from one build to the next. Enough time should be given to allow adequate testing. Even if the required number of yes votes is reach quickly, some time should be allocated to allow for dissent. When a build fails, the release process stops until another release candidate is announced based on a future build from the continuous integration server - this entire process will then repeat until a build is successfully promoted.

_**Modified (time-based) QA Cycle**_

When appropriate, the release manager may deem a QA Cycle to be time-based instead of vote-based as outlined above. In this case, the release manager will identify a release date and negative votes may prevent this release. If the negative votes do not prevent the release, the RC will be released on the date specified, even without positive votes to that end.

## General Release

General releases are builds that have passed the release QA process and have become a general release.  A general release will then enter the distribution phase. A General Release MUST be the exact same build as the approved QA build.

## Bug Fix and Security Releases

Bug fix releases (e.g. 2.0.1) or security releases do not require a QA phase as outlined above and may be released at any time by the release manager at their discretion.

## Testers

Anyone may participate in testing at any phase of the development and release cycle.  Anyone may vote in the release candidate phase.

## Guidelines

There are no specific rules that must be followed in testing. For "Preview Releases" general testing through using the software is recommended. During "Release Candidate" testing, special attention must be placed on thoroughly testing installation and upgrade as well as looking at the integrity and content of the archives. Just because the builds are packaged automatically, doesn't mean it will always be perfect.
