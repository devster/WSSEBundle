<?php

namespace Devster\WSSEBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clean nonce command.
 * Allows to gain space and avoid collisions gradually as the nonces pile
 */
class CleanNonceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wsse:nonce:clean')
            ->setDescription('Clean WSSE nonces')
            ->addOption('product', null, InputOption::VALUE_REQUIRED, 'product id')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Sync all products in Magento</comment>');

        $client = $this->getContainer()->get('square_co_magento.product');

        $repo = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('SquareCoCatalogBundle:Product');
        if ($product_id = $input->getOption('product')) {
            $products = array($repo->find($product_id));
        } else {
            $products = $repo->findAll();
        }

        foreach ($products as $p) {
            $output->write(sprintf(
                '<comment>Sync <info>#%d %s</info> orga: <info>%s</info></comment>',
                $p->getId(),
                $p->getName(),
                $p->getOrganisation()->getName()
            ));

            try {
                $client->save($p);
                $output->writeln(' <question>OK</question>');
            } catch (ApiException $e) {
                $output->writeln(' <error>KO</error>');
                $output->writeln(sprintf(' <error>%s: %s</error>', $e->getCode(), $e->getMessage()));
                foreach ($e->getErrors() as $error) {
                    $output->writeln(sprintf('  <error>%s: %s</error>', $error[0], $error[1]));
                }
            }
        }

        $output->writeln('<info>DONE</info>');
    }
}
