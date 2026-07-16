$watcher = New-Object System.IO.FileSystemWatcher
$watcher.Path = "$PWD\src"
$watcher.Filter = "*.jsx"
$watcher.IncludeSubdirectories = $true
$watcher.EnableRaisingEvents = $true

Write-Host "Real-time gepegrna Vite Watcher & Auto-Copy Script Active." -ForegroundColor Cyan
Write-Host "Monitoring src/ directory for changes..." -ForegroundColor Yellow

while ($true) {
    # Wait for file change
    $result = $watcher.WaitForChanged([System.IO.WatcherChangeTypes]::Changed, 1000)
    if ($result.TimedOut -eq $false) {
        Write-Host "`nChange detected in $($result.Name)! Rebuilding bundle..." -ForegroundColor Green
        
        # Build specifically for gepegrna
        $env:LIB_NAME="GepegRNAVisualization"
        $env:ENTRY_FILE="src/index_gepeg.jsx"
        $env:OUT_FILE_NAME="gepegrna-visualization"
        
        $process = Start-Process -NoNewWindow -Wait -PassThru -FilePath "npx.cmd" -ArgumentList "vite build"
        
        if ($process.ExitCode -eq 0) {
            # Copy straight to the PHP serving folder
            Copy-Item -Force "dist\gepegrna-visualization.iife.js" "..\js\gepegrna-visualization.min.js"
            Write-Host "Successfully bundled and deployed to js/gepegrna-visualization.min.js!" -ForegroundColor Cyan
        } else {
            Write-Host "Build failed! Check the syntax in your React files." -ForegroundColor Red
        }
        
        # Cooldown to prevent double-building
        Start-Sleep -Seconds 1
    }
}
