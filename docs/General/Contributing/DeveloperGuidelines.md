---
currentMenu: contributing
---

# Developer Guidelines

This document aims at being a step by step guide for developers on how to commit code to the core including all the QA steps.

## Commitment

Being a Core developer is to be considered a privilege and a responsibility. Many people rely on the Core development team to maintain stability, progress and momentum of the Zikula Project. Core developers are expected to contribute regularly to the project. They are expected to check the tracker regularly and proactively find tasks to do. Developers should at least inform the project lead, or (preferable) the entire team when they wont be able to contribute regularly due to personal circumstances. This helps team co-ordination.

## Communications

All developers should at the very least subscribe to the commit notices from the [core repository](http://localhost:8000/images/fork.png) or at least watch the project. It is essential you keep your pulse on the commits to the repository and also the bug tracker. Please also check our [Slack channels](https://joinslack.ziku.la/) regularly.

## Tickets

All work must be done against tickets: All features, bugs and tasks must be entered into the tracker and assigned to a milestone before you may work on the issue. Tickets will be generally be assigned to milestones by the project lead, but in the case of bug tickets, developers may use their discretion. Feature Request and Task tickets should only be worked on after approval from the project lead (when assigned with ticket status "approved". Developers are expected to do their fair share of bug fixes along with feature requests/tasks.

If you are unable to complete the ticket tickets assigned to you or accepted by you, please inform the project lead or find another developer to take on the ticket. If you cannot find someone please 'orphan' the ticket.

Any tickets with "approved" status can be taken over by anyone who feels that they are capable of completing the work. Simply assign it to yourself, and begin working on it. As above, if you later find that you cannot complete the ticket, work with another developer to take it over, or (if you have not been able to work on it at all) please 'orphan' the ticket so someone else can take it on.

## Quality Assurance

The purpose of these conventions is to promote quality. Commit history is an essential part not only of peer code review, but also in the future for others who need to understand past coding decisions. Downstream users may also follow commit history in order to integrate changes.

Contributions that do not follow the conventions may give the project manager enough cause to entirely revert them. Similarly, collaborators might refuse to merge any topic branches that are not in an acceptable state.

## Diffs

Diffs are an important part of your own QA process. Once you feel ready your code can be commited to the central repository you should diff the entire commit first before you actually commit. This is how you can catch all manner of mistakes (including accidently commiting the wrong files, or accidently leaving in some debug code that you may have commented out. You should use a visual coloured diff tool if possible as they are much easier to read than a textonly diff.

## Commit notices

See our [Commit Guidelines](CommitGuidelines.md) for the details about this.

## Changelog & Upgrading

Whenever you complete a ticket you must record a summary of the complete change in the CHANGELOG file only for the milestone the ticket is assigned. Example: "Fixed css issues in installer under IE8 (#123)" whereby `#123` references the ticket number.

If your changes include some BC breaks which make upgrades necessary for site admins or extension developers please add these to the documentation. Usually there is some UPGRADING or refactoring document available for this. These summary documents are not meant as end user documentation, but something we can construct documentation from. There are two classes of entry: end-user, and extension-developer. It should contain specific things a user must do as a consequence of the changes you made.

If aimed at a user then something like: `"Delete the entire folder system/Profile"`.
If aimed at a module developer, `"Change all template occurrences of <!--[ and ]--> to { and }"`.

The `CHANGELOG` and `UPGRADING` docs can be found in the root folder or the `docs/` folder of the codebase.

## Merging

It is important to merge your work regularly to any other branches that require it. The general rule of thumb is to "merge up" unless there are specific instructions to do something different. This means if a ticket is assigned to a milestone, you should commit your work to corrisponding branch and all open branches above it.

## Unit Testing

It is essential that you write tests for anything new you write. If you are fixing code that already has tests you must also add test cases in general. You must generally never alter a released API's tests. If you believe the need occurs please bring up a discussion on the matter.

Before making a commit you are required to run the entire test suite. You may not commit if the test suite is broken (meaning test execution halts with a PHP error). It is suggested that you run the test suite at command line with `phpunit --coverage-html /path/to/html/report tests` to be 100% sure.

## Modification Of 3rd Party Libraries

Modification of 3rd party libraries is completely forbidden. While it's easy to include local patches to the library to add features that upstream doesn't have or fix bugs that upstream hasn't addressed, this has several negative effects.

When a security issue appears, it becomes harder to fix the application bundling the library. If you attempt to upgrade to a newer version, you have to make sure your important local modifications get ported to the new version. If you attempt to backport, you have to merge the upstream fix to your own code-base which may have conflicts with the local modifications.

When working with the library that comes from upstream, there is a community of people who are interested in that library to fall back on for help. When working on your own private copy that community may not be interested in helping you work on your modified sources since they don't have control or knowledge of what your modified sources do.

Forking dilutes one of the strengths of open-source development. Instead of a project getting stronger with more people supplying patches to help drive the project and build a bigger community, the community of people interested in it are splintering, developing more and more divergent code-bases, solving the same problem over and over in different ways in different private copies of the library. Instead of everyone benefiting, everyone has to pay.
