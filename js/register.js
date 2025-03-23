async function register() {
    let username = document.getElementById("username").value;
    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;

    if (!username || !email || !password) {
        document.getElementById("error-message").innerText = "Fill in all the fields!";
        return;
    }

    let response = await fetch('/dmuk-coursework/acchandlers/register.php', {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
    });

    let result = await response.json();
    document.getElementById("error-message").innerText = result.message;

    if (result.success) {
        window.location.href = "login.php";
    }
}