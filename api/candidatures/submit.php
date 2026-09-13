<?php

declare(strict_types=1);



require_once __DIR__ . '/../bootstrap.php';



$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');



if ($method !== 'POST') {

    \SouthDistrict\API\Router::jsonError('Méthode non autorisée.', 405);

}



if (!sd_recruitment_open()) {

    \SouthDistrict\API\Router::jsonError('Le recrutement staff est actuellement fermé.', 403);

}



$input = \SouthDistrict\API\Router::jsonInput();



$pseudoRp           = trim((string) ($input['pseudo_rp'] ?? $input['pseudo_jeu'] ?? ''));

$pseudoDiscord      = trim((string) ($input['pseudo'] ?? $input['pseudo_discord'] ?? ''));

$age                = (int) ($input['age'] ?? 0);

$poste              = trim((string) ($input['poste'] ?? ''));

$disponibiliteHebdo = (int) ($input['disponibilite'] ?? $input['disponibilite_hebdo'] ?? 0);

$signature          = trim((string) ($input['signature'] ?? ''));

$rawResponses       = $input['responses'] ?? [];



if ($pseudoRp === '' || mb_strlen($pseudoRp) < 2 || mb_strlen($pseudoRp) > 64) {

    \SouthDistrict\API\Router::jsonError('Pseudo requis (2 à 64 caractères).');

}

if ($pseudoDiscord === '' || !preg_match('/^[0-9]{17,20}$/', $pseudoDiscord)) {

    \SouthDistrict\API\Router::jsonError('ID Discord invalide (17 à 20 chiffres).');

}

if ($age < 17 || $age > 99) {

    \SouthDistrict\API\Router::jsonError('Âge minimum : 17 ans.');

}

if (!in_array($poste, sd_get_recruitment_open_roles(), true)) {

    \SouthDistrict\API\Router::jsonError('Poste invalide ou non ouvert au recrutement.');

}

if ($disponibiliteHebdo < 10 || $disponibiliteHebdo > 80) {

    \SouthDistrict\API\Router::jsonError('Disponibilité hebdomadaire : 10 à 80 heures.');

}

if ($signature === '' || strlen($signature) > 500000) {

    \SouthDistrict\API\Router::jsonError('Signature requise.');

}

if (!is_array($rawResponses)) {

    \SouthDistrict\API\Router::jsonError('Réponses au formulaire invalides.');

}



$questions = sd_get_recruitment_questions();

$validated = sd_validate_candidature_responses($questions, $rawResponses);

$formResponses = $validated['responses'];

$legacy = $validated['legacy'];



$code = sd_generate_candidature_code();

$sigDir = sd_config()['uploads']['candidature_sig_dir'];

$signaturePath = sd_save_png_data_url($sigDir, $code, $signature) ?? $signature;



$currentUser = sd_current_user();

$userId = $currentUser !== null ? (int) $currentUser['id'] : null;



if ($userId === null) {

    \SouthDistrict\API\Router::jsonError('Connexion requise pour postuler.', 401);

}



$check = \SouthDistrict\API\Database::getConnection()->prepare(

    'SELECT id FROM candidatures WHERE user_id = :user_id AND status NOT IN (\'refuse\') LIMIT 1'

);

$check->execute(['user_id' => $userId]);

if ($check->fetch()) {

    \SouthDistrict\API\Router::jsonError('Vous avez déjà une candidature active.', 409);

}



$formJson = json_encode($formResponses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);



$pdo = \SouthDistrict\API\Database::getConnection();

$stmt = $pdo->prepare(

    'INSERT INTO candidatures

     (code, user_id, pseudo_discord, pseudo_rp, age, poste, disponibilite_hebdo, experience, motivation, scenario, form_responses, signature, status, submitted_ip)

     VALUES

     (:code, :user_id, :pseudo_discord, :pseudo_rp, :age, :poste, :disponibilite_hebdo, :experience, :motivation, :scenario, :form_responses, :signature, :status, :submitted_ip)'

);

$stmt->execute([

    'code'                => $code,

    'user_id'             => $userId,

    'pseudo_discord'      => $pseudoDiscord,

    'pseudo_rp'           => $pseudoRp,

    'age'                 => $age,

    'poste'               => $poste,

    'disponibilite_hebdo' => $disponibiliteHebdo,

    'experience'          => $legacy['experience'],

    'motivation'          => $legacy['motivation'],

    'scenario'            => $legacy['scenario'],

    'form_responses'      => $formJson,

    'signature'           => $signaturePath,

    'status'              => 'en_attente',

    'submitted_ip'        => substr(\SouthDistrict\API\Router::clientIp(), 0, 45),

]);



$stmt = $pdo->prepare('SELECT * FROM candidatures WHERE code = :code LIMIT 1');

$stmt->execute(['code' => $code]);

$row = $stmt->fetch();



sd_backup_candidature($row, 'on_submit', $currentUser);

sd_notify_candidature_submission_webhook($row);

\SouthDistrict\API\Router::jsonSuccess([

    'message'     => 'Candidature enregistrée.',

    'candidature' => sd_public_candidature($row, true, 'owner'),

], 201);


