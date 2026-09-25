// Heure du serveur (pas celle de l'ordinateur du joueur) : xnova_heure_serveur est fournie par la page,
// c'est l'heure locale du serveur exprimee comme une date UTC, en millisecondes
var xnova_decalage = (typeof xnova_heure_serveur != 'undefined') ? xnova_heure_serveur - new Date().getTime() : null;

function HeureCheck()
{
if (xnova_decalage !== null) {
krucial = new Date(new Date().getTime() + xnova_decalage);
heure = krucial.getUTCHours();
min = krucial.getUTCMinutes();
sec = krucial.getUTCSeconds();
jour = krucial.getUTCDate();
mois = krucial.getUTCMonth()+1;
annee = krucial.getUTCFullYear();
} else {
krucial = new Date;
heure = krucial.getHours();
min = krucial.getMinutes();
sec = krucial.getSeconds();
jour = krucial.getDate();
mois = krucial.getMonth()+1;
annee = krucial.getFullYear();
}
if (sec < 10) { sec0 = "0"; }
else { sec0 = ""; }
if (min < 10) { min0 = "0"; }
else { min0 = ""; }
if (heure < 10) { heure0 = "0"; }
else { heure0 = ""; }
if (mois < 10) { mois0 = "0"; }
else { mois0 = ""; }
if (jour < 10) { jour0 = "0"; }
else { jour0 = ""; }
if (annee < 10) { annee0 = "0"; }
else { annee0 = ""; }
DinaDate = "" + jour0 + jour + "/" +  mois0 + mois + "/" + annee0 + annee;
total = DinaDate
DinaHeure = heure0 + heure + ":" + min0 + min + ":" + sec0 + sec;
total = DinaHeure
total = "Nous sommes le " + DinaDate + " et il est " + DinaHeure + ".";

document.getElementById("dateheure").innerHTML = total;

tempo = setTimeout("HeureCheck()", 1000)
}
window.onload = HeureCheck;

//Script réalisé par Max485 membre de XNova
//Message de Tom : La flemme d'en faire un mieux ^^
