---
currentMenu: templating
---
# Debugging Twig templates

Since Core 3.0 Zikula includes the [twig-inspector](https://github.com/oroinc/twig-inspector) that is a nice and powerful tool for inspecting and debugging Twig templates.

To use it switch to the development environment by setting the `APP_ENV` variable is to `dev` inside
the `/.env.local` file.

Afterwards please follow [these instructions](https://github.com/oroinc/twig-inspector/blob/master/Bundle/Resources/doc/usage.md) to use it.

Note that by default template files are opened inside your browser. If you want to be able to open them in your IDE or editor instead you need to configure this in `/config/packages/dev/framework.yaml`.  
For more information about the `framework.ide` setting please refer to [Symfony docs](https://symfony.com/doc/current/reference/configuration/framework.html#ide).
