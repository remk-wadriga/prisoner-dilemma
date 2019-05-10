<?php


namespace App\Doctrine\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Subselect;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

class SumQueryFunction extends FunctionNode
{
    /**
     * @var Subselect
     */
    private $subSelect;

    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf('SUM((%s))', $this->subSelect->dispatch($sqlWalker));
    }

    public function parse(Parser $parser)
    {
        $parser->Match(Lexer::T_IDENTIFIER);
        $parser->Match(Lexer::T_OPEN_PARENTHESIS);

        $this->subSelect = $parser->Subselect();

        $parser->Match(Lexer::T_CLOSE_PARENTHESIS);
    }
}