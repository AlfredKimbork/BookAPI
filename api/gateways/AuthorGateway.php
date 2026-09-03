<?php
class AuthorGateway
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  // Get the total number of authors
  public function count(?string $search = null): int
  {
    $sql = "
      SELECT COUNT(*)
      FROM authors
    ";

    if($search !== null) $sql .= "
      WHERE authors.name LIKE :search
    ";

    $stmt = $this->pdo->prepare($sql);

    if($search !== null) $stmt->bindValue(":search", $search, PDO::PARAM_STR);

    $stmt->execute();

    return (int) $stmt->fetchColumn();
  }

  // Get all authors without their books
  public function getAll(int $limit, int $offset, ?string $search = null): array
  {
    $sql = "
      SELECT
        authors.id,
        authors.name,
        authors.birth_date,
        authors.death_date,
        authors.biography
      FROM authors
    ";

    if($search !== null) $sql .= "
      WHERE authors.name LIKE :search
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

  // Get one author and all books written by them
  public function getById(int $id): array|false
  {
    $stmt = $this->pdo->prepare("
      SELECT
        authors.id,
        authors.name,
        authors.birth_date,
        authors.death_date,
        authors.biography
      FROM authors

      WHERE authors.id = ?
    ");

    $stmt->execute([$id]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return false if the author does not exist
    if(!$author) return false;

    // Get all books belonging to the author
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

      WHERE books.author_id = ?
    ");

    $stmt->execute([$id]);
    $author["books"] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $author;
  }

  // Create a new author and return their new ID
  public function create(array $data): int
  {
    $stmt = $this->pdo->prepare("
      INSERT INTO authors (
        name,
        birth_date,
        death_date,
        biography
      )
        values (?, ?, ?, ?)
    ");

    $stmt->execute([
      $data["name"],
      $data["birth_date"],
      $data["death_date"] ?? null,
      $data["biography"],
    ]);

    return (int) $this->pdo->lastInsertId();
  }

  // Update only the fields allowed by the API
  public function update(int $id, array $data): array|false
  {
    $fields = [];
    $values = [];

    $allowedFields = [
      "name",
      "birth_date",
      "death_date",
      "biography",
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
      UPDATE authors
      SET " . implode(", ", $fields) . "
      WHERE id = ?
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($values);

    // Return the updated author
    return $this->getById($id);
  }

  // Delete an author and return their data
  public function delete(int $id): array|false
  {
    $author = $this->getById($id);

    // Return false if the author does not exist
    if(!$author) return false;

    $stmt = $this->pdo->prepare("
      DELETE FROM authors
      WHERE id = ?
    ");

    $stmt->execute([$id]);

    return $author;
  }
}