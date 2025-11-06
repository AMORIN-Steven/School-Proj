$(document).ready(function () {
    $("#confirmer").on("click", function () {
        

        let nouveauMdp = $("#nouveau_mdp").val();

        if (nouveauMdp.length < 12) {
            alert("Erreur : Le mot de passe doit contenir au moins 12 caractères !")
        }else{
            alert("Votre nouveau mot de passe a été enrégistré avec succès.")
        }

        let aDesLettres = /[a-zA-Z]/.test(nouveauMdp);
        let aDesChiffres = /[0-9]/.test(nouveauMdp);

        if (!aDesLettres || !aDesChiffres) {
            alert("Erreur : Le mot de passe doit contenir à la fois des lettres et des chiffres !");
        }else{
          alert("Succès ! Votre mot de passe a été modifié avec succès.");
        }

        
        setTimeout(function () {
            window.location.href = "Connexion.html";
        }, 1000);

    });
});