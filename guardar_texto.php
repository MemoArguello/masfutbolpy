<?php
header("Content-Type: text/plain");

// ============================
// Función para hacer scraping de la noticia
// ============================
function scrapeNoticia($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64)");
    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html) {
        return "No se pudo cargar la página";
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);
    $paragraphs = $xpath->query('(//div[contains(@class,"entry-content mt-4")])[1]//p');


    $texto = "";
    foreach ($paragraphs as $p) {
        $texto .= trim($p->textContent) . "\n\n";
    }

    return trim($texto) ?: "No se encontró contenido";
}

// ============================
// Leer JSON original
// ============================
$archivoOriginal = "noticias.json";
if (!file_exists($archivoOriginal)) {
    die("❌ No se encontró el archivo $archivoOriginal\n");
}

$noticiasData = json_decode(file_get_contents($archivoOriginal), true);
if (!$noticiasData || !is_array($noticiasData)) {
    die("❌ Error al leer noticias.json\n");
}


// ============================
// Procesar cada noticia
// ============================
$noticias_scraped = [];

foreach ($noticiasData as $noticia) {
    $url = $noticia['url'] ?? '';
    $descripcion = "";

    if ($url) {
        echo "⏳ Procesando: $url\n";
        $descripcion = scrapeNoticia($url);
        usleep(500000); // 0.5 segundos
        echo "✅ OK\n";
    }

    $noticias_scraped[] = [
        "id" => $noticia['id'] ?? null,
        "publishDate" => $noticia['publishDate'] ?? "",
        "title" => $noticia['title'] ?? "",
        "image" => $noticia['image'] ?? "",
        "url" => $url,
        "isMagazine" => $noticia['isMagazine'] ?? false,
        "descripcion" => $descripcion
    ];
}


// ============================
// Guardar JSON final
// ============================
file_put_contents(
    "noticias_scraped.json",
    json_encode($noticias_scraped, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "\n🎉 Scraping completado. Archivo guardado como noticias_scraped.json\n";
?>
