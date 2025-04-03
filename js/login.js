async function login() {
    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;
    let errorMessage = document.getElementById("error-message");

    if (!email || !password) {
        errorMessage.innerText = "Fill in all the fields!";
        return;
    }

    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);

    try {
        let response = await fetch('auth/login.php', {
            method: "POST",
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Server error: ${response.status}`);
        }

        let result = await response.json();
        console.log("Server response:", result);

        errorMessage.innerText = result.message;

        if (result.success) {
            localStorage.removeItem("lastPostId");
            localStorage.removeItem("lastCommentId");
            window.location.href = result.redirect;
        }
    } catch (error) {
        console.error("Login error:", error);
        errorMessage.innerText = "Unable to login. Try again later.";
    }
}