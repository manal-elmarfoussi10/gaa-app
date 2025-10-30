<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de votre compte - GS Auto</title>
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
            max-width: 600px;
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
        .form-section {
            padding: 40px;
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
        input, select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            background: white;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus, select:focus {
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
            align-items: center;
            padding: 12px;
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
        }
        .checkbox-group {
            display: grid;
            gap: 8px;
        }
        .checkbox-option {
            display: flex;
            align-items: flex-start;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s, background 0.3s;
        }
        .checkbox-option:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
        }
        .checkbox-option input[type="checkbox"] {
            margin-right: 12px;
            margin-top: 2px;
        }
        .checkbox-option label {
            margin: 0;
            cursor: pointer;
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
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #64748b;
        }
        .login-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .form-section {
                padding: 20px;
            }
            .header {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/GS.png') }}" alt="GS Auto">
            <h1>Création de votre compte</h1>
        </div>
        <div class="form-section">
            <h2>Rejoignez GS Auto</h2>
            <p>Créez votre compte professionnel.</p>

            @if(session('success'))
                <div class="success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="error" style="background: #fef2f2; color: #dc2626; padding: 12px; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div>
                    <label for="first_name">Prénom *</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required placeholder="Votre prénom">
                </div>

                <div>
                    <label for="last_name">Nom *</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required placeholder="Votre nom">
                </div>

                <div>
                    <label for="email">Adresse Email *</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="exemple@domaine.com">
                </div>

                <div>
                    <label for="company_name">Nom de la société *</label>
                    <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="Nom de votre société">
                </div>

                <div>
                    <label for="commercial_name">Nom commercial de la société</label>
                    <input id="commercial_name" type="text" name="commercial_name" value="{{ old('commercial_name') }}" placeholder="Nom commercial (optionnel)">
                </div>

                <div>
                    <label for="phone">Numéro de téléphone *</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required placeholder="01 23 45 67 89">
                </div>

                <div>
                    <label for="siret">Numéro de siret *</label>
                    <input id="siret" type="text" name="siret" value="{{ old('siret') }}" required placeholder="123 456 789 01234">
                </div>

                <div>
                    <label for="tva">Numéro de TVA *</label>
                    <input id="tva" type="text" name="tva" value="{{ old('tva') }}" required placeholder="FR 12 345678901">
                </div>

                <div>
                    <label for="garage_type">Quel type de garage avez vous ?</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="garage_type" value="fixe" {{ old('garage_type') == 'fixe' ? 'checked' : '' }}>
                            Fixe - Changement pare brise sur place
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="garage_type" value="mobile" {{ old('garage_type') == 'mobile' ? 'checked' : '' }}>
                            Mobile - Changement chez le client
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="garage_type" value="both" {{ old('garage_type') == 'both' ? 'checked' : '' }}>
                            Les deux
                        </label>
                    </div>
                </div>

                <div>
                    <label for="known_by">Comment avez-vous connu GS Auto ?</label>
                    <select id="known_by" name="known_by">
                        <option value="">Sélectionnez une option</option>
                        <option value="site_web" {{ old('known_by') == 'site_web' ? 'selected' : '' }}>Site web</option>
                        <option value="google" {{ old('known_by') == 'google' ? 'selected' : '' }}>Google</option>
                        <option value="parrainage" {{ old('known_by') == 'parrainage' ? 'selected' : '' }}>Parrainage / Recommandation</option>
                        <option value="evenements" {{ old('known_by') == 'evenements' ? 'selected' : '' }}>Évènements</option>
                        <option value="prospection" {{ old('known_by') == 'prospection' ? 'selected' : '' }}>Prospection</option>
                        <option value="chatgpt" {{ old('known_by') == 'chatgpt' ? 'selected' : '' }}>ChatGPT</option>
                        <option value="reseaux_sociaux" {{ old('known_by') == 'reseaux_sociaux' ? 'selected' : '' }}>Réseaux sociaux</option>
                        <option value="emailing" {{ old('known_by') == 'emailing' ? 'selected' : '' }}>E-mailing</option>
                        <option value="autre" {{ old('known_by') == 'autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>

                <div>
                    <label for="password">Mot de passe *</label>
                    <input id="password" type="password" name="password" required placeholder="Mot de passe">
                </div>

                <div>
                    <label for="password_confirmation">Répéter le mot de passe *</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Confirmez le mot de passe">
                </div>

                <div>
                    <div class="checkbox-group">
                        <label class="checkbox-option">
                            <input type="checkbox" name="terms" value="1" required>
                            <span>J'accepte les <a href="https://gservicesauto.com/politique-confidentialite/" target="_blank" style="color: #3b82f6; text-decoration: underline;">conditions générales d'utilisation</a> et la <a href="https://gservicesauto.com/mentions-legales/" target="_blank" style="color: #3b82f6; text-decoration: underline;">politique de confidentialité</a></span>
                        </label>
                    </div>
                </div>

                <div class="g-recaptcha" data-sitekey="6LeYtPsrAAAAADlJgiFwZWpdL4DfWOf0OH94yggu"></div>

                <button type="submit">Créer mon compte</button>
            </form>

            <div class="login-link">
                <span>Déjà un compte ?</span>
                <a href="{{ route('login') }}"> Se connecter</a>
            </div>
        </div>
    </div>
</body>
</html>
