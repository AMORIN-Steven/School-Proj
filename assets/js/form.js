function createFiliere(nom) {
  filieres.push(nom);
}

function deleteFiliere(nom) {
  const index = filieres.indexOf(nom);
  if (index!== -1) filieres.splice(index, 1);
}

function updateNote(etudiantNom, matiere, nouvelleNote) {
  const etudiant = etudiants.find(e => e.nom === etudiantNom);
  if (etudiant) {
    etudiant.notes[matiere] = nouvelleNote;
}
}
