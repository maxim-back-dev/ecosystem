<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    
    /**
     *
     * Run static analyzer on staged files.
     *
     * @param Event $event A Composer event instance.
     *
     * @return void
     */
    public static function psalm(Event $event)
    {
        // Get current root path.
        $rootPath = dirname($event->getComposer()->getConfig()->get('vendor-dir'));
        $composerIO = $event->getIO();
        $output = [];
        $retVal = 0;

        try {
            $files = self::getStagedFiles();
            if (count($files) === 0) {
                // Don't do any check if php files not changes.
                return;
            }

            // Run static analyzer on staged files.
            $files = str_replace(self::PROJECT_NAME, '', implode(' ', $files));
            $command = $rootPath . self::PSALM_BIN. ' '. $files;
            $composerIO->write("<info>{$command}</info>");
            exec($command, $output, $retVal);

            foreach ($output as $line) {
                $composerIO->write("  <comment>></comment> {$line}");
            }
        } catch (\Exception $e) {
            $composerIO->writeError('<error>'. $e->getMessage() .'</error>');
            $retVal = 1;
        }

        if ($retVal !== 0) {
            exit($retVal);
        }
    }
    
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir().'/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir().'/config';

        $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir().'/config';

        $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}'.self::CONFIG_EXTS, '/', 'glob');
    }
}
