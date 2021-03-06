<?php

namespace DynamicFormBundle\Controller\Sonata\DynamicForm;

use DynamicFormBundle\Admin\Factory\DynamicForm\FormFieldFactory;
use DynamicFormBundle\Admin\Form\Type\DynamicForm\FormFieldType;
use DynamicFormBundle\Admin\Services\Actions\FormField\CloneAction;
use DynamicFormBundle\Admin\Services\Actions\FormField\DeleteAction;
use DynamicFormBundle\Admin\Services\FormField\TemplateGuesser;
use DynamicFormBundle\Entity\DynamicForm;
use DynamicFormBundle\Entity\DynamicForm\FormField;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package DynamicFormBundle\Admin\Controller\Sonata\DynamicForm
 *
 * @Route("form/{formId}/form-field")
 */
class FormFieldController extends Controller
{
    /**
     * @param Request     $request
     * @param string      $formType
     * @param DynamicForm $dynamicForm
     *
     * @Route("/{formType}/create")
     *
     * @ParamConverter("dynamicForm", options={"mapping": {"formId": "id"}})
     *
     * @return Response
     */
    public function createAction(Request $request, $formType, DynamicForm $dynamicForm)
    {
        $formField = $this
            ->get(FormFieldFactory::class)
            ->create($dynamicForm, $formType);

        $form = $this->createForm(FormFieldType::class, $formField);
        $form->handleRequest($request);

        if (true === $form->isSubmitted() && true === $form->isValid()) {
            $entityManager = $this
                ->getDoctrine()
                ->getManager();

            $dynamicForm->addField($formField);
            $entityManager->persist($formField);
            $entityManager->flush();

            $this->addSuccessFlash($formField);

            return $this->redirectToRoute('dynamicform_sonata_dynamicform_formfield_edit', [
                'formId' => $dynamicForm->getId(),
                'fieldId' => $formField->getId()
            ]);
        }

        return $this->get(TemplateGuesser::class)->render($formField, [
            'form' => $form->createView(),
            'dynamicForm' => $dynamicForm,
            'admin_pool' => $this->container->get('sonata.admin.pool')
        ]);
    }

    /**
     * @param Request     $request
     * @param DynamicForm $dynamicForm
     * @param FormField   $formField
     *
     * @Route("/{fieldId}/edit")
     *
     * @ParamConverter("formField", options={"mapping": {"fieldId": "id"}})
     * @ParamConverter("dynamicForm", options={"mapping": {"formId": "id"}})
     *
     * @return Response
     */
    public function editAction(Request $request, DynamicForm $dynamicForm, FormField $formField)
    {
        $this
            ->get(FormFieldFactory::class)
            ->initOptions($formField);

        $form = $this
            ->createForm(FormFieldType::class, $formField)
            ->handleRequest($request);

        if (true === $form->isSubmitted() && true === $form->isValid()) {
            $this
                ->getDoctrine()
                ->getManager()
                ->flush();

            $this->addSuccessFlash($formField);

            return $this->redirectToRoute('dynamicform_sonata_dynamicform_edit', ['id' => $dynamicForm->getId()]);
        }

        return $this->get(TemplateGuesser::class)->render($formField, [
            'form' => $form->createView(),
            'dynamicForm' => $dynamicForm,
            'admin_pool' => $this->container->get('sonata.admin.pool')
        ]);
    }

    /**
     * @param DynamicForm $dynamicForm
     * @param FormField   $formField
     *
     * @Route("/{fieldId}/delete")
     *
     * @ParamConverter("formField", options={"mapping": {"fieldId": "id"}})
     * @ParamConverter("dynamicForm", options={"mapping": {"formId": "id"}})
     *
     * @return RedirectResponse
     */
    public function deleteAction(DynamicForm $dynamicForm, FormField $formField)
    {
        $this->get(DeleteAction::class)->action($formField);

        $this->addSuccessFlash($formField);

        return $this->redirectToRoute('dynamicform_sonata_dynamicform_edit', ['id' => $dynamicForm->getId()]);
    }

    /**
     * @param DynamicForm $dynamicForm
     * @param FormField   $formField
     *
     * @Route("/{fieldId}/clone")
     *
     * @ParamConverter("formField", options={"mapping": {"fieldId": "id"}})
     * @ParamConverter("dynamicForm", options={"mapping": {"formId": "id"}})
     *
     * @return RedirectResponse
     */
    public function cloneAction(DynamicForm $dynamicForm, FormField $formField)
    {
        $this->get(CloneAction::class)->action($formField);

        $this->addSuccessFlash($formField);

        return $this->redirectToRoute('dynamicform_sonata_dynamicform_edit', ['id' => $dynamicForm->getId()]);
    }

    /**
     * @param FormField $formField
     */
    private function addSuccessFlash(FormField $formField)
    {
        $formType = $this
            ->get('translator')
            ->trans(sprintf('form_type.%s', $formField->getFormType()), [], 'dynamic_form');

        $successMessage = $this
            ->get('translator')
            ->trans('successfully.saved', [], 'dynamic_form');

        $this->addFlash('success', sprintf('%s: %s', $formType, $successMessage));
    }
}
