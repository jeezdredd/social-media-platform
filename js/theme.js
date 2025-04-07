document.addEventListener("DOMContentLoaded", function() {
    const savedTheme = localStorage.getItem("theme");
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

    // Set initial theme
    if (savedTheme === "dark" || (!savedTheme && prefersDark)) {
        document.body.classList.add("dark-theme");
        updateToggleButton("light");
    } else {
        updateToggleButton("dark");
    }

    // Theme toggle button functionality
    const themeToggle = document.getElementById("themeToggle");
    if (themeToggle) {
        themeToggle.addEventListener("click", function() {
            if (document.body.classList.contains("dark-theme")) {
                document.body.classList.remove("dark-theme");
                localStorage.setItem("theme", "light");
                updateToggleButton("dark");
            } else {
                document.body.classList.add("dark-theme");
                localStorage.setItem("theme", "dark");
                updateToggleButton("light");
            }
        });
    }

    // Update button text based on current/next theme
    function updateToggleButton(nextTheme) {
        const themeToggle = document.getElementById("themeToggle");
        if (themeToggle) {
            themeToggle.innerHTML = nextTheme === "dark"
                ? "🌙 Dark mode"
                : "☀️ Light mode";
        }
    }
});