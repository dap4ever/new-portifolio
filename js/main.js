/* =========================================
   MOSTRAR E ESCONDER MENU MOBILE
   ========================================= */
const navMenu = document.getElementById("nav-menu");
const navToggle = document.getElementById("nav-toggle");
const navClose = document.getElementById("nav-close");

/* Validar se a constante existe antes de aplicar */
if (navToggle) {
  navToggle.addEventListener("click", () => {
    navMenu.classList.add("show-menu");
  });
}

if (navClose) {
  navClose.addEventListener("click", () => {
    navMenu.classList.remove("show-menu");
  });
}

/* =========================================
   FECHAR MENU MOBILE AO CLICAR EM UM LINK
   ========================================= */
const navLinks = document.querySelectorAll(".nav-link");

function linkAction() {
  const navMenu = document.getElementById("nav-menu");
  // Quando qualquer nav-link é clicado, remove a classe show-menu
  navMenu.classList.remove("show-menu");
}

navLinks.forEach((n) => n.addEventListener("click", linkAction));

/* =========================================
   MUDAR BACKGROUND DO HEADER NO SCROLL
   ========================================= */
function scrollHeader() {
  const header = document.getElementById("header");
  // Quando o scroll for maior que 50 viewport height, adiciona a classe scroll-header na tag header
  if (this.scrollY >= 50) {
    header.classList.add("scroll-header");
  } else {
    header.classList.remove("scroll-header");
  }
}
window.addEventListener("scroll", scrollHeader);
