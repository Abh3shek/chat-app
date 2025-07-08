    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const togglePassword = document.querySelector("#togglePassword");
        const passwordField = document.querySelector("#inputPassword4");

        // Check if the elements exist
        if (togglePassword && passwordField) {
            togglePassword.addEventListener("click", function () {
                const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
                passwordField.setAttribute("type", type);

                // Optional: toggle icon/text
                this.textContent = type === "password" ? "👁️" : "🙈";
            });
        }

        const togglePassword2 = document.getElementById("togglePassword");
        const passwordInput = document.getElementById("inputPassword");

        // Check if the elements exist
        if (togglePassword2 && passwordInput) {
            togglePassword2.addEventListener("click", () => {
                const type = passwordInput.type === "password" ? "text" : "password";
                passwordInput.type = type;
                togglePassword.textContent = type === "password" ? "👁️" : "🙈";
            });
        }
    });

    </script>

</body>
</html>