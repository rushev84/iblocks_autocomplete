<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");

function dump(...$data)
{
    echo '<pre>';
    print_r(...$data);
    echo '</pre>';
    die();
}