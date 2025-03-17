document.addEventListener("DOMContentLoaded", function () {
    const uploadModal = document.getElementById("uploadPhotoModal");
    const uploadBtn = document.getElementById("uploadPhotoBtn");
    const closeUploadBtn = uploadModal.querySelector(".close");

    uploadBtn.onclick = function () {
        uploadModal.style.display = "block";
    }

    closeUploadBtn.onclick = function () {
        uploadModal.style.display = "none";
    }

    const editModal = document.getElementById("editProfileModal");
    const editBtn = document.getElementById("editProfileBtn");
    const closeEditBtn = editModal.querySelector(".close");

    editBtn.onclick = function () {
        editModal.style.display = "block";
    }

    closeEditBtn.onclick = function () {
        editModal.style.display = "none";
    }

    window.onclick = function (event) {
        if (event.target == uploadModal) {
            uploadModal.style.display = "none";
        }
        if (event.target == editModal) {
            editModal.style.display = "none";
        }
    }

    document.getElementById("profilePicForm").addEventListener("submit", async function(event) {
        event.preventDefault();
        let formData = new FormData(this);
        let response = await fetch("acchandlers/upload_profile.php", { method: "POST", body: formData });
        let result = await response.json();
        document.getElementById("uploadMessage").innerText = result.message;
        if (result.success) location.reload();
    });

    const logoutModal = document.getElementById("logoutConfirmModal");
    const logoutBtn = document.getElementById("logoutConfirmBtn");
    const confirmLogout = document.getElementById("confirmLogout");
    const cancelLogout = document.getElementById("cancelLogout");

    logoutBtn.onclick = function () {
        logoutModal.style.display = "block";
    }

    cancelLogout.onclick = function () {
        logoutModal.style.display = "none";
    }

    confirmLogout.onclick = function () {
        window.location.href = "acchandlers/logout.php";
    }

    window.onclick = function (event) {
        if (event.target == logoutModal) {
            logoutModal.style.display = "none";
        }
    }
});