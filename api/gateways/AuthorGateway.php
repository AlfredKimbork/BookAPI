<?php
class AuthorGateway
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
        authors.id,
        authors.name,
        authors.birth_date,
        authors.death_date,
        authors.biography
      FROM authors
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

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

    if(!$author) return false;

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

    return $this->getById($id);
  }

  public function delete(int $id): array|false
  {
    $author = $this->getById($id);

    if(!$author) return false;

    $stmt = $this->pdo->prepare("
      DELETE FROM authors
      WHERE id = ?
    ");

    $stmt->execute([$id]);

    return $author;
  }
}