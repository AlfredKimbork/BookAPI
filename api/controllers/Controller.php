<?php
class Controller
{
  protected function getJsonBody(): array
  {
    $contentType = $_SERVER["CONTENT_TYPE"] ?? "";

    if (!str_starts_with(strtolower(($contentType)), "application/json")) ErrorHandler::unsupportedMediaType("Content-Type must be application/json");
    $data = json_decode(
      file_get_contents("php://input"),
      true
    );

    if(json_last_error() !== JSON_ERROR_NONE) ErrorHandler::badRequest("Request body must contain valid JSON.");
    if(!is_array($data)) ErrorHandler::badRequest("Request body must be a JSON object.");

    return $data;
  }

  protected function respond(array $data, int $status = 200, ?string $location = null): void
  {
    if($location === "") $location === null;
    http_response_code($status);
    if($location) header($location);
    if($_SERVER["REQUEST_METHOD"] !== "HEAD") echo json_encode($data);

    exit;
  }

  protected function formatBooks(array $book): array {
    return [
      "id" => $book["id"],
      "title" => $book["title"],
      "author" => $book["author_name"],
      "genre" => $book["genre_name"],
      "cover_url" => $book["cover_url"],
      "url" => "/api/books/" . $book["id"]
    ];
  }
}