// XNova Renaissance : compteur de ressources en direct dans la barre du haut (meme calcul que PlanetResourceUpdate).
// Pour chaque ressource : [quantite, production par seconde, plafond de production, capacite du hangar].
// Au-dela du plafond (hangar + debordement autorise), plus de production ; au-dela du hangar, chiffre en rouge.
function ResourceCounter(Resources) {
	var Start = new Date().getTime();

	// Meme format que pretty_number() : partie entiere, points des milliers
	function Format(Value) {
		var Rounded = Math.floor(Value);
		var Digits  = String(Math.abs(Rounded));
		var Text    = '';
		while (Digits.length > 3) {
			Text   = '.' + Digits.substr(Digits.length - 3) + Text;
			Digits = Digits.substr(0, Digits.length - 3);
		}
		return (Rounded < 0 ? '-' : '') + Digits + Text;
	}

	function Refresh() {
		var Elapsed = (new Date().getTime() - Start) / 1000;
		for (var Res in Resources) {
			var Cell = document.getElementById('res_' + Res);
			if (!Cell) {
				continue;
			}
			var Data  = Resources[Res];
			var Value = Data[0];
			if (Value <= Data[2]) {
				Value = Math.min(Value + Data[1] * Elapsed, Data[2]);
			}
			Cell.innerHTML = (Value > Data[3]) ? '<font color="#ff0000">' + Format(Value) + '</font>' : Format(Value);
		}
	}

	setInterval(Refresh, 1000);
}
