<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactez-nous - GS Auto</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 1000px;
            width: 100%;
            margin: 20px;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1e293b, #334155);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }
        .header img {
            height: 60px;
            width: auto;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 600;
        }
        .content {
            display: flex;
            min-height: 500px;
        }
        .form-section {
            flex: 1;
            padding: 40px;
            background: #f8fafc;
        }
        .contact-info {
            flex: 1;
            padding: 40px;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .contact-info h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #1e293b;
        }
        .contact-info p {
            margin: 15px 0;
            color: #64748b;
            font-size: 1rem;
        }
        .form-section h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1e293b;
        }
        .form-section p {
            color: #64748b;
            margin-bottom: 30px;
        }
        form {
            display: grid;
            gap: 20px;
        }
        label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 5px;
            display: block;
        }
        input, textarea, select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            background: white;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .radio-group {
            display: grid;
            gap: 12px;
        }
        .radio-option {
            display: flex;
            align-items: flex-start;
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s, background 0.3s;
        }
        .radio-option:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
        }
        .radio-option input[type="radio"] {
            margin-right: 12px;
            margin-top: 2px;
        }
        .radio-option strong {
            display: block;
            color: #1e293b;
        }
        .radio-option span {
            font-size: 0.875rem;
            color: #64748b;
        }
        button {
            background: linear-gradient(135deg, #1e293b, #334155);
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30, 41, 59, 0.3);
        }
        .success {
            background: #dcfce7;
            color: #166534;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #bbf7d0;
            font-weight: 500;
        }
        .error {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 5px;
        }
        @media (max-width: 768px) {
            .content {
                flex-direction: column;
            }
            .form-section, .contact-info {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/GS.png') }}" alt="GS Auto">
            <h1>Contactez-nous</h1>
        </div>
        <div class="content">
            <div class="form-section">
                <h2>Envoyez-nous un message</h2>
                <p>Nous vous répondrons rapidement.</p>

                @if(session('success'))
                    <div class="success">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.send') }}">
                    @csrf

                    <div>
                        <label for="type">Objet de votre demande *</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="type" value="general" required>
                                <strong>Contact général</strong>
                                <span>Questions, support ou informations</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="type" value="demo">
                                <strong>Demande de démonstration</strong>
                                <span>Découvrez nos fonctionnalités en live</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="type" value="partner">
                                <strong>Devenir partenaire</strong>
                                <span>Opportunités de collaboration commerciale</span>
                            </label>
                        </div>
                        @error('type') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="name">Nom complet *</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required placeholder="Votre nom complet">
                        @error('name') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="email">Adresse e-mail *</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="exemple@domaine.com">
                        @error('email') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" rows="4" required placeholder="Décrivez votre demande en détail...">{{ old('message') }}</textarea>
                        @error('message') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="g-recaptcha" data-sitekey="6LeYtPsrAAAAADlJgiFwZWpdL4DfWOf0OH94yggu"></div>

                    <button type="submit">Envoyer le message</button>
                </form>
            </div>
            <div class="contact-info">
                <h2>Informations de contact</h2>
                <p><strong>Téléphone :</strong> 01 84 80 68 32</p>
                <p><strong>Email :</strong> contact@gagestion.fr</p>
                <p><strong>Horaires :</strong> Lundi - Samedi, 9h - 18h</p>
            </div>
        </div>
    </div>
</body>
</html>
