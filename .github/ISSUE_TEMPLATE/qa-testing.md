---
name: QA Testing
about: internal issue template used for testing releases
title: QA testing for release of ZKVERSION build #BUILDNUMBER
labels: QA
assignees: ''

---

**Replace ZKVERSION, BUILDNUMBER, BUILDURL and ANNOUNCEMENTTEXT by the correct values!**

Please test build [#BUILDNUMBER](BUILDURL) and decide if it should become the next official release. **Anyone may participate in the testing process.**

Testing guidelines can be found in [Release Testing Guideline](https://github.com/zikula/core/wiki/Release-Testing-Guidelines)

Major and Minor Feature Releases require three +1 votes to promote the build and a minimum testing period of three days testing before the build can pass.  Two "-1" votes (with reason) will cause us to fail the build. If this build fails, **votes cannot be transferred** to the new release candidate, **testing must resume from the beginning**.

Please **do not** report bugs in this ticket, only register your approval or disapproval. You must give a reason and reference if appropriate (e.g. link to a ticket) for negative votes.

**Please report issues in a separate ticket.**

### Notes, References, and/or Special Instructions

Do not vote negatively if you find non-release blocking bugs. Minor and major bugs may be scheduled in a future version.

### Announcement

> ANNOUNCEMENTTEXT
