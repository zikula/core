---
currentMenu: contributing
---
# Contribution workflows

## How to do patches

To contribute a patch simply `git fetch` to synchronise the remotes. Note `git fetch` does not update any checked out repositories, it just sychronises the upstream repos locally as `<remote_name>/<branch_name>`.

Make a branch from the upstream remote you want to update. In the example below we'll submit a bug-fix to the `2.0` branch:

```shell
git branch ticket_1234 zikula/2.0
git checkout ticket_1234
```

Or as a shortcut:

```shell
git checkout -b ticket_1234 zikula/2.0
```

Make any changes you need and commit as often as you need:

```shell
git commit <file-list>
```

or:

```shell
git commit -a
```

When you are ready please push your branch to _your_ GitHub repository:

```shell
git push <your_username> <branchname>
```

for example:

```shell
git push bob ticket_1234
```

If the topic branch you are working on takes a long time and in the meantime others have committed changes which you might need to rely on (and only if), you can periodically rebase your local topic branch checkout like this:

```shell
git fetch
git checkout ticket_1234
git rebase zikula/2.0
```

## Submitting a Pull Request

When you are ready to submit the branch to the main project simply visit your fork URL, e.g. <https://github.com/bob/core>, select the branch you want and then press the `PULL REQUEST` button. **MAKE VERY SURE** you tell Github the correct branch you intend your patch for. Typically, you will want to merge to the _lowest_ possible branch where the bug may exist. This fix will then be "merged up" into higher branches.

Above the PULL REQUEST dialog you will see something like:

```shell
**You are asking core to pull 3 commits into core:2.0 from bob:ticket_1234**
```

Github does not/cannot detect which branch you intend your patch for, so if this is wrong, press the `[change commits]` button then on the left where is says `Base branch · tag · commit`, you want to select the target branch for your patch.

The collaborator who reviews your contribution may ask you some questions or ask you to modify some stuff before completing the merge. You can simply continue to push your branch to your fork and it will be tracked in the same `PULL REQUEST` at Github.

When your branch has been merged, you can remove it from your public fork with `git push your_repo :ticket_1234` and of course you can delete it locally with `git branch -d ticket_1234` (note you need to checkout another branch before GIT will allow you to delete the branch).

## Pull Request Template

The title field may include a bracketed component name:

```shell
[component name] <description>
```

For example:

```shell
[HookDispatcher] Added fluid interface to dispatcher method
```

If the PR is not ready to be merged, you may prefix with `[WIP]`. This allows feedback to come in while perfecting the PR and let's the maintainers know the PR is not ready for merge. If the PR is simply in RFC stage, prefix with `[RFC]`:

```shell
[WIP][AdminModule] Added js drag and drop to menus
```

All pull requests must have the following template in the description dialog box:

```markdown
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

```markdown
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

If you want to contribute a feature, you can open a ticket or start a proof of concept PR. Before doing this you might want to discuss the feature in our Slack channels before to refine the idea.

Once the feature has been submitted to the tracker it will be assigned to a milestone by the project lead and you can begin work on the ticket as above.

New Features will not be included in "point" releases (e.g. 2.0.2). Features are only included in minor (or major) releases (e.g. 2.1.0).

## Bugs Fix Workflow

Either submit a ticket or choose a ticket you wish to contribute to. Fix the bug in a topic branch and make a pull request.

Sometimes bug fixes are trivial enough to not require a ticket, but a detailed commit message is enough.
