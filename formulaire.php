<?
$type = $_GET;

if ($type) {

    filter_var($type['todo-add'], FILTER_SANITIZE_STRING);

}