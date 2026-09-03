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

  // Checks whether a date is valid and follows the YYYY-MM-DD format exactly
  private function validDate(string $date): bool
  {
    $parsed = DateTime::createFromFormat("Y-m-d", $date);

    return $parsed !== false && $parsed->format("Y-m-d") === $date;
  }

  public function getAll(): void
  {
    $page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;
    $limit = isset($_GET["limit"]) ? (int) $_GET["limit"] : 10;
    $search = isset($_GET["search"]) ? trim($_GET["search"]) : null;
    
    // Page and limit must be positive integers
    if($page < 1) ErrorHandler::badRequest("Page must be a positive integer");
    if($limit < 1) ErrorHandler::badRequest("Limit must be a positive integer");

    // Empty search should behave the same as no search
    if($search === "") $search = null;

    // Calculate how many books should be skipped
    $offset = ($page - 1) * $limit;

    $authors = $this->gateway->getAll($limit, $offset, $search);

    $total = $this->gateway->count($search);

    $pages = (int) ceil($total / $limit);

    echo json_encode([
      "data" => $authors,
      "pagination" => [
        "page" => $page,
        "limit" => $limit,
        "total" => $total,
        "pages" => $pages
      ]
    ]);
  }

  public function getById(int $id): void
  {
    $author = $this->gateway->getById($id);

    if (!$author) ErrorHandler::notFound("Author not found");

    // Format all books written by the author
    $books = [];
    foreach ($author["books"] as $book) $books[] = $this->formatBook($book);
    $author["books"] = $books;

    echo json_encode($author);
  }

  public function create(): void
  {
    $data = json_decode(
      file_get_contents("php://input"),
      true
    );

    // Make sure the request contains valid JSON
    if ($data === null) ErrorHandler::badRequest("Request body must contain valid JSON. See the POST /authors documentation for the expected format.");

    $errors = [];

    // Validate required fields
    if (!isset($data["name"]) || trim($data["name"]) === "") $errors["name"] = "Name is required";
    if (!isset($data["biography"]) || trim($data["biography"]) === "") $errors["biography"] = "Biography is required";
    if (!isset($data["birth_date"]) || !$this->validDate($data["birth_date"])) $errors["birth_date"] = "Birth date must be a valid date in YYYY-MM-DD format";

    // Death date is optional, but must be valid if provided
    if (isset($data["death_date"]) && $data["death_date"] !== null && !$this->validDate($data["death_date"])) $errors["death_date"] = "Death date must be a valid date in YYYY-MM-DD format";

    // Make sure the death date is after the birth date
    if (isset($data["birth_date"]) && isset($data["death_date"]) && $data["death_date"] !== null && $data["death_date"] <= $data["birth_date"]) $errors["death_date"] = "Death date must be later than birth date";

    // Return all validation errors at once
    if (!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    try {
      $id = $this->gateway->create($data);

      // Get the newly created author so the complete object can be returned
      $author = $this->gateway->getById($id);

      http_response_code(201);
      echo json_encode($author);
    } 
    
    catch (PDOException $e) {
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
    if ($data === null) ErrorHandler::badRequest("Request body must contain valid JSON. See the PATCH /authors/{id} documentation for the expected format.");

    // PATCH must contain at least one field
    if (!is_array($data) || empty($data)) ErrorHandler::badRequest("Request body must contain at least one field to update.");

    // Check that the author exists before updating
    $author = $this->gateway->getById($id);

    if (!$author) ErrorHandler::notFound("Author not found");

    $errors = [];

    // Only these fields can be changed
    $allowedFields = [
      "name",
      "birth_date",
      "death_date",
      "biography",
    ];

    // Check if the request contains fields that cannot be updated
    foreach ($data as $field => $_) if (!in_array($field, $allowedFields, true)) $errors[$field] = "This field cannot be updated";

    // Validate fields if they are included in the PATCH request
    if (isset($data["name"]) && trim($data["name"]) === "") $errors["name"] = "Name cannot be empty";
    if (isset($data["biography"]) && trim($data["biography"]) === "") $errors["biography"] = "Biography cannot be empty";
    if (isset($data["birth_date"]) && !$this->validDate($data["birth_date"])) $errors["birth_date"] = "Birth date must be a valid date in YYYY-MM-DD format";
    if (isset($data["death_date"]) && $data["death_date"] !== null && !$this->validDate($data["death_date"])) $errors["death_date"] = "Death date must be a valid date in YYYY-MM-DD format";

    // Return validation errors before continuing
    if (!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    // Use the new value if it was provided, otherwise use the existing value
    // array_key_exists() is used so that "death_date": null can be handled correctly
    $birthDate = array_key_exists("birth_date", $data) ? $data["birth_date"] : $author["birth_date"];
    $deathDate = array_key_exists("death_date", $data) ? $data["death_date"] : $author["death_date"];

    // Make sure the death date is after the birth date
    if ($deathDate !== null && $birthDate !== null && $deathDate <= $birthDate) $errors["death_date"] = "Death date must be later than birth date";

    // Return date comparison errors
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
      // 1451 means the author is still referenced by books
      if ($e->errorInfo[1] === 1451) {
        ErrorHandler::conflict("Author cannot be deleted because they have books associated with them");
      }

      ErrorHandler::serverError();
    }
  }
}