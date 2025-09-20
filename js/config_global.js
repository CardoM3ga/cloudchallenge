document.addEventListener("DOMContentLoaded", () => {
    // ---- Variáveis globais ----
    const root = document.documentElement; // pega o <html>
    let tamanhoTexto = 100; // tamanho padrão em %
    let darkMode = false;

    // ---- Carrega preferências ----
    if (localStorage.getItem('tamanhoTexto')) {
        tamanhoTexto = parseInt(localStorage.getItem('tamanhoTexto'));
        root.style.fontSize = tamanhoTexto + "%";
    }

    if (localStorage.getItem('darkMode') === 'true') {
        darkMode = true;
        document.body.classList.add('dark-mode');
        const checkbox = document.getElementById("modoEscuro");
        if (checkbox) checkbox.checked = true;
    }

    // ---- Aumentar e Diminuir Texto ----
    const btnAumentar = document.getElementById("aumentarTexto");
    const btnDiminuir = document.getElementById("diminuirTexto");

    if (btnAumentar) {
        btnAumentar.addEventListener("click", () => {
            if (tamanhoTexto < 200) { // limite máximo
                tamanhoTexto += 10;
                root.style.fontSize = tamanhoTexto + "%";
                localStorage.setItem('tamanhoTexto', tamanhoTexto);
            }
        });
    }

    if (btnDiminuir) {
        btnDiminuir.addEventListener("click", () => {
            if (tamanhoTexto > 50) { // limite mínimo
                tamanhoTexto -= 10;
                root.style.fontSize = tamanhoTexto + "%";
                localStorage.setItem('tamanhoTexto', tamanhoTexto);
            }
        });
    }

    // ---- Modo Escuro ----
    const modoEscuroCheckbox = document.getElementById("modoEscuro");
    if (modoEscuroCheckbox) {
        modoEscuroCheckbox.addEventListener("change", () => {
            darkMode = modoEscuroCheckbox.checked;
            document.body.classList.toggle("dark-mode", darkMode);
            localStorage.setItem('darkMode', darkMode);
        });
    }
});

function handleAddGoogle(response) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    const tokenInput = document.createElement('input');
    tokenInput.name = 'google_token';
    tokenInput.value = response.credential; // JWT retornado pelo Google
    form.appendChild(tokenInput);

    document.body.appendChild(form);
    form.submit();
}