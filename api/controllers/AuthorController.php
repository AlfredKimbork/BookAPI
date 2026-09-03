<?php
require_once __DIR__ . "/Controller.php";
require_once __DIR__ . "/../gateways/AuthorGateway.php";
require_once __DIR__ . "/../errors/ErrorHandler.php";

class AuthorController extends Controller
{
  private AuthorGateway $gateway;

  public function __construct(AuthorGateway $gateway)
  {
    $this->gateway = $gateway;
  }

  public function getAll(): void
  {
    $authors = $this->gateway->getAll();

    echo json_encode($authors);
  }

  public function getById(int $id): void
  {
    $author = $this->gateway->getById($id);

    if (!$author) ErrorHandler::notFound("Author not found");

    $books = [];

    foreach ($author["books"] as $book) {
      $books[] = $this->formatBook($book);
    }

    $author["books"] = $books;

    echo json_encode($author);
  }

  public function create(): void
  {
    $data = json_decode(
      file_get_contents("php://input"),
      true
    );

    if ($data === null) {
      ErrorHandler::badRequest("Request body must contain valid JSON. See the POST /authors documentation for the expected format.");
    }

    $errors = [];

    if (!isset($data["name"]) || trim($data["name"]) === "") $errors["name"] = "Name is required";
    if (!isset($data["biography"]) || trim($data["biography"]) === "") $errors["biography"] = "Biography is required";
    if (isset($data["birth_date"]) && !DateTime::createFromFormat("Y-m-d", $data["birth_date"])) $errors["birth_date"] = "Birth date must be a valid date in YYYY-MM-DD format";
    if (isset($data["death_date"]) && !DateTime::createFromFormat("Y-m-d", $data["death_date"])) $errors["death_date"] = "Death date must be a valid date in YYYY-MM-DD format";
    if (isset($data["birth_date"]) && isset($data["death_date"]) && $data["death_date"] <= $data["birth_date"]) $errors["death_date"] = "Death date must be later than birth date";

    if (!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    try {
      $id = $this->gateway->create($data);

      $author = $this->gateway->getById($id);

      http_response_code(201);
      echo json_encode($author);
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

    if ($data === null) ErrorHandler::badRequest("Request body must contain valid JSON. See the PATCH /authors/{id} documentation for the expected format.");
    if (!is_array($data) || empty($data)) ErrorHandler::badRequest("Request body must contain at least one field to update.");

    $author = $this->gateway->getById($id);

    if (!$author) ErrorHandler::notFound("Author not found");

    $errors = [];

    $allowedFields = [
      "name",
      "birth_date",
      "death_date",
      "biography",
    ];

    foreach ($data as $field => $_) {
      if (!in_array($field, $allowedFields, true)) {
        $errors[$field] = "This field cannot be updated";
      }
    }

    if (isset($data["name"]) && trim($data["name"]) === "") $errors["name"] = "Name cannot be empty";
    if (isset($data["biography"]) && trim($data["biography"]) === "") $errors["biography"] = "Biography cannot be empty";
    if (isset($data["birth_date"]) && !DateTime::createFromFormat("Y-m-d", $data["birth_date"])) $errors["birth_date"] = "Birth date must be a valid date in YYYY-MM-DD format";
    if (isset($data["death_date"]) && !DateTime::createFromFormat("Y-m-d", $data["death_date"])) $errors["death_date"] = "Death date must be a valid date in YYYY-MM-DD format";
    if (!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    $birthDate = $data["birth_date"] ?? $author["birth_date"];
    $deathDate = $data["death_date"] ?? $author["death_date"];

    if ($deathDate !== null && $birthDate !== null && $deathDate <= $birthDate) $errors["death_date"] = "Death date must be later than birth date";

    if (!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid",$errors);

    try {
      $author = $this->gateway->update($id, $data);

      http_response_code(200);

      echo json_encode($author);
    } 
    
    catch (PDOException $e) {
      ErrorHandler::serverError();
    }
  }

  public function delete(int $id): void
  {
    try {
      $author = $this->gateway->delete($id);

      if (!$author) ErrorHandler::notFound("Author not found");

      http_response_code(200);
      echo json_encode([
        "message" => "Author deleted successfully",
        "deleted_author" => [
          "id" => $author["id"],
          "name" => $author["name"],
        ],
      ]);
      exit;
    } 
    
    catch (PDOException $e) {
      ErrorHandler::serverError();
    }
  }
}
