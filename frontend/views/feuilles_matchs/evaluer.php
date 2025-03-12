<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../views/header.php'; ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Évaluation des joueurs</title>
    <link rel="stylesheet" href="../views/css/style.css">
    <script>
        function toggleEvaluation(checkbox, index) {
            const evaluationField = document.getElementById(`evaluation_${index}`);
            evaluationField.disabled = !checkbox.checked;
            if (!checkbox.checked) {
                evaluationField.value = ''; // Réinitialiser la valeur
            }

            // Check the state of all checkboxes to enable/disable the submit button
            updateSubmitButtonState();
        }

        function updateSubmitButtonState() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            const submitButton = document.querySelector('button[type="submit"]');

            // Enable the button if any checkbox is checked
            const isAnyCheckboxChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
            submitButton.disabled = !isAnyCheckboxChecked;
        }

        function validateEvaluations(event) {
            const evaluations = document.querySelectorAll('input[type="number"]:not([disabled])');
            let isValid = true;

            evaluations.forEach((input) => {
                const value = parseInt(input.value, 10);
                if (isNaN(value) || value < 1 || value > 5) {
                    isValid = false;
                }
            });

            if (!isValid) {
                event.preventDefault();
                alert("Toutes les évaluations doivent être des nombres entre 1 et 5.");
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            updateSubmitButtonState(); // Initialize the button state on page load
        });
    </script>
</head>

<body>
    <div class="container">
        <a href="../controllers/FeuilleMatchController.php?action=afficher&id_match=<?= htmlspecialchars($id_match) ?>" class="btn btn-back">Retour</a>
        <h1>Évaluation des joueurs</h1>

        <?php if (!empty($participe)): ?>
            <form action="FeuilleMatchController.php?action=valider_evaluation&id_match=<?= htmlspecialchars($id_match) ?>" method="POST" onsubmit="validateEvaluations(event)">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Poste</th>
                            <th>Rôle</th>
                            <th>Évaluer</th>
                            <th>Évaluation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participe as $index => $joueur): ?>
                            <tr>
                                <td><?= htmlspecialchars($joueur['nom_joueur'] ?? 'N/A'); ?></td>
                                <td><?= htmlspecialchars($joueur['prenom_joueur'] ?? 'N/A'); ?></td>
                                <td><?= htmlspecialchars($joueur['poste'] ?? 'N/A'); ?></td>
                                <td><?= htmlspecialchars($joueur['role'] ?? 'N/A'); ?></td>
                                <td>
                                    <input type="checkbox" id="checkbox_<?= $index ?>" onclick="toggleEvaluation(this, <?= $index ?>)">
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        name="evaluations[<?= htmlspecialchars($joueur['numero_licence']) ?>]"
                                        id="evaluation_<?= htmlspecialchars($index) ?>"
                                        min="1"
                                        max="5"
                                        placeholder="1-5"
                                        style="width: 40px; text-align: center;"
                                        disabled>
                                    <input
                                        type="hidden"
                                        name="roles[<?= htmlspecialchars($joueur['numero_licence']) ?>]"
                                        value="<?= htmlspecialchars($joueur['role']) ?>">
                                    <input
                                        type="hidden"
                                        name="postes[<?= htmlspecialchars($joueur['numero_licence']) ?>]"
                                        value="<?= htmlspecialchars($joueur['poste']) ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" disabled>Valider les Évaluations</button>
            </form>
        <?php else: ?>
            <p>Aucun joueur sélectionné pour ce match.</p>
        <?php endif; ?>
    </div>
</body>

</html>