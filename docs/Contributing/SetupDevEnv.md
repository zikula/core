---
currentMenu: contributing
---
# Setting up a core development environment

This document is intended to be a place to collect useful techniques for developing the Zikula Core. It is required that you have set up the basic git repository like described [here](Introduction.md#setup).

## Enable `debug` and set kernel environment to `dev`

If not already configured, it is recommend to set the kernel environment to `dev` and set `debug` to true. To do so, adjust the parameters in your `.env.local` file.

## Email sending

Emails are always difficult to handle during development, especially if you want to test a module sending lot's of them, i.e. a newsletter module. Of course you don't want to spam other peoples inboxes, and always use your own email address instead. But this leads to problems when your module only allows unique emails. To overcome this problem, follow the instructions in the [official Symfony documentation](https://symfony.com/doc/current/mailer.html#development-debugging) and either disable sending completely or redirect all emails to your development email address.
