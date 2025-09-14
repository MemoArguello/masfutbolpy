<?php
header("Content-Type: text/plain");

// URLs de 365Scores
$urlTabla = "https://webws.365scores.com/web/standings/?appTypeId=5&langId=14&timezoneName=America/Asuncion&userCountryId=108&competitions=621&live=false&stageNum=4";
$urlGoleadores = "https://webws.365scores.com/web/stats/?appTypeId=5&langId=14&timezoneName=America/Asuncion&userCountryId=108&competitions=621&competitors=&phaseNum=-1&withSeasons=true";
$urlResultados = "https://webws.365scores.com/web/games/results/?appTypeId=5&langId=14&timezoneName=America/Asuncion&userCountryId=108&competitions=621&showOdds=true&includeTopBettingOpportunity=1";
$urlNoticias = "https://webws.365scores.com/web/news/?appTypeId=5&langId=14&timezoneName=America/Asuncion&userCountryId=108&competitions=621&isPreview=false";
$urlNoticiasPy = "https://webws.365scores.com/web/news/?appTypeId=5&langId=14&timezoneName=America/Asuncion&userCountryId=108&competitors=5070&isPreview=true";

// Función para traer JSON de una URL
function fetchJson($url) {
    $json = @file_get_contents($url);
    if (!$json) {
        echo "Error al cargar: $url\n";
        return null;
    }
    return json_decode($json, true);
}

$tablaData = fetchJson($urlTabla);
$goleadoresData = fetchJson($urlGoleadores);
$resultadosData = fetchJson($urlResultados);
$noticiasData = fetchJson($urlNoticias);
$noticiasPyData = fetchJson($urlNoticiasPy);

// Guardar tabla
if ($tablaData) {
    file_put_contents("tabla.json", json_encode($tablaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "tabla.json guardado correctamente.\n";
}

// Guardar goleadores
if ($goleadoresData) {
    file_put_contents("goleadores.json", json_encode($goleadoresData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "goleadores.json guardado correctamente.\n";
}

// Guardar resultados
if ($resultadosData) {
    file_put_contents("resultados.json", json_encode($resultadosData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "resultados.json guardado correctamente.\n";
}

// Guardar noticias
if ($noticiasData && isset($noticiasData['news'])) {

    $noticiasData['news'] = array_slice($noticiasData['news'], -13);

    // 1. Agregar campo descripcion vacío
    foreach ($noticiasData['news'] as &$noticia) {
        $noticia['descripcion'] = ""; 
    }


    // 3. Crear CSV con títulos
    $csvFile = fopen("noticias.csv", "w");
    fwrite($csvFile, "\xEF\xBB\xBF"); // BOM UTF-8
    fputcsv($csvFile, ["title"]);
    foreach ($noticiasData['news'] as $noticia) {
        fputcsv($csvFile, [$noticia['title']]);
    }
    fclose($csvFile);

    // 4. Crear JSON con solo las URLs
    $urls = [];
    foreach ($noticiasData['news'] as $noticia) {
        $urls[] = $noticia['url'];
    }

    file_put_contents(
        "noticias_urls.json",
        json_encode($urls, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    echo "✅ noticias.csv y noticias.json guardados correctamente.\n";
} else {
    echo "❌ No se pudieron obtener noticias.\n";
}

// Guardar noticias albirroja
if ($noticiasPyData) {
    file_put_contents("noticiasparaguay.json", json_encode($noticiasPyData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "noticiasparaguay.json guardado correctamente.\n";
}
?>
