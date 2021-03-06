<?php
/**
 * Copyright (C) 2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

namespace r2q;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use function dirname;

class Kernel extends BaseKernel {
	use MicroKernelTrait;

	private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

	public function registerBundles(): iterable {
		$contents = require $this->getProjectDir() . '/config/bundles.php';
		foreach ($contents as $class => $envs) {
			if ($envs[$this->environment] ?? $envs['all'] ?? false) {
				yield new $class();
			}
		}
	}

	public function getProjectDir(): string {
		return dirname(__DIR__);
	}

	protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void {
		$container->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
		$container->setParameter('container.dumper.inline_class_loader', true);
		$confDir = $this->getProjectDir() . '/config';

		$loader->load($confDir . '/{packages}/*' . self::CONFIG_EXTS, 'glob');
		$loader->load($confDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, 'glob');
		$loader->load($confDir . '/{services}' . self::CONFIG_EXTS, 'glob');
		$loader->load($confDir . '/{services}_' . $this->environment . self::CONFIG_EXTS, 'glob');
	}

	protected function configureRoutes(RouteCollectionBuilder $routes): void {
		$confDir = $this->getProjectDir() . '/config';

		$routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, '/', 'glob');
		$routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/', 'glob');
		$routes->import($confDir . '/{routes}' . self::CONFIG_EXTS, '/', 'glob');
	}
}
