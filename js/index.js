// document.getElementById("loginForm").addEventListener("submit", function (e) {
//   e.preventDefault();
//   const username = document.getElementById("username").value.trim();
//   const password = document.getElementById("password").value;
//   const message = document.getElementById("message");

//   if (username === "admin" && password === "1234") {
//     message.style.color = "green";
//     message.textContent = "Connexion réussie !";
//     setTimeout(() => {
//       window.location.href = "home.php";
//     }, 1000);
//   } else {
//     message.style.color = "red";
//     message.textContent = "Identifiants incorrects.";
//   }
// });

// Show/hide password
const toggle = document.getElementById("togglePassword");
const passwordInput = document.getElementById("password");

toggle.addEventListener("click", () => {
  const type = passwordInput.type === "password" ? "text" : "password";
  passwordInput.type = type;
  toggle.classList.toggle("fa-eye");
  toggle.classList.toggle("fa-eye-slash");
});

// // Pour ajouter la classe "active" selon la page
// const current = window.location.pathname;
// document.querySelectorAll(".menu li a").forEach((link) => {
//   if (link.getAttribute("href") === current.split("/").pop()) {
//     link.parentElement.classList.add("active");
//   }
// });
