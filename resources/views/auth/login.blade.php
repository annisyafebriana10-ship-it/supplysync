<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Global Supply Chain Risk Intelligence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --corporate-dark: #1E293B;
            --matcha-500: #86A789;
            --matcha-700: #4F6F52;
        }

        body {
            background-color: #F8FAFC;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex justify-content-center align-items-center mb-3 shadow-sm"
                                style="width: 60px; height: 60px; background-color: var(--matcha-500); border-radius: 12px;">
                                <i class="fa-solid fa-anchor text-white fs-3"></i>
                            </div>
                            <h4 class="fw-bold" style="color: var(--corporate-dark);">SupplySync</h4>
                            <p class="text-muted small">Global Supply Chain Risk Intelligence</p>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger small rounded-3 border-0">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $errors->first() }}
                            </div>
                        @endif

                        <div id="loginError" class="alert alert-danger d-none"></div>

                        <form id="loginForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-bold">ALAMAT EMAIL</label>
                                <input id="email" type="email" class="form-control bg-light border-0 py-2" required
                                    autofocus>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-secondary small fw-bold">PASSWORD</label>
                                <input id="password" type="password" class="form-control bg-light border-0 py-2"
                                    required>
                            </div>
                            <button type="submit" class="btn w-100 text-white fw-bold py-2 mb-3 shadow-sm"
                                style="background-color: var(--matcha-700); border-radius: 8px;">
                                Masuk ke Sistem
                            </button>
                            <div class="d-flex align-items-center my-3">
                                <hr class="flex-grow-1">
                                <span class="px-2 text-muted small">ATAU</span>
                                <hr class="flex-grow-1">
                            </div>

                            <button type="button" id="googleLogin" class="btn btn-outline-secondary w-100 py-2">

                                <i class="fab fa-google me-2"></i>
                                Masuk dengan Google

                            </button>

                            <div class="text-center mt-3">
                                Belum punya akun?
                                <a href="{{ route('register') }}">Daftar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="module">

        import { initializeApp } from "https://www.gstatic.com/firebasejs/12.0.0/firebase-app.js";

        import {
            getAuth,
            signInWithEmailAndPassword,
            GoogleAuthProvider,
            signInWithPopup
        }
            from "https://www.gstatic.com/firebasejs/12.0.0/firebase-auth.js";

        const firebaseConfig = {
            apiKey: "AIzaSyDAvBztDGbruXundThEdIfjxx-ILsfUgdQ",
            authDomain: "supplysync-de524.firebaseapp.com",
            projectId: "supplysync-de524",
            storageBucket: "supplysync-de524.firebasestorage.app",
            messagingSenderId: "223249963655",
            appId: "1:223249963655:web:0f38fefb93f07bcccc3622"
        };

        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);

        const provider = new GoogleAuthProvider();

        const form = document.getElementById("loginForm");
        const errorBox = document.getElementById("loginError");

        form.addEventListener("submit", async function (e) {

            e.preventDefault();

            errorBox.classList.add("d-none");

            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value;

            try {

                // Login ke Firebase
                const credential = await signInWithEmailAndPassword(
                    auth,
                    email,
                    password
                );

                // Ambil ID Token
                const idToken = await credential.user.getIdToken();

                // Kirim ke Laravel
                const response = await fetch("/firebase-login", {

                    method: "POST",

                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },

                    body: JSON.stringify({
                        idToken: idToken
                    })

                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || "Login gagal.");
                }

                // Redirect sesuai role
                window.location.href = result.redirect;

            } catch (error) {

                errorBox.textContent = error.message;
                errorBox.classList.remove("d-none");

            }

        });

        document
            .getElementById("googleLogin")
            .addEventListener("click", async () => {

                try {

                    const result = await signInWithPopup(auth, provider);

                    const idToken = await result.user.getIdToken();

                    const response = await fetch("/firebase-login", {

                        method: "POST",

                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },

                        body: JSON.stringify({
                            idToken: idToken
                        })

                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || "Login gagal.");
                    }

                    window.location.href = data.redirect;

                } catch (error) {

                    alert(error.message);

                }

            });

    </script>

</body>

</html>