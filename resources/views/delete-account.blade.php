<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <title>Hisobni O'chirish</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: sans-serif;
            background: #f6f8fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 360px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #0002;
            padding: 32px 24px;
        }

        h1 {
            font-size: 1.6rem;
            margin-bottom: 16px;
        }

        label {
            font-size: 1rem;
            margin-bottom: 8px;
            display: block;
        }

        input[type="tel"],
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #e53935;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            font-weight: 600;
            margin-bottom: 8px;
        }

        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .msg {
            margin-top: 12px;
            font-size: 1rem;
            color: #388e3c;
            text-align: center;
        }

        .error {
            color: #e53935;
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Hisobni O'chirish</h1>
        <form id="step1">
            <label for="phone">Telefon raqamingizni kiriting:</label>
            <input type="tel" id="phone" name="phone" placeholder="+998901234567" pattern="^\+998\d{9}$" required>
            <button type="submit">Davom etish</button>
            <div class="msg error hidden" id="phoneError"></div>
        </form>
        <form id="step2" class="hidden">
            <label for="otp">SMS orqali kelgan kodni kiriting:</label>
            <input type="text" id="otp" name="otp" maxlength="6" minlength="4" required pattern="^\d{4,6}$" autocomplete="one-time-code">
            <button type="submit">Hisobni o'chirish</button>
            <button type="button" id="resendBtn" style="background:#1976d2;">Qayta yuborish</button>
            <div class="msg error hidden" id="otpError"></div>
            <div class="msg hidden" id="otpSuccess"></div>
        </form>
        <div class="msg hidden" id="finalMsg"></div>
    </div>
    <script>
        // Emulate OTP (for demo only!)
        let generatedOTP = '';
        let phone = '';

        function randomOTP() {
            return "1111";
            return ('' + Math.floor(1000 + Math.random() * 9000));
        }

        function show(el) {
            el.classList.remove("hidden");
        }

        function hide(el) {
            el.classList.add("hidden");
        }

        document.getElementById('step1').onsubmit = function(e) {
            e.preventDefault();
            phone = document.getElementById('phone').value.trim();
            const phoneError = document.getElementById('phoneError');
            phoneError.textContent = '';
            hide(phoneError);

            if (!/^\+998\d{9}$/.test(phone)) {
                phoneError.textContent = "To'g'ri formatda raqam kiriting: +998901234567";
                show(phoneError);
                return;
            }

            // Emulate sending OTP
            generatedOTP = randomOTP();
            //   alert("Demo: Sizning OTP kodi: " + generatedOTP);

            hide(document.getElementById('step1'));
            show(document.getElementById('step2'));
            document.getElementById('otp').focus();
        };

        document.getElementById('step2').onsubmit = function(e) {
            e.preventDefault();
            const otpInput = document.getElementById('otp').value.trim();
            const otpError = document.getElementById('otpError');
            otpError.textContent = '';
            hide(otpError);

            if (otpInput === generatedOTP) {
                hide(document.getElementById('step2'));
                const finalMsg = document.getElementById('finalMsg');
                finalMsg.textContent = "Hisobingiz muvaffaqiyatli o'chirildi!";
                show(finalMsg);
            } else {
                otpError.textContent = "Yuborilgan kod noto'g'ri. Qayta urinib ko'ring.";
                show(otpError);
            }
        };

        document.getElementById('resendBtn').onclick = function() {
            generatedOTP = randomOTP();
            //   alert("Demo: Sizning yangi OTP kodi: " + generatedOTP);
            const otpSuccess = document.getElementById('otpSuccess');
            otpSuccess.textContent = "Yangi kod yuborildi!";
            show(otpSuccess);
            setTimeout(() => {
                hide(otpSuccess);
            }, 2500);
        };
    </script>
</body>

</html>