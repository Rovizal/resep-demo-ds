"use strict";

document.addEventListener("DOMContentLoaded", function () {
    const formAuthentication = document.querySelector("#formAuthentication");
    const btn = document.querySelector("#btn-login");
    const alert = document.querySelector("#login-alert");

    if (formAuthentication) {
        const fv = FormValidation.formValidation(formAuthentication, {
            fields: {
                email: {
                    validators: {
                        notEmpty: {
                            message: "Please enter your email",
                        },
                        stringLength: {
                            min: 3,
                            message: "Must be at least 3 characters",
                        },
                        emailAddress: {
                            message: "Email format is invalid",
                        },
                    },
                },
                password: {
                    validators: {
                        notEmpty: {
                            message: "Please enter your password",
                        },
                        stringLength: {
                            min: 6,
                            message: "Password must be at least 6 characters",
                        },
                    },
                },
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: "",
                    rowSelector: ".mb-3",
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus(),
            },
            init: (instance) => {
                instance.on("plugins.message.placed", function (e) {
                    if (
                        e.element.parentElement.classList.contains(
                            "input-group"
                        )
                    ) {
                        e.element.parentElement.insertAdjacentElement(
                            "afterend",
                            e.messageElement
                        );
                    }
                });
            },
        });

        // Submit via AJAX
        fv.on("core.form.valid", function () {
            const formData = {
                _token: formAuthentication.querySelector('input[name="_token"]')
                    .value,
                email: formAuthentication.querySelector('input[name="email"]')
                    .value,
                password: formAuthentication.querySelector(
                    'input[name="password"]'
                ).value,
            };

            btn.disabled = true;
            btn.innerText = "Logging in...";
            alert.classList.add("d-none");
            alert.innerText = "";

            $.ajax({
                url: "/login",
                method: "POST",
                data: formData,
                success: function (res) {
                    alert.classList.remove("alert-danger");
                    alert.classList.add("alert-success");
                    alert.innerText =
                        res.message ?? "Login success, redirecting...";
                    alert.classList.remove("d-none");
                    setTimeout(() => {
                        window.location.href = res.redirect ?? "/dashboard";
                    }, 1000);
                },
                error: function (xhr) {
                    let msg = "Login failed. Please try again.";

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const firstError = Object.values(
                            xhr.responseJSON.errors
                        )[0];
                        msg = Array.isArray(firstError)
                            ? firstError[0]
                            : firstError;
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }

                    alert.innerText = msg;
                    alert.classList.remove("d-none");
                    btn.disabled = false;
                    btn.innerText = "Sign in";
                },
            });
        });
    }
});
