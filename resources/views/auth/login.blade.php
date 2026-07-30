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
            --primary-green: #86A789;
            --primary-green-hover: #4F6F52;
            --bg-light: #F8FAFC;
        }

        body {
            background-color: var(--bg-light);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden; /* Mencegah munculnya scrollbar yang tidak perlu */
        }

        .login-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .icon-container {
            width: 50px;
            height: 50px;
            background-color: var(--primary-green);
            border-radius: 10px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 10px rgba(79, 190, 137, 0.25);
        }

        .btn-primary-custom {
            background-color: var(--primary-green);
            border: none;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-primary-custom:hover {
            background-color: var(--primary-green-hover);
            color: white;
            transform: translateY(-1px);
        }

        .form-control {
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <!-- Kolom diperkecil untuk desktop agar kartu tidak terlalu lebar -->
            <div class="col-11 col-sm-8 col-md-6 col-lg-4">
                <div class="card login-card">
                    <!-- Padding dikurangi dari p-5 menjadi p-4 -->
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <div class="icon-container mb-2">
                                <i class="fa-solid fa-anchor text-white fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: var(--corporate-dark);">SupplySync</h5>
                            <p class="text-muted small mb-0">Global Supply Chain Risk Intelligence</p>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger small rounded-3 border-0 py-2 mb-3">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $errors->first() }}
                            </div>
                        @endif

                        <div id="loginError" class="alert alert-danger d-none py-2 small mb-3"></div>

                        <form id="loginForm">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label text-secondary" style="font-size: 0.75rem; font-weight: 700;">ALAMAT EMAIL</label>
                                <input id="email" type="email" class="form-control bg-light border-0" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-secondary" style="font-size: 0.75rem; font-weight: 700;">PASSWORD</label>
                                <input id="password" type="password" class="form-control bg-light border-0" required>
                            </div>
                            <button type="submit" class="btn btn-primary-custom w-100 fw-bold py-2 mb-2" style="border-radius: 8px; font-size: 0.9rem;">
                                Masuk ke Sistem
                            </button>
                            
                            <div class="d-flex align-items-center my-2">
                                <hr class="flex-grow-1">
                                <span class="px-2 text-muted" style="font-size: 0.7rem;">ATAU</span>
                                <hr class="flex-grow-1">
                            </div>

                            <button type="button" id="googleLogin" class="btn btn-outline-secondary w-100 py-1" style="font-size: 0.9rem;">
                                <i class="fab fa-google me-2"></i> Masuk dengan Google
                            </button>

                            <div class="text-center mt-3" style="font-size: 0.85rem;">
                                Belum punya akun?
                                <a href="{{ route('register') }}" style="color: var(--primary-green); text-decoration: none; font-weight: 600;">Daftar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LOGIKA JAVASCRIPT SAMA SEKALI TIDAK DISENTUH -->
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