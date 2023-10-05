<html lang="fr">
<head>
    <!-- Meta -->
    <meta charset="utf-8">
    <title>OSCAR : Erreur fatale</title>
    <!-- Links -->
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            background: #990000;
            color: white;
            font-size: 1.6em;
        }
        body section {
            display: flex;
            align-items: center;
            justify-items: center;
            align-content: center;
            justify-content: center;
            font-family: sans-serif;
            text-align: center;
        }
    </style>
</head>

<body>
    <section>
        <div class="error">
            <h1>OSCAR ! Erreur fatale</h1>
            <p>
            <?php if( $err ): ?>
                <?= $err ?>
            <?php else: ?>
                Erreur inconnue
            <?php endif; ?>
            </p>
        </div>
    </section>
</body>
</html>
