<?php
require_once '../tool/projeqtor.php';

$listId = explode('_', $_REQUEST['listId']);

$listName = array();

foreach ($listId as $id) {
    Security::checkValidId($id);
    $bf = new BusinessFeature($id);
    $product = new Product($bf->idProduct);
    $listName[$id] = $product->name;
}

echo json_encode($listName);

?>