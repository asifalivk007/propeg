$env:LIB_NAME="GepegRNAVisualization"
$env:ENTRY_FILE="src/index_gepeg.jsx"
$env:OUT_FILE_NAME="gepegrna-visualization"

Write-Host "Rebuilding gepegrna via Vite..." -ForegroundColor Cyan

$process = Start-Process -NoNewWindow -Wait -PassThru -FilePath "npx.cmd" -ArgumentList "vite build"

if ($process.ExitCode -eq 0) {
    Copy-Item -Force "dist\gepegrna-visualization.iife.js" "..\js\gepegrna-visualization.min.js"
    Write-Host "Build and Copy Successful!" -ForegroundColor Green
} else {
    Write-Host "Build Failed!" -ForegroundColor Red
}
