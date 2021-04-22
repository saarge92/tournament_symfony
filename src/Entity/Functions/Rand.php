<?php

namespace App\Entity\Functions;

use Doctrine\ORM\Query\Lexer;

class Rand extends \Doctrine\ORM\Query\AST\Functions\FunctionNode
{

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker): string
    {
        return 'RAND()';
    }
}