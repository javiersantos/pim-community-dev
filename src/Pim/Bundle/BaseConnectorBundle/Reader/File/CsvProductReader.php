<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Doctrine\ORM\EntityManager;

/**
 * Product csv reader
 *
 * This specialized csv reader exists because, as the product are bulk inserted,
 * we cannot rely on the UniqueValueValidator which rely on data present inside the database.
 * Its second purpose is to replace relative media path to absolute path, in order for later
 * process to know where to find the files.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvProductReader extends CsvReader
{
    /** @var array Media attribute codes */
    protected $mediaAttributes = array();

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     * @param string        $attributeClass
     */
    public function __construct(EntityManager $entityManager, $attributeClass)
    {
        $repository = $entityManager->getRepository($attributeClass);
        $this->mediaAttributes = $repository->findMediaAttributeCodes();
    }

    /**
     * Set the media attributes
     *
     * @param array $mediaAttributes
     *
     * @return CsvProductReader
     */
    public function setMediaAttributes(array $mediaAttributes)
    {
        $this->mediaAttributes = $mediaAttributes;

        return $this;
    }

    /**
     * Get the media attributes
     *
     * @return array
     */
    public function getMediaAttributes()
    {
        return $this->mediaAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            [
                'mediaAttributes' => [
                    'system' => true
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $data = parent::read();

        if (!is_array($data)) {
            return $data;
        }

        return $this->transformMediaPathToAbsolute($data);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function transformMediaPathToAbsolute(array $data)
    {
        foreach ($data as $code => $value) {
            $pos = strpos($code, '-');
            $attributeCode = false !== $pos ? substr($code, 0, $pos) : $code;

            if (in_array($attributeCode, $this->mediaAttributes)) {
                $data[$code] = dirname($this->filePath) . '/' . $value;
            }
        }

        return $data;
    }
}
