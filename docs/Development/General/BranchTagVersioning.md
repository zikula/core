---
currentMenu: dev-general
---
# Branch, tag and versioning

Modules must be released with versioning that represents the a.b.c [Semantic Versioning](https://semver.org/). For example 2.3.0, 2.3.1 … 2.3.12 and so on. Where the c is the maintenance (bugfix) version.

If you maintain just one version series, then you should have just the master branch. e.g.

```
master - Your development branch
```

If you maintain more than one bugfix branch (e.g. 2.3 and 2.4 series) as well as a current new version, you must name them like this:

```
2.3 - maintenance branch of 2.3.x series
2.4 - maintenance branch of 2.4.x series
master - latest development branch (could be 2.5, 2.6 or even 3.0 doesn’t matter)
```

When tagging releases you must tag them as the final release version. So 2.3.1 would be tagged 2.3.1, 2.4.0 would be tagged 2.4.0. You can optionally prefix it with a ‘v’, e.g. v2.3.0 but that is entirely optional. Remember Semantic Versioning allows a.b.c-d so you can also tag beta/rc releases if you wish, e.g. 2.3.0-beta1.

When making bug fixes you’d always commit to the lowest branch where the fix should be applied, and then merge up. For example.

```
git checkout 2.3
# commit a set of fixes

git checkout 2.4
git merge 2.3 # merges the 2.3 branch to current checkout (2.4)
git checkout master
git merge 2.4 # merges the 2.3 branch to current checkout (master)
```

Renaming branches is as simple as follows. Let’s rename release-3 to 3.4:

```
git checkout -b 3.4 release-3
git push origin 3.4
git branch -D release-3
git push origin :release-3
```
