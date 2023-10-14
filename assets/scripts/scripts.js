//Função que verifica se o nome do usuário existe no banco de dados
function verificaNomeUsuario() {
    const usuarioInput = document.getElementById("usuario");
    const usuarioValidationMessage = document.getElementById("usuarioValidationMessage");

    usuarioInput.addEventListener("input", function () {
        const usuario = usuarioInput.value;

        // Enviar uma solicitação AJAX para verificar_usuario.php
        fetch("verificar_usuario.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `usuario=${encodeURIComponent(usuario)}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    usuarioValidationMessage.textContent = "Nome de usuário disponível.";
                    usuarioValidationMessage.style.color = "green";
                } else {
                    usuarioValidationMessage.textContent = "Nome de usuário já está em uso.";
                    usuarioValidationMessage.style.color = "red";
                }
            })
            .catch(error => {
                console.error("Erro na solicitação AJAX: " + error);
            });
    });
}

//Função que verifica se o nome do Local existe no banco de dados
function verificaLocal() {
    const localInput = document.getElementById("local");
    const localValidationMessage = document.getElementById("localValidationMessage");

    localInput.addEventListener("input", function () {
        const local = localInput.value;

        // Enviar uma solicitação AJAX para verificar_local.php
        fetch("verificar_local.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `local=${encodeURIComponent(local)}`
        })
            .then(response => response.json())
            .then(local => {
                if (local.valid) {
                    localValidationMessage.textContent = "";
                    cadastraOcorrencia.disabled = false;
                } else {
                    localValidationMessage.textContent = "Local inválido.";
                    localValidationMessage.style.color = "red";
                    cadastraOcorrencia.disabled = true;
                }
            })
            .catch(error => {
                console.error("Erro na solicitação AJAX: " + error);
            });
    });
}

function filtraOcorrencia() {
    

}
