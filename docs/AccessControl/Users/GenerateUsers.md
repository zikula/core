---
currentMenu: users
---
# Generate Users

You can generate any number of users by using the CLI command below:

## Via CLI

    bin/console zikula:users:generate
    
### Options

 - `--active=[0|1|2]`
   - `0` all users inactive
   - `1` all users active
   - `2` random assignment per user (`0|1`)
 - `--verified=[0|1|2]`
   - `0` all user emails unverified
   - `1` all user emails verified
   - `2` random assignment per user (`0|1`)


    bin/console zikula:users:generate --active=0 --verified=2
