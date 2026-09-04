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

    if(json_last_error() !== JSON_ERROR_NONE) ErrorHandler::badRequest("Request body must contain valid JSON.");
    if(!is_array($data)) ErrorHandler::badRequest("Request body must be a JSON object.");

    $name = trim($data["name"] ?? "");
    if($name === "" || !is_string($name)) ErrorHandler::badRequest("Name is required and must be a string");
    if(strlen($name) > 100) ErrorHandler::badRequest("Name cannot be longer than 100 characters");

    $canWrite = $data["can_write"] ?? false;
    if(!is_bool($canWrite)) ErrorHandler::badRequest("Can write must be a boolean");

    $apiKey = bin2hex(random_bytes(32));
    $keyHash = hash("sha256", $apiKey);

    $stmt = $this->pdo->prepare("
      INSERT INTO api_keys (name, key_hash, can_read, can_write)
      VALUES (:name, :key_hash, TRUE, :can_write)
    ");

    $stmt->bindValue(":name", $name, PDO::PARAM_STR);
    $stmt->bindValue(":key_hash", $keyHash, PDO::PARAM_STR);
    $stmt->bindValue(":can_write", $canWrite, PDO::PARAM_BOOL);

    try {
      $stmt->execute();

      http_response_code(201);
      header("Cache-Control: no-store");
      echo json_encode([
        "message" => "API key created",
        "name" => $name,
        "api_key" => $apiKey
      ]);
    }

    catch(PDOException $e) {
      error_log("API key creation failed: " . $e->getMessage());
      ErrorHandler::serverError();
    }

  }
}