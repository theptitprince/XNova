// XNova Renaissance : verification en direct du pseudo et de l'adresse e-mail pendant l'inscription.
// Le script d'origine (repris d'OGame) visait un autre formulaire et appelait check_registration.php, jamais ecrit.
// Les messages s'affichent sous chaque champ ; reg.php refait toutes les verifications a l'envoi.
var ajaxUser = new sack("check_registration.php");
var ajaxMail = new sack("check_registration.php");

function regField(name) {
	return document.getElementsByName(name)[0];
}

// Valeurs encodees ici (sack coupe sur « & » et « = » : « abc&def » aurait ete verifie comme « abc »)
function regRequest(ajax, action, name, value) {
	ajax.encodeURIString = false;
	ajax.URLString       = "action=" + action + "&" + name + "=" + encodeURIComponent(value);
	if (typeof xnova_csrf != "undefined") {
		ajax.URLString += "&csrf_token=" + xnova_csrf;
	}
	ajax.onCompletion = whenResponse;
	ajax.runAJAX();
}

function showCheck(id, text) {
	var el = document.getElementById(id);
	if (el) {
		el.innerHTML = text;
	}
}

checkUsername.oldname = "";
checkUsername.timer   = null;
function checkUsername() {
	clearTimeout(checkUsername.timer);
	checkUsername.timer = setTimeout(function () {
		var username = regField("character").value;
		if (username == checkUsername.oldname) {
			return;
		}
		checkUsername.oldname = username;
		if (username == "") {
			showCheck("check_character", "");
			return;
		}
		regRequest(ajaxUser, "check_username", "username", username);
	}, 400);
}

checkEmail.oldmail = "";
checkEmail.timer   = null;
function checkEmail() {
	clearTimeout(checkEmail.timer);
	checkEmail.timer = setTimeout(function () {
		var email = regField("email").value;
		if (email == checkEmail.oldmail) {
			return;
		}
		checkEmail.oldmail = email;
		if (email == "") {
			showCheck("check_email", "");
			return;
		}
		regRequest(ajaxMail, "check_email", "email", email);
	}, 400);
}

// Captcha (mod de theptitprince) : autre image, champ vide
function RefreshCaptcha() {
	var img = document.getElementById("captcha_img");
	if (img) {
		img.src = "captcha.php?" + new Date().getTime();
		regField("captcha").value = "";
	}
	return false;
}

// Retour sur le formulaire (bouton Precedent apres une erreur) : le code affiche a deja servi, un nouveau s'affiche
window.addEventListener("pageshow", function (e) {
	if (e.persisted) {
		RefreshCaptcha();
	}
});

// Reponse : "1|ok|message" (pseudo) ou "2|ok|message" (e-mail) ; reponse perimee ignoree (champ modifie depuis)
function whenResponse() {
	var retVals = this.response.split("|");
	if (retVals.length < 3) {
		return;
	}
	var text = '<font color="' + (retVals[1] == "1" ? "lime" : "red") + '">' + retVals.slice(2).join("|") + '</font>';
	switch (retVals[0]) {
		case "1":
			if (regField("character").value == checkUsername.oldname) {
				showCheck("check_character", text);
			}
			break;
		case "2":
			if (regField("email").value == checkEmail.oldmail) {
				showCheck("check_email", text);
			}
			break;
	}
}
