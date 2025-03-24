const burgerButton = document.querySelector(".burger");
const mobileMenu = document.querySelector(".navbar--mobile");
const closeMenuBtn = document.querySelector(".navbar__close");

closeMenuBtn.addEventListener("click", () => {
    mobileMenu.classList.remove("open");
    mobileMenu.classList.add("close");
});

burgerButton.addEventListener("click", () => {
    mobileMenu.classList.remove("close");
    mobileMenu.classList.add("open");
});