<?php

/**
 * Kód pro definici instrukcí a jejich operandů
 * 
 * @author Jakub Kořínek <xkorin17@stud.fit.vutbr.cz> 
 */

declare(strict_types=1);

$instructions = [
    'MOVE' => ['var', 'symb'],
    'CREATEFRAME' => [],
    'PUSHFRAME' => [],
    'POPFRAME' => [],
    'DEFVAR' => ['var'],
    'CALL' => ['label'],
    'RETURN' => [],
    'PUSHS' => ['symb'],
    'POPS' => ['var'],
    'ADD' => ['var', 'symb', 'symb'],
    'SUB' => ['var', 'symb', 'symb'],
    'MUL' =>['var', 'symb', 'symb'],
    'IDIV' => ['var', 'symb', 'symb'],
    'LT' => ['var', 'symb', 'symb'],
    'GT' => ['var', 'symb', 'symb'],
    'EQ' => ['var', 'symb', 'symb'],
    'AND' => ['var', 'symb', 'symb'],
    'OR' => ['var', 'symb', 'symb'],
    'NOT' => ['var', 'symb'],
    'INT2CHAR' => ['var', 'symb'],
    'STRI2INT' => ['var', 'symb', 'symb'],
    'READ' => ['var', 'type'],
    'WRITE' => ['symb'],
    'CONCAT' => ['var', 'symb', 'symb'],
    'STRLEN' => ['var', 'symb'],
    'GETCHAR' => ['var', 'symb', 'symb'],
    'SETCHAR' => ['var', 'symb', 'symb'],
    'TYPE' => ['var', 'symb'],
    'LABEL' => ['label'],
    'JUMP' => ['label'],
    'JUMPIFEQ' => ['label', 'symb', 'symb'],
    'JUMPIFNEQ' => ['label', 'symb', 'symb'],
    'EXIT' => ['symb'],
    'DPRINT' => ['symb'],
    'BREAK' => [],
]

?>