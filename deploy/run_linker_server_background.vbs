' run_linker_server_background.vbs
' Launches the pegLIT linker server with no visible window.
' Lives in <project>/deploy/, peglit lives one level up under <project>/ext_tools/peglit/.
' Use this when you want the Flask server running in the background
' (e.g. via Task Scheduler at startup, or a shortcut in shell:startup).
' The Python process keeps running until you kill pythonw.exe via Task
' Manager or reboot.

Set fso = CreateObject("Scripting.FileSystemObject")
deployDir   = fso.GetParentFolderName(WScript.ScriptFullName)
projectRoot = fso.GetParentFolderName(deployDir)
peglitDir   = projectRoot & "\ext_tools\peglit"

Set shell = CreateObject("WScript.Shell")
shell.CurrentDirectory = peglitDir
' 0 = hidden window; False = don't wait for completion
shell.Run """" & peglitDir & "\venv\Scripts\pythonw.exe"" linker_server.py", 0, False
