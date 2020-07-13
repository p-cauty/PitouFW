<?php class L {
const problem = 'Il semblerait qu\'il y ai un problème par ici...';
const back = 'Retour';
const errors_401 = 'Authentification requise';
const errors_403 = 'Accès interdit';
const errors_404 = 'Page non trouvée';
const errors_500 = 'Erreur interne du serveur';
public static function __callStatic($string, $args) {
    return vsprintf(constant("self::" . $string), $args);
}
}
function L($string, $args=NULL) {
    $return = constant("L::".$string);
    return $args ? vsprintf($return,$args) : $return;
}