<?php

namespace Doctrine\DBAL\Internal;

class CommitOrderNode
{
    /** @var string */
    public $hash;

    /** @var int */
    public $state;

    /** @var object */
    public $value;

    /** @var CommitOrderEdge[] */
    public $dependencyList = [];
}
