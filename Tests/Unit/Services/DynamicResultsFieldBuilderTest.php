<?php

namespace DynamicFormBundle\Tests\Unit;

use DynamicFormBundle\Entity\DynamicForm;
use DynamicFormBundle\Entity\DynamicResult;
use DynamicFormBundle\Entity\DynamicResult\FieldValue;
use DynamicFormBundle\Entity\DynamicForm\FormField;
use DynamicFormBundle\Entity\DynamicResult\ResultValue\StringValue;
use DynamicFormBundle\Entity\DynamicResult\ResultValue\TextValue;
use DynamicFormBundle\Services\DynamicResultFieldBuilder;
use DynamicFormBundle\Services\FormType\Configuration\Registry;
use DynamicFormBundle\Services\FormType\Configuration\TextAreaTypeConfiguration;
use DynamicFormBundle\Services\FormType\Configuration\TextTypeConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @package DynamicFormBundle\Tests\Unit
 */
class DynamicResultsFieldBuilderTest extends TestCase
{
    /**
     * @var DynamicResultFieldBuilder
     */
    private $builder;

    protected function setUp()
    {
        $registry = new Registry();
        $registry->addConfiguration(new TextTypeConfiguration([]));
        $registry->addConfiguration(new TextAreaTypeConfiguration([]));

        $this->builder = new DynamicResultFieldBuilder($registry);
    }

    public function testInitNewFieldsWihValues()
    {
        $result = new DynamicResult();

        $form = new DynamicForm();
        $form->addField(new FormField('name', 'text', 'name'));
        $form->addField(new FormField('description', 'textarea', 'description'));

        $this->builder->initFields($result, $form);

        $this->assertCount(2, $result->getFieldValues());
        $this->assertInstanceOf(StringValue::class, $result->getFieldValue('name')->getValue());
        $this->assertInstanceOf(TextValue::class, $result->getFieldValue('description')->getValue());
    }

    public function testDoNotInitExistingFields()
    {
        $nameField = new FormField('name', 'text', 'name');

        $form = new DynamicForm();
        $form->addField($nameField);
        $form->addField(new FormField('description', 'textarea'));

        $result = new DynamicResult();
        $result->addFieldValue(new FieldValue($nameField));

        $this->builder->initFields($result, $form);

        $this->assertCount(2, $result->getFieldValues());
    }

    public function testInitRemoveFieldsNotExistInForm()
    {
        $fieldValue = new FieldValue();
        $fieldValue->setFormField(new FormField('name', null, 'name'));

        $result = new DynamicResult();
        $result->addFieldValue($fieldValue);

        $form = new DynamicForm();

        $this->builder->initFields($result, $form);
        $this->assertCount(0, $result->getFieldValues());
    }
}
