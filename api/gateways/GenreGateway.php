<?php
class GenreGateway
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

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

    if(!$genre) return false;

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

  public function update(int $id, array $data): array|false
  {
    $fields = [];
    $values = [];

    $allowedFields = [
      "name",
      "description"
    ];

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

    return $this->getById($id);
  }

  public function delete(int $id): array|false
  {
    $genre = $this->getById($id);

    if(!$genre) return false;

    $stmt = $this->pdo->prepare("
      DELETE FROM genres
      WHERE id = ?
    ");

    $stmt->execute([$id]);

    return $genre;
  }
}