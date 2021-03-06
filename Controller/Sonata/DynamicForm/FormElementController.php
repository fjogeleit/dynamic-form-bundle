<?php

namespace DynamicFormBundle\Controller\Sonata\DynamicForm;

use DynamicFormBundle\Admin\Factory\DynamicForm\FormElementFactory;
use DynamicFormBundle\Admin\Form\Type\DynamicForm\FormElementType;
use DynamicFormBundle\Admin\Services\Actions\FormElement\CloneAction;
use DynamicFormBundle\Admin\Services\Actions\FormElement\DeleteAction;
use DynamicFormBundle\Admin\Services\FormElement\TemplateGuesser;
use DynamicFormBundle\Entity\DynamicForm;
use DynamicFormBundle\Entity\DynamicForm\FormElement;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package DynamicFormBundle\Controller\Sonata\DynamicForm
 *
 * @Route("form/{formId}/form-element")
 */
class FormElementController extends Controller
{
    /**
     * @param Request     $request
     * @param string      $formElementType
     * @param DynamicForm $dynamicForm
     *
     * @Route("/{formElementType}/create")
     *
     * @ParamConverter("dynamicForm", options={"mapping": {"formId": "id"}})
     *
     * @return Response
     */
    public function createAction(Request $request, $formElementType, DynamicForm $dynamicForm)
    {
        $formElement = $this
            ->get(FormElementFactory::class)
            ->create($dynamicForm, $formElementType);

        $form = $this->createForm(FormElementType::class, $formElement);
        $form->handleRequest($request);

        if (true === $form->isSubmitted() && true === $form->isValid()) {
            $this
                ->getDoctrine()
                ->getManager()
                ->flush($dynamicForm);

            $this->addSuccessFlash($formElement);

            return $this->redirectToRoute('dynamicform_sonata_dynamicform_formelement_edit', [
                'formId' => $dynamicForm->getId(),
                'elementId' => $formElement->getId()
            ]);
        }

        return $this->get(TemplateGuesser::class)->render($formElement, [
            'form' => $form->createView(),
            'dynamicForm' => $dynamicForm,
            'admin_pool' => $this->container->get('sonata.admin.pool')
        ]);
    }

    /**
     * @param Request     $request
     * @param DynamicForm $dynamicForm
     * @param FormElement $formElement
     *
     * @Route("/{elementId}/edit")
     *
     * @ParamConverter("formElement", options={"mapping": {"elementId": "id"}})
     * @ParamConverter("dynamicForm", options={"mapping": {"formId": "id"}})
     *
     * @return Response
     */
    public function editAction(Request $request, DynamicForm $dynamicForm, FormElement $formElement)
    {
        $form = $this->createForm(FormElementType::class, $formElement);
        $form->handleRequest($request);

        if (true === $form->isSubmitted() && true === $form->isValid()) {
            $this
                ->getDoctrine()
                ->getManager()
                ->flush();

            $this->addSuccessFlash($formElement);

            return $this->redirectToRoute('dynamicform_sonata_dynamicform_edit', ['id' => $dynamicForm->getId()]);
        }

        return $this->get(TemplateGuesser::class)->render($formElement, [
            'form' => $form->createView(),
            'dynamicForm' => $dynamicForm,
            'admin_pool' => $this->container->get('sonata.admin.pool')
        ]);
    }

    /**
     * @param DynamicForm $dynamicForm
     * @param FormElement $formElement
     *
     * @Route("/{elementId}/delete")
     *
     * @ParamConverter("formElement", options={"mapping": {"elementId": "id"}})
     * @ParamConverter("dynamicForm", options={"mapping": {"formId": "id"}})
     *
     * @return RedirectResponse
     */
    public function deleteAction(DynamicForm $dynamicForm, FormElement $formElement)
    {
        $this->get(DeleteAction::class)->action($formElement);

        return $this->redirectToRoute('dynamicform_sonata_dynamicform_edit', ['id' => $dynamicForm->getId()]);
    }

    /**
     * @param DynamicForm $dynamicForm
     * @param FormElement $formElement
     *
     * @Route("/{elementId}/clone")
     *
     * @ParamConverter("formElement", options={"mapping": {"elementId": "id"}})
     * @ParamConverter("dynamicForm", options={"mapping": {"formId": "id"}})
     *
     * @return RedirectResponse
     */
    public function cloneAction(DynamicForm $dynamicForm, FormElement $formElement)
    {
        $this->get(CloneAction::class)->action($formElement);

        $this->addSuccessFlash($formElement);

        return $this->redirectToRoute('dynamicform_sonata_dynamicform_edit', ['id' => $dynamicForm->getId()]);
    }

    /**
     * @param FormElement $formElement
     */
    private function addSuccessFlash(FormElement $formElement)
    {
        $formType = $this
            ->get('translator')
            ->trans(sprintf('form_element.%s', $formElement->getElementType()), [], 'dynamic_form');

        $successMessage = $this
            ->get('translator')
            ->trans('successfully.saved', [], 'dynamic_form');

        $this->addFlash('success', sprintf('%s: %s', $formType, $successMessage));
    }
}
