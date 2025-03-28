document.addEventListener('DOMContentLoaded', function() {
    // Modal handling for edit profile
    const editModal = document.getElementById("editProfileModal");
    const editBtn = document.getElementById("editProfileBtn");
    const closeEditBtn = editModal.querySelector(".close");

    // Section toggle buttons
    const photoToggleBtn = document.getElementById("photoToggleBtn");
    const infoToggleBtn = document.getElementById("infoToggleBtn");
    const photoSection = document.getElementById("photoSection");
    const infoSection = document.getElementById("infoSection");

    // Toggle section functionality
    photoToggleBtn.addEventListener("click", function() {
        photoToggleBtn.classList.add("active");
        infoToggleBtn.classList.remove("active");
        photoSection.classList.add("active");
        infoSection.classList.remove("active");
    });

    infoToggleBtn.addEventListener("click", function() {
        infoToggleBtn.classList.add("active");
        photoToggleBtn.classList.remove("active");
        infoSection.classList.add("active");
        photoSection.classList.remove("active");
    });

    // Open edit profile modal
    editBtn.onclick = function() {
        editModal.style.display = "block";
    }

    // Close edit profile modal
    closeEditBtn.onclick = function() {
        editModal.style.display = "none";
    }

    // Handle modals when clicking outside content area
    window.onclick = function(event) {
        if (event.target == editModal) {
            editModal.style.display = "none";
        }
        if (event.target == logoutModal) {
            logoutModal.style.display = "none";
        }
    }

    // Profile picture upload handling
    document.getElementById("profilePicForm").addEventListener("submit", async function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        const uploadMessage = document.getElementById("uploadMessage");
        uploadMessage.innerText = "Uploading...";

        try {
            const response = await fetch("profile/upload_profile.php", {
                method: "POST",
                body: formData
            });

            const result = await response.json();
            uploadMessage.innerText = result.message;

            if (result.success) {
                // Reload page after showing success message
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            uploadMessage.innerText = "Error uploading profile picture";
            console.error("Upload error:", error);
        }
    });

    // Logout confirmation handling
    const logoutModal = document.getElementById("logoutConfirmModal");
    const logoutBtn = document.getElementById("logoutConfirmBtn");
    const confirmLogout = document.getElementById("confirmLogout");
    const cancelLogout = document.getElementById("cancelLogout");

    logoutBtn.onclick = function() {
        logoutModal.style.display = "block";
    }

    cancelLogout.onclick = function() {
        logoutModal.style.display = "none";
    }

    confirmLogout.onclick = function() {
        window.location.href = "auth/logout.php";
    }
});