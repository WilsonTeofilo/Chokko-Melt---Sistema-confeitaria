@echo off
set EDGE="C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
if not exist %EDGE% set EDGE="%ProgramFiles%\Microsoft\Edge\Application\msedge.exe"
set HTML=%~dp0dicionario_chokko_melt.html
set PDF=%~dp0Dicionario_de_Dados_Chokko_Melt.pdf
echo Gerando PDF...
%EDGE% --headless --disable-gpu --no-pdf-header-footer --print-to-pdf=%PDF% "file:///%HTML:\=/%"
if exist %PDF% (echo OK: %PDF%) else (echo Falhou - abra dicionario_chokko_melt.html no Edge e Ctrl+P -^> Salvar como PDF.)
pause
