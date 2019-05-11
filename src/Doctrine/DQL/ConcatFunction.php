<?php

namespace App\Doctrine\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

class ConcatFunction extends FunctionNode
{
    /**
     * Holds the timestamp of the DATE_FORMAT DQL statement
     * @var $dateExpression
     */
    protected $field;

    /**
     * Holds the '% separator' parameter
     * var String
     */
    protected $separator;

    public function getSql(SqlWalker $sqlWalker)
    {
        $separator = $this->separator === null ? "','" : $sqlWalker->walkStringPrimary($this->separator);

        return sprintf('GROUP_CONCAT(%s SEPARATOR %s)', $sqlWalker->walkArithmeticExpression($this->field), $separator);
    }

    public function parse(Parser $parser)
    {
        $parser->Match(Lexer::T_IDENTIFIER);
        $parser->Match(Lexer::T_OPEN_PARENTHESIS);

        $this->field = $parser->ArithmeticExpression();

        if ($parser->getLexer()->isNextToken(Lexer::T_COMMA)) {
            $parser->Match(Lexer::T_COMMA);
            $this->separator = $parser->ArithmeticExpression();
        }

        $parser->Match(Lexer::T_CLOSE_PARENTHESIS);
    }
}