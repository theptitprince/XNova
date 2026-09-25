var x = "";
var e = null;

// Compteur de caracteres du premier formulaire de la page (champ "text"), limite a m caracteres
function cntchar(m) {
	var t = window.document.forms[0].text;
	if(t.value.length > m) {
		t.value = x;
	} else {
		x = t.value;
	}
	if(e == null)
	e = document.getElementById('cntChars');
	if(e != null)
	e.innerHTML = t.value.length;
}
