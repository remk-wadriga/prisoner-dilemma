<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 03.05.2019
 * Time: 12:23
 */

namespace App\Doctrine\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

class DateFormatFunction extends FunctionNode
{
    /**
     * Holds the timestamp of the DATE_FORMAT DQL statement
     * @var $dateExpression
     */
    protected $dateExpression;

    /**
     * Holds the '% format' parameter of the DATE_FORMAT DQL statement
     * var String
     */
    protected $formatChar;

    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf('DATE_FORMAT(%s, %s)', $sqlWalker->walkArithmeticExpression($this->dateExpression), $sqlWalker->walkStringPrimary($this->formatChar));
    }

    public function parse(Parser $parser)
    {
        $parser->Match(Lexer::T_IDENTIFIER);
        $parser->Match(Lexer::T_OPEN_PARENTHESIS);

        $this->dateExpression = $parser->ArithmeticExpression();
        $parser->Match(Lexer::T_COMMA);

        $this->formatChar = $parser->ArithmeticExpression();

        $parser->Match(Lexer::T_CLOSE_PARENTHESIS);
    }
}