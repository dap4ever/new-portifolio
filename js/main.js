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
  if (this.scrollY >= 50) {
    header.classList.add("scroll-header");
  } else {
    header.classList.remove("scroll-header");
  }
}
window.addEventListener("scroll", scrollHeader);

/* =========================================
   FORMULÁRIO DE CONTATO VIA AJAX + MODAL
   ========================================= */
const contactForm = document.getElementById("contact-form");
const successModal = document.getElementById("success-modal");
const modalClose   = document.getElementById("modal-close");
const modalOk      = document.getElementById("modal-ok");

function openModal() {
  successModal.classList.add("active");
  document.body.style.overflow = "hidden";
}

function closeModal() {
  successModal.classList.remove("active");
  document.body.style.overflow = "";
}

if (contactForm) {
  contactForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const submitBtn = contactForm.querySelector("[type='submit']");
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = 'Enviando... <i class="fa-solid fa-spinner fa-spin"></i>';
    submitBtn.disabled = true;

    try {
      // Gera token reCAPTCHA v3
      const token = await grecaptcha.execute('6LeO1IMsAAAAANl0zSGh0IePT7bUNLMLvzathyb5', { action: 'contact' });

      const formData = new FormData(contactForm);
      formData.append('g-recaptcha-response', token);

      const res  = await fetch("contact.php", { method: "POST", body: formData });
      const data = await res.json();

      if (data.success) {
        contactForm.reset();
        openModal();
      } else {
        alert(data.message || "Erro ao enviar. Tente novamente.");
      }
    } catch {
      alert("Erro de rede. Verifique sua conexão e tente novamente.");
    } finally {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
  });
}

if (modalClose) modalClose.addEventListener("click", closeModal);
if (modalOk)    modalOk.addEventListener("click", closeModal);
if (successModal) {
  successModal.addEventListener("click", (e) => {
    if (e.target === successModal) closeModal();
  });
}

