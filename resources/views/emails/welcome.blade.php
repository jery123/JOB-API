<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #4CAF50;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 0.9em;
            color: #777777;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .container {
                width: 100%;
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue, {{ $user->nom }} !</h1>
        </div>
        <div class="content">
            <p>Merci de vous être inscrit sur notre plateforme. Nous sommes ravis de vous avoir parmi nous.</p>
            <p>Veuillez vérifier votre adresse e-mail en utilisant le code OTP que nous vous avons envoyé.</p>
            <p>Pour toute question ou assistance, n'hésitez pas à <a href="mailto:support@exemple.com">contacter notre équipe de support</a>.</p>
        </div>
        <div class="footer">
            <p>Cordialement,<br>L'équipe de support</p>
        </div>
    </div>
</body>
</html>
