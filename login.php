<?php
session_start();
// Eğer kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap / Kayıt Ol - SmartHR</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#faf5ff', 100: '#f3e8ff', 500: '#a855f7', 600: '#9333ea', 700: '#7e22ce', 900: '#581c87',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-50 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-2xl shadow-xl border border-brand-100 w-full max-w-md relative overflow-hidden">
        <!-- Dekoratif Arkaplan Şekli -->
        <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-brand-100 opacity-50 blur-2xl"></div>
        <div class="absolute bottom-0 left-0 -ml-8 -mb-8 w-24 h-24 rounded-full bg-brand-500 opacity-20 blur-xl"></div>

        <div class="text-center mb-8 relative z-10">
            <h1 class="text-3xl font-extrabold text-brand-700">SmartHR</h1>
            <p class="text-gray-500 mt-2">Kariyerinize yön verin</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm text-center font-medium border border-red-200 relative z-10">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-sm text-center font-medium border border-green-200 relative z-10">
                Kayıt başarılı! Şimdi giriş yapabilirsiniz.
            </div>
        <?php endif; ?>

        <!-- Tab Seçimi -->
        <div class="flex mb-6 border-b border-gray-200 relative z-10">
            <button id="tab-login" onclick="switchTab('login')" class="w-1/2 pb-3 font-semibold text-brand-600 border-b-2 border-brand-600 transition-colors">Giriş Yap</button>
            <button id="tab-register" onclick="switchTab('register')" class="w-1/2 pb-3 font-semibold text-gray-500 hover:text-brand-500 transition-colors">Üye Ol</button>
        </div>

        <!-- Giriş Yap Formu -->
        <form id="form-login" action="auth.php" method="POST" class="space-y-5 relative z-10">
            <input type="hidden" name="action" value="login">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-Posta Adresi</label>
                <input type="email" name="email" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition outline-none" placeholder="ornek@mail.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Şifre</label>
                <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition outline-none" placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-brand-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-brand-700 hover:shadow-lg transition transform hover:-translate-y-0.5">
                Giriş Yap
            </button>
        </form>

        <!-- Üye Ol Formu (Gizli Başlar) -->
        <form id="form-register" action="auth.php" method="POST" class="space-y-5 hidden relative z-10">
            <input type="hidden" name="action" value="register">

            <div class="grid grid-cols-2 gap-4">
                <label class="cursor-pointer">
                    <input type="radio" name="user_type" value="candidate" class="peer sr-only" checked onchange="toggleFields()">
                    <div class="text-center px-4 py-2 border border-gray-300 rounded-lg peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 font-medium transition">Adayım</div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="user_type" value="employer" class="peer sr-only" onchange="toggleFields()">
                    <div class="text-center px-4 py-2 border border-gray-300 rounded-lg peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 font-medium transition">İşverenim</div>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-Posta Adresi</label>
                <input type="email" name="email" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500 outline-none" placeholder="ornek@mail.com">
            </div>

            <div id="candidate-fields" class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ad</label>
                    <input type="text" name="first_name" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Soyad</label>
                    <input type="text" name="last_name" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
            </div>

            <div id="employer-fields" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Şirket Adı</label>
                <input type="text" name="company_name" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Şifre</label>
                <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500 outline-none" placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-brand-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-brand-700 hover:shadow-lg transition transform hover:-translate-y-0.5">
                Kayıt Ol
            </button>
        </form>
    </div>

    <script>
        function switchTab(tab) {
            const btnLogin = document.getElementById('tab-login');
            const btnRegister = document.getElementById('tab-register');
            const formLogin = document.getElementById('form-login');
            const formRegister = document.getElementById('form-register');

            if (tab === 'login') {
                btnLogin.classList.add('text-brand-600', 'border-b-2', 'border-brand-600');
                btnLogin.classList.remove('text-gray-500');
                btnRegister.classList.remove('text-brand-600', 'border-b-2', 'border-brand-600');
                btnRegister.classList.add('text-gray-500');
                
                formLogin.classList.remove('hidden');
                formRegister.classList.add('hidden');
            } else {
                btnRegister.classList.add('text-brand-600', 'border-b-2', 'border-brand-600');
                btnRegister.classList.remove('text-gray-500');
                btnLogin.classList.remove('text-brand-600', 'border-b-2', 'border-brand-600');
                btnLogin.classList.add('text-gray-500');

                formRegister.classList.remove('hidden');
                formLogin.classList.add('hidden');
            }
        }

        function toggleFields() {
            const userType = document.querySelector('input[name="user_type"]:checked').value;
            const candidateFields = document.getElementById('candidate-fields');
            const employerFields = document.getElementById('employer-fields');

            if (userType === 'candidate') {
                candidateFields.classList.remove('hidden');
                employerFields.classList.add('hidden');
            } else {
                candidateFields.classList.add('hidden');
                employerFields.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
