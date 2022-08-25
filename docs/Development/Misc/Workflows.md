---
currentMenu: workflows
---
# Workflows

Zikula utilises the Symfony Workflow component to provide workflow functionality.

You can read more about it in [the manual](https://symfony.com/doc/current/components/workflow.html).

## Workflow events

Please see the [Symfony docs](https://symfony.com/doc/current/workflow/usage.html#using-events) for a list of existing workflow events.

## Custom functionality

There are some slight differences regarding workflow behaviour in Zikula.

### Workflow locations

It seems that usually workflow definitions can only be stored at a central location. We wanted to make this more flexible so we allowed three different levels:

1. Central workflows in the core system are placed in: `/src/Zikula/Bundle/CoreBundle/Resources/workflows/`
2. Modules can define their own workflows in: `/src/extensions/Acme/MyBundle/Resources/workflows/`
3. Also it is possible to define custom workflows (or override existing ones) in: `/config/workflows/`

Each of these directories may contain several YML (`*.yaml`) or XML (`*.xml`) files.

**Caution:** when overriding existing workflows in `/config/workflows/someFile.yaml` (or `someFile.xml`) ensure that these workflows get new, unique names. Otherwise transitions will be added to the original workflow instead of redefining a custom workflow.
