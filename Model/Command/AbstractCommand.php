<?php

declare(strict_types=1);

/**
 * This file is part of Code Generator for Magento.
 * (c) 2017. Rostyslav Tymoshenko <krifollk@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Krifollk\CodeGenerator\Model\Command;

use Krifollk\CodeGenerator\Api\ModulesDirProviderInterface;
use Krifollk\CodeGenerator\Model\GeneratorResult\Container;
use Krifollk\CodeGenerator\Model\ModuleNameEntity;
use Magento\Framework\Filesystem\Driver\File;
use Krifollk\CodeGenerator\Model\GeneratorResult;

/**
 * Class AbstractCommand
 *
 * @package Krifollk\CodeGenerator\Model\Command
 */
class AbstractCommand
{
    /** @var File */
    private $file;

    /** @var ModulesDirProviderInterface */
    private $modulesDirProvider;

    /**
     * AbstractCommand constructor.
     *
     * @param File                        $file
     * @param ModulesDirProviderInterface $modulesDirProvider
     */
    public function __construct(File $file, ModulesDirProviderInterface $modulesDirProvider)
    {
        $this->file = $file;
        $this->modulesDirProvider = $modulesDirProvider;
    }

    /**
     * Generate files
     *
     * @param Container        $container
     * @param ModuleNameEntity $moduleNameEntity
     * @param string           $dir
     *
     * @return \Generator
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function generateFiles(
        Container $container,
        ModuleNameEntity $moduleNameEntity,
        string $dir
    ) {
        /** @var GeneratorResult $entity */
        foreach ($container->getAll() as $entity) {
            $absoluteFilePath = $this->getAbsoluteFilePath($moduleNameEntity, $dir, $entity);

            $absoluteDir = dirname($absoluteFilePath);
            $this->file->createDirectory($absoluteDir);
            $this->file->filePutContents($absoluteFilePath, $entity->getContent());

            yield $absoluteFilePath;
        }
    }

    /**
     * @return Container
     */
    protected function createResultContainer(): Container
    {
        return new Container();
    }

    private function getModulesDir(): string
    {
        return $this->modulesDirProvider->getModulesDir();
    }

    private function getAbsoluteFilePath(
        ModuleNameEntity $moduleNameEntity,
        string $dir,
        GeneratorResult $entity
    ): string {
        $absoluteFilePath = $this->getModulesDir() . $entity->getDestinationFile();

        if ($dir !== '') {
            $absoluteFilePath = sprintf('%s/%s%s', BP, $dir,
                str_replace($moduleNameEntity->asPartOfPath(), '', $entity->getDestinationFile())
            );
        }

        return $absoluteFilePath;
    }
}
