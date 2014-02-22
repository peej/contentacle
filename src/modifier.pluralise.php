<?php

function smarty_modifier_pluralise($string, $number = 0, $postfix = 's') {
    if ($number == 1) {
        return $string;
    }
    return $string.$postfix;
}