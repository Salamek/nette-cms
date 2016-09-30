<?php

namespace Salamek\Cms\Console;

use Salamek\Cms\Cms;
use Salamek\Cms\Models\IMenuRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

class GeneratePresentersCommand extends Command
{
    protected function configure()
    {
        $this->setName('cms:presenters:generate')
            ->setDescription('Generates presenters for all menu content');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var IMenuRepository $menuRepository */
        $menuRepository = $this->getHelper('container')->getByType('Salamek\Cms\Models\IMenuRepository');

        /** @var Cms $cms */
        $cms = $this->getHelper('container')->getByType('Salamek\Cms\Cms');

        try {
            foreach($menuRepository->getAll() AS $menu)
            {
                $cms->generateMenuPage($menu);
            }
            $output->writeLn('All presenters successfully generated');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}