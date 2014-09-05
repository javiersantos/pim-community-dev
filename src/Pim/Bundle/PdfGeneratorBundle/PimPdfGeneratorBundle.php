<?php

namespace Pim\Bundle\PdfGeneratorBundle;

use Akeneo\Bundle\BatchBundle\Connector\Connector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\PdfGeneratorBundle\DependencyInjection\Compiler;

/**
 * PDF Generator bundle
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimPdfGeneratorBundle extends Connector
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new Compiler\RegisterRendererPass());
    }
}
