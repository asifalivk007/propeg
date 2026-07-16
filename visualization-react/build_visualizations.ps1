$env:LIB_NAME="PegRNAVisualization"
$env:ENTRY_FILE="src/index.jsx"
$env:OUT_FILE_NAME="pegrna-visualization"
npm run build
copy "dist\pegrna-visualization.iife.js" "..\js\pegrna-visualization.min.js"

$env:LIB_NAME="GpegRNAVisualization"
$env:ENTRY_FILE="src/index_gpeg.jsx"
$env:OUT_FILE_NAME="gpegrna-visualization"
npm run build
copy "dist\gpegrna-visualization.iife.js" "..\js\gpegrna-visualization.min.js"

$env:LIB_NAME="GepegRNAVisualization"
$env:ENTRY_FILE="src/index_gepeg.jsx"
$env:OUT_FILE_NAME="gepegrna-visualization"
npm run build
copy "dist\gepegrna-visualization.iife.js" "..\js\gepegrna-visualization.min.js"

Write-Host "All visualizations built and copied to ../js/"
