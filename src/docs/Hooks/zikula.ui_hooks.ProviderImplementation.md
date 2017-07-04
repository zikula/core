Provider Display Hooks
----------------------

A provider hook handler should respond to hookable events similar to the following:

Sample ProviderHandler class:

    namespace Zikula\FooHookModule\Handler;
    
    use Symfony\Component\HttpFoundation\RequestStack;
    use Zikula\Bundle\HookBundle\Hook\DisplayHook;
    use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
    use Zikula\Bundle\HookBundle\Hook\FilterHook;
    use Zikula\Bundle\HookBundle\Hook\ProcessHook;
    use Zikula\Bundle\HookBundle\Hook\ValidationHook;
    use Zikula\Bundle\HookBundle\Hook\ValidationResponse;
    use Zikula\FooHookModule\Container\HookContainer;

    class UiHooksProviderHandler
    {
        /**
         * @var RequestStack
         */
        private $requestStack;
    
        /**
         * ProviderHandler constructor.
         * @param RequestStack $requestStack
         */
        public function __construct(RequestStack $requestStack)
        {
            $this->requestStack = $requestStack;
        }
    
        public function uiView(DisplayHook $hook)
        {
            $hook->setResponse(new DisplayHookResponse(HookContainer::PROVIDER_UIAREANAME, 'This is the ZikulaFooHookModule uiView Display Hook Response.'));
        }
    
        public function uiEdit(DisplayHook $hook)
        {
            $hook->setResponse(new DisplayHookResponse(HookContainer::PROVIDER_UIAREANAME, '<div>ZikulaFooModuleContent hooked.</div><input name="zikulafoomodule[name]" value="zikula" type="hidden">'));
        }
    
        public function validateEdit(ValidationHook $hook)
        {
            $post = $this->requestStack->getCurrentRequest()->request->all();
            if ($this->requestStack->getCurrentRequest()->request->has('zikulafoomodule') && $post['zikulafoomodule']['name'] == 'zikula') {
                return true;
            } else {
                $response = new ValidationResponse('mykey', $post['zikulafoomodule']);
                $response->addError('name', sprintf('Name must be zikula but was %s', $post['zikulafoomodule']['name']));
                $hook->setValidator(HookContainer::PROVIDER_UIAREANAME, $response);
    
                return false;
            }
        }
    
        public function processEdit(ProcessHook $hook)
        {
            $this->requestStack->getMasterRequest()->getSession()->getFlashBag()->add('success', 'Ui hook properly processed!');
        }
    
        public function filter(FilterHook $hook)
        {
            $content = $hook->getData();
            $hook->setData('PRE>>> ' . $content . ' <<<POST');
        }
    }

sample `Resources/config/services.yml`

    services:
        zikula_foohook_module.hook_handler:
            class: Zikula\FooHookModule\Handler\UiHooksProviderHandler
            arguments:
              - "@request_stack"
    
        zikula_foohook_module.hook_handler.filter:
            class: Zikula\FooHookModule\Handler\UiHooksProviderHandler
            arguments:
              - "@request_stack"
