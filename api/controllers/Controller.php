<?php
class Controller
{
  protected function formatBook(array $book): array
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
}