<?php

namespace Pim\Bundle\PdfGeneratorBundle\Processor;

use Symfony\Component\Translation\TranslatorInterface;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\BaseConnectorBundle\Validator\Import\ImportValidatorInterface;
use Pim\Bundle\TransformBundle\Transformer\EntityTransformerInterface;
use Pim\Bundle\PdfGeneratorBundle\Renderer\RendererRegistry;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Abstract processor for transformer based imports
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPdfProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /**
     * @Assert\NotBlank(groups={"Execution"})
     *
     * @var string $channel Channel code
     */
    protected $channel;

    /** @var RendererRegistry */
    protected $renderRegistry;

    /** @var ChannelManager */
    protected $channelManager;

    /**
     * @param RendererInterface $renderRegistry
     * @param ChannelManager    $channelManager
     */
    public function __construct(
        RendererRegistry $renderRegistry,
        ChannelManager $channelManager
    ) {
        $this->renderRegistry = $renderRegistry;
        $this->channelManager = $channelManager;
    }

    public function process($item)
    {
        $channel = $this->channelManager->getChannelByCode($this->channel);

        $context = [
            'locale'        => $channel->getLocaleCodes()[0],
            'scope'         => $channel->getCode(),
            'renderingDate' => new \DateTime()
        ];

        return ['identifier' => (string) $item->getIdentifier(), 'product' => $this->renderRegistry->render($item, 'plain', $context)];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help'
                ]
            ]
        ];
    }

    /**
     * Set channel
     *
     * @param string $channelCode Channel code
     *
     * @return $this
     */
    public function setChannel($channelCode)
    {
        $this->channel = $channelCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Get channel
     *
     * @return string Channel code
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
