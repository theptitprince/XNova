<?php

/**
 * includes/functions/BBcodeFunction.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original : XNova Team, d'après UGamela
 * @license GNU GPL v2
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
        '/\[color=(.*?)\](.*?)\[\/color\]/is'    => '<span style="color: \1;">\2</span>',
        // sQuote() n'a jamais existe dans XNova : simple citation
        '/\[quote\](.*?)\[\/quote\]/is'          => function ($m) { return '<blockquote>' . $m[1] . '</blockquote>'; },
        '/\[code\](.*?)\[\/code\]/is'            => function ($m) { return sCode($m[1]); },
    );

    $string = nl2br(htmlspecialchars(stripslashes($string)));
    foreach ($rules as $pattern => $replace) {
        $string = is_callable($replace) ? preg_replace_callback($pattern, $replace, $string) : preg_replace($pattern, $replace, $string);
    }
    return $string;
}



function image($string)
        {
		//On va pas se casser le fion a lire les accents quand meme !!!!!!!
        $string = str_replace("&#39;", "'", $string);
		
	
		//Emoticones.... COPIEZ COLLEZ CES LIGNES POUR RAJOUTER LES VOTRES !
        $string = str_replace("Smile", "[img]../emoticones/Smile.png[/img]", $string);
		$string = str_replace("cool", "[img]../emoticones/cool.png[/img]", $string);
        $string = str_replace("grrr", "[img]../emoticones/grrr.png[/img]", $string);
        $string = str_replace("love", "[img]../emoticones/love.png[/img]", $string);
        $string = str_replace("msn", "[img]../emoticones/msn.png[/img]", $string);
        $string = str_replace("Oo", "[img]../emoticones/Oo.png[/img]", $string);
        $string = str_replace("perdu", "[img]../emoticones/perdu.png[/img]", $string);
        $string = str_replace("wink", "[img]../emoticones/wink.png[/img]", $string);
        $string = str_replace("wow", "[img]../emoticones/wow.png[/img]", $string);

        return $string;
        }




function sCode($string){
    $pattern =  '/\<img src=\\\"(.*?)img\/smilies\/(.*?).png\\\" alt=\\\"(.*?)\\\" \/>/s';
    $string = preg_replace($pattern, '\3', $string);
    return '<pre>' . trim($string) . '</pre>';
}
   
function sList($string) {
    $tmp = explode('[*]', stripslashes($string));
    $out = null;
    foreach($tmp as $list) {
        if(strlen(str_replace('', '', $list)) > 0) {
            $out .= '<li>' . trim($list) . '</li>';
        }
    }
    return '<ul>' . $out . '</ul>';
}

function imagefix($img) {
    if(substr($img, 0, 7) != 'http://') {
        $img = './images/' . $img;
    }
    return '<img src="' . $img . '" alt="' . $img . '" title="' . $img . '" />';
}

function urlfix($url, $title) {
    $title = stripslashes($title);
    return '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
}
?>