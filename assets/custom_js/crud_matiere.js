let matieres = [
    { id: 1, nom: "Mathématiques", filieres: ["SIL", "SSRI"] },
    { id: 2, nom: "Physique", filieres: ["SSRI", "RIT"] },
    { id: 3, nom: "Informatique", filieres: ["SIL", "RIT"] }
];

let currentMatiereId = null;
let modal = null;

document.addEventListener('DOMContentLoaded', function() {
    modal = new bootstrap.Modal(document.getElementById('matiereModal'));
    updateMatiereList();
});

function openModal(action, matiereId) {
    const modalTitle = document.getElementById('modalTitle');
    
    document.querySelectorAll('.form-check-input').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    if (action === 'ajouter') {
        modalTitle.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Ajouter une matière';
        document.getElementById('matiereNom').value = '';
        document.getElementById('matiereId').value = '';
        currentMatiereId = null;
    } else {
        modalTitle.innerHTML = '<i class="bi bi-pencil-square me-2"></i>Modifier une matière';
        currentMatiereId = matiereId;
        
        const matiere = matieres.find(m => m.id === matiereId);
        if (matiere) {
            document.getElementById('matiereNom').value = matiere.nom;
            document.getElementById('matiereId').value = matiere.id;
            
            matiere.filieres.forEach(filiere => {
                const checkbox = document.querySelector(`input[value="${filiere.trim()}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        }
    }
    
    modal.show();
}

function validateForm() {
    const nom = document.getElementById('matiereNom').value.trim();
    const matiereId = document.getElementById('matiereId').value;
    
    const selectedFilieres = [];
    document.querySelectorAll('.form-check-input:checked').forEach(checkbox => {
        selectedFilieres.push(checkbox.value);
    });
    
    if (!nom) {
        showAlert('Veuillez saisir le nom de la matière', 'warning');
        return;
    }
    
    if (selectedFilieres.length === 0) {
        showAlert('Veuillez sélectionner au moins une filière', 'warning');
        return;
    }
    
    if (matiereId) {
        const index = matieres.findIndex(m => m.id === parseInt(matiereId));
        if (index !== -1) {
            matieres[index].nom = nom;
            matieres[index].filieres = selectedFilieres;
            showAlert('Matière modifiée avec succès!', 'success');
        }
    } else {
        const newId = matieres.length > 0 ? Math.max(...matieres.map(m => m.id)) + 1 : 1;
        matieres.push({ id: newId, nom: nom, filieres: selectedFilieres });
        showAlert('Matière ajoutée avec succès!', 'success');
    }
    
    updateMatiereList();
    modal.hide();
}

function supprimerMatiere(matiereId) {
    const matiere = matieres.find(m => m.id === matiereId);
    const matiereName = matiere ? matiere.nom : 'cette matière';
    
    if (confirm(`Êtes-vous sûr de vouloir supprimer la matière "${matiereName}" ?`)) {
        matieres = matieres.filter(m => m.id !== matiereId);
        updateMatiereList();
        showAlert('Matière supprimée avec succès!', 'success');
    }
}

function updateMatiereList() {
    const matiereList = document.getElementById('matiereList');
    const matiereCount = document.getElementById('matiereCount');
    
    if (matieres.length === 0) {
        matiereList.innerHTML = `
            <div class="col-12">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune matière enregistrée</h5>
                        <p class="text-muted mb-0">Commencez par ajouter votre première matière</p>
                    </div>
                </div>
            </div>
        `;
        matiereCount.textContent = '0';
        return;
    }
    
    matiereList.innerHTML = matieres.map((matiere, index) => {
        const filieresHTML = matiere.filieres.map(filiere => 
            `<span class="badge bg-secondary badge-custom me-1">${filiere}</span>`
        ).join('');
        
        return `
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card card-hover border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-primary rounded-pill">#${index + 1}</span>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary btn-action" onclick="openModal('modifier', ${matiere.id})" 
                                        title="Modifier la matière">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-action" onclick="supprimerMatiere(${matiere.id})" 
                                        title="Supprimer la matière">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                        
                        <h6 class="card-title mb-3">${matiere.nom}</h6>
                        
                        <div class="filieres-container">
                            ${filieresHTML}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    matiereCount.textContent = matieres.length;
}

function showAlert(message, type) {
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
    toastContainer.style.zIndex = '1060';
    
    const toastId = 'toast-' + Date.now();
    
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'warning') icon = 'exclamation-triangle';
    if (type === 'danger') icon = 'x-circle';
    
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${icon}-fill me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    toastContainer.innerHTML = toastHtml;
    document.body.appendChild(toastContainer);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 3000
    });
    
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastContainer.remove();
    });
}