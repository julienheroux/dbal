<?php

namespace Doctrine\Tests\DBAL\Internal;

use Doctrine\DBAL\Internal\CommitOrderCalculator;
use Doctrine\DBAL\Schema\Table;
use Doctrine\Tests\DbalTestCase;

/**
 * Tests of the commit order calculation.
 *
 * IMPORTANT: When writing tests here consider that a lot of graph constellations
 * can have many valid orderings, so you may want to build a graph that has only
 * 1 valid order to simplify your tests.
 */
class CommitOrderCalculatorTest extends DbalTestCase
{
    /** @var CommitOrderCalculator */
    private $calculator;

    protected function setUp() : void
    {
        $this->calculator = new CommitOrderCalculator();
    }

    public function testCommitOrdering1()
    {
        $table1 = new Table('table1');
        $table2 = new Table('table2');
        $table3 = new Table('table3');
        $table4 = new Table('table4');
        $table5 = new Table('table5');

        $this->calculator->addNode($table1->getName(), $table1);
        $this->calculator->addNode($table2->getName(), $table2);
        $this->calculator->addNode($table3->getName(), $table3);
        $this->calculator->addNode($table4->getName(), $table4);
        $this->calculator->addNode($table5->getName(), $table5);

        $this->calculator->addDependency($table1->getName(), $table2->getName(), 1);
        $this->calculator->addDependency($table2->getName(), $table3->getName(), 1);
        $this->calculator->addDependency($table3->getName(), $table4->getName(), 1);
        $this->calculator->addDependency($table5->getName(), $table1->getName(), 1);

        $sorted = $this->calculator->sort();

        // There is only 1 valid ordering for this constellation
        $correctOrder = [$table5, $table1, $table2, $table3, $table4];

        $this->assertSame($correctOrder, $sorted);
    }

    public function testCommitOrdering2()
    {
        $table1 = new Table('table1');
        $table2 = new Table('table2');

        $this->calculator->addNode($table1->getName(), $table1);
        $this->calculator->addNode($table2->getName(), $table2);

        $this->calculator->addDependency($table1->getName(), $table2->getName(), 0);
        $this->calculator->addDependency($table2->getName(), $table1->getName(), 1);

        $sorted = $this->calculator->sort();

        // There is only 1 valid ordering for this constellation
        $correctOrder = [$table2, $table1];

        $this->assertSame($correctOrder, $sorted);
    }

    public function testCommitOrdering3()
    {
        // this test corresponds to the GH7259Test::testPersistFileBeforeVersion functional test
        $table1 = new Table('table1');
        $table2 = new Table('table2');
        $table3 = new Table('table3');
        $table4 = new Table('table4');

        $this->calculator->addNode($table1->getName(), $table1);
        $this->calculator->addNode($table2->getName(), $table2);
        $this->calculator->addNode($table3->getName(), $table3);
        $this->calculator->addNode($table4->getName(), $table4);

        $this->calculator->addDependency($table4->getName(), $table1->getName(), 1);
        $this->calculator->addDependency($table1->getName(), $table2->getName(), 1);
        $this->calculator->addDependency($table4->getName(), $table3->getName(), 1);
        $this->calculator->addDependency($table1->getName(), $table4->getName(), 0);

        $sorted = $this->calculator->sort();

        // There is only multiple valid ordering for this constellation, but
        // the class4, class1, class2 ordering is important to break the cycle
        // on the nullable link.
        $correctOrders = [
            [$table4, $table1, $table2, $table3],
            [$table4, $table1, $table3, $table2],
            [$table4, $table3, $table1, $table2],
        ];

        // We want to perform a strict comparison of the array
        $this->assertContains($sorted, $correctOrders, '');
    }
}
