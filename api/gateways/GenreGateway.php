<?php
class GenreGateway
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  // Get all genres without their books
  public function getAll(): array
  {
    $stmt = $this->pdo->query("
      SELECT
        genres.id,
        genres.name,
        genres.description
      FROM genres
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Get one genre and all books belonging to it
  public function getById(int $id): array|false
  {
    $stmt = $this->pdo->prepare("
      SELECT
        genres.id,
        genres.name,
        genres.description
      FROM genres

      WHERE genres.id = ?
    ");

    $stmt->execute([$id]);
    $genre = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return false if the genre does not exist
    if(!$genre) return false;

    // Get all books belonging to the genre
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

      WHERE books.genre_id = ?
    ");

    $stmt->execute([$id]);
    $genre["books"] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $genre;
  }

  // Create a new genre and return its new ID
  public function create(array $data): int
  {
    $stmt = $this->pdo->prepare("
      INSERT INTO genres (
        name,
        description
      )
        values (?, ?)
    ");

    $stmt->execute([
      $data["name"],
      $data["description"],
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
      "description"
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
      UPDATE genres
      SET " . implode(", ", $fields) . "
      WHERE id = ?
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($values);

    // Return the updated genre
    return $this->getById($id);
  }

  // Delete a genre and return its data
  public function delete(int $id): array|false
  {
    $genre = $this->getById($id);

    // Return false if the genre does not exist
    if(!$genre) return false;

    $stmt = $this->pdo->prepare("
      DELETE FROM genres
      WHERE id = ?
    ");

    $stmt->execute([$id]);

    return $genre;
  }
}