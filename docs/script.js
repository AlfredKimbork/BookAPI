const API_BASE = "../api";

// --------------------------------------------------
// API KEY
// --------------------------------------------------

const keyForm = document.querySelector("#key-form");

let apiKey = sessionStorage.getItem("bookApiKey") || "";

function getApiKey() {
  return apiKey;
}

function setApiKey(key) {
  apiKey = key;

  if (key) {
    sessionStorage.setItem("bookApiKey", key);
  } else {
    sessionStorage.removeItem("bookApiKey");
  }

  const input = document.querySelector("#api-key");

  if (input) {
    input.value = key;
  }
}

// --------------------------------------------------
// KEY GENERATOR
// --------------------------------------------------

if (keyForm) {
  const keyName = document.querySelector("#key-name");
  const keyResult = document.querySelector("#key-result");
  const keyError = document.querySelector("#key-error");
  const generatedKey = document.querySelector("#generated-key");
  const copyButton = document.querySelector("#copy-key");
  const useKeyButton = document.querySelector("#use-generated-key");

  keyForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    keyError.hidden = true;
    keyResult.hidden = true;

    const name = keyName.value.trim();
    const canWrite = document.querySelector("#can-write").checked;

    if (!name) {
      showKeyError("Name is required.");
      return;
    }

    try {
      const response = await fetch(`${API_BASE}/keys`, {
        method: "POST",

        headers: {
          "Content-Type": "application/json",
        },

        body: JSON.stringify({
          name: name,
          can_write: false
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        showKeyError(data.message || data.error || "Something went wrong.");

        return;
      }

      generatedKey.textContent = data.api_key;

      keyResult.hidden = false;

      setApiKey(data.api_key);

      keyForm.reset();
    } catch (error) {
      showKeyError("Could not connect to the API.");

      console.error(error);
    }
  });

  copyButton.addEventListener("click", async () => {
    const key = generatedKey.textContent;

    try {
      await navigator.clipboard.writeText(key);

      copyButton.textContent = "Copied!";

      setTimeout(() => {
        copyButton.textContent = "Copy";
      }, 1500);
    } catch (error) {
      copyButton.textContent = "Failed";

      console.error(error);
    }
  });

  useKeyButton.addEventListener("click", () => {
    setApiKey(generatedKey.textContent);

    useKeyButton.textContent = "Key saved!";

    setTimeout(() => {
      useKeyButton.textContent = "Use this key for API testing";
    }, 1500);
  });

  function showKeyError(message) {
    keyError.textContent = message;

    keyError.hidden = false;
  }
}

// --------------------------------------------------
// API TESTER
// --------------------------------------------------

const apiKeyInput = document.querySelector("#api-key");
const saveKeyButton = document.querySelector("#save-key");
const clearKeyButton = document.querySelector("#clear-key");

if (apiKeyInput) {
  apiKeyInput.value = getApiKey();
}

if (saveKeyButton) {
  saveKeyButton.addEventListener("click", () => {
    setApiKey(apiKeyInput.value.trim());

    saveKeyButton.textContent = "Saved!";

    setTimeout(() => {
      saveKeyButton.textContent = "Save";
    }, 1000);
  });
}

if (clearKeyButton) {
  clearKeyButton.addEventListener("click", () => {
    setApiKey("");

    apiKeyInput.value = "";
  });
}

// --------------------------------------------------
// REQUEST HELPER
// --------------------------------------------------

async function apiRequest(url, options = {}) {
  const key = getApiKey();

  const headers = {
    ...(options.headers || {}),
  };

  if (key) {
    headers["X-API-Key"] = key;
  }

  const response = await fetch(url, {
    ...options,
    headers,
  });

  let data = null;

  if (response.status !== 204) {
    const text = await response.text();

    try {
      data = JSON.parse(text);
    } catch {
      data = text;
    }
  }

  return {
    response,
    data,
  };
}

// --------------------------------------------------
// DISPLAY RESPONSE
// --------------------------------------------------

function showResponse(element, result) {
  if (!element) return;

  element.hidden = false;

  element.className = "response";

  if (result.response.ok) {
    element.classList.add("success");
  } else {
    element.classList.add("failure");
  }

  const status = document.createElement("div");

  status.className = "response-status";

  status.textContent = `${result.response.status} ${result.response.statusText}`;

  const pre = document.createElement("pre");

  const code = document.createElement("code");

  if (result.data === null) {
    code.textContent = "";
  } else if (typeof result.data === "string") {
    code.textContent = result.data;
  } else {
    code.textContent = JSON.stringify(result.data, null, 4);
  }

  pre.appendChild(code);

  element.replaceChildren(status, pre);
}

// --------------------------------------------------
// BOOK REQUESTS
// --------------------------------------------------

const getBooksButton = document.querySelector("#get-books");

if (getBooksButton) {
  getBooksButton.addEventListener("click", async () => {
    const page = document.querySelector("#books-page").value || 1;

    const limit = document.querySelector("#books-limit").value || 10;

    const search = document.querySelector("#books-search").value.trim();

    const params = new URLSearchParams();

    params.set("page", page);
    params.set("limit", limit);

    if (search) {
      params.set("search", search);
    }

    const result = await apiRequest(`${API_BASE}/books?${params}`);

    showResponse(document.querySelector("#books-response"), result);
  });
}

const getBookButton = document.querySelector("#get-book");

if (getBookButton) {
  getBookButton.addEventListener("click", async () => {
    const id = document.querySelector("#book-id").value;

    if (!id) return;

    const result = await apiRequest(`${API_BASE}/books/${id}`);

    showResponse(document.querySelector("#book-response"), result);
  });
}

// --------------------------------------------------
// COPY BUTTONS
// --------------------------------------------------

document.querySelectorAll(".copy-text").forEach((button) => {
  button.addEventListener("click", async () => {
    const text = button.dataset.copy;

    try {
      await navigator.clipboard.writeText(text);

      button.textContent = "Copied!";

      setTimeout(() => {
        button.textContent = "Copy";
      }, 1500);
    } catch {
      button.textContent = "Failed";
    }
  });
});

// --------------------------------------------------
// AUTHOR REQUESTS
// --------------------------------------------------

const getAuthorsButton = document.querySelector("#get-authors");

if (getAuthorsButton) {
  getAuthorsButton.addEventListener("click", async () => {
    const page = document.querySelector("#authors-page").value || 1;

    const limit = document.querySelector("#authors-limit").value || 10;

    const search = document
      .querySelector("#authors-search")
      .value
      .trim();

    const params = new URLSearchParams();

    params.set("page", page);
    params.set("limit", limit);

    if (search) {
      params.set("search", search);
    }

    const result = await apiRequest(
      `${API_BASE}/authors?${params}`
    );

    showResponse(
      document.querySelector("#authors-response"),
      result
    );
  });
}


const getAuthorButton = document.querySelector("#get-author");

if (getAuthorButton) {
  getAuthorButton.addEventListener("click", async () => {
    const id = document.querySelector("#author-id").value;

    if (!id) return;

    const result = await apiRequest(
      `${API_BASE}/authors/${id}`
    );

    showResponse(
      document.querySelector("#author-response"),
      result
    );
  });
}


// --------------------------------------------------
// GENRE REQUESTS
// --------------------------------------------------

const getGenresButton = document.querySelector("#get-genres");

if (getGenresButton) {
  getGenresButton.addEventListener("click", async () => {
    const page = document.querySelector("#genres-page").value || 1;

    const limit = document.querySelector("#genres-limit").value || 10;

    const search = document
      .querySelector("#genres-search")
      .value
      .trim();

    const params = new URLSearchParams();

    params.set("page", page);
    params.set("limit", limit);

    if (search) {
      params.set("search", search);
    }

    const result = await apiRequest(
      `${API_BASE}/genres?${params}`
    );

    showResponse(
      document.querySelector("#genres-response"),
      result
    );
  });
}


const getGenreButton = document.querySelector("#get-genre");

if (getGenreButton) {
  getGenreButton.addEventListener("click", async () => {
    const id = document.querySelector("#genre-id").value;

    if (!id) return;

    const result = await apiRequest(
      `${API_BASE}/genres/${id}`
    );

    showResponse(
      document.querySelector("#genre-response"),
      result
    );
  });
}
