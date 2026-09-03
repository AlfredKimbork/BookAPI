<?php
class KeyController
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  public function create(): void
  {
    $data = json_decode(
      file_get_contents("php://input"), 
      true
    );

    if(!is_array($data)) ErrorHandler::badRequest("Request must be valid JSON");

    $name = trim($data["name"] ?? "");
    if($name === "") ErrorHandler::badRequest("Name is required");
    if(strlen($name) > 100) ErrorHandler::badRequest("Name cannot be longer than 100 characters");

    $apiKey = bin2hex(random_bytes(32));
    $keyHash = hash("sha256", $apiKey);

    $stmt = $this->pdo->prepare("
      INSERT INTO api_keys (name, key_hash, can_read, can_write)
      VALUES (:name, :key_hash, TRUE, FALSE)
    ");

    $stmt->bindValue(":name", $name, PDO::PARAM_STR);
    $stmt->bindValue(":key_hash", $keyHash, PDO::PARAM_STR);

    $stmt->execute();

    http_response_code(201);
    echo json_encode([
      "message" => "API key created",
      "name" => $name,
      "api_key" => $apiKey
    ]);
  }
}