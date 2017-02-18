<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.4 (http://modulestudio.de).
 */

namespace Zikula\RoutesModule\Form\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * Configuration form type base class.
 */
abstract class AbstractAppSettingsType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var VariableApi
     */
    protected $variableApi;

    /**
     * @var array
     */
    protected $modVars;

    /**
     * AppSettingsType constructor.
     *
     * @param TranslatorInterface $translator  Translator service instance
     * @param VariableApi         $variableApi VariableApi service instance
     */
    public function __construct(TranslatorInterface $translator, VariableApi $variableApi)
    {
        $this->setTranslator($translator);
        $this->variableApi = $variableApi;
        $this->modVars = $this->variableApi->getAll('ZikulaRoutesModule');
    }

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance
     */
    public function setTranslator(/*TranslatorInterface */$translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addListViewsFields($builder, $options);

        $builder
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Update configuration'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default',
                    'formnovalidate' => 'formnovalidate'
                ]
            ])
        ;
    }

    /**
     * Adds fields for list views fields.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function addListViewsFields(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('routeEntriesPerPage', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $this->__('Route entries per page') . ':',
                'label_attr' => [
                    'class' => 'tooltips',
                    'title' => $this->__('The amount of routes shown per page')
                ],
                'help' => $this->__('The amount of routes shown per page'),
                'required' => false,
                'data' => isset($this->modVars['routeEntriesPerPage']) ? $this->modVars['routeEntriesPerPage'] : '',
                'empty_data' => intval('10'),
                'attr' => [
                    'maxlength' => 255,
                    'title' => $this->__('Enter the route entries per page.') . ' ' . $this->__('Only digits are allowed.')
                ],'scale' => 0
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikularoutesmodule_appsettings';
    }
}
