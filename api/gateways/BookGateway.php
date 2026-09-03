<?php
class BookGateway
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  // Check whether an author exists
  public function authorExists(int $id): bool
  {
    $stmt = $this->pdo->prepare("
      SELECT id
      FROM authors
      WHERE id = ?
    ");

    $stmt->execute([$id]);

    return $stmt->fetch() !== false;
  }
  
  // Check whether a genre exists
  public function genreExists(int $id): bool
  {
    $stmt = $this->pdo->prepare("
      SELECT id
      FROM genres
      WHERE id = ?
    ");

    $stmt->execute([$id]);

    return $stmt->fetch() !== false;
  }

  // Get the total number of books
  public function count(?string $search = null): int
  {
    $sql = "
      SELECT COUNT(*)
      FROM books
    ";

    if($search !== null) $sql .= "
      WHERE books.title LIKE :search
      OR books.description LIKE :search
    ";

    $stmt = $this->pdo->prepare($sql);

    if($search !== null) $stmt->bindValue(":search", $search, PDO::PARAM_STR);

    $stmt->execute();

    return (int) $stmt->fetchColumn();
  }

  // Get all books together with their author and genre
  public function getAll(int $limit, int $offset, ?string $search = null): array
  {
    $sql = "
      SELECT
        books.id,
        books.title,
        books.description,
        books.published_year,
        books.isbn,
        books.pages,
        books.cover_url,
        authors.id AS author_id,
        authors.name AS author_name,
        genres.id AS genre_id,
        genres.name AS genre_name
      FROM books

      JOIN authors
        ON books.author_id = authors.id

      JOIN genres
        ON books.genre_id = genres.id
    ";

    if($search !== null) $sql .= "
      WHERE books.title LIKE :search
      OR books.description LIKE :search
    ";

    $sql .= "
      LIMIT :limit OFFSET :offset
    ";

    $stmt = $this->pdo->prepare($sql);

    if($search !== null) $stmt->bindValue(":search", $search, PDO::PARAM_STR);


    $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Get one book together with its author and genre
  public function getById(int $id): array|false
  {
    $stmt = $this->pdo->prepare("
      SELECT
        books.id,
        books.title,
        books.description,
        books.published_year,
        books.isbn,
        books.pages,
        books.cover_url,
        authors.id AS author_id,
        authors.name AS author_name,
        genres.id AS genre_id,
        genres.name AS genre_name
      FROM books

      JOIN authors
        ON books.author_id = authors.id

      JOIN genres
        ON books.genre_id = genres.id

      WHERE books.id = ?
    ");

    $stmt->execute([$id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // Create a new book and return its new ID
  public function create(array $data): int
  {
    $stmt = $this->pdo->prepare("
      INSERT INTO books (
        title,
        description,
        published_year,
        isbn,
        pages,
        cover_url,
        author_id,
        genre_id
      )
        values (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
      $data["title"],
      $data["description"],
      $data["published_year"],
      $data["isbn"],
      $data["pages"],
      $data["cover_url"] ?? null,
      $data["author_id"],
      $data["genre_id"],
    ]);

    return (int) $this->pdo->lastInsertId();
  }

  // Update only the fields allowed by the API
  public function update(int $id, array $data): array|false
  {
    $fields = [];
    $values = [];

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

    // Build the UPDATE query from the provided fields
    foreach($data as $field => $value) {
      if(in_array($field, $allowedFields, true)) {
        $fields[] = "$field = ?";
        $values[] = $value;
      }
    }

    $values[] = $id;

    $sql = "
      UPDATE books
      SET " . implode(", ", $fields) . "
      WHERE id = ?
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($values);

    // Return the updated book
    return $this->getById($id);
  }

  // Delete a book and return its data
  public function delete(int $id): array|false
  {
    $book = $this->getById($id);

    // Return false if the book does not exist
    if(!$book) return false;

    $stmt = $this->pdo->prepare("
      DELETE FROM books
      WHERE id = ?
    ");

    $stmt->execute([$id]);

    return $book;
  }
}