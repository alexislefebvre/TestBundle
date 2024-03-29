<?php

declare(strict_types=1);

/*
 * This file is part of the Liip/FunctionalTestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace AlexisLefebvre\TestBundle;

use Doctrine\Common\Annotations\Reader;
use AlexisLefebvre\TestBundle\Annotations\QueryCount;
use AlexisLefebvre\TestBundle\Exception\AllowedQueriesExceededException;

final class QueryCounter
{
    /** @var int */
    private $defaultMaxCount;

    /** @var Reader */
    private $annotationReader;

    public function __construct(?int $defaultMaxCount, Reader $annotationReader)
    {
        $this->defaultMaxCount = $defaultMaxCount;
        $this->annotationReader = $annotationReader;
    }

    public function checkQueryCount(int $actualQueryCount): void
    {
        $maxQueryCount = $this->getMaxQueryCount();

        if (null === $maxQueryCount) {
            return;
        }

        if ($actualQueryCount > $maxQueryCount) {
            throw new AllowedQueriesExceededException(
                "Allowed amount of queries ($maxQueryCount) exceeded (actual: $actualQueryCount)."
            );
        }
    }

    private function getMaxQueryCount(): ?int
    {
        $maxQueryCount = $this->getMaxQueryAnnotation();

        if (null !== $maxQueryCount) {
            return $maxQueryCount;
        }

        return $this->defaultMaxCount;
    }

    private function getMaxQueryAnnotation(): ?int
    {
        foreach (debug_backtrace() as $step) {
            if ('test' === substr($step['function'], 0, 4)) { //TODO: handle tests with the @test annotation
                $annotations = $this->annotationReader->getMethodAnnotations(
                    new \ReflectionMethod($step['class'], $step['function'])
                );

                foreach ($annotations as $annotationClass) {
                    if ($annotationClass instanceof QueryCount && isset($annotationClass->maxQueries)) {
                        /* @var $annotations \AlexisLefebvre\TestBundle\Annotations\QueryCount */

                        return $annotationClass->maxQueries;
                    }
                }
            }
        }

        return null;
    }
}
