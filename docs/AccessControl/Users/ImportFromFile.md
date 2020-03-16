---
currentMenu: users
---
# Importing users from a CSV file

You can import a CSV file to create many users quickly. **The CLI method is recommended**.
For both methods, you must create a CSV file:

The first row of the CSV file must contain the field names. It must be like this:

    uname,pass,email,activated,sendmail,groups
where:

  * uname(mandatory) - The user name. This value must be unique.
  * pass(mandatory) - The user password. It must have 8 characters or more. Preferentially containing letters and numbers.
  * email(mandatory) - The user email. If the validation method is based on the user email this value must be unique.
  * activated- Type 0 if user is not active, 1 if the user must be active. The default value is 1.
  * sendmail- Type 1 if the system must send the password to the user via email and 0 otherwise. The default value is 1. The module Mailer must be active and correctly configured. The email is sent only if user activated value is upper than 0.
  * groups- The identities of the groups where the user must belong separated by the character |. If you do not specify any group, the default group is Users. Undefined groups will be ignored.

An example of a valid CSV file

    uname,pass,email,activated,sendmail,groups
    albert,12secure09,albert@example.org,1,1,2
    george,lesssecure,george@example.org,1,0,1|5
    robert,hispassword,robert@example.org,,,

Another example of a valid CSV file

    uname,pass,email
    albert,12secure09,albert@example.org
    george,lesssecure,george@example.org
    robert,hispassword,robert@example.org

## Via CLI (recommended)

    bin/console zikula:users:import path/to/my/file/importUsers.txt

## Via web interface

    route: /zauth/fileIO/import
