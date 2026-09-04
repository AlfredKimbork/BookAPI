<?php
require_once __DIR__ . "/Controller.php";
require_once __DIR__ . "/../gateways/BookGateway.php";
require_once __DIR__ . "/../errors/ErrorHandler.php";

class BookController extends Controller
{
  private BookGateway $gateway;

  public function __construct(BookGateway $gateway)
  {
    $this->gateway = $gateway;
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

    $books = $this->gateway->getAll($limit, $offset, $search);

    // Format every book into API response structure
    $books = array_map(
      [$this, "formatBook"],
      $books
    );

    $total = $this->gateway->count($search);

    $pages = (int) ceil($total / $limit);

    echo json_encode([
      "data" => $books,
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
    $book = $this->gateway->getById($id);

    if (!$book) ErrorHandler::notFound("Book not found");

    echo json_encode($this->formatBook($book));
  }

  public function create(): void
  {
    $data = $this->getJsonBody();
    
    if(json_last_error() !== JSON_ERROR_NONE) ErrorHandler::badRequest("Request body must contain valid JSON. See the PATCH /books documentation for the expected format.");
    if(!is_array($data)) ErrorHandler::badRequest("Request body must be a JSON object. See the PATCH /books documentation for the expected format.");

    $errors = [];

    if(!isset($data["title"]) || !is_string($data["title"]) || trim($data["title"]) === "") $errors["title"] = "Title is required and must be a string";
    if(!isset($data["description"]) || !is_string($data["description"]) || trim($data["description"]) === "") $errors["description"] = "Description is required and must be a string";
    if(!isset($data["published_year"]) || (!is_int($data["published_year"]) || $data["published_year"] <= 0)) $errors["published_year"] = "Published year must be a positive integer";
    if(!isset($data["isbn"]) || !is_string($data["isbn"]) || trim($data["isbn"]) === "") $errors["isbn"] = "ISBN is required and must be a string";
    if(!isset($data["pages"]) || (!is_int($data["pages"]) || $data["pages"] <= 0)) $errors["pages"] = "Pages must be a positive integer";
    if(isset($data["cover_url"]) && filter_var($data["cover_url"], FILTER_VALIDATE_URL)) $errors["book_url"] = "Book URL must be a valid URL";
    if(!isset($data["author_id"]) || (!is_int($data["author_id"]) || $data["author_id"] <= 0)) $errors["author_id"] = "Author ID must be a positive integer";
    if(!isset($data["genre_id"]) || (!is_int($data["genre_id"]) || $data["genre_id"] <= 0)) $errors["genre_id"] = "Genre ID must be a positive integer";
    if(!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    if(!$this->gateway->authorExists($data["author_id"])) $errors["author_id"] = "Author does not exist";
    if(!$this->gateway->genreExists($data["genre_id"])) $errors["genre_id"] = "Genre does not exist";
    if(!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    try {
      $id = $this->gateway->create($data);
  
      $book = $this->gateway->getById($id);

      $this->respond(
        $this->formatBook($book),
        201,
        "Location: /api/books/" . $id
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
    $data = $this->getJsonBody();

    if(json_last_error() !== JSON_ERROR_NONE) ErrorHandler::badRequest("Request body must contain valid JSON. See the PATCH /books/{id} documentation for the expected format.");
    if(!is_array($data)) ErrorHandler::badRequest("Request body must be a JSON object. See the PATCH /books/{id} documentation for the expected format.");
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

    foreach($data as $field => $_) {
      if(!in_array($field, $allowedFields, true)) $errors[$field] = "This field cannot be updated";
    }

    if(isset($data["title"]) && !is_string($data["title"]) && trim($data["title"]) === "") $errors["title"] = "Title cannot be empty";
    if(isset($data["description"]) && !is_string($data["description"]) && trim($data["description"]) === "") $errors["description"] = "Description cannot be empty";
    if(isset($data["published_year"]) && (!is_int($data["published_year"]) || $data["published_year"] <= 0)) $errors["published_year"] = "Published year must be a positive integer";
    if(isset($data["isbn"]) && !is_string($data["isbn"]) && trim($data["isbn"]) === "") $errors["isbn"] = "ISBN cannot be empty";
    if(isset($data["pages"]) && (!is_int($data["pages"]) || $data["pages"] <= 0)) $errors["pages"] = "Pages must be a positive integer";
    if(isset($data["cover_url"]) && filter_var($data["cover_url"], FILTER_VALIDATE_URL)) $errors["book_url"] = "Book URL must be a valid URL";
    if(isset($data["author_id"]) && (!is_int($data["author_id"]) || $data["author_id"] <= 0)) $errors["author_id"] = "Author ID must be a positive integer";
    if(isset($data["genre_id"]) && (!is_int($data["genre_id"]) || $data["genre_id"] <= 0)) $errors["genre_id"] = "Genre ID must be a positive integer";
    if(!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    if(isset($data["author_id"]) && !$this->gateway->authorExists($data["author_id"])) $errors["author_id"] = "Author does not exist";
    if(isset($data["genre_id"]) && !$this->gateway->genreExists($data["genre_id"])) $errors["genre_id"] = "Genre does not exist";
    if(!empty($errors)) ErrorHandler::badRequest("One or more fields are invalid", $errors);

    try {
      $book = $this->gateway->update($id, $data);

      $this->respond(
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

      $this->respond([
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