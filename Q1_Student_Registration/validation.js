const form = document.getElementById("registrationForm");
const message = document.getElementById("formMessage");
const successPanel = document.getElementById("successPanel");

const fieldIds = ["fullName", "rollNo", "email", "course", "password", "confirmPassword", "profilePhoto", "document"];

function showError(id, text) {
  const field = document.getElementById(id);
  const error = document.getElementById(id + "Error");
  if (field && field.classList) {
    field.classList.remove("valid");
    field.classList.add("invalid");
  }
  if (error) error.textContent = text;
}
function showValid(id) {
  const field = document.getElementById(id);
  const error = document.getElementById(id + "Error");
  if (field && field.classList) {
    field.classList.remove("invalid");
    field.classList.add("valid");
  }
  if (error) error.textContent = "";
}
function clearErrors() {
  fieldIds.forEach(id => {
    const field = document.getElementById(id);
    const error = document.getElementById(id + "Error");
    if (field) field.classList.remove("invalid", "valid");
    if (error) error.textContent = "";
  });
  document.getElementById("termsError").textContent = "";
  message.className = "message";
  message.textContent = "";
}
function validateFile(inputId, maxMB, allowedTypes, required) {
  const input = document.getElementById(inputId);
  const file = input.files[0];
  if (!file) {
    if (required) { showError(inputId, "Please choose a file."); return false; }
    showValid(inputId);
    return true;
  }
  const extension = file.name.split(".").pop().toLowerCase();
  const allowedExtensions = allowedTypes.map(type => type.split("/").pop().replace("jpeg", "jpg"));
  const validMime = allowedTypes.includes(file.type);
  const validExtension = allowedExtensions.includes(extension) || (extension === "jpeg" && allowedTypes.includes("image/jpeg"));
  if (!validMime && !validExtension) {
    showError(inputId, "File type is not allowed."); input.value = ""; return false;
  }
  if (file.size > maxMB * 1024 * 1024) {
    showError(inputId, `File must be ${maxMB} MB or smaller.`); input.value = ""; return false;
  }
  showValid(inputId);
  return true;
}
function updatePasswordStrength() {
  const password = document.getElementById("password").value;
  const bar = document.getElementById("strengthBar");
  const text = document.getElementById("strengthText");
  let score = 0;
  if (password.length >= 8) score++;
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
  if (/\d/.test(password)) score++;
  if (/[^A-Za-z0-9]/.test(password)) score++;
  const levels = [
    { width: "0%", color: "#e8edf5", label: "Use uppercase, lowercase, a number and a symbol." },
    { width: "25%", color: "#c73636", label: "Weak password" },
    { width: "50%", color: "#e28a24", label: "Fair password" },
    { width: "75%", color: "#d6a400", label: "Good password" },
    { width: "100%", color: "#16835d", label: "Strong password" }
  ];
  bar.style.width = levels[password ? score : 0].width;
  bar.style.backgroundColor = levels[password ? score : 0].color;
  text.textContent = levels[password ? score : 0].label;
}
document.getElementById("password").addEventListener("input", updatePasswordStrength);
document.getElementById("profilePhoto").addEventListener("change", () => validateFile("profilePhoto", 2, ["image/jpeg", "image/png"], true));
document.getElementById("document").addEventListener("change", () => validateFile("document", 5, ["application/pdf", "image/jpeg", "image/png"], false));

form.addEventListener("submit", event => {
  event.preventDefault();
  clearErrors();
  successPanel.hidden = true;
  let valid = true;
  const name = document.getElementById("fullName").value.trim();
  const roll = document.getElementById("rollNo").value.trim();
  const email = document.getElementById("email").value.trim();
  const course = document.getElementById("course").value;
  const password = document.getElementById("password").value;
  const confirm = document.getElementById("confirmPassword").value;

  if (!/^[A-Za-z][A-Za-z .'-]{1,79}$/.test(name)) { showError("fullName", "Enter a valid name (at least 2 letters)."); valid = false; } else showValid("fullName");
  if (!/^[0-9]{2}[A-Za-z]{2,4}[0-9]{3}$/.test(roll)) { showError("rollNo", "Use a format such as 24CS015."); valid = false; } else showValid("rollNo");
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) { showError("email", "Enter a valid email address."); valid = false; } else showValid("email");
  if (!course) { showError("course", "Select a course."); valid = false; } else showValid("course");
  if (password.length < 8 || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/\d/.test(password) || !/[^A-Za-z0-9]/.test(password)) {
    showError("password", "Use 8+ characters with upper/lowercase, a number and a symbol."); valid = false;
  } else showValid("password");
  if (!confirm || confirm !== password) { showError("confirmPassword", "Passwords are required and must match."); valid = false; } else showValid("confirmPassword");
  if (!validateFile("profilePhoto", 2, ["image/jpeg", "image/png"], true)) valid = false;
  if (!validateFile("document", 5, ["application/pdf", "image/jpeg", "image/png"], false)) valid = false;
  if (!document.getElementById("terms").checked) {
    document.getElementById("termsError").textContent = "Please confirm the declaration.";
    valid = false;
  }
  if (!valid) {
    message.className = "message error-message";
    message.textContent = "Please correct the highlighted fields and try again.";
    const firstInvalid = form.querySelector(".invalid");
    if (firstInvalid) firstInvalid.focus();
    return;
  }
  message.className = "message success-message";
  message.textContent = "All required fields passed client-side validation.";
  document.getElementById("successSummary").textContent = `${name} (${roll.toUpperCase()}) — ${course}.`;
  successPanel.hidden = false;
  successPanel.scrollIntoView({ behavior: "smooth", block: "nearest" });
});
form.addEventListener("reset", () => {
  setTimeout(() => { clearErrors(); successPanel.hidden = true; updatePasswordStrength(); }, 0);
});
