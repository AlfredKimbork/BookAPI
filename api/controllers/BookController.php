<?php
require_once __DIR__ . "/../gateways/BookGateway.php";
require_once __DIR__ . "/../errors/ErrorHandler.php";

class BookController
{
  private BookGateway $gateway;

  private function formatBook(array $book): array
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
      ],

      "genre" => [
        "id" => $book["genre_id"],
        "name" => $book["genre_name"],
      ],
    ];
  }

  public function __construct(BookGateway $gateway)
  {
    $this->gateway = $gateway;
  }

  public function getAll(): void
  {
    $books = $this->gateway->getAll();

    $books = array_map(
      [$this, "formatBook"],
      $books
    );

    echo json_encode($books);
  }

  public function getById(int $id): void
  {
    $book = $this->gateway->getById($id);

    if (!$book) ErrorHandler::notFound("Book not found");

    echo json_encode($this->formatBook($book));
  }

  public function create(): void
  {
    $data = json_decode(
      file_get_contents("php://input"),
      true
    );

    if($data === null) {
      ErrorHandler::badRequest("Request body must contain valid JSON. See the POST /books documentation for the expected format.");
    }

    $errors = [];

    if(!isset($data["title"]) || trim($data["title"]) === "") $errors["title"] = "Title is required";
    if(!isset($data["description"]) || trim($data["description"]) === "") $errors["description"] = "Description is required";
    if(!isset($data["published_year"]) || (!is_int($data["published_year"]) || $data["published_year"] <= 0)) $errors["published_year"] = "Published year must be a positive integer";
    if(!isset($data["isbn"]) || trim($data["isbn"]) === "") $errors["isbn"] = "ISBN is required";
    if(!isset($data["pages"]) || (!is_int($data["pages"]) || $data["pages"] <= 0)) $errors["pages"] = "Pages must be a positive integer";
    if(!isset($data["author_id"]) || (!is_int($data["author_id"]) || $data["author_id"] <= 0)) $errors["author_id"] = "Author ID must be a positive integer";
    if(!isset($data["genre_id"]) || (!is_int($data["genre_id"]) || $data["genre_id"] <= 0)) $errors["genre_id"] = "Genre ID must be a positive integer";
    if(!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    if(!$this->gateway->authorExists($data["author_id"])) $errors["author_id"] = "Author does not exist";
    if(!$this->gateway->genreExists($data["genre_id"])) $errors["genre_id"] = "Genre does not exist";
    if(!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    try {
      $id = $this->gateway->create($data);
  
      $book = $this->gateway->getById($id);
  
      http_response_code(201);
      echo json_encode(
        $this->formatBook($book)
      );
    }
    catch(PDOException $e) {
      if($e->errorInfo[1] === 1062){
        ErrorHandler::conflict("A book with this ISBN already exists");
      }
      ErrorHandler::serverError();
    }
  }

  public function update(int $id): void
  {
    $data = json_decode(
      file_get_contents("php://input"),
      true
    );

    if($data === null) ErrorHandler::badRequest("Request body must contain valid JSON. See the PATCH /books/{id} documentation for the expected format.");
    if(!is_array($data) || empty($data)) ErrorHandler::badRequest("Request body must contain at least one field to update.");

    $book = $this->gateway->getById($id);

    if(!$book) ErrorHandler::notFound("Book not found");

    $errors = [];

    $allowedFields = [
      "title",
      "description",
      "published_year",
      "isbn",
      "pages",
      "cover_url",
      "author_id",
      "genre_id",
    ];

    foreach($data as $field => $value) {
      if(!in_array($field, $allowedFields, true)) $errors[$field] = "This field cannot be updated";
    }

    if(isset($data["title"]) && trim($data["title"]) === "") $errors["title"] = "Title cannot be empty";
    if(isset($data["description"]) && trim($data["description"]) === "") $errors["description"] = "Title cannot be empty";
    if(isset($data["description"]) && trim($data["description"]) === "") $errors["description"] = "Description cannot be empty";
    if(isset($data["published_year"]) && (!is_int($data["published_year"]) || $data["published_year"] <= 0)) $errors["published_year"] = "Published year must be a positive integer";
    if(isset($data["isbn"]) && trim($data["isbn"]) === "") $errors["isbn"] = "ISBN cannot be empty";
    if(isset($data["pages"]) && (!is_int($data["pages"]) || $data["pages"] <= 0)) $errors["pages"] = "Pages must be a positive integer";
    if(isset($data["author_id"]) && (!is_int($data["author_id"]) || $data["author_id"] <= 0)) $errors["author_id"] = "Author ID must be a positive integer";
    if(isset($data["genre_id"]) && (!is_int($data["genre_id"]) || $data["genre_id"] <= 0)) $errors["genre_id"] = "Genre ID must be a positive integer";
    if(!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    if(isset($data["author_id"]) && !$this->gateway->authorExists($data["author_id"])) $errors["author_id"] = "Author does not exist";
    if(isset($data["genre_id"]) && !$this->gateway->genreExists($data["genre_id"])) $errors["genre_id"] = "Genre does not exist";
    if(!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    try {
      $book = $this->gateway->update($id, $data);

      http_response_code(200);
      echo json_encode(
        $this->formatBook($book)
      );
    }

    catch(PDOException $e) {
      if($e->errorInfo[1] === 1062){
        ErrorHandler::conflict("A book with this ISBN already exists");
      }
      ErrorHandler::serverError();
    }
  }

  public function delete(int $id): void
  {
    try {
      $book = $this->gateway->delete($id);

      if(!$book) ErrorHandler::notFound("Book not found");

      http_response_code(200);
      echo json_encode([
        "message" => "Book deleted successfully",
        "deleted_book" => [
          "id" => $book["id"],
          "title" => $book["title"],
        ],
      ]);

      exit;
    }

    catch(PDOException $e) {
      ErrorHandler::serverError();
    }
  }
}