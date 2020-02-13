---
currentMenu: developer-doctrine
---
# UTC DateTime column type

The `utcdatetime` column type forces the DateTime instance to be stored in UTC timezone.

Use like:

```
* @ORM\Column(type="utcdatetime")
```

Refs: [Working with DateTime Instances](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/working-with-datetime.html).
