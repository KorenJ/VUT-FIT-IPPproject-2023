<?php

/**
 * Hlavní kód projektu IPP 2023.
 * 
 * @author Jakub Kořínek <xkorin17@stud.fit.vutbr.cz>
 */

declare(strict_types=1);

/**
 * Import návratových kódů
 */
include 'returncodes.php';
/**
 * Import seznamu instrukcí
 */
include 'instructions.php';

//chybové stavy
ini_set('display_errors', 'stderr');

/**
 * Ošetření argumentů skriptu
 */
foreach ($argv as $arg){
    if ($arg === "--help"){
        if (count($argv) > 2){
            FWRITE(STDERR, "Chyba argumentů skriptu.\n");
            exit(WRONG_PARAMETER_ERROR);
        }
        else{
            echo "Nápověda:\n"
                ."Skript načte ze standardního vstupu zdrojový kód v IPPcode23," 
                ." zkontroluje lexikální a syntaktickou správnost kódu a vypíše na standardní"
                ." výstup XML reprezentaci programu.\n";
            exit(SUCCESS);
        }
    }
}

/**
 * Nastavení xmlwriteru.
 */
$order = 1;
$xw = xmlwriter_open_memory();
xmlwriter_set_indent($xw, true);
$res = xmlwriter_set_indent_string($xw, ' ');

xmlwriter_start_document($xw, '1.0', 'UTF-8');
xmlwriter_start_element($xw, "program");
xmlwriter_start_attribute($xw, "language");
xmlwriter_text($xw, "IPPcode23");
xmlwriter_end_attribute($xw);
function add_arg($operand, $argtype, $argnumber){
    global $xw;
    xmlwriter_start_element($xw, "arg" . strval($argnumber));
    xmlwriter_start_attribute($xw, "type");
    xmlwriter_text($xw, $argtype);
    xmlwriter_end_attribute($xw);
    xmlwriter_text($xw, $operand);
    xmlwriter_end_element($xw);
}

/**
 * Definice regulárních výrazů
 */
$type_nil = '/^nil@nil$/';
$comment = '/^\s*(#.*)?$/';
$inline_comment = '\s*(#.*)?';
$type_int = '/^int@(\+|\-)?\d+$/';
$type_string = '/^string@(?:[^\s#\\\\]|\\\\\d{3})*$/';
$type_bool = '/^bool@(true|false)$/';

$const = '/(?:^bool@(true|false)$|^int@(\+|\-)?\d+$|^string@(?:[^\s#\\\\]|\\\\\d{3})*$|^nil@nil$)/';
$const_without_delimeter = '(?:^bool@(true|false)$|^int@(\+|\-)?\d+$|^string@(?:[^\s#\\\\]|\\\\\d{3})*$|^nil@nil$)';
$identifier_special_chars = '_\-\$&%\*!\?';
$identifier = '[[:alpha:]' . $identifier_special_chars . '][[:alnum:]' . $identifier_special_chars . ']*';
$var = '/^((?:GF|LF|TF)@' . $identifier . ')$/';
$var_without_delimeter = '((?:GF|LF|TF)@' . $identifier . ')';
$symb = '/^(?:' . $const_without_delimeter . '|' . $var_without_delimeter . ')$/';
$label = '/^' . $identifier . '$/';
$type = '/^(?:bool|int|string|nil)$/';

$header = false;

while (($line = fgets(STDIN)) !== false){
    //kontrola komentáře
    if (preg_match($comment, $line))
        continue;
    //kontrola headeru
    elseif ($header === false){
        if (preg_match('/^\.IPPcode23' . $inline_comment . "$/", trim($line))){
            $header = true;
            continue;
        }
        else {
            fwrite(STDERR, "Chybějící nebo chybná hlavička.\n");
            exit(WRONG_HEADER_ERROR);
        }
    }  
    else {
        //oddělení komentáře na konci
        $nocomments = explode("#", $line);
        //rozdělení operandů a instrukce do pole $parsedline
        $parsedline = explode(" ", $nocomments[0]);
        //začátek syntaktické analýzy
        $operand_index = 0;
        $capitalized_instruction = trim(strtoupper($parsedline[0]));
        //kontrola existence instrukce
        if (isset($instructions[$capitalized_instruction])){
            //přidáme zápis do xml
            xmlwriter_start_element($xw, "instruction"); //začátek bloku jedné instrukce
            xmlwriter_start_attribute($xw, "order");
            xmlwriter_text($xw, strval($order++));
            xmlwriter_end_attribute($xw);
            xmlwriter_start_attribute($xw, "opcode");
            xmlwriter_text($xw, $capitalized_instruction);
            xmlwriter_end_attribute($xw);
            $operand_count = 0;
            //spočítáme počet operandů
            foreach ($instructions[$capitalized_instruction] as $instruction => $operand)
                $operand_count++;
            //spočítáme prázdné "operandy" nebo "operandy" jako komentáře
            $delete_blank = 0;
            foreach ($parsedline as $word)
                if (trim($word) === ""){
                    $delete_blank++;
                }
            //vstupní počet operandů je větší, než je třeba
            if ($operand_count < count($parsedline)-1-$delete_blank){
                FWRITE(STDERR, "Neznámý nebo chybný operační kód.\n");
                exit(OTHER_PHPPARSE_ERROR);
            }
            foreach ($instructions[$capitalized_instruction] as $instruction => $operand){
                $operand_index++;
                $fix_argument_number = 0;
                //mimo pole => nedostatek argumentů
                if ($operand_index < count($parsedline)){
                    //odstranění případných prázdných prvků pole, poté se musí odečíst od předávaného čisla argumentu do XML
                    while (trim($parsedline[$operand_index]) === ""){
                        if ($operand_index+1 >= count($parsedline)){
                            break;
                        }
                        $operand_index++;
                        $fix_argument_number++;
                    }
                    $parsedline[$operand_index] = trim($parsedline[$operand_index]);
                }
                else{
                    FWRITE(STDERR, "Neznámý nebo chybný operační kód.\n");
                    exit(OTHER_PHPPARSE_ERROR);
                };
                switch($operand){
                    case 'label':
                        //název labelu nesmí být symbol např int@42 nebo GF@blabla
                        if (preg_match($symb, $parsedline[$operand_index])){
                            FWRITE(STDERR, "Neznámý nebo chybný operační kód.\n");
                            exit(OTHER_PHPPARSE_ERROR);
                        }
                        elseif (preg_match($label, $parsedline[$operand_index])){
                            add_arg($parsedline[$operand_index], 'label', $operand_index-$fix_argument_number);
                            break;
                        }
                        else{                           
                            FWRITE(STDERR, "Jiná syntaktická chyba.\n");
                            exit(OTHER_PHPPARSE_ERROR);
                        }
                    case 'var':
                        if (preg_match($var, $parsedline[$operand_index])){
                            add_arg($parsedline[$operand_index], 'var', $operand_index-$fix_argument_number);
                            break;
                        }
                        else{
                            FWRITE(STDERR, "Jiná syntaktická chyba.\n");
                            exit(OTHER_PHPPARSE_ERROR);
                        }
                    case 'symb':
                        if (preg_match($symb, $parsedline[$operand_index])){
                            if (preg_match($var, $parsedline[$operand_index])){
                                add_arg($parsedline[$operand_index], 'var', $operand_index-$fix_argument_number);
                            }
                            else{ //je to konstanta, je třeba rozlišit string/int atd.
                                if (preg_match($type_nil, $parsedline[$operand_index])){
                                    $argument = explode("@", $parsedline[$operand_index]);
                                    add_arg($argument[1], 'nil', $operand_index-$fix_argument_number);
                                }
                                elseif (preg_match($type_bool, $parsedline[$operand_index])){
                                    $argument = explode("@", $parsedline[$operand_index]);
                                    add_arg($argument[1], 'bool', $operand_index-$fix_argument_number);
                                }
                                elseif (preg_match($type_string, $parsedline[$operand_index])){
                                    $argument = explode("@", $parsedline[$operand_index]);
                                    //ošetření případu, že i ve stringu je znak @
                                    $iter = 2;
                                    while ($argument[$iter] !== null){
                                        $argument[1] .= "@" . $argument[$iter];
                                        $iter++;
                                    }
                                    add_arg($argument[1], 'string', $operand_index-$fix_argument_number);
                                }
                                elseif (preg_match($type_int, $parsedline[$operand_index])){
                                    $argument = explode("@", $parsedline[$operand_index]);
                                    add_arg($argument[1], 'int', $operand_index-$fix_argument_number);
                                }
                            }
                            break;
                        }
                        else{
                            FWRITE(STDERR, "Jiná syntaktická chyba.\n");
                            exit(OTHER_PHPPARSE_ERROR);
                        }
                    case 'type':
                        if (preg_match($type, $parsedline[$operand_index])){
                            add_arg($parsedline[$operand_index], 'type', $operand_index-$fix_argument_number);
                            break;
                        }
                        else{
                            FWRITE(STDERR, "Jiná syntaktická chyba.\n");
                            exit(OTHER_PHPPARSE_ERROR);
                        }
                    default:
                        FWRITE(STDERR, "Jiná syntaktická chyba.\n");
                        exit(OTHER_PHPPARSE_ERROR);
                }
            }
        }
        else{
            FWRITE(STDERR, "Neznámý nebo chybný operační kód.\n");
            exit(WRONG_CODE_ERROR);
        }
        xmlwriter_end_element($xw); //konec bloku jedné instrukce
    }
}

xmlwriter_end_element($xw); //konec bloku programu
xmlwriter_end_document($xw);
echo xmlwriter_output_memory($xw);

exit(SUCCESS);
?>