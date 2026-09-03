const keyForm = document.querySelector("#key-form");

if (keyForm) {
    const keyName = document.querySelector("#key-name");
    const keyResult = document.querySelector("#key-result");
    const keyError = document.querySelector("#key-error");
    const generatedKey = document.querySelector("#generated-key");
    const copyButton = document.querySelector("#copy-key");

    keyForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        keyError.hidden = true;
        keyResult.hidden = true;

        const name = keyName.value.trim();

        if (!name) {
            showError("Name is required.");
            return;
        }

        try {
            const response = await fetch("../api/keys", {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    name: name
                })
            });

            const data = await response.json();

            if (!response.ok) {
                showError(
                    data.message ||
                    data.error ||
                    "Something went wrong."
                );

                return;
            }

            generatedKey.textContent = data.api_key;

            keyResult.hidden = false;

            keyForm.reset();

        } catch (error) {

            showError(
                "Could not connect to the API."
            );

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


    function showError(message) {

        keyError.textContent = message;

        keyError.hidden = false;
    }
}