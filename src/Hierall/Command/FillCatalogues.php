<?php
/**
 * Команда заполнения БД данными.
 */

namespace Hierall\Command;

use DirectoryIterator;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

/**
 * Class FillCatalogues
 * Команда заполения БД данными.
 * @package Hierall\Command
 */
class FillCatalogues extends Command
{
    /** @var \Hierall\CatalogueRepository $catalogueRepo */
    private $catalogueRepo = null;

    /**
     * Конфигурация команды.
     */
    protected function configure()
    {
        $this
            ->setName("fill-catalogue")
            ->setDescription("Clear and fill catalogues DB with data from /usr directory");
    }

    /**
     * Очищает и заполняет БД данными из директории /usr.
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $this->catalogueRepo) {
            $this->catalogueRepo = $this->getSilexApplication()['hierall.catalogues'];
        }

        $this->catalogueRepo->truncateCatalogues();
        $this->fillCatalogueFromDir(new DirectoryIterator('/usr'), null);
        $output->writeln("Imported.");
    }

    /**
     * Рекурсивное содание каталогов в БД.
     * @param DirectoryIterator $dir
     * @param int               $parentId
     */
    private function fillCatalogueFromDir(DirectoryIterator $dir, $parentId)
    {
        foreach ($dir as $node) {
            if ($node->isFile() || !$node->isDot()) {
                $id = $this->catalogueRepo->addCatalogue($node->getFilename(), $parentId);

                if ($node->isDir()) {
                    try {
                        $pathDir = new DirectoryIterator($node->getPathname());
                        $this->fillCatalogueFromDir($pathDir, $id);
                    } catch (UnexpectedValueException $e) {
                        // don't worry, we can't read directory, simple skip it
                    }
                }
            }
        }
    }
}
