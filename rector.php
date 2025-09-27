<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/appinfo',
        __DIR__ . '/lib',
        __DIR__ . '/templates',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php84: true)
    ->withTypeCoverageLevel(0)
	->withSkip([
		// skip rule since it marks code that's intended, as it is
		\Rector\Php70\Rector\FuncCall\RandomFunctionRector::class,

		// skip rule since it's introduced with php8.4 and not compatible to php8.1
		\Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector::class,
		// skip rule since it's introduced with php8.3 and not compatible to php8.1
		\Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector::class
	]);
