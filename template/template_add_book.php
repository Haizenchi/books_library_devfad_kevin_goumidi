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
        <h1>Ajouter un livre</h1>
        <form action="" method="post">
            <input type="text" name="title" placeholder="saisir le titre">
            <textarea name="content" placeholder="saisir le résumé"></textarea>
            <label for="publish_at">Saisir le date de publication
                <input type="date" name="publish_at">
            </label>
            <input type="text" name="author" placeholder="saisir le nom de l'auteur">
            <select name="categories[]" multiple size="4">
                <option disabled>
                    Selectionner les categories ...
                </option>
                <?php foreach ($data["categories"] ?? [] as $category) : ?>
                   <option value="<?= $category->getId() ?>"><?= $category->getName() ?></option>
                <?php endforeach ?>
            </select>
            <input type="submit" value="Inscription" name="submit">
        </form>
        <p><?= htmlspecialchars($data["msg"] ?? "", ENT_QUOTES, 'UTF-8') ?></p>
    </main>
</body>

</html>