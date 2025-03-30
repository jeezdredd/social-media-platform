async function login() {
    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;
    let errorMessage = document.getElementById("error-message");

    if (!email || !password) {
        errorMessage.innerText = "Fill in all the fields!";
        return;
    }

    try {
        let response = await fetch('auth/login.php', {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        });
        
        if (!response.ok) {
            throw new Error(`Server error: ${response.status}`);
        }
        
        let result = await response.json();
        console.log(result);
        console.log("Server response:", result);

        errorMessage.innerText = result.message;

        if (result.success) {
            localStorage.removeItem("lastPostId");
            localStorage.removeItem("lastCommentId");
            window.location.href = result.redirect || "dashboard.php";
        }
    } catch (error) {
        console.error("Login error:", error);
        errorMessage.innerText = "Unable to login. Try again later.";
    }
}