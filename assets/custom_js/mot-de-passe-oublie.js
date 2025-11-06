$(document).ready(function () {
    $("#confirmer").on("click", function () {
        

        let nouveauMdp = $("#nouveau_mdp").val();

        // Vérification 1 : Au moins 12 caractères
        if (nouveauMdp.length < 12) {
            alert("Erreur : Le mot de passe doit contenir au moins 12 caractères !")
        }else{
            alert("Votre nouveau mot de passe a été enrégistré avec succès.")
        }

        // Vérification 2 : Doit contenir des lettres ET des chiffres
        let aDesLettres = /[a-zA-Z]/.test(nouveauMdp);
        let aDesChiffres = /[0-9]/.test(nouveauMdp);

        if (!aDesLettres || !aDesChiffres) {
            alert("Erreur : Le mot de passe doit contenir à la fois des lettres et des chiffres !");
        }else{
          alert("Succès ! Votre mot de passe a été modifié avec succès.");
        }

        // Si tout est bon → message de succès + simulation d'envoi
        
        
        // Optionnel : rediriger vers la page de connexion après 1 seconde
        setTimeout(function () {
            window.location.href = "Connexion.html";
        }, 1000);

        // Ici tu pourras plus tard ajouter l'AJAX vers PHP pour sauvegarder en base
        // $.ajax({ url: "php/update_password.php", ... });
    });
});