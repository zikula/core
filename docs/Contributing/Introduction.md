---
currentMenu: contributing
---
# Introduction

Everyone is welcome to contribute code to the project. This document explain our contribution workflow and guidelines which help us manage the project smoothly and maintain standards.

## Prerequisites

All contributors need to have an account at [GitHub](https://github.com) and to sign the [CLA agreement](https://www.clahub.com/agreements/zikula/core) (registration required).

## Setup

This is only done once for each project you wish to contribute to.

In this example we'll fork the _github.com/zikula/core_ repository. 'zikula' is the Github username, and 'core' is the repository name. We're using 'zikula/core' to refer to this repository. If you are contributing to another repository, say _github.com/foo/bar_ then simply replace the username and repository accordingly, e.g 'foo/bar'.

Simply fork the project repository <https://github.com/zikula/core> by pressing the `fork` button on the website.

Now setup the local repository:

```
git clone -o zikula git://github.com/zikula/core.git
```

Add your fork as a remote:

```
cd core
git remote add <your_username> git@github.com:/<your_username>/core.git
```

'core' points to the main Zikula repository.  The remote called 'your_username' points to your fork. Using this naming makes it easier for you to know which repository you are referring to. If you are already fluent with GIT you may choose any remote naming schema you prefer.

Keeping your code up to date with rebase:

```
git fetch
git rebase master zikula/master
git rebase 2.0 zikula/2.0
```

Your public fork's only purpose is to public your patches, there is absolutely no reason to push your local `master` branch (or other branches present in the central repository you have forked from).

We use rebase to prevent unnecessary merge commits when synchronising with the upstream repositories. It rewinds your work, updates from the central repository, then replays your work on top. This keeps a linear, logical history.

## Patches

To contribute a patch simply `git fetch` to synchronise the the remotes. Note `git fetch` does not update any checked out repositories, it just sychronises the upstream repos locally as `<remote_name>/<branch_name>`.

Make a branch from the upstream remote you want to update. In the example below we'll submit a bug-fix to the `2.0` branch:

```
git branch ticket_1234 zikula/2.0
git checkout ticket_1234
```

Or as a shortcut:

```
git checkout -b ticket_1234 zikula/2.0
```

Make any changes you need and commit as often as you need:

```
git commit <file-list>
```

or:

```
git commit -a
```

When you are ready please push your branch to _your_ GitHub repository:

```
git push <your_username> <branchname>
```

for example:

```
git push bob ticket_1234
```

If the topic branch you are working on takes a long time and in the meantime others have committed changes which you might need to rely on (and only if), you can periodically rebase your local topic branch checkout like this:

```
git fetch
git checkout ticket_1234
git rebase zikula/2.0
```

## Contribution Workflow

When you are ready to submit the branch to the main project simply visit your fork URL, e.g. <https://github.com/bob/core>, select the branch you want and then press the `PULL REQUEST` button. **MAKE VERY SURE** you tell Github the correct branch you intend your patch for. Typically, you will want to merge to the _lowest_ possible branch where the bug may exist. This fix will then be "merged up" into higher branches.

Above the PULL REQUEST dialog you will see something like:

```
**You're asking core to pull 3 commits into core:2.0 from bob:ticket_1234**
```

Github does not/cannot detect which branch you intend your patch for, so if this is wrong, press the `[change commits]` button then on the left where is says `Base branch · tag · commit`, you want to select the target branch for your patch.

The collaborator who reviews your contribution may ask you some questions or ask you to modify some stuff before completing the merge. You can simply continue to push your branch to your fork and it will be tracked in the same `PULL REQUEST` at Github.

When your branch has been merged, you can remove it from your public fork with `git push your_repo :ticket_1234` and of course you can delete it locally with `git branch -d ticket_1234` (note you need to checkout another branch before GIT will allow you to delete the branch).

## Pull Request Template

The title field may include a bracketed component name:

```
[component name] <description>
```

For example:

```
[HookDispatcher] Added fluid interface to dispatcher method
```

If the PR is not ready to be merged, you may prefix with `[WIP]`. This allows feedback to come in while perfecting the PR and let's the maintainers know the PR is not ready for merge. If the PR is simply in RFC stage, prefix with `[RFC]`:

```
[WIP][AdminModule] Added js drag and drop to menus
```

All pull requests must have the following template in the description dialog box:

```
| Q                 | A
| ----------------- | ---
| Bug fix?          | [yes/no]
| New feature?      | [yes/no]
| BC breaks?        | no
| Deprecations?     | no
| Fixed tickets     | -
| Refs tickets      | -
| License           | MIT
| Changelog updated | [yes/no]

## Description
A few sentences describing the overall goals of the pull request's commits.

## Todos
- [ ] Tests
- [ ] Documentation
- [ ] Changelog
```

For example:

```
| Q                 | A
| ----------------- | ---
| Bug fix?          | yes
| New feature?      | no
| BC breaks?        | no
| Deprecations?     | no
| Fixed tickets     | #1234
| Refs tickets      | -
| License           | MIT
| Changelog updated | yes

## Description
This PR corrects issue #1234 by improving the logic of the loop.

## Todos
- [x] Tests
- [x] Documentation
- [x] Changelog
```

Below this, you may add any extra information and description text required. Please be as descriptive and informative as possible.

## Feature Request Workflow

If you want to contribute a feature, you can open a ticket or start a proof of concept PR. Before doing this you might want to discuss the feature on the mailing list or other medium to refine the idea.

Once the feature has been submitted to the tracker it will be assigned to a milestone by the project lead and you can begin work on the ticket as above.

New Features will not be included in "point" releases (e.g. 2.0.2). Features are only included in minor (or major) releases (e.g. 2.1.0).

## Bugs Fix Workflow

Either submit a ticket or choose a ticket you wish to contribute to. Fix the bug in a topic branch and make a pull request.

Sometimes bug fixes are trivial enough to not require a ticket, but a detailed commit message is enough.

## Guidelines

Please follow these guidelines when contributing

- [Coding Standards](wiki/Coding-Standards)
- [Commit Guidelines](wiki/Commit-Guidelines)
  
