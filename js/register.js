async function register() {
    const username = document.getElementById("username").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    if (!username || !email || !password) {
        document.getElementById("error-message").innerText = "Fill in all the fields!";
        return;
    }

    const response = await fetch('/dmuk-coursework/acchandlers/register.php', {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
    });

    const result = await response.json();
    document.getElementById("error-message").innerText = result.message;

    if (result.success) {
        window.location.href = `/dmuk-coursework/${result.redirect || "login.php"}`;
    }
}