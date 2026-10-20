<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <title><?= $title ?? "" ?></title>
</head>
<body>
    <?php include 'component/navbar.php'; ?>
    <main class="container-fluid">
        <h1>Se connecter</h1>
        <form action="/login" method="post">
            <label for="email">Entrez votre email :</label>
            <input type="text" name="email" placeholder="saisir votre Email" required>
            <label for="password">Entrez votre mot de passe :</label>
            <input type="password" name="password" placeholder="saisir le mot de passe" required>
            <input type="submit" value="Connexion" name="submit">
        </form>
        <p><?= $data["msg"] ?? "" ?></p>
    </main>
</body>
</html>
