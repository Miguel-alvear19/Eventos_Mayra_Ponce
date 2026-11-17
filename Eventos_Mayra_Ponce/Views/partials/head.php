<?php
/*
 * Cabecera HTML común.
 * Variables: $titulo (texto de la pestaña), $estilos (hojas extra), $claseBody
 */
$estilos   = $estilos ?? [];
$claseBody = $claseBody ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Eventos Mayra Ponce') ?> | Eventos Mayra Ponce</title>
    <meta name="description" content="Eventos Mayra Ponce: salón para bodas, cumpleaños, eventos sociales y empresariales.">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23B8893A' stroke-width='2'><path d='M2 18h20l-2-11-5 5-3-7-3 7-5-5z'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Great+Vibes&family=Lato:wght@400;700&family=Playfair+Display:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="<?= recurso('Styles/main.css') ?>">
<?php foreach ($estilos as $hoja): ?>
    <link rel="stylesheet" href="<?= recurso('Styles/' . $hoja) ?>">
<?php endforeach; ?>
</head>
<body class="<?= e($claseBody) ?>">
