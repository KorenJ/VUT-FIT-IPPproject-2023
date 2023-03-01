<?php

/**
 * Kód pro definici návratových hodnot.
 * 
 * @author Jakub Kořínek <xkorin17@stud.fit.vutbr.cz>
 */

declare(strict_types=1);
const
    SUCCESS = 0,
    
    WRONG_PARAMETER_ERROR = 10,
    FAILED_OPEN = 11,
    FAILED_WRITE = 12,

    WRONG_HEADER_ERROR = 21,
    WRONG_CODE_ERROR = 22,
    OTHER_PHPPARSE_ERROR = 23,

    INTERNAL_ERROR = 99;
?>