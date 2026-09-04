<?php

header("Content-Type: application/json");
set_exception_handler(function (Throwable $e) {
  error_log($e->getMessage());

  http_response_code(500);
  echo json_encode([
    "error" => "Internal Server Error",
    "message" => "An unexpected error occurred on the server."
  ]);

  exit;
});

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/controllers/BookController.php";
require_once __DIR__ . "/gateways/BookGateway.php";
require_once __DIR__ . "/controllers/AuthorController.php";
require_once __DIR__ . "/gateways/AuthorGateway.php";
require_once __DIR__ . "/controllers/GenreController.php";
require_once __DIR__ . "/gateways/GenreGateway.php";
require_once __DIR__ . "/controllers/KeyController.php";
require_once __DIR__ . "/auth/ApiKey.php";
require_once __DIR__ . "/errors/ErrorHandler.php";

$bookGateway = new BookGateway($pdo);
$bookController = new BookController($bookGateway);
$authorGateway = new AuthorGateway($pdo);
$authorController = new AuthorController($authorGateway);
$genreGateway = new GenreGateway($pdo);
$genreController = new GenreController($genreGateway);
$keyController = new KeyController($pdo);

$method = $_SERVER["REQUEST_METHOD"];
$path = $_GET["path"] ?? "";
$parts = explode("/", trim($path, "/"));

$resources = $parts[0] ?? null;
$id = $parts[1] ?? null;
if ($id !== null && !ctype_digit($id) || $id !== null && (int) $id < 1) ErrorHandler::badRequest("ID must be a positive integer");
if (count($parts) > 2) ErrorHandler::notFound("Endpoint not found");

// keys is public
if ($resources === "keys") {
  if ($method === "POST" && $id === null) {
    $keyController->create();
    exit;
  }

  ErrorHandler::methodNotAllowed("Method not allowed for /keys", "POST");
}

// OPTIONS are public
if($method === "OPTIONS") {
  if($resources === "books"){
    if($id === null) ErrorHandler::options("GET, HEAD, POST, OPTIONS");
    if($id !== null) ErrorHandler::options("GET, HEAD, PATCH, DELETE, OPTIONS");
  }

  if($resources === "authors"){
    if($id === null) ErrorHandler::options("GET, HEAD, POST, OPTIONS");
    if($id !== null) ErrorHandler::options("GET, HEAD, PATCH, DELETE, OPTIONS");
  }

  if($resources === "genres"){
    if($id === null) ErrorHandler::options("GET, HEAD, POST, OPTIONS");
    if($id !== null) ErrorHandler::options("GET, HEAD, PATCH, DELETE, OPTIONS");
  }
  if($resources === "keys" && $id === null) ErrorHandler::options("POST, OPTIONS");

  ErrorHandler::notFound("Endpoint not found");
}

$apiKey = new ApiKey($pdo);
$key = $apiKey->authenticate();
$apiKey->authorize($key, $method);

if ($resources === "books") {
  if ($id === null) {
    if ($method === "GET" || $method === "HEAD") {
      $bookController->getAll();
      exit;
    }

    if ($method === "POST") {
      $bookController->create();
      exit;
    }

    ErrorHandler::methodNotAllowed("Method not allowed for this endpoint", "GET, HEAD, POST, OPTIONS");
  }

  if ($id !== null) {
    if ($method === "GET" || $method === "HEAD") {
      $bookController->getById((int) $id);
      exit;
    }

    if ($method === "PATCH") {
      $bookController->update((int) $id);
      exit;
    }

    if ($method === "DELETE") {
      $bookController->delete((int) $id);
      exit;
    }
  }

  ErrorHandler::methodNotAllowed("Method not allowed for this endpoint", "GET, PATCH, DELETE, OPTIONS");
}

if ($resources === "authors") {
  if ($id === null) {
    if ($method === "GET") {
      $authorController->getAll();
      exit;
    }

    if ($method === "POST") {
      $authorController->create();
      exit;
    }

    ErrorHandler::methodNotAllowed("Method not allowed for this endpoint", "GET, HEAD, POST, OPTIONS");
  }

  if ($id !== null) {
    if ($method === "GET") {
      $authorController->getById((int) $id);
      exit;
    }

    if ($method === "PATCH") {
      $authorController->update((int) $id);
      exit;
    }

    if ($method === "DELETE") {
      $authorController->delete((int) $id);
      exit;
    }

    ErrorHandler::methodNotAllowed("Method not allowed for this endpoint", "GET, PATCH, DELETE, OPTIONS");
  }

}

if ($resources === "genres") {
  if ($id === null) {
    if ($method === "GET") {
      $genreController->getAll();
      exit;
    }

    if ($method === "POST") {
      $genreController->create();
      exit;
    }

    ErrorHandler::methodNotAllowed("Method not allowed for this endpoint", "GET, HEAD, POST, OPTIONS");
  }
  if ($id !== null) {
    if ($method === "GET") {
      $genreController->getById((int) $id);
      exit;
    }

    if ($method === "PATCH") {
      $genreController->update((int) $id);
      exit;
    }

    if ($method === "DELETE") {
      $genreController->delete((int) $id);
      exit;
    }
    ErrorHandler::methodNotAllowed("Method not allowed for this endpoint", "GET, POST, DELETE, OPTIONS");
  }
}


ErrorHandler::notFound("Endpoint not found");
