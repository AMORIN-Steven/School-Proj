// Configuration
const API_BASE = 'list.php';

// Éléments DOM
const filiereSelect = document.getElementById("filiere");
const matiereSelect = document.getElementById("matiere");
const form = document.getElementById("selectionForm");
const resultTitle = document.getElementById("resultTitle");
const studentTableBody = document.getElementById("studentTableBody");
const resultSection = document.getElementById("resultSection");
const noDataMessage = document.getElementById("noDataMessage");
const submitBtn = document.getElementById("submitBtn");
const loadingSpinner = document.getElementById("loadingSpinner");

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Page chargée - Début du chargement des filières');
    loadFilieres();
});

// Charger les filières
async function loadFilieres() {
    try {
        showLoading(true);
        const response = await fetch(`${API_BASE}?action=filieres`);
        
        if (!response.ok) {
            throw new Error('Erreur de chargement');
        }
        
        const filieres = await response.json();
        
        filiereSelect.innerHTML = '<option value="">-- Choisir une filière --</option>';
        filieres.forEach(filiere => {
            const option = document.createElement("option");
            option.value = filiere.Id_fil;
            option.textContent = filiere.nom;
            filiereSelect.appendChild(option);
        });
        
    } catch (error) {
        console.error('Erreur:', error);
        showError('Erreur lors du chargement des filières');
    } finally {
        showLoading(false);
    }
}

// Charger les matières quand une filière est sélectionnée
filiereSelect.addEventListener("change", async function() {
    const filiereId = this.value;
    
    if (!filiereId) {
        matiereSelect.innerHTML = '<option value="">-- Choisissez d\'abord une filière --</option>';
        matiereSelect.disabled = true;
        return;
    }
    
    await loadMatieres(filiereId);
});

// Charger les matières d'une filière
async function loadMatieres(filiereId) {
    try {
        showLoading(true);
        matiereSelect.innerHTML = '<option value="">-- Chargement... --</option>';
        matiereSelect.disabled = true;
        
        const response = await fetch(`${API_BASE}?action=matieres&filiereId=${filiereId}`);
        
        if (!response.ok) {
            throw new Error('Erreur de chargement');
        }
        
        const matieres = await response.json();
        
        matiereSelect.innerHTML = '<option value="">-- Choisir une matière --</option>';
        matieres.forEach(matiere => {
            const option = document.createElement("option");
            option.value = matiere.Id_mat;
            option.textContent = matiere.nom;
            matiereSelect.appendChild(option);
        });
        
        matiereSelect.disabled = false;
        
    } catch (error) {
        console.error('Erreur:', error);
        showError('Erreur lors du chargement des matières');
    } finally {
        showLoading(false);
    }
}

// Soumission du formulaire
form.addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const filiereId = filiereSelect.value;
    const matiereId = matiereSelect.value;
    
    if (!filiereId || !matiereId) {
        alert('Veuillez sélectionner une filière et une matière');
        return;
    }
    
    await loadEtudiants(filiereId, matiereId);
});

// Charger les étudiants
async function loadEtudiants(filiereId, matiereId) {
    try {
        showLoading(true);
        
        const response = await fetch(`${API_BASE}?action=etudiants&filiereId=${filiereId}&matiereId=${matiereId}`);
        
        if (!response.ok) {
            throw new Error('Erreur de chargement');
        }
        
        const etudiants = await response.json();
        displayEtudiants(etudiants, filiereId, matiereId);
        
    } catch (error) {
        console.error('Erreur:', error);
        showError('Erreur lors du chargement des étudiants');
    } finally {
        showLoading(false);
    }
}

// Afficher les étudiants dans le tableau
function displayEtudiants(etudiants, filiereId, matiereId) {
    const filiereNom = filiereSelect.options[filiereSelect.selectedIndex].text;
    const matiereNom = matiereSelect.options[matiereSelect.selectedIndex].text;
    
    resultTitle.textContent = `Filière : ${filiereNom} | Matière : ${matiereNom}`;
    studentTableBody.innerHTML = "";
    
    if (etudiants.length === 0) {
        noDataMessage.classList.remove('d-none');
        studentTableBody.innerHTML = '';
    } else {
        noDataMessage.classList.add('d-none');
        etudiants.forEach(etudiant => {
            const moyenne = calculerMoyenne(etudiant.cc, etudiant.exam);
            const row = creerLigneEtudiant(etudiant, moyenne);
            studentTableBody.appendChild(row);
        });
    }
    
    resultSection.classList.remove('d-none');
}

// Calculer la moyenne
function calculerMoyenne(cc, exam) {
    if (cc === null || exam === null || cc === undefined || exam === undefined) {
        return 'N/A';
    }
    return ((parseFloat(cc) + parseFloat(exam)) / 2).toFixed(2);
}

// Créer une ligne du tableau
function creerLigneEtudiant(etudiant, moyenne) {
    const row = document.createElement("tr");
    
    const classeNote = getClasseNote(moyenne);
    
    row.innerHTML = `
        <td>${etudiant.matricule || 'N/A'}</td>
        <td>${etudiant.nom}</td>
        <td>${etudiant.prenom || 'N/A'}</td>
        <td>${etudiant.mail || 'N/A'}</td>
        <td>${etudiant.cc !== null && etudiant.cc !== undefined ? etudiant.cc : 'N/A'}</td>
        <td>${etudiant.exam !== null && etudiant.exam !== undefined ? etudiant.exam : 'N/A'}</td>
        <td class="${classeNote} fw-bold">${moyenne}</td>
    `;
    
    return row;
}

// Obtenir la classe CSS pour la note
function getClasseNote(moyenne) {
    if (moyenne === 'N/A') return '';
    
    const note = parseFloat(moyenne);
    if (note >= 16) return 'note-excellente';
    if (note >= 12) return 'note-bonne';
    if (note >= 8) return 'note-moyenne';
    return 'note-faible';
}

// Gestion du loading
function showLoading(show) {
    if (show) {
        submitBtn.disabled = true;
        loadingSpinner.classList.remove('d-none');
    } else {
        submitBtn.disabled = false;
        loadingSpinner.classList.add('d-none');
    }
}

// Afficher une erreur
function showError(message) {
    alert(message);
}