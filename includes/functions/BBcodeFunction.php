<?php

/**
 * includes/functions/BBcodeFunction.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original : XNova Team, d'après UGamela
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function bbcode($string) {
    // PHP 7+ : le modificateur /e n'existe plus, les remplacements dynamiques passent par des callbacks
    $rules = array(
        '/\\n/'                                  => '',
        '/\\r/'                                  => '',
        '/\[list\](.*?)\[\/list\]/is'            => function ($m) { return sList($m[1]); },
        '/\[b\](.*?)\[\/b\]/is'                  => '<b>\1</b>',
        '/\[strong\](.*?)\[\/strong\]/is'        => '<strong>\1</strong>',
        '/\[i\](.*?)\[\/i\]/is'                  => '<i>\1</i>',
        '/\[u\](.*?)\[\/u\]/is'                  => '<span style="text-decoration: underline;">\1</span>',
        '/\[s\](.*?)\[\/s\]/is'                  => '<span style="text-decoration: line-through;">\1</span>',
        '/\[del\](.*?)\[\/del\]/is'              => '<span style="text-decoration: line-through;">\1</span>',
        '/\[url=(.*?)\](.*?)\[\/url\]/is'        => function ($m) { return urlfix($m[1], $m[2]); },
        '/\[email=(.*?)\](.*?)\[\/email\]/is'    => '<a href="mailto:\1" title="\1">\2</a>',
        '/\[img](.*?)\[\/img\]/is'               => function ($m) { return imagefix($m[1]); },
        // Couleur : un nom ou un code #rgb / #rrggbb seulement (avant : n'importe quel style CSS)
        '/\[color=([a-z]{3,20}|#[0-9a-f]{3}|#[0-9a-f]{6})\](.*?)\[\/color\]/is' => '<span style="color: \1;">\2</span>',
        // sQuote() n'a jamais existe dans XNova : simple citation
        '/\[quote\](.*?)\[\/quote\]/is'          => function ($m) { return '<blockquote>' . $m[1] . '</blockquote>'; },
        '/\[code\](.*?)\[\/code\]/is'            => function ($m) { return sCode($m[1]); },
    );

    // (plus de stripslashes : sans magic quotes, il effacait les \ tapes par le joueur)
    $string = nl2br(htmlspecialchars($string));
    foreach ($rules as $pattern => $replace) {
        $string = is_callable($replace) ? preg_replace_callback($pattern, $replace, $string) : preg_replace($pattern, $replace, $string);
    }
    return $string;
}



function image($string)
        {
		//On va pas se casser le fion a lire les accents quand meme !!!!!!!
        $string = str_replace("&#39;", "'", $string);
		
	
		// Emoticones : code entre deux-points (:cool:, :perdu:...). Avant, le mot seul etait remplace partout
		// (« j'ai perdu ma flotte », « trop cool » recevaient une image). Pour en ajouter : completer la liste.
		$Smileys = array('Smile', 'cool', 'grrr', 'love', 'msn', 'Oo', 'perdu', 'wink', 'wow');
		foreach ($Smileys as $Smiley) {
			$string = str_ireplace(':' . $Smiley . ':', '[img]emoticones/' . $Smiley . '.png[/img]', $string);
		}

        return $string;
        }




function sCode($string){
    $pattern =  '/\<img src=\\\"(.*?)img\/smilies\/(.*?).png\\\" alt=\\\"(.*?)\\\" \/>/s';
    $string = preg_replace($pattern, '\3', $string);
    return '<pre>' . trim($string) . '</pre>';
}
   
function sList($string) {
    $tmp = explode('[*]', $string);
    $out = null;
    foreach($tmp as $list) {
        if(strlen(str_replace('', '', $list)) > 0) {
            $out .= '<li>' . trim($list) . '</li>';
        }
    }
    return '<ul>' . $out . '</ul>';
}

function imagefix($img) {
    // XNova Renaissance : adresse http(s) propre, ou image locale sans caractere dangereux
    $img = html_entity_decode($img, ENT_QUOTES, 'UTF-8');
    if (SafeUrl($img) == '') {
        $img = './images/' . preg_replace('#[^A-Za-z0-9_./\-]#', '', str_replace('..', '', $img));
    }
    $img = htmlspecialchars($img, ENT_QUOTES, 'UTF-8');
    // Emoticone : infobulle « :cool: » plutot que le chemin de l'image
    $alt = preg_match('#^\./images/emoticones/([A-Za-z]+)\.png$#', $img, $m) ? ':' . $m[1] . ':' : $img;
    return '<img src="' . $img . '" alt="' . $alt . '" title="' . $alt . '" />';
}

function urlfix($url, $title) {
    // XNova Renaissance : liens http(s) uniquement (pas de javascript:)
    $url   = htmlspecialchars(SafeUrl(html_entity_decode($url, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8');
    $title = stripslashes($title);
    return '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
}
?>