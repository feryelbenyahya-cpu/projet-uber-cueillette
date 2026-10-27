// ── Utilitaires ──────────────────────────────
function showError(fieldId, msg) {  // Affiche un message d'erreur et marque le champ en rouge 
  var field = document.getElementById(fieldId); // Champ de saisie 
  var errEl = document.getElementById(fieldId + '_err'); // Élément de message d'erreur associé
  if (field) { field.classList.add('error'); field.classList.remove('valid'); } // Marque le champ en rouge
  if (errEl) { errEl.textContent = msg; errEl.classList.add('show'); } // Affiche le message d'erreur
}

function showValid(fieldId) {
  var field = document.getElementById(fieldId); 
  var errEl = document.getElementById(fieldId + '_err'); 
  if (field) { field.classList.remove('error'); field.classList.add('valid'); } // Marque le champ en vert
  if (errEl) { errEl.classList.remove('show'); } // Cache le message d'erreur
}

function clearErrors() { // Réinitialise tous les champs et messages d'erreur 
  document.querySelectorAll('.form-control').forEach(el => { // Tous les champs de saisie
    el.classList.remove('error', 'valid'); // Réinitialise les classes de validation 
  }); 
  document.querySelectorAll('.error-msg').forEach(el => { // Tous les éléments de message d'erreur
    el.classList.remove('show'); // Cache tous les messages d'erreur
  });
}

// ── Règles de validation ──────────────────────
var REGEX = {
  cin:      /^\d{8}$/, // 8 chiffres exacts
  pseudo:   /^[A-Za-z]+$/, // Lettres uniquement
  password: /^[A-Za-z0-9]{7,}[$#]$/,   // ≥8 chars, finit par $ ou #
  email:    /^[^\s@]+@[^\s@]+\.[^\s@]+$/ // Format email basique 
};

function validateCIN(value) {
  return REGEX.cin.test(value);
}
function validatePseudo(value) {
  return REGEX.pseudo.test(value);
}
function validatePassword(value) {
  return REGEX.password.test(value);
}
function validateEmail(value) {
  return REGEX.email.test(value);
}

// ── Validation inscription agriculteur ────────
function validateInscriptionAgriculteur() {
  clearErrors();
  let valid = true;

  const nom = document.getElementById('nom').value.trim();
  const prenom = document.getElementById('prenom').value.trim();
  const cin = document.getElementById('cin').value.trim();
  const email = document.getElementById('email').value.trim();
  const adresse = document.getElementById('adresse').value.trim();
  const pseudo = document.getElementById('pseudo').value.trim();
  const password = document.getElementById('password').value;
  const password2 = document.getElementById('password2').value;

  if (!nom) { showError('nom', 'Le nom est obligatoire.'); valid = false; }
  else showValid('nom');

  if (!prenom) { showError('prenom', 'Le prénom est obligatoire.'); valid = false; }
  else showValid('prenom');

  if (!validateCIN(cin)) {
    showError('cin', 'Le CIN doit contenir exactement 8 chiffres.');
    valid = false;
  } else showValid('cin');

  if (!validateEmail(email)) {
    showError('email', 'Adresse email invalide.');
    valid = false;
  } else showValid('email');

  if (!adresse) { showError('adresse', "L'adresse est obligatoire."); valid = false; }
  else showValid('adresse');

  if (!validatePseudo(pseudo)) {
    showError('pseudo', 'Le pseudo doit contenir uniquement des lettres.');
    valid = false;
  } else showValid('pseudo');

  if (!validatePassword(password)) {
    showError('password', 'Le mot de passe doit contenir au moins 8 caractères (lettres/chiffres) et finir par $ ou #.');
    valid = false;
  } else showValid('password');

  if (password !== password2) {
    showError('password2', 'Les mots de passe ne correspondent pas.');
    valid = false;
  } else if (password2) showValid('password2');

  return valid;
}

// ── Validation inscription ouvrier ────────────
function validateInscriptionOuvrier() {
  clearErrors();
  let valid = true;

  const nom = document.getElementById('nom').value.trim();
  const prenom = document.getElementById('prenom').value.trim();
  const cin = document.getElementById('cin').value.trim();
  const email = document.getElementById('email').value.trim();
  const pseudo = document.getElementById('pseudo').value.trim();
  const password = document.getElementById('password').value;
  const password2 = document.getElementById('password2').value;

  if (!nom) { showError('nom', 'Le nom est obligatoire.'); valid = false; }
  else showValid('nom');

  if (!prenom) { showError('prenom', 'Le prénom est obligatoire.'); valid = false; }
  else showValid('prenom');

  if (!validateCIN(cin)) {
    showError('cin', 'Le CIN doit contenir exactement 8 chiffres.');
    valid = false;
  } else showValid('cin');

  if (!validateEmail(email)) {
    showError('email', 'Adresse email invalide.');
    valid = false;
  } else showValid('email');

  if (!validatePseudo(pseudo)) {
    showError('pseudo', 'Le pseudo doit contenir uniquement des lettres.');
    valid = false;
  } else showValid('pseudo');

  if (!validatePassword(password)) {
    showError('password', 'Le mot de passe doit contenir au moins 8 caractères (lettres/chiffres) et finir par $ ou #.');
    valid = false;
  } else showValid('password');

  if (password !== password2) {
    showError('password2', 'Les mots de passe ne correspondent pas.');
    valid = false;
  } else if (password2) showValid('password2');

  return valid;
}

// ── Validation formulaire offre ───────────────
function validateOffre() {
  clearErrors();
  let valid = true;

  const dateDebut = document.getElementById('date_debut').value;
  const dateFin   = document.getElementById('date_fin').value;
  const dateLimite = document.getElementById('date_limite').value;
  const nbrOuvriers = parseInt(document.getElementById('nombre_ouvriers').value);
  const prixJournee = parseFloat(document.getElementById('prix_journee').value);
  const today = new Date().toISOString().split('T')[0];

  if (!dateDebut || dateDebut < today) {
    showError('date_debut', 'La date de début doit être aujourd\'hui ou une date future.');
    valid = false;
  } else showValid('date_debut');

  if (!dateFin || dateFin <= dateDebut) {
    showError('date_fin', 'La date de fin doit être après la date de début.');
    valid = false;
  } else showValid('date_fin');

  if (!dateLimite || dateLimite > dateDebut) {
    showError('date_limite', 'La date limite de candidature doit être avant la date de début.');
    valid = false;
  } else showValid('date_limite');

  if (isNaN(nbrOuvriers) || nbrOuvriers < 1) {
    showError('nombre_ouvriers', 'Le nombre d\'ouvriers doit être supérieur à 0.');
    valid = false;
  } else showValid('nombre_ouvriers');

  if (isNaN(prixJournee) || prixJournee <= 0) {
    showError('prix_journee', 'Le prix à la journée doit être un nombre positif.');
    valid = false;
  } else showValid('prix_journee');

  return valid;
}

// ── Validation notation ───────────────────────
function validateNotation(form) {
  const noteInput = form.querySelector('input[name="note"]');
  if (noteInput) {
    const note = parseInt(noteInput.value);
    if (isNaN(note) || note < 0 || note > 10) {
      alert('La note doit être comprise entre 0 et 10.');
      return false;
    }
  }
  return true;
}

// ── Validation login ──────────────────────────
function validateLogin() {
  clearErrors();
  let valid = true;
  const pseudo = document.getElementById('pseudo').value.trim();
  const password = document.getElementById('password').value;

  if (!pseudo) { showError('pseudo', 'Le pseudo est obligatoire.'); valid = false; }
  else showValid('pseudo');

  if (!password) { showError('password', 'Le mot de passe est obligatoire.'); valid = false; }
  else showValid('password');

  return valid;
}

// ── Feedback temps réel ───────────────────────
document.addEventListener('DOMContentLoaded', function () {
  // CIN
  const cinField = document.getElementById('cin');
  if (cinField) {
    cinField.addEventListener('input', function () {
      if (validateCIN(this.value.trim())) showValid('cin');
      else if (this.value.length > 0) showError('cin', 'Doit contenir exactement 8 chiffres.');
    });
  }

  // Pseudo
  const pseudoField = document.getElementById('pseudo');
  if (pseudoField) {
    pseudoField.addEventListener('input', function () {
      if (validatePseudo(this.value.trim())) showValid('pseudo');
      else if (this.value.length > 0) showError('pseudo', 'Lettres uniquement.');
    });
  }

  // Password
  const passField = document.getElementById('password');
  if (passField) {
    passField.addEventListener('input', function () {
      if (validatePassword(this.value)) showValid('password');
      else if (this.value.length > 0) showError('password', '≥8 chars (lettres/chiffres) et finir par $ ou #.');
    });
  }

  // Confirm Password
  const pass2Field = document.getElementById('password2');
  if (pass2Field) {
    pass2Field.addEventListener('input', function () {
      const pass1 = document.getElementById('password').value;
      if (this.value === pass1) showValid('password2');
      else showError('password2', 'Les mots de passe ne correspondent pas.');
    });
  }

  // Email
  const emailField = document.getElementById('email');
  if (emailField) {
    emailField.addEventListener('blur', function () {
      if (validateEmail(this.value.trim())) showValid('email');
      else showError('email', 'Email invalide.');
    });
  }

  // Aperçu de la photo
  const photoField = document.getElementById('photo');
  const photoPreview = document.getElementById('photo_preview');
  if (photoField && photoPreview) {
    photoField.addEventListener('change', function () {
      const file = this.files[0];
      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => { photoPreview.src = e.target.result; photoPreview.style.display = 'block'; };
        reader.readAsDataURL(file);
      }
    });
  }
});
