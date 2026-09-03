<?php
require_once __DIR__ . "/Controller.php";
require_once __DIR__ . "/../gateways/GenreGateway.php";
require_once __DIR__ . "/../errors/ErrorHandler.php";

class GenreController extends Controller
{
  private GenreGateway $gateway;

  public function __construct(GenreGateway $gateway)
  {
    $this->gateway = $gateway;
  }

  public function getAll(): void
  {
    $genres = $this->gateway->getAll();

    echo json_encode($genres);
  }

  public function getById(int $id): void
  {
    $genre = $this->gateway->getById($id);

    // Return an error if the requested genre does not exist
    if (!$genre) ErrorHandler::notFound("Genre not found");

    // Format all books belonging to the genre
    $books = [];

    foreach ($genre["books"] as $book) {
      $books[] = $this->formatBook($book);
    }

    $genre["books"] = $books;

    echo json_encode($genre);
  }

  public function create(): void
  {
    $data = json_decode(
      file_get_contents("php://input"),
      true
    );

    // Make sure the request contains valid JSON
    if ($data === null) {
      ErrorHandler::badRequest("Request body must contain valid JSON. See the POST /genres documentation for the expected format.");
    }

    $errors = [];

    // Validate required fields
    if (!isset($data["name"]) || trim($data["name"]) === "") $errors["name"] = "Name is required";
    if (!isset($data["description"]) || trim($data["description"]) === "") $errors["description"] = "Description is required";
    
    // Return validation errors before creating the genre
    if (!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    try {
      $id = $this->gateway->create($data);

      // Get the newly created genre so the complete object can be returned
      $genre = $this->gateway->getById($id);

      http_response_code(201);
      echo json_encode($genre);
    } catch (PDOException $e) {
      ErrorHandler::serverError();
    }
  }

  public function update(int $id): void
  {
    $data = json_decode(
      file_get_contents("php://input"),
      true
    );

    // Make sure the request contains valid JSON
    if ($data === null) ErrorHandler::badRequest("Request body must contain valid JSON. See the PATCH /genres/{id} documentation for the expected format.");

    // PATCH must contain at least one field
    if (!is_array($data) || empty($data)) ErrorHandler::badRequest("Request body must contain at least one field to update.");

    // Check that the genre exists before updating
    $genre = $this->gateway->getById($id);

    if (!$genre) ErrorHandler::notFound("Genre not found");

    $errors = [];

    // Only these fields can be changed
    $allowedFields = [
      "name",
      "description"
    ];

    // Check if the request contains fields that cannot be updated
    foreach ($data as $field => $_) {
      if (!in_array($field, $allowedFields, true)) {
        $errors[$field] = "This field cannot be updated";
      }
    }

    // Validate fields if they are included in the PATCH request
    if (isset($data["name"]) && trim($data["name"]) === "") $errors["name"] = "Name cannot be empty";
    if (isset($data["description"]) && trim($data["description"]) === "") $errors["description"] = "Description cannot be empty";

    // Return validation errors before updating
    if (!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    try {
      $genre = $this->gateway->update($id, $data);

      http_response_code(200);

      echo json_encode($genre);
    } 
    
    catch (PDOException $e) {
      ErrorHandler::serverError();
    }
  }

  public function delete(int $id): void
  {
    try {
      $genre = $this->gateway->delete($id);

      if (!$genre) ErrorHandler::notFound("Genre not found");

      http_response_code(200);
      echo json_encode([
        "message" => "Genre deleted successfully",
        "deleted_genre" => [
          "id" => $genre["id"],
          "name" => $genre["name"],
        ],
      ]);
      exit;
    } 
    
    catch (PDOException $e) {
      // 1451 means the genre is still referenced by books
      if ($e->errorInfo[1] === 1451) {
        ErrorHandler::conflict("Genre cannot be deleted because it has books associated with it");
      }

      ErrorHandler::serverError();
    }
  }
}