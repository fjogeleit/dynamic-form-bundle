<?php

namespace DynamicFormBundle\Tests\Unit\EventListener\Values;

use Doctrine\ORM\Event\LifecycleEventArgs;
use DynamicFormBundle\Entity\DynamicResult\ResultValue\FileValue;
use DynamicFormBundle\EventListener\Values\FileListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @package DynamicFormBundle\Tests\Unit\EventListener\Values
 */
class FileListenerTest extends TestCase
{
    /**
     * @var FileListener
     */
    private $listener;

    /**
     * @var string
     */
    private $path;

    protected function setUp()
    {
        $this->path = sprintf('%s/../../../Fixtures/app/../web/uploads', __DIR__);

        $this->listener = new FileListener($this->path);
    }

    public function testPostLoadSetContentToFileInstance()
    {
        $fileValue = new FileValue();
        $fileValue->setFileUri('test.txt');

        $this->listener->convertContent($fileValue, $this->getEvent());

        $this->assertInstanceOf(File::class, $fileValue->getContent());
    }

    public function testPostLoadDoesNothingIfFileUriIsEmpty()
    {
        $fileValue = new FileValue();

        $this->listener->convertContent($fileValue, $this->getEvent());

        $this->assertNull($fileValue->getContent());
    }

    public function testUploadFileMoveUploadedFileToExpectedDir()
    {
        $fileValue = new FileValue();
        $fileValue->setContent($this->getUploadedFile(1));

        $this->listener->uploadFile($fileValue, $this->getEvent());
    }

    public function testUploadFileDoesNotUploadIfUploadedFileIsNull()
    {
        $fileValue = $this->createMock(FileValue::class);

        $fileValue
            ->expects($this->never())
            ->method('setFileUri');

        $this->listener->uploadFile($fileValue, $this->getEvent());
    }

    /**
     * @param int $expectedCalls
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|UploadedFile
     */
    private function getUploadedFile($expectedCalls = 0)
    {
        $uploadedFile = $this->createMock(UploadedFile::class);

        $uploadedFile
            ->expects($this->exactly($expectedCalls))
            ->method('getClientOriginalName')
            ->willReturn('test.txt');

        $uploadedFile
            ->expects($this->exactly($expectedCalls))
            ->method('move')
            ->with($this->path, $this->anything())
            ->willReturn($this->createMock(File::class));

        return $uploadedFile;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LifecycleEventArgs
     */
    private function getEvent()
    {
        return $this->createMock(LifecycleEventArgs::class);
    }
}
