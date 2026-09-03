<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book API Documentation</title>
  <link rel="stylesheet" href="./style.css">
</head>
<body>
  <header>
    <div>
      <h1>Book API</h1>
      <p>
        A REST API for managing books, authors and genres.
      </p>
    </div>
  </header>
  <nav>
    <ul>
      <li><a href="#get-all">GET Books</a></li>
      <li><a href="#get-one">GET Book</a></li>
      <li><a href="#post">POST</a></li>
      <li><a href="#patch">PATCH</a></li>
      <li><a href="#delete">DELETE</a></li>
    </ul>
  </nav>
  <main>
    <section>
      <h2>Books</h2>
      <!-- GET ALL -->
      <div class="endpoint" id="get-all">
        <div class="endpoint-header">
          <span class="method get">GET</span>
          <span class="endpoint-path">/api/books</span>
        </div>
        <p>Returns a list of all books.</p>
        
        <h4>Request</h4>
        <pre><code>GET /api/books</code></pre>

        <h4>Response</h4>
        <pre><code>[
  {
    "id": 1,
    "title": "The Hobbit",
    "description": "A fantasy novel about Bilbo Baggins.",
    "published_year": 1937,
    "isbn": "9780261102217",
    "pages": 310,
    "cover_url": "https://example.com/cover.jpg",
    "author": {
      "id": 1,
      "name": "J.R.R. Tolkien"
    },
    "genre": {
      "id": 1,
      "name": "Fantasy"
    }
  }
]</code></pre>
      </div>

      <!-- GET ONE -->
      <div class="endpoint" id="get-one">
        <div class="endpoint-header">
          <span class="method get">GET</span>
          <span class="endpoint-path">/api/books/{id}</span>
        </div>
        <p>Returns a single book by its ID.</p>

        <h4>Request</h4>
        <pre><code>GET /api/books/3</code></pre>
        <h4>Parameters</h4>
        <ul>
          <li>
            <strong>id</strong> — The ID of the book to retrieve.
          </li>
        </ul>
        <h4>Response</h4>
        <pre><code>{
  "id": 3,
  "title": "The Hobbit",
  "description": "A fantasy novel about Bilbo Baggins.",
  "published_year": 1937,
  "isbn": "9780261102217",
  "pages": 310,
  "cover_url": "https://example.com/cover.jpg",
  "author": {
    "id": 1,
    "name": "J.R.R. Tolkien"
  },
  "genre": {
    "id": 1,
    "name": "Fantasy"
  }
}</code></pre>

        <h4>Errors</h4>
        <ul>
          <li><strong>404</strong> — Book not found.</li>
        </ul>
      </div>

      <!-- POST -->
      <div class="endpoint" id="post">
        <div class="endpoint-header">
          <span class="method post">POST</span>
          <span class="endpoint-path">/api/books</span>
        </div>
        <p>Creates a new book.</p>

        <h4>Request</h4>
        <pre><code>POST /api/books
Content-Type: application/json</code></pre>

        <h4>Request Body</h4>
        <pre><code>{
  "title": "Wool",
  "description": "A science fiction novel.",
  "published_year": 2011,
  "isbn": "9781476733958",
  "pages": 509,
  "cover_url": "https://example.com/cover.jpg",
  "author_id": 5,
  "genre_id": 2
}</code></pre>

        <h4>Fields</h4>
        <ul>
          <li><strong>title</strong> — The title of the book. Required.</li>
          <li><strong>description</strong> — A description of the book. Required.</li>
          <li><strong>published_year</strong> — The year the book was published. Required.</li>
          <li><strong>isbn</strong> — The ISBN of the book. Required.</li>
          <li><strong>pages</strong> — The number of pages. Required.</li>
          <li><strong>cover_url</strong> — URL to the book's cover. Optional.</li>
          <li><strong>author_id</strong> — ID of an existing author. Required.</li>
          <li><strong>genre_id</strong> — ID of an existing genre. Required.</li>
        </ul>

        <h4>Response</h4>
        <pre><code>{
  "id": 6,
  "title": "Wool",
  "description": "A science fiction novel.",
  "published_year": 2011,
  "isbn": "9781476733958",
  "pages": 509,
  "cover_url": "https://example.com/cover.jpg",
  "author": {
    "id": 5,
    "name": "Hugh Howey"
  },
  "genre": {
    "id": 2,
    "name": "Science Fiction"
  }
}</code></pre>

        <h4>Status Codes</h4>
        <ul>
          <li><strong>201</strong> — Book created successfully.</li>
          <li><strong>400</strong> — Invalid request or validation failed.</li>
          <li><strong>409</strong> — A book with this ISBN already exists.</li>
          <li><strong>500</strong> — Internal server error.</li>
        </ul>
      </div>

      <!-- PATCH -->
      <div class="endpoint" id="patch">
        <div class="endpoint-header">
          <span class="method patch">PATCH</span>
          <span class="endpoint-path">/api/books/{id}</span>
        </div>

        <p>
          Updates one or more fields of an existing book.
          Only the fields that need to be changed have to be included.
        </p>

        <h4>Request</h4>
        <pre><code>PATCH /api/books/3
Content-Type: application/json</code></pre>

        <pre><code>{
  "title": "The Hobbit - Updated",
  "pages": 500,
  "description": "An updated description."
}</code></pre>

        <h4>Available Fields</h4>
        <table>
          <thead>
            <tr>
              <th>Field</th>
              <th>Type</th>
              <th>Required</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>title</code></td>
              <td>string</td>
              <td>No</td>
            </tr>
            <tr>
              <td><code>description</code></td>
              <td>string</td>
              <td>No</td>
            </tr>
            <tr>
              <td><code>published_year</code></td>
              <td>integer</td>
              <td>No</td>
            </tr>
            <tr>
              <td><code>isbn</code></td>
              <td>string</td>
              <td>No</td>
            </tr>
            <tr>
              <td><code>pages</code></td>
              <td>integer</td>
              <td>No</td>
            </tr>
            <tr>
              <td><code>cover_url</code></td>
              <td>string / null</td>
              <td>No</td>
            </tr>
            <tr>
              <td><code>author_id</code></td>
              <td>integer</td>
              <td>No</td>
            </tr>
            <tr>
              <td><code>genre_id</code></td>
              <td>integer</td>
              <td>No</td>
            </tr>
          </tbody>
        </table>
        <p>At least one field must be provided.</p>

        <h4>Response</h4>
        <pre><code>{
  "id": 3,
  "title": "The Hobbit - Updated",
  "description": "An updated description.",
  "published_year": 1937,
  "isbn": "9780261102217",
  "pages": 500,
  "cover_url": null,
  "author": {
    "id": 1,
    "name": "J.R.R. Tolkien"
  },
  "genre": {
    "id": 1,
    "name": "Fantasy"
  }
}</code></pre>

        <h4>Status Codes</h4>
        <ul>
          <li><strong>200</strong> — Book updated successfully.</li>
          <li><strong>400</strong> — Invalid JSON or invalid fields.</li>
          <li><strong>404</strong> — Book not found.</li>
          <li><strong>409</strong> — ISBN already exists.</li>
          <li><strong>500</strong> — Internal server error.</li>
        </ul>
      </div>

      <!-- DELETE -->
      <div class="endpoint" id="delete">
        <div class="endpoint-header">
          <span class="method delete">DELETE</span>
          <span class="endpoint-path">/api/books/{id}</span>
        </div>
        <p>Deletes an existing book.</p>

        <h4>Request</h4>
        <pre><code>DELETE /api/books/6</code></pre>
        <p>
          The book ID is provided in the URL.
          No request body is required.
        </p>

        <h4>Response</h4>
        <p>
          Returns a confirmation containing the ID and title
          of the deleted book.
        </p>

        <pre><code>{
  "message": "Book deleted successfully",
  "deleted_book": {
    "id": 6,
    "title": "Dune"
  }
}</code></pre>

        <h4>Status Codes</h4>
        <ul>
          <li><strong>200</strong> — Book deleted successfully.</li>
          <li><strong>404</strong> — Book not found.</li>
          <li><strong>500</strong> — Internal server error.</li>
        </ul>
      </div>
    </section>
  </main>
</body>
</html>