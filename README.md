# Book API

A RESTful API built with **PHP and MySQL** for managing books, authors and genres.

The project includes authentication with API keys, pagination, searching, CRUD operations and interactive API documentation.

## Features

* REST API using PHP
* MySQL database
* API key authentication
* Read/write permissions
* CRUD operations
* Pagination
* Search
* `GET`, `HEAD`, `POST`, `PATCH`, `DELETE` and `OPTIONS`
* JSON responses
* HTTP status codes and error handling
* Interactive API documentation
* HTTPie and cURL examples

## Resources

The API provides three main resources:

* **Books** — `/api/books`
* **Authors** — `/api/authors`
* **Genres** — `/api/genres`

API keys can be generated through:

```text
POST /api/keys
```

Generated keys have read-only permissions by default.

## Project structure

```text
BookAPI/
├── api/
│   ├── auth/
│   ├── controllers/
│   ├── gateways/
│   ├── errors/
│   ├── db.php
│   ├── index.php
│   └── .htaccess
│
├── docs/
│   ├── index.html
│   ├── authentication.html
│   ├── books.html
│   ├── authors.html
│   ├── genres.html
│   ├── style.css
│   └── script.js
│
└── README.md
```

## Requirements

* PHP 7.4+
* MySQL
* Apache with `mod_rewrite`
* MAMP or another PHP/MySQL environment

## Running locally

1. Clone the repository into your web server directory.

2. Create the `BookAPI` database in MySQL.

3. Import the database structure and sample data.

4. Configure the database connection in:

```text
api/db.php
```

5. Make sure Apache's rewrite module is enabled.

6. Open the documentation:

```text
http://localhost:8888/BookAPI/docs/
```

## Authentication

Most API endpoints require an API key.

Include it using the `X-API-Key` header:

```text
X-API-Key: your-api-key
```

For example:

```bash
curl http://localhost:8888/BookAPI/api/books \
-H "X-API-Key: your-api-key"
```

## Example response

```json
{
    "data": [
        {
            "id": 1,
            "title": "Dune",
            "published_year": 1965
        }
    ],
    "pagination": {
        "page": 1,
        "limit": 10,
        "total": 1,
        "pages": 1
    }
}
```

## Documentation

The `docs/` directory contains interactive documentation for the API, including endpoint descriptions, request bodies, responses, HTTPie examples and cURL examples.

---

**Built with PHP + MySQL**
