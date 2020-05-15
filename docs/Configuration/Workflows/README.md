---
currentMenu: workflows
---
# Workflows

Zikula utilises the Symfony Workflow component to provide workflow functionality.

You can read more about it in [the manual](https://symfony.com/doc/master/components/workflow.html).

## Workflow events

Please see the [Symfony docs](https://symfony.com/doc/current/workflow/usage.html#using-events) for a list of existing workflow events.

## Custom functionality

There are some slight differences regarding workflow behaviour in Zikula.

### Workflow locations

It seems that usually workflow definitions can only be stored at a central location. We wanted to make this more flexible so we allowed three different levels:

1. Central workflows in the core system are placed in: `/src/Zikula/CoreBundle/Resources/workflows/`
2. Modules can define their own workflows in: `/src/modules/Acme/MyBundle/Resources/workflows/`
3. Also it is possible to define custom workflows (or override existing ones) in: `/config/workflows/`

Each of these directories may contain several YML (`*.yaml`) or XML (`*.xml`) files.

**Caution:** when overriding existing workflows in `/config/workflows/someFile.yaml` (or `someFile.xml`) ensure that these workflows get new, unique names. Otherwise transitions will be added to the original workflow instead of redefining a custom workflow.

### Workflow editor

Zikula provides a JavaScript-based workflow editor.

![Workflow editor](images/workflow_ui.png)

The editor expects the name of a workflow which should be loaded. You can for example include a link to it like this:

```twig
<p>
    <a href="{{ path('zikula_workflow_editor_index', {workflow: 'acmetestmodule_enterprise'}) }}" title="{% trans %}Edit workflow for articles{% endtrans %}" target="_blank">{% trans %}Articles workflow{% endtrans %}</a>
</p>
```

The editor can switch between `workflow` (petri net) and `state_machine` modes. There are several differences between these. For example if you have a transition with more than one input like this:

```yaml
accept:
    from: [initial, waiting]
    to: accepted
```

When using in a petri net this transition is only enabled if the marking has both places. So your object needs both `froms` to transition into `to`. If you want two different `froms` you'll need two different transitions.

At the moment the editor supports YAML and XML output. It does not provide means for saving the changed workflows, because when upgrading an existing workflows it must be considered that objects could be in a state which does not exist anymore. You need to update your database to update these objects in order to avoid problems.
