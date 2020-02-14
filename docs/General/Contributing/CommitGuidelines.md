---
currentMenu: contributing
---
# Commit Guidelines

This document covers best practice when committing your work. These conventions have come about as a result of considerable experience. Following these conventions makes life easier for everyone involved in the project.

## Introduction

A commit represents a complete and stable unit of work, but not necessarily an entire issue, feature or bug fix. How much, how often and how you present the commit is the subject of these guidelines.

## General

Let's use a book analogy. A book is separated into chapters. Those chapters are collections of paragraphs. Paragraphs are collections of sentences. There is a relation and purpose for each of these. Sentences are arranged logically to give meaning. Paragraphs generally explain one idea.

Put another way, each commit should be logically independent and conceptually small. Each commit should make a specific change which can be reviewed on its own and verified to do what it says it does.

## When to commit

Taking the above analogy, a commit is like a paragraph. In the same way a paragraph of text relates to one idea or concept, a commit should be similarly focused. Let's explore some of this in detail with specific examples to make the concepts clear:

Generally, in order to fix a specific issue we may need to perform various actions: fix some bugs, refactor some ugly code and reorder some code. Each of these procedures should probably be the subject of a separate commit. 

Special care should generally be made not to mix up refactoring with other operations. By definition, "to refactor" means to change code in such a way that it behaves in exactly the same way before and after refactoring. For this reason a refactor should be one commit.

If you mix other operations like bug fixing into the same commit as your refactoring, then the commit will represent two distinct changes. If problems arose, it would not be possible to determine if there was an error in the refactoring or the 'bug fix' introduced a new bug. This will ultimately cause pain to someone else later on.

Even if you believe your code is bug free, it must be subject to peer review. If the diffs are too difficult to read it makes review more difficult and less effective.

Updates to a vendor library should be performed with a single large commit, encapsulating all of the changes made to the library between versions.

File renaming or moving should generally be done as a separate commit. It is generally not a good practice to rename or move files and alter them in the same commit.

## Commit Messages

Commit messages are a very important part of version control history. Hence, when committing work to the repository, please write a commit notice that adequately describes the changes you made. The commit notice should include the ticket number the change relates to with the word "closes", "refs", "fixes" or "fixed" followed by the ticket number. e.g.

```plaintext
Changed template delimiters from old to new format refs #1234
```

The words "refs, closes, fixed, and fixes" will update the ticket in the tracker with the commit message and close the ticket. The word "refs" will update the ticket in the bug tracker". If you are making a partial commit, please use the word "refs". You may only use "closed, fixed, fixes" when you have completed all the work and can close the corrisponding tracker ticket.

You should not assume that reading the diff of the commit is enough, or that people will be reading the diff at all. Reports that list commits generally show only the first line, for example. The commit message, therefore, should consist of a one-line summary including any ticket references, then a blank line, and then a sentence or more summarising the changes, rationale and other details making your logic and reasoning clear:

```plaintext
Added interfaces to event notification system refs #1234

By moving away from a concrete class we can start making specific
events for specific systems and take advantage of OOP and also make
the concrete event classes IDE friendly.
```

So overall, commit messages must always be complete and detailed. It is not enough to reference a ticket, the commit message must speak for itself. Sometimes a short message is self explanatory enough:

```plaintext
Fixed syntax errors.
```

Sometimes a ticket reference is not required:

```plaintext
Removed use of method static variable caching.

There is actually no need for caching because this is outsourced
to the database caching layer, however since this is a refactoring
commit I've shifted this to class static to maintain the same
behaviour and for safety in case I missed something important.
```

Please note that a further reason for detailed commit messages is that commit messages are searchable in the local repository without needing to reference any ticket tracker or other online tools. When you write commit messages, try to think of someone 6 months in the future who is unfamiliar with the code but needs to understand why a change was made. This should give you the right mindset.

## Tickets / Pull Requests

The issue tracker is used primarily for project management purposes - it allows one to see clearly what is pending and work to be separated out and prioritised in milestones.

Issue tickets can be opened for bugs and RFCs (for discussion). Pull requests can also be opened for RFC purposes where you may present a rough outline of a feature for example to get feedback or peer review.

Pull requests and pull requests serve as a place for discussion on the relevant issues but they are not a replacement for detailed commit messages.

Commit messages must always contain full details of the commit, reasoning and logic so the commit may be understood without reference to a ticket. Commit messages track the changes of the particular commit, not the overall issue being addressed in the ticket.

As a rule of thumb, if you need to make a small change and it can be done immediately, simply committing it is enough. If it requires a lot of change, or is a bigger/complex issue that requires time to solve, then it must be tracked as a ticket - this prevents the issue being forgotten about and let anyone know what is pending. There is no point making tickets for small things like syntax errors, typos and other things which can just be fixed now. Issues like this really do not need tracking.

Overall, a certain degree of common sense is required. If changes do not seem to fit in the milestone, or there is any doubt in your mind, it should also be tracked as a ticket - this allows issues to be deferred or fixed and merged in a later milestone without being forgotten about.

Remember, just because you can fix an issue, does not mean it can be included in the current branch. Tickets help track these situations and make it clear what is coming in future milestones.

## Topic Branches

In general all work should be done in local topic branches. If you are collaborating with others on this, those topic branches should be published to _your_ repository forks and not the central repository (if you have access).

Please name your topic branches so they are clear to understand - generally with the ticket number, e.g. `ticket_1234` or `hooks_ui`.

When your work is ready to merge either create a pull request or if you have write access to the central repository, you may merge to the correct branch (usually master) and push into the central repository.

## Contributors

Everyone is a contributor, including collaborators. If you are a collaborator, you should treat your own contributions as if you received a pull request. That is to say, work in a topic branch and merge in the code. You don't need to push your branches to your fork, but you should merge your branches as if they were a pull request.

## Rebase

Because GIT allows you to rewrite history, it must be used with care. Do so in a meaningful way by using it to get your branch history into a meaningful state before it's merged into the main line. You may rebase as much as you like before pushing the branch.

After you push work you should not rebase your local branch again because it will be out of sync when you try to push again. Furthermore anyone who already pulled on your branch will have difficulty synchronising again.
