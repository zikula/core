Subscriber Workflow for `form_aware_hook`
=========================================

recommend use of class constants for all hook names

Controller edit method
----------------------

    public function edit() {
        // ...
        $form = $this->createForm(...);
        $formHook = new FormAwareHook($form);
        $this->get('hook_dispatcher')->dispatch('hook name...', $formHook);
        $form->handleRequest($request);
        if ($form->isValid()) { // FormAwareHook validates here automatically
            // ... persist e.g. $page object, etc
            $routeUrl = new RouteUrl('acmefoomodule_user_display', ['urltitle' => $page->getUrltitle()]);
            $this->get('hook_dispatcher')->dispatch('hook name...', new FormAwareResponse($form, $page, $routeUrl));
            // return redirect...
        }

        return $this->render('AcmeFooModule:Foo:modify.html.twig', [
            'form' => $form->createView(),
            'hook_templates' => $formHook->getTemplates()
        ]);
    }


Controller edit method
----------------------

    public function delete() {
        // ...
        $form = $this->createForm(...);
        $formHook = new FormAwareHook($form);
        $this->get('hook_dispatcher')->dispatch('hook name...', $formHook);
        $form->handleRequest($request);
        if ($form->isValid()) { // FormAwareHook validates here automatically
            $pageId = $page->getId();
            // ... delete e.g. $page object, etc
            $this->get('hook_dispatcher')->dispatch('hook name...', new FormAwareResponse($form, $pageId));
            // return redirect...
        }

        return $this->render('AcmeFooModule:Foo:delete.html.twig', [
            'form' => $form->createView(),
            'hook_templates' => $formHook->getTemplates()
        ]);
    }


Template for either method
--------------------------

    {{ form_start(form) }}
    {{ form_errors(form) }}
    ...
    {% for hook_template in hook_templates %}
        {{ include(hook_template.0, hook_template.1, ignore_missing = true) }}
    {% endfor %}
    ... submit/cancel buttons
    {{ form_end(form) }}
