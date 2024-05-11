let validations = [
  { pattern: /[a-z]/, issue: "Must include lowercase letters." },
  { pattern: /[A-Z]/, issue: "Must include uppercase letters." },
  { pattern: /[0-9]/, issue: "Must include numbers." },
  { pattern: /.{5,}/, issue: "Must be at least 5 characters long." },
];

function password_check(pwd) {
  pwd = pwd.value;
  let validationIssues = validations
    .filter((validation) => {
      return !pwd?.match?.(validation.pattern);
    })
    .map((validation) => validation.issue);
  if (validationIssues.length > 0) {
    return `Your password was too weak for the following reasons: ${validationIssues.join(
      "\n"
    )}`;
  }
  return "Hard Password!";
}

function togglePasswordInput(password_sel, togglePassword_sel) {
  const togglePassword = document.querySelector(togglePassword_sel);
  const password = document.querySelector(password_sel);

  togglePassword.addEventListener("click", function () {
    // toggle the type attribute
    const type =
      password.getAttribute("type") === "password" ? "text" : "password";
    password.setAttribute("type", type);
    // toggle the eye icon
    this.classList.toggle("fa-eye");
    this.classList.toggle("fa-eye-slash");
  });
}
