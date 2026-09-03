<?php

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/controllers/BookController.php";
require_once __DIR__ . "/gateways/BookGateway.php";
require_once __DIR__ . "/controllers/AuthorController.php";
require_once __DIR__ . "/gateways/AuthorGateway.php";
require_once __DIR__ . "/controllers/GenreController.php";
require_once __DIR__ . "/gateways/GenreGateway.php";
require_once __DIR__ . "/errors/ErrorHandler.php";

header("Content-Type: application/json");

$method = $_SERVER["REQUEST_METHOD"];

$path = $_GET["path"] ?? "";

$parts = explode("/", trim($path, "/"));

$resources = $parts[0] ?? null;
$id = $parts[1] ?? null;

$bookGateway = new BookGateway($pdo);
$bookController = new BookController($bookGateway);
$authorGateway = new AuthorGateway($pdo);
$authorController = new AuthorController($authorGateway);
$genreGateway = new GenreGateway($pdo);
$genreController = new GenreController($genreGateway);


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

  ErrorHandler::methodNotAllowed("Method not allowed for this endpoint");
}

if($resources === "authors") {
  if($method === "GET" && $id === null) {
    $authorController->getAll();
    exit;
  }

  if($method === "GET" && $id !== null) {
    $authorController->getById((int) $id);
    exit;
  }

  if($method === "POST" && $id === null) {
    $authorController->create();
    exit;
  }

  if($method === "PATCH" && $id !== null) {
    $authorController->update((int) $id);
    exit;
  }

  if($method === "DELETE" && $id !== null) {
    $authorController->delete((int) $id);
    exit;
  }

  ErrorHandler::methodNotAllowed("Method not allowed for this endpoint");
}

if($resources === "genres") {
  if($method === "GET" && $id === null) {
    $genreController->getAll();
    exit;
  }

  if($method === "GET" && $id !== null) {
    $genreController->getById((int) $id);
    exit;
  }

  if($method === "POST" && $id === null) {
    $genreController->create();
    exit;
  }

  if($method === "PATCH" && $id !== null) {
    $genreController->update((int) $id);
    exit;
  }

  if($method === "DELETE" && $id !== null) {
    $genreController->delete((int) $id);
    exit;
  }

  ErrorHandler::methodNotAllowed("Method not allowed for this endpoint");
}


ErrorHandler::notFound("Endpoint not found");