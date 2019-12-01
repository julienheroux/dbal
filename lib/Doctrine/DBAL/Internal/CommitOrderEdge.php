<?php

namespace Doctrine\DBAL\Internal;

class CommitOrderEdge
{
    /** @var string */
    public $from;

    /** @var string */
    public $to;

    /** @var int */
    public $weight;
}
