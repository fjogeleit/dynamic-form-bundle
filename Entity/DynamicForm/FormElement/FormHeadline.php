<?php

namespace DynamicFormBundle\Entity\DynamicForm\FormElement;

use DynamicFormBundle\Entity\DynamicForm\FormElement;
use DynamicFormBundle\Statics\FormElements;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="dynamic_form_element_headline")
 *
 * @package DynamicFormBundle\Entity
 */
class FormHeadline extends FormElement
{
    /**
     * @return string
     */
    public function getElementType()
    {
        return FormElements::HEADLINE;
    }

    /**
     * @return string
     */
    public function getAnchor()
    {
        return sprintf('form_headline_%s', $this->getId());
    }
}
