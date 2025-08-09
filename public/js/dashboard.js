/**
 * DASHBOARD.JS - Gestion du tableau de bord Energy+
 * Fonctionnalités : Compteur temps réel, graphiques, factures, déconnexion
 */

// ==================== CONFIGURATION GRAPHIQUE ====================
// Initialisation du graphique de consommation avec Chart.js
const ctx = document.getElementById('consumptionChart').getContext('2d');
const chart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: [], // Labels temporels (heures)
    datasets: [{
      label: 'kWh Cumulé',
      data: [], // Données de consommation
      borderColor: 'rgb(75, 192, 192)',
      tension: 0.1
    }]
  },
  options: {
    scales: {
      y: {
        beginAtZero: true // Commencer l'axe Y à zéro
      }
    }
  }
});

// ==================== VARIABLES GLOBALES ====================
let meterRunning = false;      // État du compteur (actif/inactif)
let meterInterval;             // Référence de l'intervalle du compteur
let currentConsumption = 0;    // Consommation actuelle cumulée
const PRICE_PER_KWH = 100;     // Prix par kWh en Francs Congolais

// ==================== ÉLÉMENTS DOM ====================
// Éléments d'affichage temps réel
const realTimeKwh = document.getElementById('realTimeKwh');
const realTimeAmount = document.getElementById('realTimeAmount');
const simulateBtn = document.getElementById('simulateBtn');
const generateInvoice = document.getElementById('generateInvoice');

// ==================== FONCTIONS UTILITAIRES ====================

/**
 * Met à jour l'affichage temps réel de la consommation
 * Affiche la consommation en kWh et le montant en FC
 */
function updateRealTimeDisplay() {
  realTimeKwh.textContent = currentConsumption.toFixed(1);
  realTimeAmount.textContent = (currentConsumption * PRICE_PER_KWH).toLocaleString();
}

// ==================== GESTION DU COMPTEUR ====================

/**
 * Démarre la simulation du compteur électrique
 * Envoie des données à l'API et met à jour le graphique
 */
async function startMeter() {
  // Empêcher le démarrage multiple
  if (meterRunning) return;
  
  meterRunning = true;
  simulateBtn.textContent = 'Compteur en cours...';
  simulateBtn.disabled = true;
  
  // Démarrer l'intervalle de simulation (toutes les 2 secondes)
  meterInterval = setInterval(async () => {
    try {
      // Envoyer la consommation à l'API
      await fetch('../api/consumption.php', { credentials: 'include' });
      
      // Simuler une consommation réaliste (1-2 kWh par intervalle)
      currentConsumption += Math.floor(Math.random() * 2 + 1);
      
      // Mettre à jour l'affichage temps réel
      updateRealTimeDisplay();
      
      // Ajouter les données au graphique
      const now = new Date().toLocaleTimeString('fr-FR');
      chart.data.labels.push(now);
      chart.data.datasets[0].data.push(currentConsumption);
      
      // Limiter l'affichage aux 20 derniers points
      if (chart.data.labels.length > 20) {
        chart.data.labels.shift();
        chart.data.datasets[0].data.shift();
      }
      
      // Actualiser le graphique
      chart.update();
    } catch (error) {
      console.error('Erreur lors de l\'envoi de consommation:', error);
    }
  }, 2000);
}

/**
 * Arrête la simulation du compteur électrique
 */
function stopMeter() {
  if (!meterRunning) return;
  
  meterRunning = false;
  clearInterval(meterInterval);
  simulateBtn.textContent = 'Redémarrer compteur';
  simulateBtn.disabled = false;
}

// ==================== GESTION DES FACTURES ====================

/**
 * Charge et affiche la liste des factures de l'utilisateur
 */
async function loadInvoices() {
  try {
    const res = await fetch('../api/invoice.php?action=list', { credentials: 'include' });
    if (!res.ok) return;
    
    const data = await res.json();
    const tbody = document.querySelector('#invoiceTable tbody');
    tbody.innerHTML = '';
    
    // Parcourir chaque facture et créer une ligne de tableau
    data.forEach(inv => {
      const statusClass = inv.status === 'paid' ? 'status-paid' : 'status-unpaid';
      const statusText = inv.status === 'paid' ? 'Payé' : 'Impayé';
      
      // Déterminer le bouton d'action selon le statut
      let actionButton = '';
      if (inv.status === 'unpaid') {
        actionButton = `<button class="btn btn-success btn-sm payBtn" data-id="${inv.id}">💳 Payer</button>`;
      } else {
        actionButton = `
          <span class="text-success">✅ Payée</span><br>
          <button class="btn btn-primary btn-sm mt-1 pdfBtn" data-id="${inv.id}">📄 PDF</button>
        `;
      }
      
      // Insérer la ligne dans le tableau
      tbody.insertAdjacentHTML('beforeend', `<tr>
        <td><strong>#${inv.id}</strong></td>
        <td><strong>${inv.amount} FC</strong></td>
        <td><span class="${statusClass}">${statusText}</span></td>
        <td>${new Date(inv.issued_at).toLocaleDateString('fr-FR')}</td>
        <td>${actionButton}</td>
      </tr>`);
    });
  } catch (error) {
    console.error('Erreur lors du chargement des factures:', error);
  }
}

// ==================== ÉVÉNEMENTS ====================

// Événement : Démarrer le compteur
simulateBtn.onclick = startMeter;

// Événement : Générer une nouvelle facture
generateInvoice.onclick = async () => {
  try {
    stopMeter(); // Arrêter le compteur
    
    // Appeler l'API de génération de facture
    await fetch('../api/invoice.php?action=generate', { credentials: 'include' });
    
    // Reset du compteur après facturation
    currentConsumption = 0;
    updateRealTimeDisplay();
    
    // Vider le graphique
    chart.data.labels = [];
    chart.data.datasets[0].data = [];
    chart.update();
    
    // Recharger la liste des factures
    loadInvoices();
  } catch (error) {
    console.error('Erreur lors de la génération de facture:', error);
  }
};

// Événement : Gestion des boutons dans le tableau des factures
document.querySelector('#invoiceTable').addEventListener('click', async (e) => {
  // Paiement d'une facture
  if (e.target.classList.contains('payBtn')) {
    const id = e.target.dataset.id;
    const fd = new FormData();
    fd.append('invoice_id', id);
    
    try {
      await fetch('../api/invoice.php?action=pay', {
        method: 'POST',
        body: fd,
        credentials: 'include'
      });
      loadInvoices(); // Recharger la liste après paiement
    } catch (error) {
      console.error('Erreur lors du paiement:', error);
    }
  }
  
  // Téléchargement du PDF de facture
  if (e.target.classList.contains('pdfBtn')) {
    const id = e.target.dataset.id;
    // Ouvrir le PDF dans un nouvel onglet
    window.open(`../api/invoice.php?action=pdf&id=${id}`, '_blank');
  }
});

// ==================== DÉCONNEXION ====================

/**
 * Gestion de la déconnexion utilisateur
 * Détruit la session et redirige vers la page de connexion
 */
document.getElementById('logoutBtn').addEventListener('click', async () => {
  try {
    const res = await fetch('../api/logout.php', {
      method: 'POST',
      credentials: 'include'
    });
    
    if (res.ok) {
      // Arrêter le compteur si il est en cours
      stopMeter();
      
      // Rediriger vers la page d'acceuil
      window.location.href = 'index.html';
    }
  } catch (error) {
    console.error('Erreur de déconnexion:', error);
    // Forcer la redirection même en cas d'erreur
    window.location.href = 'index.html';
  }
});

// ==================== INITIALISATION ====================

// Initialiser l'affichage temps réel au chargement
updateRealTimeDisplay();

// Charger les factures au chargement de la page
loadInvoices();