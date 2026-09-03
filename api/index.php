<?php

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/controllers/BookController.php";
require_once __DIR__ . "/gateways/BookGateway.php";
require_once __DIR__ . "/errors/ErrorHandler.php";

header("Content-Type: application/json");

$method = $_SERVER["REQUEST_METHOD"];

$path = $_GET["path"] ?? "";

$parts = explode("/", trim($path, "/"));

$resources = $parts[0] ?? null;
$id = $parts[1] ?? null;

$bookGateway = new BookGateway($pdo);
$bookController = new BookController($bookGateway);


if($resources === "books") {

  if($method === "GET" && $id === null) {
    $bookController->getAll();
    exit;
  }

  if($method === "GET" && $id !== null) {
    $bookController->getById((int) $id);
    exit;
  }

  if($method === "POST" && $id === null) {
    $bookController->create();
    exit;
  }

  if($method === "PATCH" && $id !== null) {
    $bookController->update((int) $id);
    exit;
  }

  if($method === "DELETE" && $id !== null) {
    $bookController->delete((int) $id);
    exit;
  }
}


ErrorHandler::notFound("Endpoint not found");