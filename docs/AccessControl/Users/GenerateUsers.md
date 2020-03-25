---
currentMenu: users
---
# Generate Users

You can generate any number of users by using the CLI command below:

## Via CLI

    bin/console zikula:users:generate
    
### Options

 - `--active=[A|I|P|M|R]`
   - `A` all users active (default)
   - `I` all users inactive
   - `P` all users pending
   - `M` all users marked for deletion
   - `R` random assignment per user (`A|I|P|M`)
 - `--verified=[0|1|2]`
   - `0` all user emails unverified
   - `1` all user emails verified (default)
   - `2` random assignment per user (`0|1`)
 - `--regdate='(>)YYYYMMDD'`
   - setting just a date like `20000101` will set that as regdate for all created users.
   - adding `>` before the date will make each regdate a random date between the provided date and now.
 

    bin/console zikula:users:generate --active=I --verified=2 --regdate='>20000101'
