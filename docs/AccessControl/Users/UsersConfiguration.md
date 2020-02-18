---
currentMenu: users
---
# Users configuration

This document helps to configure the Users module, allowing the site administrator
to control how the extension handles various aspects of its functions and features.

## General settings

These settings affect the overall behavior of the Users module, or functions of
the module that do not fall into a specific category.

### Name displayed for anonymous user

Default: Guest

An 'anonymous' user is a web site visitor that has not logged in. This setting
establishes the display name used when a user name is requested for an anonymous user.

### Number of items displayed per page

Default: 25

This setting controls the maximum number of records or items in a list displayed
on a page. If more than this number of records or items are available to be
displayed, then a "pager" will appear at the bottom of the list allowing the
user to navigate through the list.

## Account page settings

When a user who is logged in clicks on the "My Account" menu option, the "user
account page" is displayed. This page contains links (typically represented by
a series of icons) to user-specific functions made available by modules installed
in the system. These settings control the behavior of that page.

### Display graphics on user's account page

Default: Yes

The user account page can consist of a series of icons with text linking to
user-specific functions, or of a series of text-only links to those functions.
This setting controls which of those options is in effect.

## Registration settings

These options control how a new user requests a new user account on the site.

### Allow new user account registrations

Default: Yes

Controls whether new users can request new user accounts at all on the system.

For a private or semi-private web site, this option can be set to 'No,' and the
site administrator(s) can create new user accounts through the Users module
administration page.

This option can also be set to 'No' to temporarily disable new user registration
for any reason the site's administrator or owner determines.

### Statement displayed if registration disabled

**Note:** If JavaScript is enabled on your browser, then this option is only
displayed if the 'Allow new user account registrations' option is set to 'No.'
This option is ignored if 'Allow new user account registrations' is set to 'Yes.'

Default: *Sorry! New user registration is currently disabled.*

When new user registration is disabled, a message is displayed to any user who
attempts to access the registration process. The text of this option is the
message that is displayed.

### E-mail address to notify of registrations

Default: _blank, no notification e-mail is sent_

If the administrator wishes to be notified of each new request for a new user
account, then an e-mail address can be entered here. Every time a new user
registration request is made an e-mail is sent to the address.

_Tip_: To send an e-mail message to more than one person, set the address in this
option to a group alias or mailing list managed by the e-mail server.

### User registration is moderated

Default: No

Controls whether new user accounts must be approved by a site administrator prior
to becoming full user account records.

If this option is set to 'Yes,' then registration requests will appear on the
"Pending registrations" list available on the Users module administration page.
Site administrators may approve (or deny) pending registration requests from
that list.

If e-mail addresses must be verified during registration, and the user has not
yet verified his e-mail address, then the registration request will remain on
the pending list. If the user has verified his e-mail address, or e-mail
verification is not required at all, then the user registration request is
converted to a full user account record immediately upon approval.

### Newly registered users are logged in automatically

**Note:** If JavaScript is enabled on your browser, then this option is only
displayed if the 'User registration is moderated' option is set to 'No.'
This option is ignored if the 'User registration is moderated' option
is set to 'Yes.'_

Default: Newly registered users are logged in automatically.

If neither e-mail address verification nor moderation are required for
registration, then a registration request results in a full user account.
Because the user has a full user account and has just created a password (or
has authenticated through some third-party authentication module), then asking
the user to log into the site at the end of the registration process might seem
redundant.

If set to 'Newly registered users are logged in automatically,' then the user
is automatically sent into the log-in process at the completion of the
registration process. The credentials supplied during the registration process
are passed to the log-in process as if the user had typed them into the normal
log-in page, and the user proceeds through the full log-in process complete with
all events that might be generated by that process.

### Reserved user names

Default: *root, webmaster, admin, administrator, nobody, anonymous, username*

This option contains a comma-separated list of user names that cannot be chosen
by new users at the time of registration.

### Banned user agents

Default: _blank, no banned agents_

To provide additional protection against registration by automated sources of
spam, "user agents" or browser identification strings can be banned from the
registration process. Each browser identifies itself to the web server it is
visiting with a user agent identification string.

To ban a particular user agent enter enough of the identification string to be
unique, starting from the beginning of the string. To ban more than one agent,
separate each string fragment with a comma. The strings entered in this option
are matched to the beginning part of the user agent identification string
reported by the visiting browser. If the beginning part of the identification
string matches one in this list then it is prevented from entering the
registration process.

**Note:** It is very easy to spoof (report a fake) user agent string, making
a bot or other undesirable browser seem to the server as if it were one that was
desirable. This option is less effective in preventing undesirable registrants
than it has been in the past.

### Banned e-mail address domains

Default: _blank, no banned domains_

Some site administrators or owners might want to prevent users from registering
with e-mail addresses from specific domains. The domain portion of an e-mail
address is that which follows (and does not include) the '@' symbol. Enter banned
e-mail address domains in this option, separating each one by a comma.

**Note:** In the past it was common to attempt to prevent spam by banning users
with so-called "free" e-mail accounts from registering with web sites. More
recently, these e-mail address domains are in common use by many users that sites
would consider legitimate and desirable users. Banning these types of "free"
e-mail accounts from the registration process is no longer as effective in
preventing spam as it had been in the past. Moreover, some potential users of
your site may have one of these e-mail addresses as their only e-mail account.

## User log-in settings

These options control aspects of the log-in process.

### Failed login displays inactive status

Default: No. A generic error message is displayed.

After a failed attempt at logging into a site it is common to provide a generic
error message suggesting that the credentials supplied were not valid or not
found in the system.

If a user with a valid set of credentials (e.g., a user name and a password)
cannot log in because a site administrator has marked his account as inactive,
then with this option set to 'No,' the user will receive the generic error
message when his attempt to log in fails.

If a site wishes to indicate that the reason for the failed log-in attempt is
because the user's account is marked inactive, then set this option to 'Yes,' and
this more specific message will be displayed to the user instead of the generic
message.

### Failed login displays verification status

Default: No. A generic error message is displayed.

After a failed attempt at logging into a site it is common to provide a generic
error message suggesting that the credentials supplied were not valid or not
found in the system.

If a user who recently registered with the site but who has not completed the
e-mail address verification process attempts to log into the site with an
otherwise valid set of credentials (e.g., a user name and a password),
then with this option set to 'No,' the user will receive the generic error
message when his attempt to log in fails.

If a site wishes to indicate that the reason for the failed log-in attempt is
because the user's registration request is still waiting for the verification
process to be completed, then set this option to 'Yes,' and this more specific
message will be displayed to the user instead of the generic message.

### Failed login displays approval status

Default: No. A generic error message is displayed.

After a failed attempt at logging into a site it is common to provide a generic
error message suggesting that the credentials supplied were not valid or not
found in the system.

If a user who recently registered with the site but whose registration request
has not yet been approved by a site administrator attempts to log into the site
with an otherwise valid set of credentials (e.g., a user name and a password),
then with this option set to 'No,' the user will receive the generic error
message when his attempt to log in fails.

If a site wishes to indicate that the reason for the failed log-in attempt is
because the user's registration request is still waiting for administrator
approval, then set this option to 'Yes,' and this more specific message will be
displayed to the user instead of the generic message.
