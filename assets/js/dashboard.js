// Fonction appelée quand l'utilisateur choisit un rôle
function selectRole(role) {
  document.getElementById("role-title").textContent = role;
  document.getElementById("role-selection").classList.add("d-none");
  document.getElementById("dashboard").classList.remove("d-none");
  renderMenu(role);
}

// Fonction pour revenir au choix du rôle
function logout() {
  location.reload();
}

// Structure des menus par rôle
const menuItems = {
  Administrateur: [
    { label: "Filière (CRUD)", action: showFilieres},
    { label: "Matière (CRUD)", action: showMatieres},
    { label: "Ajouter Professeurs", action: showAjoutProfs},
    { label: "Ajouter Matières par Filière/Matière", action: showAjoutMatiereFiliere},
    { label: "Gestion Étudiants", action: showGestionEtudiants},
    { label: "Paramètres", action: showSettings},
    { label: "Profil", action: showProfilAdmin},
    { label: "Déconnexion", action: logout}
  ],
  Parent: [
    { label: "Liste des Enfants", action: showEnfants},
    { label: "Notes par Enfant", action: showNotesEnfant},
    { label: "Profil", action: showProfilParent},
    { label: "Paramètres", action: showSettings},
    { label: "Déconnexion", action: logout}
  ],
  Professeur: [
    { label: "Liste de mes Matières", action: showMatieres},
    { label: "Notes par Matière", action: showNotesParMatiere},
    { label: "Profil", action: showProfilProf},
    { label: "Paramètres", action: showSettings},
    { label: "Déconnexion", action: logout}
  ],
  Etudiant: [
    { label: "Consulter mes Notes", action: showMesNotes},
    { label: "Liste des Professeurs", action: showProfesseurs},
    { label: "Liste des Matières", action: showMatieres},
    { label: "Profil", action: showProfilEtudiant},
    { label: "Paramètres", action: showSettings},
    { label: "Déconnexion", action: logout}
  ]
};

// Générer le menu latéral
function renderMenu(role) {
  const sidebar = document.getElementById("sidebar-menu");
  sidebar.innerHTML = "";
  menuItems[role].forEach(item => {
    const li = document.createElement("li");
    li.className = "nav-item mb-2";
    const a = document.createElement("a");
    a.className = "nav-link text-white";
    a.href = "#";
    a.textContent = item.label;
    a.onclick = item.action;
    li.appendChild(a);
    sidebar.appendChild(li);
});
}

// Fonction utilitaire pour afficher une section
function renderSection(title, items) {
  const content = document.getElementById("dashboard-content");
  content.innerHTML = <h2>${title}</h2><ul class="list-group mt-3">${items.map(item => `<li class="list-group-item">${item}</li>).join("")}</ul>`;
}

// Fonctions pour l'Administrateur
function showFilieres() {
  renderSection("Gestion des Filières", ["Ajouter", "Modifier", "Supprimer"]);
}

function showMatieres() {
  renderSection("Gestion des Matières", ["Ajouter", "Modifier", "Supprimer"]);
}

function showAjoutProfs() {
  renderSection("Ajout des Professeurs", ["Associer à Filière", "Associer à Matière"]);
}

function showAjoutMatiereFiliere() {
  renderSection("Ajout de Matières par Filière/Matière", ["Filière: Informatique → Programmation", "Filière: Gestion → Comptabilité"]);
}

function showGestionEtudiants() {
  const content = document.getElementById("dashboard-content");
  content.innerHTML = `
    <h2>Gestion des Étudiants</h2>
    <div class="list-group mt-3">
      <button class="list-group-item list-group-item-action" onclick="showListeEtudiants()">📋 Liste des étudiants par filière/matière</button>
      <button class="list-group-item list-group-item-action" onclick="showNotesEtudiant()">📊 Notes par étudiant</button>
      <button class="list-group-item list-group-item-action" onclick="showAjoutNotes()">📝 Ajouter des notes</button>
      <button class="list-group-item list-group-item-action" onclick="showModifNotes()">✏️ Modifier les notes</button>
    </div>
  `;
}

function showListeEtudiants() {
  const content =document.getElementById("dashboard-content");
  const items =etudiants.map(e => '${e.filiere} - ${e.nom}');
  renderSection("Liste des Étudiants par Filière/Matière", ["Informatique - Alice", "Gestion - Bob", "Mathématiques - Charlie"]);
}

function showNotesEtudiant() {
  renderSection("Notes par Étudiant", ["Alice - Mathématiques: 15", "Bob - Programmation: 13"]);
}

function showAjoutNotes() {
  renderSection("Ajout de Notes", ["Formulaire: Étudiant + Matière + Note"]);
}

function showModifNotes() {
  renderSection("Modification des Notes", ["Alice - Mathématiques: 15 → 17", "Bob - Programmation: 13 → 14"]);
}

function showProfilAdmin() {
  renderSection("Profil Administrateur", ["Consulter Profil", "Ajouter autre autorisation"]);
}

// Fonctions pour le Parent
function showEnfants() {
  renderSection("Liste des Enfants", ["Alice", "Bob"]);
}

function showNotesEnfant() {
  renderSection("Notes par Enfant", ["Alice - Mathématiques: 14", "Bob - Gestion: 13"]);
}

function showProfilParent() {
  renderSection("Profil Parent", ["Nom: Mme Parent", "Email: parent@example.com"]);
}

// Fonctions pour le Professeur
function showNotesParMatiere() {
  renderSection("Notes par Matière", ["Mathématiques - Étudiant A: 15", "Programmation - Étudiant B: 17"]);
}

function showProfilProf() {
  renderSection("Profil Professeur", ["Nom: M. Professeur", "Email: prof@example.com"]);
}
function showAjoutNotes() {
  const content = document.getElementById("dashboard-content");
  content.innerHTML = `
    <h2>Ajouter une Note</h2>
    <form onsubmit="handleAjoutNote(event)">
      <input type="text" id="etudiantNom" class="form-control mb-2" placeholder="Nom de l'étudiant" required>
      <input type="text" id="matiereNom" class="form-control mb-2" placeholder="Matière" required>
      <input type="number" id="noteValeur" class="form-control mb-2" placeholder="Note" required>
      <button type="submit" class="btn btn-primary">Ajouter</button>
    </form>
  `;
}

function handleAjoutNote(event) {
  event.preventDefault();
  const nom = document.getElementById("etudiantNom").value;
  const matiere = document.getElementById("matiereNom").value;
  const note = parseInt(document.getElementById("noteValeur").value);
  updateNote(nom, matiere, note);
  alert("Note ajoutée!");
}


// Fonctions pour l'Étudiant
function showMesNotes() {
  renderSection("Mes Notes", ["Mathématiques: 15", "Programmation: 17"]);
}

function showProfesseurs() {
  renderSection("Liste des Professeurs", ["Mme Dupont", "M. Diallo"]);
}

function showProfilEtudiant() {
  renderSection("Profil Étudiant", ["Nom: Étudiant", "Modifier Profil", "Modifier mot de passe"]);
}

// Commun
function showSettings() {
  renderSection("Paramètres", ["Modifier Profil", "Changer Mot de Passe"]);
}