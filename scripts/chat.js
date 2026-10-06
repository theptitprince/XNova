// Pseudo : chat_add.php reprend celui du compte connecte et ignore cette valeur (fichier servi en statique)
var nick="";

// Scrolling automatique
function descendreTchat(){
 	var elDiv =document.getElementById('shoutbox');
 	elDiv.scrollTop = elDiv.scrollHeight-elDiv.offsetHeight;
}

// Ajout de message
function addMessage(){
var x_object = null;
if(window.XMLHttpRequest){
	x_object = new XMLHttpRequest();
}else if(window.ActiveXObject){
	x_object = new ActiveXObject("Microsoft.XMLHTTP");
}else{
	alert('AJAX Error');
return;
}
	x_object.open("POST","chat_add.php",true);
	x_object.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	// XNova Renaissance 0.9k : message refuse (chat desactive, messages trop rapproches) : texte du serveur affiche
	// et message remis dans la zone de saisie. Texte simple de chat_add.php seulement : une page HTML (session
	// expiree, jeton refuse) n'est pas montree brute dans l'alerte
	var sent = msg.value;
	x_object.onreadystatechange = function(){
		if(x_object.readyState==4 && x_object.status==200){
			if(x_object.responseText.replace(/\s+/g, "") != "" && x_object.responseText.indexOf("<") == -1){
				var text = document.createElement("div");
				text.innerHTML = x_object.responseText;
				alert(text.textContent || text.innerText);
				if(msg.value == "") msg.value = sent;
			}
			showMessage();
		}
	}
	// XNova Renaissance : message encode (les + et & cassaient l'envoi) et jeton CSRF
	x_object.send("nick="+encodeURIComponent(nick)+"&msg="+encodeURIComponent(msg.value)+"&csrf_token="+encodeURIComponent(document.getElementById("csrf_token").value));
	msg.value = "";
}

// Affichage des messages
function showMessage(){
var x_object2 = null;
	if(window.XMLHttpRequest){
		x_object2 = new XMLHttpRequest();
	}else if(window.ActiveXObject){
		x_object2 = new ActiveXObject("Microsoft.XMLHTTP");
	}else{
		alert('AJAX Error');
	return;
	}
	x_object2.open("POST","chat_msg.php",true);
	x_object2.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	x_object2.send("csrf_token="+encodeURIComponent(document.getElementById("csrf_token").value));
	
	x_object2.onreadystatechange = function(){
		if(x_object2.readyState==4){
			if(x_object2.status==200){
			document.getElementById('shoutbox').innerHTML = x_object2.responseText;
			descendreTchat();
			// Indicateur de chargement : absent du modele, on ne le masque que s'il existe
			var loader = document.getElementById('Layer1');
			if (loader) loader.style.visibility="hidden";
			}
		}
	}
	
}

// Raccourcis des smileys
function addSmiley(smiley){
	msg.value=msg.value+smiley;
	msg.focus();
}

// Intervalle entre les messages
setInterval(showMessage,3000);