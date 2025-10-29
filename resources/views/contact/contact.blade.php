<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactez-nous - GS Auto</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 1200px;
            width: 100%;
            margin: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1f2937, #374151);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header img {
            height: 80px;
            width: auto;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: bold;
        }
        .content {
            display: flex;
            flex-direction: column;
            padding: 40px;
        }
        .contact-info {
            flex: 1;
            padding-right: 40px;
            border-right: 1px solid #e5e7eb;
            margin-bottom: 40px;
        }
        .contact-info h2 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #1f2937;
        }
        .contact-info p {
            margin: 10px 0;
            color: #6b7280;
        }
        .form-section {
            flex: 1;
            padding-left: 40px;
        }
        .form-section h2 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1f2937;
        }
        .form-section p {
            color: #6b7280;
            margin-bottom: 30px;
        }
        form {
            display: grid;
            gap: 20px;
        }
        label {
            font-weight: 600;
            color: #374151;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            background: #f9fafb;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        button {
            background: #1f2937;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #374151;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #a7f3d0;
        }
        .error {
            color: #dc2626;
            font-size: 0.875rem;
        }
        @media (max-width: 768px) {
            .content {
                flex-direction: column;
                padding: 20px;
            }
            .contact-info {
                padding-right: 0;
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
                padding-bottom: 20px;
                margin-bottom: 20px;
            }
            .form-section {
                padding-left: 0;
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
            <div class="contact-info">
                <h2>Informations de contact</h2>
                <p><strong>Téléphone :</strong> 01 84 80 68 32</p>
                <p><strong>Email :</strong> contact@gagestion.fr</p>
                <p><strong>Horaires :</strong> Lundi - Samedi, 9h - 18h</p>
            </div>
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
                    <input type="hidden" name="type" value="general">

                    <div>
                        <label for="company_name">Nom de l'entreprise (optionnel)</label>
                        <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Nom de votre entreprise">
                        @error('company_name') <span class="error">{{ $message }}</span> @enderror
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
                        <label for="subject">Objet *</label>
                        <input id="subject" type="text" name="subject" value="{{ old('subject') }}" required placeholder="Objet de votre message">
                        @error('subject') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" rows="4" required placeholder="Décrivez votre demande en détail...">{{ old('message') }}</textarea>
                        @error('message') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit">Envoyer le message</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
