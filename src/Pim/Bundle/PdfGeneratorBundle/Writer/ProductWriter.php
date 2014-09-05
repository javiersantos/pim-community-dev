<?php

namespace Pim\Bundle\PdfGeneratorBundle\Writer;

use Symfony\Component\Validator\Constraints as Assert;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\WritableDirectory;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Pim\Bundle\BaseConnectorBundle\Writer\File\ArchivableWriterInterface;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\BaseConnectorBundle\Writer\File\FileWriter;

/**
 * Write data into a file on the filesystem
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends FileWriter implements ItemWriterInterface, ArchivableWriterInterface,
    StepExecutionAwareInterface
{
    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @WritableDirectory(groups={"Execution"})
     */
    protected $folderPath = '/tmp/export_%datetime%/';

    private $resolvedFilePath;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    private $handler;

    private $resolvedFolderPath;

    protected $fileCount = 0;

    /**
     * @var array
     */
    protected $writtenFiles = array();

    /**
     * Set the file path
     *
     * @param string $folderPath
     *
     * @return FileWriter
     */
    public function setFolderPath($folderPath)
    {
        $this->folderPath = $folderPath;

        return $this;
    }

    /**
     * Get the file path
     *
     * @return string
     */
    public function getFolderPath()
    {
        if (!isset($this->resolvedFilePath)) {
            $this->resolvedFilePath = strtr(
                $this->folderPath,
                array(
                    '%datetime%' => date('Y-m-d_H:i:s')
                )
            );
        }

        return $this->resolvedFilePath;
    }

    /**
     * Get the file path in which to write the data
     *
     * @return string
     */
    public function getFilePath()
    {
        return sprintf('%s.pdf', $this->getFolderPath());
    }

    /**
     * Get the file path in which to write the data
     *
     * @return string
     */
    public function getPdfFilePath($identifier)
    {
        return sprintf('%s%s.pdf', $this->getFolderPath(), $identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $data)
    {
        $path = $this->getPdfFilePath('toto');
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        foreach ($data as $entry) {
            $identifier = $entry['identifier'];
            $product = $entry['product'];

            $this->handler = fopen($this->getPdfFilePath($identifier), 'w');
            if (false === fwrite($this->handler, $product)) {
                throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPdfFilePath($identifier)]);
            } else {
                $this->writtenFiles[$this->getPdfFilePath($identifier)] = basename($this->getPdfFilePath($identifier));
                $this->fileCount++;
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }
    }

    /**
     * Close handler when desctructing the current instance
     */
    public function __destruct()
    {
        if ($this->handler) {
            fclose($this->handler);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'folderPath' => array(
                'options' => array(
                    'label' => 'pim_pdf_connector.export.folderPath.label',
                    'help'  => 'pim_pdf_connector.export.folderPath.help'
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getWrittenFiles()
    {
        return $this->writtenFiles;
    }

    public function getName()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
