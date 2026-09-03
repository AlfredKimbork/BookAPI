<?php
class BookGateway
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

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

  public function getAll(): array
  {
    $stmt = $this->pdo->query("
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
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

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

    return $this->getById($id);
  }

  public function delete(int $id): array|false
  {
    $book = $this->getById($id);

    if(!$book) return false;

    $stmt = $this->pdo->prepare("
      DELETE FROM books
      WHERE id = ?
    ");

    $stmt->execute([$id]);

    return $book;
  }
}