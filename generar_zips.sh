#!/usr/bin/env sh
# Genera ZIPs de los proyectos en /workspace/exchange para descargarlos o subirlos.

set -eu

ROOT=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
DESTINATION="$ROOT/exchange"

if ! command -v php >/dev/null 2>&1; then
  echo "Error: este devcontainer necesita PHP con la extensión ZipArchive." >&2
  exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
  echo "Error: este devcontainer necesita composer para las dependencias de ZentryGate." >&2
  exit 1
fi

# El SDK de Stripe no está versionado: se instala aquí para que el ZIP sea autocontenido.
composer install --no-dev --optimize-autoloader --working-dir="$ROOT/ZentryGate"

mkdir -p "$DESTINATION"

export ZIP_ROOT="$ROOT"
export ZIP_DESTINATION="$DESTINATION"

php <<'PHP'
<?php
declare(strict_types=1);

$root = getenv('ZIP_ROOT');
$destination = getenv('ZIP_DESTINATION');
$projects = ['AGJ_Editorial', 'agjWpTheme', 'ZentryGate'];
$excludedDirectories = ['.git', 'node_modules', '__pycache__'];
$excludedFiles = ['.DS_Store'];

if (!class_exists('ZipArchive')) {
    fwrite(STDERR, "Error: la extensión ZipArchive no está disponible.\n");
    exit(1);
}

foreach ($projects as $project) {
    $source = "$root/$project";
    if (!is_dir($source)) {
        fwrite(STDERR, "Error: no existe la carpeta $source\n");
        exit(1);
    }

    $archivePath = "$destination/$project.zip";
    $archive = new ZipArchive();
    if ($archive->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        fwrite(STDERR, "Error: no se pudo crear $archivePath\n");
        exit(1);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            static function (SplFileInfo $file) use ($excludedDirectories, $excludedFiles): bool {
                return !($file->isDir() && in_array($file->getFilename(), $excludedDirectories, true))
                    && !in_array($file->getFilename(), $excludedFiles, true);
            }
        ),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->isLink()) {
            continue;
        }
        $relativePath = substr($file->getPathname(), strlen($source) + 1);
        $archive->addFile($file->getPathname(), "$project/$relativePath");
    }

    $archive->close();
    echo "Creado: exchange/$project.zip\n";
}
PHP
