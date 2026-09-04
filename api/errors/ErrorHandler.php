<?php
class ErrorHandler
{
  public static function options(string $methods): void
  {
    http_response_code(204);
    header("Allow: " . $methods);

    exit;
  }

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

  public static function unauthorized(string $message): void
  {
    http_response_code(401);
    echo json_encode([
      "error" => "Unauthorized",
      "message" => $message
    ]);
    
    exit;
  }

  public static function forbidden(string $message): void 
  {
    http_response_code(403);
    echo json_encode([
      "error" => "Forbidden",
      "message" => $message
    ]);

    exit;
  }

  public static function notFound(string $message): void
  {
    http_response_code(404);
    echo json_encode([
      "error" => "Not found",
      "message" => $message
    ]);

    exit;
  }

  public static function methodNotAllowed(string $message, string $allowedMethods = ""): void
  {
    http_response_code(405);
    if(!empty($allowedMethods)) header("Allow: " . $allowedMethods);

    echo json_encode([
      "error" => "Method not allowed",
      "message" => $message
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

  public static function unsupportedMediaType(string $message): void
  {
    http_response_code(415);
    echo json_encode([
      "error" => "Unsupported Media Type",
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
