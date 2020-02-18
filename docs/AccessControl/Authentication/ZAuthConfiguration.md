---
currentMenu: authentication
---
# ZAuth configuration

This document helps to configure the ZAuth module, allowing the site administrator
to control how the extension handles various aspects of its functions and features.

## User credential settings

These options control various aspects of how the user logs into the system and
how his account credentials are managed.

### Minimum length for user passwords

Default: 5

Passwords for user accounts set during new account registration or set during
a password change request must be at least this number of characters long to
be accepted.

### Show password strength meter

Default: No

On pages where a user has the opportunity to create or change his account
password, this option controls whether the strength of the user's new password
is shown in the form of a password strength meter. The password strength meter
is JavaScript-based, therefore if the user's browser has JavaScript disabled,
then the meter will not be displayed, despite this option's setting.

### E-mail address verifications expire in
Default: 0 (e-mail verification requests do not expire)

When a user changes his e-mail address, the user is asked to verify the new
e-mail address. This option controls how long the e-mail verification code sent
via e-mail to the user is valid. A value of zero (0) means that the verification
code never expires. A positive integer indicates that the verification code is
valid for that number of days. For example, a value of 180 indicates that the
verification code is valid for 180 days, or approximately 6 months.

When an e-mail address verification code expires the record of the e-mail
address change request is deleted, and the e-mail address associated with the
user's account is not changed. (If the user verifies his e-mail address prior to
the expiration of the code, then the e-mail address is changed and the request
record is removed.)

### Password reset requests expire in
Default: 0 (password reset requests do not expire)

When a user requests to change the password on his account (assuming he has a
password, and has not registered with a non-User module authentication method),
he is sent a verification code via e-mail. This verification code must be used
in order to change the password on the account. This option controls how long
the code is valid before the request to change the account password expires.  A
value of zero (0) means that the verification code never expires. A positive
integer indicates that the verification code is valid for that number of days.
For example, a value of 180 indicates that the verification code is valid for
180 days, or approximately 6 months.

When a passowrd change request verification code expires the record of the
password change request is deleted, and the password associated with the user's
account is not changed. (If the user enters the verification code prior to its
expiration, then user is permitted to change the account password, and the request
record is removed.)

## Registration settings

These options control how a new user requests a new user account on the site.

### New users must verify their email address on registration.

Default: Yes. User chooses password, then activates account via e-mail

Controls whether a user requesting a new user account through the registration
process must verify his e-mail address. If set to 'Yes,' then following the
submission of a registration request the user is sent an e-mail with a
verification code. The user must enter this code on the page whose link is
provided in the e-mail message in order to verify his address.

### Registrations pending verification expire in

**Note:** This option is ignored if 'New users must verify their email address on registration.'
is set to 'No.'

Default: 0 (registration requests do not expire)

This option controls how long a registration request awaiting e-mail address
verification will remain valid. If the verification process is not complete
before the expiration, then the entire registration request record is removed
from the system.

A value of zero (0) indicates that registration requests awaiting e-mail
verification never expire. A positive integer indicates that the registration
request is valid for that number of days. For example, a value of 180 indicates
that the registration request is valid for 180 days, or approximately 6 months.

### Spam protection question

Default: _blank, no spam protection question is asked_

To protect a site, especially one not requiring approval or e-mail verification,
from "bots" or other non-human sources of spam, a question can be asked as part
of the registration process. The question asked should have a simple and
well-known answer that is unambiguous and easy for a human to answer, but
extremely difficult or impossible for a computer to answer.

If this option is blank, then no spam protection question is asked of the user
during registration.

Multi-language features are not enabled for this option, so if your site supports
more than one language, the question should be easy enough for all of your
visitors to answer.
