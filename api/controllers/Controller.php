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

  protected function formatBook(array $book): array
  {
    return [
      "id" => $book["id"],
      "title" => $book["title"],
      "description" => $book["description"],
      "published_year" => $book["published_year"],
      "isbn" => $book["isbn"],
      "pages" => $book["pages"],
      "cover_url" => $book["cover_url"],

      "author" => [
        "id" => $book["author_id"],
        "name" => $book["author_name"],
        "url" => "/api/authors/" . $book["author_id"]
      ],

      "genre" => [
        "id" => $book["genre_id"],
        "name" => $book["genre_name"],
        "url" => "/api/genres/" . $book["genre_id"]

      ],
    ];
  }
}