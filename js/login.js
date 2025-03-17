async function login() {
    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;
    let errorMessage = document.getElementById("error-message");

    if (!email || !password) {
        errorMessage.innerText = "Заполните все поля!";
        return;
    }

    try {
        let response = await fetch('/dmuk-coursework/acchandlers/login.php', {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        });

        if (!response.ok) {
            throw new Error(`Ошибка сервера: ${response.status}`);
        }

        let result = await response.json();
        console.log("Ответ сервера:", result); // Логируем ответ в консоль для отладки

        errorMessage.innerText = result.message;

        if (result.success) {
            window.location.href = result.redirect || "dashboard.php";
        }
    } catch (error) {
        console.error("Ошибка при входе:", error);
        errorMessage.innerText = "Ошибка при входе. Попробуйте еще раз.";
    }
}