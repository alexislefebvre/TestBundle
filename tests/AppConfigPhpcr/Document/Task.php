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

namespace AlexisLefebvre\TestBundle\Tests\AppConfigPhpcr\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;

/**
 * @PHPCR\Document()
 */
class Task
{
    /**
     * @PHPCR\Id()
     */
    protected $id;

    /**
     * @PHPCR\Field(type="string")
     */
    protected $description;

    /**
     * @PHPCR\Field(type="boolean")
     */
    protected $done = false;

    /**
     * @PHPCR\ParentDocument()
     */
    protected $parentDocument;

    public function setDescription($description): void
    {
        $this->description = $description;
    }

    public function setParentDocument($parentDocument): void
    {
        $this->parentDocument = $parentDocument;
    }
}
