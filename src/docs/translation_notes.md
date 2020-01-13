##in PHP files:

alter use of TranslatorTrait
    - remove old methods
    - add `trans` method with automatic domain setting (maybe from bundle instead of Translator?)
    - also auto-set the locale?

look at \Zikula\Core\Controller\AbstractController use of translation (e.g. `decorateTranslator`) is this functional?
 can we override `$this->trans` calls with module domain automatically?

Maybe still use our own Translator and override `trans` method with our custom domains (use decoration)
  https://stackoverflow.com/questions/39470596/replacing-the-translator-service-in-symfony-3

Maybe a listener to add something to every template to set the domain?
    `{% trans_default_domain "custom_domain" %}`

##Actual usage in template for plural+

    {% trans with {'%count%': node.children.count} %}n.direct1.child{% endtrans %}<br>
    {% trans count node.children.count with {'%count%': node.children.count} %}n.direct2.child{% endtrans %}<br>
    {% trans count node.children.count with {'%children%': node.children.count} %}(%children%).direct3.child{% endtrans %}<br>
    {% trans count node.children.count %}n.direct4.child{% endtrans %}<br>

with translations:

    n.direct1.child: "{count, plural,\n  one   {one direct child}\n  other {# direct children}\n}"
    n.direct2.child: "{count, plural,\n  one   {one direct child}\n  other {# direct children}\n}"
    n.direct3.child: "{count, plural,\n  one   {one direct child}\n  other {{children} direct children}\n}"
    n.direct4.child: "{count, plural,\n  one   {one direct child}\n  other {# direct children}\n}"

assuming count=2 produces

    2 direct children
    2 direct children
    __(%children%).direct3.child
    2 direct children

So the fourth option `{% trans count node.children.count %}` seems to be the most efficient
