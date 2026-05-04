@echo off
chcp 65001 >nul
echo ============================================================
echo  Chokko Melt — Gerador do Dicionario de Dados (PDF)
echo ============================================================
echo.

:: Tenta Edge em ambos os locais padrao do Windows
set EDGE="%ProgramFiles%\Microsoft\Edge\Application\msedge.exe"
if not exist %EDGE% set EDGE="%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe"
if not exist %EDGE% (
    echo ERRO: Microsoft Edge nao encontrado.
    echo Abra dicionario_chokko_melt.html no navegador e use Ctrl+P ^> Salvar como PDF.
    pause
    exit /b 1
)

set HTML=%~dp0dicionario_chokko_melt.html
set PDF=%~dp0Dicionario_de_Dados_Chokko_Melt.pdf

echo Gerando PDF a partir de:
echo   %HTML%
echo.

%EDGE% --headless --disable-gpu --no-pdf-header-footer --print-to-pdf="%PDF%" "file:///%HTML:\=/%"

timeout /t 2 /nobreak >nul

if exist "%PDF%" (
    echo OK: PDF gerado com sucesso!
    echo   %PDF%
    echo.
    echo Abrindo o PDF...
    start "" "%PDF%"
) else (
    echo FALHOU: o Edge nao conseguiu gerar o PDF automaticamente.
    echo Alternativa manual: abra dicionario_chokko_melt.html no navegador ^> Ctrl+P ^> Salvar como PDF.
)

echo.
pause
