<?php
require_once 'ini.php';
require_once 'MyIBlock.php';

$request = $_POST['request'];

$iBlock = new MyIBlock($request['IBLOCK_ID']);

$iBlock->addElements($request);
?>


