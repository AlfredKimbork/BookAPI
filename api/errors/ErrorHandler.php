<?php
class ErrorHandler
{
  public static function badRequest(string $message, array $fields = []): void
  {
    http_response_code(400);

    $response = [
      "error" => "Bad Request",
      "message" => $message,
    ];

    if (!empty($fields)) {
      $response["fields"] = $fields;
    }

    echo json_encode($response);

    exit;
  }

  public static function notFound(string $message): void
  {
    http_response_code(404);
    echo json_encode([
      "error" => $message,
    ]);

    exit;
  }

  public static function methodNotAllowed(string $message): void
  {
    http_response_code(405);
    echo json_encode([
      "error" => "Method not allowed",
      "message" => $message,
    ]);

    exit;
  }


  public static function conflict(string $message): void
  {
    http_response_code(409);
    echo json_encode([
      "error" => "Conflict",
      "message" => $message
    ]);

    exit;
  }

  public static function serverError(): void
  {
    http_response_code(500);
    echo json_encode([
      "error" => "Internal Server Error",
      "message" => "An unexpected error occurred on the server.",
    ]);

    exit;
  }
}
