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

function readVersion(string $headerFile): string {
    $contents = file_get_contents($headerFile);
    if ($contents === false || !preg_match('/^\s*\*?\s*Version:\s*(.+?)\s*$/mi', $contents, $matches)) {
        fwrite(STDERR, "Error: no se encontró la cabecera Version en $headerFile\n");
        exit(1);
    }

    return preg_replace('/[^A-Za-z0-9._-]/', '-', trim($matches[1]));
}

function bumpPatchVersion(string $version): string {
    if (!preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $version, $matches)) {
        fwrite(STDERR, "Error: la versión $version no tiene el formato x.y.z.\n");
        exit(1);
    }
    return $matches[1] . '.' . $matches[2] . '.' . ((int) $matches[3] + 1);
}

function writeVersion(string $headerFile, string $version, bool $isPlugin): void {
    $contents = file_get_contents($headerFile);
    if ($contents === false) {
        fwrite(STDERR, "Error: no se pudo leer $headerFile\n");
        exit(1);
    }
    $updated = preg_replace('/^(\s*\*?\s*Version:\s*).+$/mi', '$1' . $version, $contents, 1);
    if ($isPlugin) {
        $updated = preg_replace("/(define\s*\(\s*'ZENTRYGATE_VERSION_(?:DB|PLUGIN)'\s*,\s*')[^']+('\s*\);)/", '$1' . $version . '$2', $updated);
    }
    if ($updated === null || file_put_contents($headerFile, $updated) === false) {
        fwrite(STDERR, "Error: no se pudo actualizar la versión en $headerFile\n");
        exit(1);
    }
}

function currentCommit(string $repository): string {
    $command = 'git -C ' . escapeshellarg($repository) . ' rev-parse HEAD';
    exec($command, $output, $status);
    if ($status !== 0 || empty($output[0])) {
        fwrite(STDERR, "Error: no se pudo leer el commit de $repository\n");
        exit(1);
    }
    return trim($output[0]);
}

function commitOnlyChangesVersion(string $repository, string $from, string $to, string $projectPath, bool $isPlugin): bool {
    $command = 'git -C ' . escapeshellarg($repository)
        . ' diff --unified=0 --no-ext-diff ' . escapeshellarg($from) . ' ' . escapeshellarg($to)
        . ' -- ' . escapeshellarg($projectPath);
    exec($command, $lines, $status);
    if ($status !== 0) {
        fwrite(STDERR, "Error: no se pudo comparar los commits de $projectPath\n");
        exit(1);
    }

    $hasVersionChange = false;
    foreach ($lines as $line) {
        if (str_starts_with($line, '+++') || str_starts_with($line, '---') || !preg_match('/^[+-]/', $line)) {
            continue;
        }

        $isHeaderVersion = preg_match('/^[+-]\s*\*?\s*Version:\s*\d+\.\d+\.\d+\s*$/', $line) === 1;
        $isPluginConstant = $isPlugin && preg_match("/^[+-]\s*define\s*\(\s*'ZENTRYGATE_VERSION_(?:DB|PLUGIN)'\s*,\s*'\d+\.\d+\.\d+'\s*\);\s*$/", $line) === 1;
        if (!$isHeaderVersion && !$isPluginConstant) {
            return false;
        }
        $hasVersionChange = true;
    }

    return $hasVersionChange;
}

function commitAffectsProject(string $repository, string $from, string $to, string $projectPath): bool {
    $command = 'git -C ' . escapeshellarg($repository)
        . ' diff --name-only --no-ext-diff ' . escapeshellarg($from) . ' ' . escapeshellarg($to)
        . ' -- ' . escapeshellarg($projectPath);
    exec($command, $files, $status);
    if ($status !== 0) {
        fwrite(STDERR, "Error: no se pudo comprobar los cambios de $projectPath\n");
        exit(1);
    }
    return $files !== [];
}

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

    $headerFile = $project === 'ZentryGate'
        ? "$source/zentrygate.php"
        : "$source/style.css";
    $repository = $project === 'ZentryGate' ? $source : $root;
    $projectPath = $project === 'ZentryGate' ? '.' : $project;
    $commit = currentCommit($repository);
    $commitFile = "$destination/$project.commit";
    $lastCommit = is_file($commitFile) ? trim((string) file_get_contents($commitFile)) : '';

    if ($lastCommit === $commit) {
        echo "Sin cambios de commit: exchange/$project.commit\n";
        continue;
    }

    if ($lastCommit !== '' && !commitAffectsProject($repository, $lastCommit, $commit, $projectPath)) {
        if (file_put_contents($commitFile, $commit . PHP_EOL) === false) {
            fwrite(STDERR, "Error: no se pudo guardar $commitFile\n");
            exit(1);
        }
        echo "Sin cambios en $project/: exchange/$project.commit actualizado\n";
        continue;
    }

    if ($lastCommit !== '' && commitOnlyChangesVersion($repository, $lastCommit, $commit, $projectPath, $project === 'ZentryGate')) {
        if (file_put_contents($commitFile, $commit . PHP_EOL) === false) {
            fwrite(STDERR, "Error: no se pudo guardar $commitFile\n");
            exit(1);
        }
        echo "Commit solo de versión: exchange/$project.commit actualizado\n";
        continue;
    }

    $version = readVersion($headerFile);
    if ($lastCommit !== '') {
        $version = bumpPatchVersion($version);
        writeVersion($headerFile, $version, $project === 'ZentryGate');
        echo "Versión incrementada: $project $version\n";
    }
    $archivePath = "$destination/$project-$version.zip";
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
    if (file_put_contents($commitFile, $commit . PHP_EOL) === false) {
        fwrite(STDERR, "Error: no se pudo guardar $commitFile\n");
        exit(1);
    }
    echo "Creado: exchange/$project-$version.zip\n";
}
PHP
