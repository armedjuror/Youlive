<?php

class Database
{
    // Get Type char for prepared statement
    public static function get_type_char($var): string
    {
        if (is_string($var)) $type_string = 's';
        else if (is_int($var)) $type_string = 'i';
        else if (is_float($var)) $type_string = 'd';
        else $type_string = 'b';
        return $type_string;
    }
}