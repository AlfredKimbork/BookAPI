<?php
class ApiKey
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  public function authenticate(): array 
  {
    $apiKey = $_SERVER["HTTP_X_API_KEY"] ?? null;

    if($apiKey === null) ErrorHandler::unauthorized("API key is required");

    $keyHash = hash("sha256", $apiKey);

    $stmt = $this->pdo->prepare("
      SELECT *
      FROM api_keys
      WHERE key_hash = ?
      AND active = true
    ");

    $stmt->execute([$keyHash]);

    $key = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$key) ErrorHandler::unauthorized("Invalid API key");

    return $key;
  }

  public function authorize(array $key, string $method): void
  {
    $readMethod = ["GET"];
    $writeMethod = ["POST", "PATCH", "DELETE"];

    if(in_array($method, $readMethod) && !$key["can_read"]) ErrorHandler::forbidden("This API key does not have read permissions");
    if(in_array($method, $writeMethod) && !$key["can_write"]) ErrorHandler::forbidden("This API key does not have write permissions");

  }
}