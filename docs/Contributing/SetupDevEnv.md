---
currentMenu: contributing
---
# Setting up a core development environment

This document is intended to be a place to collect useful techniques for developing the Zikula Core.

First, it is required that you set up the basic git repository.

## Setup

This is only done once for each project you wish to contribute to.

In this example we'll fork the _github.com/zikula/core_ repository. 'zikula' is the Github username, and 'core' is the repository name. We're using 'zikula/core' to refer to this repository. If you are contributing to another repository, say _github.com/foo/bar_ then simply replace the username and repository accordingly, e.g 'foo/bar'.

Simply fork the project repository <https://github.com/zikula/core> by pressing the `fork` button on the website.

Now setup the local repository:

```shell
git clone -o zikula git://github.com/zikula/core.git
```

Add your fork as a remote:

```shell
cd core
git remote add <your_username> git@github.com:/<your_username>/core.git
```

'core' points to the main Zikula repository.  The remote called 'your_username' points to your fork. Using this naming makes it easier for you to know which repository you are referring to. If you are already fluent with GIT you may choose any remote naming schema you prefer.

Keeping your code up to date with rebase:

```shell
git fetch
git rebase master zikula/master
git rebase 2.0 zikula/2.0
```

Your public fork's only purpose is to public your patches, there is absolutely no reason to push your local `master` branch (or other branches present in the central repository you have forked from).

We use rebase to prevent unnecessary merge commits when synchronising with the upstream repositories. It rewinds your work, updates from the central repository, then replays your work on top. This keeps a linear, logical history.

## Enable `debug` and set kernel environment to `dev`

If not already configured, it is recommend to set the kernel environment to `dev` and set `debug` to true. To do so, adjust the parameters in your `.env.local` file.

## Email sending

Emails are always difficult to handle during development, especially if you want to test a module sending lot's of them, i.e. a newsletter module. Of course you don't want to spam other peoples inboxes, and always use your own email address instead. But this leads to problems when your module only allows unique emails. To overcome this problem, follow the instructions in the [official Symfony documentation](https://symfony.com/doc/current/mailer.html#development-debugging) and either disable sending completely or redirect all emails to your development email address.
